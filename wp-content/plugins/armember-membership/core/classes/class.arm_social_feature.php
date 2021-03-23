<?php

if (!class_exists('ARM_social_feature')) {

    class ARM_social_feature {

        var $social_settings;
        var $isSocialFeature;
      

        function __construct() {
            global $wpdb, $ARMember, $arm_slugs;
            $is_social_feature = get_option('arm_is_social_feature', 0);
            $this->isSocialFeature = ($is_social_feature == '1') ? true : false;
            
            
            
            add_action('wp_ajax_arm_update_social_settings', array(&$this, 'arm_update_social_settings_func'));
            add_action('wp_ajax_arm_update_social_network_from_form', array(&$this, 'arm_update_social_network_from_form_func'));

            



            add_action('wp_ajax_arm_install_free_plugin', array(&$this, 'arm_install_free_plugin'));


            add_action('wp_ajax_arm_install_plugin', array(&$this, 'arm_plugin_install'), 10);
            add_action('wp_ajax_arm_active_plugin', array(&$this, 'arm_activate_plugin'), 10);
            add_action('wp_ajax_arm_deactive_plugin', array(&$this, 'arm_deactivate_plugin'), 10);

            add_filter('plugins_api_args', array(&$this, 'arm_plugin_api_args'), 100000, 2);
            add_filter('plugins_api', array(&$this, 'arm_plugin_api'), 100000, 3);
            add_filter('plugins_api_result', array(&$this, 'arm_plugins_api_result'), 100000, 3);
            add_filter('upgrader_package_options', array(&$this, 'arm_upgrader_package_options'), 100000);
        }

        function arm_upgrader_package_options($options) {
            $options['is_multi'] = false;
            return $options;
        }

        function arm_deactivate_plugin() {
            global $ARMember;
            $plugin = $_POST['slug'];
            $silent = false;
            $network_wide = false;
            if (is_multisite())
                $network_current = get_site_option('active_sitewide_plugins', array());
            $current = get_option('active_plugins', array());
            $do_blog = $do_network = false;


            $plugin = plugin_basename(trim($plugin));


            $network_deactivating = false !== $network_wide && is_plugin_active_for_network($plugin);

            if (!$silent) {
                do_action('deactivate_plugin', $plugin, $network_deactivating);
            }

            if (false != $network_wide) {
                if (is_plugin_active_for_network($plugin)) {
                    $do_network = true;
                    unset($network_current[$plugin]);
                } elseif ($network_wide) {
                    
                }
            }

            if (true != $network_wide) {
                $key = array_search($plugin, $current);
                if (false !== $key) {
                    $do_blog = true;
                    unset($current[$key]);
                }
            }

            if (!$silent) {
                do_action('deactivate_' . $plugin, $network_deactivating);
                do_action('deactivated_plugin', $plugin, $network_deactivating);
            }


            if ($do_blog)
                update_option('active_plugins', $current);
            if ($do_network)
                update_site_option('active_sitewide_plugins', $network_current);

            $response = array(
                'type' => 'success'
            );
            echo json_encode($response);
            die();
        }

        function arm_activate_plugin() {
            global $ARMember;
            $plugin = $_POST['slug'];
            $plugin = plugin_basename(trim($plugin));
            $network_wide = false;
            $silent = false;
            $redirect = '';
            if (is_multisite() && ( $network_wide || is_network_only_plugin($plugin) )) {
                $network_wide = true;
                $current = get_site_option('active_sitewide_plugins', array());
                $_GET['networkwide'] = 1; // Back compat for plugins looking for this value.
            } else {
                $current = get_option('active_plugins', array());
            }

            $valid = validate_plugin($plugin);
            if (is_wp_error($valid))
                return $valid;

            if (( $network_wide && !isset($current[$plugin]) ) || (!$network_wide && !in_array($plugin, $current) )) {
                if (!empty($redirect))
                    wp_redirect(add_query_arg('_error_nonce', wp_create_nonce('plugin-activation-error_' . $plugin), $redirect)); // we'll override this later if the plugin can be included without fatal error
            
                wp_register_plugin_realpath(WP_PLUGIN_DIR . '/' . $plugin);
                $_wp_plugin_file = $plugin;
                include_once( WP_PLUGIN_DIR . '/' . $plugin );
                $plugin = $_wp_plugin_file; // Avoid stomping of the $plugin variable in a plugin.

                if (!$silent) {
                    do_action('activate_plugin', $plugin, $network_wide);
                    do_action('activate_' . $plugin, $network_wide);
                }

                if ($network_wide) {
                    $current = get_site_option('active_sitewide_plugins', array());
                    $current[$plugin] = time();
                    update_site_option('active_sitewide_plugins', $current);
                } else {
                    $current = get_option('active_plugins', array());
                    $current[] = $plugin;
                    sort($current);
                    update_option('active_plugins', $current);
                }

                if (!$silent) {
                    do_action('activated_plugin', $plugin, $network_wide);
                }
                $response = array();
                if (ob_get_length() > 0) {
                    $response = array(
                        'type' => 'error'
                    );
                    echo json_encode($response);
                    die();
                } else {
                    $response = array(
                        'type' => 'success'
                    );
                    echo json_encode($response);
                    die();
                }
            }
        }

        function arm_plugin_install() {
            global $ARMember;
            if (empty($_POST['slug'])) {
                wp_send_json_error(array(
                    'slug' => '',
                    'errorCode' => 'no_plugin_specified',
                    'errorMessage' => __('No plugin specified.', 'ARMember'),
                ));
            }

            $status = array(
                'install' => 'plugin',
                'slug' => sanitize_key(wp_unslash($_POST['slug'])),
            );

            if (!current_user_can('install_plugins')) {
                $status['errorMessage'] = __('Sorry, you are not allowed to install plugins on this site.', 'ARMember');
                wp_send_json_error($status);
            }
            if (file_exists(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php')) {
                include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
            }
            if (file_exists(ABSPATH . 'wp-admin/includes/plugin-install.php'))
                include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );

            $api = plugins_api('plugin_information', array(
                'slug' => sanitize_key(wp_unslash($_POST['slug'])),
                'fields' => array(
                    'sections' => false,
                ),
            ));

            if (is_wp_error($api)) {
                $status['errorMessage'] = $api->get_error_message();
                wp_send_json_error($status);
            }

            $status['pluginName'] = $api->name;

            $skin = new WP_Ajax_Upgrader_Skin();
            $upgrader = new Plugin_Upgrader($skin);

            $result = $upgrader->install($api->download_link);

            if (defined('WP_DEBUG') && WP_DEBUG) {
                $status['debug'] = $skin->get_upgrade_messages();
            }

            if (is_wp_error($result)) {
                $status['errorCode'] = $result->get_error_code();
                $status['errorMessage'] = $result->get_error_message();
                wp_send_json_error($status);
            } elseif (is_wp_error($skin->result)) {
                $status['errorCode'] = $skin->result->get_error_code();
                $status['errorMessage'] = $skin->result->get_error_message();
                wp_send_json_error($status);
            } elseif ($skin->get_errors()->get_error_code()) {
                $status['errorMessage'] = $skin->get_error_messages();
                wp_send_json_error($status);
            } elseif (is_null($result)) {
                global $wp_filesystem;

                $status['errorCode'] = 'unable_to_connect_to_filesystem';
                $status['errorMessage'] = __('Unable to connect to the filesystem. Please confirm your credentials.', 'ARMember');

                if ($wp_filesystem instanceof WP_Filesystem_Base && is_wp_error($wp_filesystem->errors) && $wp_filesystem->errors->get_error_code()) {
                    $status['errorMessage'] = esc_html($wp_filesystem->errors->get_error_message());
                }

                wp_send_json_error($status);
            }
            $install_status = $this->arm_install_plugin_install_status($api);


            if (current_user_can('activate_plugins') && is_plugin_inactive($install_status['file'])) {
                $status['activateUrl'] = add_query_arg(array(
                    '_wpnonce' => wp_create_nonce('activate-plugin_' . $install_status['file']),
                    'action' => 'activate',
                    'plugin' => $install_status['file'],
                        ), network_admin_url('plugins.php'));
            }

            if (is_multisite() && current_user_can('manage_network_plugins')) {
                $status['activateUrl'] = add_query_arg(array('networkwide' => 1), $status['activateUrl']);
            }
            $status['pluginFile'] = $install_status['file'];

            wp_send_json_success($status);
        }

        function arm_plugin_api_args($args, $action) {
            return $args;
        }

        function arm_plugin_api($res, $action, $args) {
            global $ARMember;
            $ARMember->arm_session_start();
            if (isset($_SESSION['arm_member_addon']) && !empty($_SESSION['arm_member_addon'])) {
                $armember_addons = $_SESSION['arm_member_addon'];
                $obj = array();
                foreach ($armember_addons as $slug => $armember_addon) {
                    if (isset($slug) && isset($args->slug)) {
                        if ($slug != $args->slug) {
                            continue;
                        } else {
                            $obj['name'] = $armember_addon['full_name'];
                            $obj['slug'] = $slug;
                            $obj['version'] = $armember_addon['plugin_version'];
                            $obj['download_link'] = $armember_addon['install_url'];
                            return (object) $obj;
                        }
                    } else {
                        continue;
                    }
                }
            }
            return $res;
        }

        function arm_plugins_api_result($res, $action, $args) {
            global $ARMember;
            return $res;
        }

        function arm_get_social_settings() {
            global $wpdb, $ARMember, $arm_members_class, $arm_member_forms;
            $social_settings = get_option('arm_social_settings');
            $social_settings = maybe_unserialize($social_settings);
            if (!empty($social_settings['options'])) {
                $options = $social_settings['options'];
                $options['facebook']['label'] = __('Facebook', 'ARMember');
                $options['twitter']['label'] = __('Twitter', 'ARMember');
                $options['linkedin']['label'] = __('LinkedIn', 'ARMember');
                $options['vk']['label'] = __('VK', 'ARMember');
                $social_settings['options'] = $options;
            }
            $social_settings = apply_filters('arm_get_social_settings', $social_settings);
            return $social_settings;
        }

        function arm_get_active_social_options() {
            global $wpdb, $ARMember, $arm_members_class, $arm_member_forms;
            $social_options = isset($this->social_settings['options']) ? $this->social_settings['options'] : array();
            $active_opts = array();
            if (!empty($social_options)) {
                foreach ($social_options as $key => $opt) {
                    if (isset($opt['status']) && $opt['status'] == '1') {
                        $active_opts[$key] = $opt;
                    }
                }
            }
            $active_opts = apply_filters('arm_get_active_social_options', $active_opts);
            return $active_opts;
        }

        function arm_update_social_settings_func() {
            global $wp, $wpdb, $ARMember, $arm_slugs, $arm_global_settings;
            $post_data = $_POST;
            if (isset($post_data['s_action']) && $post_data['s_action'] == 'arm_update_social_settings') {
                $social_settings = $post_data['arm_social_settings'];
                $social_settings = arm_array_map($social_settings);
                $new_social_settings_result = $social_settings;
                update_option('arm_social_settings', $new_social_settings_result);
                $response = array('type' => 'success', 'msg' => __('Social Setting(s) has been Saved Successfully.', 'ARMember'));
            } else {
                $response = array('type' => 'error', 'msg' => __('There is a error while updating settings, please try again.', 'ARMember'));
            }
            echo json_encode($response);
            die();
        }

        function arm_update_social_network_from_form_func() {
            $response = array('type' => 'error', 'msg' => __('There is a error while updating settings, please try again.', 'ARMember'), 'old_settings' => '');
            global $wp, $wpdb, $ARMember, $arm_slugs, $arm_global_settings;
            if (isset($_POST['action']) && $_POST['action'] == 'arm_update_social_network_from_form') {
                $socialOptions = isset($_POST['arm_social_settings']['options']) ? $_POST['arm_social_settings']['options'] : array();
                if (!empty($socialOptions)) {
                    foreach ($socialOptions as $snk => $snv) {
                        if (!empty($snv)) {
                            $icons = get_option('arm_social_icons_' . $snk, array());
                            $icons = maybe_unserialize($icons);
                            if (!empty($snv['custom_icon'])) {
                                foreach ($snv['custom_icon'] as $custom_icon) {
                                    $baseName = basename($custom_icon);
                                    if (isset($snv['icon']) && $snv['icon'] == 'custom') {
                                        $snv['icon'] = $baseName;
                                    }
                                    $icons[$baseName] = $custom_icon;
                                    update_option('arm_social_icons_' . $snk, $icons);
                                }
                            }
                        }
                    }
                }
                $response = array('type' => 'success', 'msg' => __('Social Setting(s) has been Saved Successfully.', 'ARMember'), 'old_settings' => maybe_serialize($socialOptions));
            }
            echo json_encode($response);
            die();
        }

       

        

        function arm_get_user_id_by_meta($meta_key = '', $meta_value = '') {
            global $wp, $wpdb, $ARMember, $arm_slugs, $arm_global_settings, $arm_member_forms;
            $user_id = 0;
            if (!empty($meta_key) && !empty($meta_value)) {
                $user_id = $wpdb->get_var("SELECT `user_id` FROM `$wpdb->usermeta` WHERE `meta_key`='$meta_key' AND `meta_value`='$meta_value'");
            }
            return $user_id;
        }

       

        function get_rand_alphanumeric($length) {
            if ($length > 0) {
                $rand_id = "";
                for ($i = 1; $i <= $length; $i++) {
                    mt_srand((double) microtime() * 1000000);
                    $num = mt_rand(1, 36);
                    $rand_id .= $this->assign_rand_value($num);
                }
            }
            return $rand_id;
        }

        function assign_rand_value($num) {
            switch ($num) {
                case "1" : $rand_value = "a";
                    break;
                case "2" : $rand_value = "b";
                    break;
                case "3" : $rand_value = "c";
                    break;
                case "4" : $rand_value = "d";
                    break;
                case "5" : $rand_value = "e";
                    break;
                case "6" : $rand_value = "f";
                    break;
                case "7" : $rand_value = "g";
                    break;
                case "8" : $rand_value = "h";
                    break;
                case "9" : $rand_value = "i";
                    break;
                case "10" : $rand_value = "j";
                    break;
                case "11" : $rand_value = "k";
                    break;
                case "12" : $rand_value = "l";
                    break;
                case "13" : $rand_value = "m";
                    break;
                case "14" : $rand_value = "n";
                    break;
                case "15" : $rand_value = "o";
                    break;
                case "16" : $rand_value = "p";
                    break;
                case "17" : $rand_value = "q";
                    break;
                case "18" : $rand_value = "r";
                    break;
                case "19" : $rand_value = "s";
                    break;
                case "20" : $rand_value = "t";
                    break;
                case "21" : $rand_value = "u";
                    break;
                case "22" : $rand_value = "v";
                    break;
                case "23" : $rand_value = "w";
                    break;
                case "24" : $rand_value = "x";
                    break;
                case "25" : $rand_value = "y";
                    break;
                case "26" : $rand_value = "z";
                    break;
                case "27" : $rand_value = "0";
                    break;
                case "28" : $rand_value = "1";
                    break;
                case "29" : $rand_value = "2";
                    break;
                case "30" : $rand_value = "3";
                    break;
                case "31" : $rand_value = "4";
                    break;
                case "32" : $rand_value = "5";
                    break;
                case "33" : $rand_value = "6";
                    break;
                case "34" : $rand_value = "7";
                    break;
                case "35" : $rand_value = "8";
                    break;
                case "36" : $rand_value = "9";
                    break;
            }
            return $rand_value;
        }

        function CheckpluginStatus($mypluginsarray, $pluginname, $attr, $purchase_addon, $plugin_type, $install_url, $compatible_version, $armember_version) {
            foreach ($mypluginsarray as $pluginarr) {
                $response = "";
                if ($pluginname == $pluginarr[$attr]) {
                    if ($pluginarr['is_active'] == 1) {
                        $response = "ACTIVE";
                        $actionurl = $pluginarr["deactivation_url"];
                        break;
                    } else {
                        $response = "NOT ACTIVE";
                        $actionurl = $pluginarr["activation_url"];
                        break;
                    }
                } else {
                    if ($plugin_type == "free") {
                        $response = "NOT INSTALLED FREE";
                        $actionurl = $install_url;
                    } else if ($plugin_type == "paid") {
                        $response = "NOT INSTALLED PAID";
                        $actionurl = $install_url;
                    }
                }
            }
            $myicon = "";
            $divclassname = "";
            $arm_plugin_name = explode('/', $pluginname);
            if ($response == "NOT INSTALLED FREE") {
                $myicon = '<div class="arm_feature_button_activate_container"><a id="arm_free_addon" href="javascript:void(0);"  class="arm_feature_activate_btn" data-name=' . $purchase_addon . ' data-plugin=' . $arm_plugin_name[0] . '  data-href="javascript:void(0);" data-version="'.$compatible_version.'" data-arm_version="'.$armember_version.'" data-type ="free_addon">Install</a></div>';
            } else if ($response == "NOT INSTALLED PAID") {
                $myicon = '<div class="arm_feature_button_activate_container"><a class="arm_feature_activate_btn" href=javascript:void(0); data-version="'.$compatible_version.'" data-arm_version="'.$armember_version.'" data-type ="paid_addon" data-href="'.$actionurl.'"><img src="https://www.arformsplugin.com/arf/addons/images/buynow-icon.png"/> Get It</a></div>';
            } else if ($response == "ACTIVE") {
                $myicon = '<div class="arm_feature_button_deactivate_container"><a id="arm_feature_deactivate_btn" class="arm_feature_activate_btn arm_deactive_addon" data-file="' . $pluginname . '" href="javascript:void(0);"  data-version="'.$compatible_version.'" data-arm_version="'.$armember_version.'" data-type ="deactivate_addon">Deactivate</a></div>';
            } else if ($response == "NOT ACTIVE") {
                $myicon = '<div class="arm_feature_button_activate_container"><a class="arm_feature_activate_btn arm_active_addon" data-file="' . $pluginname . '" href="javascript:void(0);"  data-version="'.$compatible_version.'" data-arm_version="'.$armember_version.'" data-type ="activate_addon">Activate</a></div>';
            }
            return $myicon;
        }

        function addons_page() {
            $plugins = get_plugins();
            $installed_plugins = array();
            foreach ($plugins as $key => $plugin) {
                $is_active = is_plugin_active($key);
                $installed_plugin = array("plugin" => $key, "name" => $plugin["Name"], "is_active" => $is_active);
                $installed_plugin["activation_url"] = $is_active ? "" : wp_nonce_url("plugins.php?action=activate&plugin={$key}", "activate-plugin_{$key}");
                $installed_plugin["deactivation_url"] = !$is_active ? "" : wp_nonce_url("plugins.php?action=deactivate&plugin={$key}", "deactivate-plugin_{$key}");

                $installed_plugins[] = $installed_plugin;
            }

            global $arm_version;
            $bloginformation = array();
            $str = $this->get_rand_alphanumeric(10);

            if (is_multisite())
                $multisiteenv = "Multi Site";
            else
                $multisiteenv = "Single Site";

            $addon_listing = 1;

            $bloginformation[] = get_bloginfo('name');
            $bloginformation[] = get_bloginfo('description');
            $bloginformation[] = ARMLITE_HOME_URL;
            $bloginformation[] = get_bloginfo('admin_email');
            $bloginformation[] = get_bloginfo('version');
            $bloginformation[] = get_bloginfo('language');
            $bloginformation[] = $arm_version;
            $bloginformation[] = $_SERVER['REMOTE_ADDR'];
            $bloginformation[] = $str;
            $bloginformation[] = $multisiteenv;
            $bloginformation[] = $addon_listing;

            $valstring = implode("||", $bloginformation);
            $encodedval = base64_encode($valstring);

            $urltopost = 'https://www.armemberplugin.com/armember_addons/addon_list.php';

            $raw_response = wp_remote_post($urltopost, array(
                'method' => 'POST',
                'timeout' => 45,
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking' => true,
                'headers' => array(),
                'body' => array('plugins' => urlencode(serialize($installed_plugins)), 'wpversion' => $encodedval),
                'cookies' => array()
                    )
            );


            if (is_wp_error($raw_response) || $raw_response['response']['code'] != 200) {
                return "0|^^|<div class='error_message' style='margin-top:100px; padding:20px;'>" . __("Add-On listing is currently unavailable. Please try again later.", 'ARMember') . "</div>";
            } else {
                return "1|^^|" . $raw_response['body'];
            }
        }

        function arm_install_plugin_install_status($api, $loop = false) {
            // This function is called recursively, $loop prevents further loops.
            if (is_array($api))
                $api = (object) $api;

            // Default to a "new" plugin
            $status = 'install';
            $url = false;
            $update_file = false;

            /*
             * Check to see if this plugin is known to be installed,
             * and has an update awaiting it.
             */
            $update_plugins = get_site_transient('update_plugins');
            if (isset($update_plugins->response)) {
                foreach ((array) $update_plugins->response as $file => $plugin) {
                    if ($plugin->slug === $api->slug) {
                        $status = 'update_available';
                        $update_file = $file;
                        $version = $plugin->new_version;
                        if (current_user_can('update_plugins'))
                            $url = wp_nonce_url(self_admin_url('update.php?action=upgrade-plugin&plugin=' . $update_file), 'upgrade-plugin_' . $update_file);
                        break;
                    }
                }
            }

            if ('install' == $status) {
                if (is_dir(WP_PLUGIN_DIR . '/' . $api->slug)) {
                    $installed_plugin = get_plugins('/' . $api->slug);
                    if (empty($installed_plugin)) {
                        if (current_user_can('install_plugins'))
                            $url = wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=' . $api->slug), 'install-plugin_' . $api->slug);
                    } else {
                        $key = array_keys($installed_plugin);
                        $key = reset($key); //Use the first plugin regardless of the name, Could have issues for multiple-plugins in one directory if they share different version numbers
                        $update_file = $api->slug . '/' . $key;
                        if (version_compare($api->version, $installed_plugin[$key]['Version'], '=')) {
                            $status = 'latest_installed';
                        } elseif (version_compare($api->version, $installed_plugin[$key]['Version'], '<')) {
                            $status = 'newer_installed';
                            $version = $installed_plugin[$key]['Version'];
                        } else {
                            //If the above update check failed, Then that probably means that the update checker has out-of-date information, force a refresh
                            if (!$loop) {
                                delete_site_transient('update_plugins');
                                wp_update_plugins();
                                return arm_install_plugin_install_status($api, true);
                            }
                        }
                    }
                } else {
                    // "install" & no directory with that slug
                    if (current_user_can('install_plugins'))
                        $url = wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=' . $api->slug), 'install-plugin_' . $api->slug);
                }
            }
            if (isset($_GET['from']))
                $url .= '&amp;from=' . urlencode(wp_unslash($_GET['from']));

            $file = $update_file;
            return compact('status', 'url', 'version', 'file');
        }

    }

}
global $arm_social_feature;
$arm_social_feature = new ARM_social_feature();

/*
  wp_unslash function to remove slashes. default in wordpress 4.6
 */

if (!function_exists('wp_unslash')) {

    function wp_unslash($value) {
        return stripslashes_deep($value);
    }

}

if (!class_exists('Automatic_Upgrader_Skin')) {
    if (version_compare($GLOBALS['wp_version'], '4.6', '<'))
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
    else
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader-skin.php';

    if (version_compare($GLOBALS['wp_version'], '3.8', '<')) {

        class Automatic_Upgrader_Skin extends WP_Upgrader_Skin {

            protected $messages = array();

            /**
             * Determines whether the upgrader needs FTP/SSH details in order to connect
             * to the filesystem.
             *
             * @since 3.7.0
             * @since 4.6.0 The `$context` parameter default changed from `false` to an empty string.
             *
             * @see request_filesystem_credentials()
             *
             * @param bool   $error                        Optional. Whether the current request has failed to connect.
             *                                             Default false.
             * @param string $context                      Optional. Full path to the directory that is tested
             *                                             for being writable. Default empty.
             * @param bool   $allow_relaxed_file_ownership Optional. Whether to allow Group/World writable. Default false.
             * @return bool True on success, false on failure.
             */
            public function request_filesystem_credentials($error = false, $context = '', $allow_relaxed_file_ownership = false) {
                if ($context) {
                    $this->options['context'] = $context;
                }
                // TODO: fix up request_filesystem_credentials(), or split it, to allow us to request a no-output version
                // This will output a credentials form in event of failure, We don't want that, so just hide with a buffer
                $result = parent::request_filesystem_credentials($error, $context, $allow_relaxed_file_ownership);
                ob_end_clean();
                return $result;
            }

            /**
             * @access public
             *
             * @return array
             */
            public function get_upgrade_messages() {
                return $this->messages;
            }

            /**
             * @param string|array|WP_Error $data
             */
            public function feedback($data) {
                if (is_wp_error($data)) {
                    $string = $data->get_error_message();
                } elseif (is_array($data)) {
                    return;
                } else {
                    $string = $data;
                }
                if (!empty($this->upgrader->strings[$string]))
                    $string = $this->upgrader->strings[$string];

                if (strpos($string, '%') !== false) {
                    $args = func_get_args();
                    $args = array_splice($args, 1);
                    if (!empty($args))
                        $string = vsprintf($string, $args);
                }

                $string = trim($string);

                // Only allow basic HTML in the messages, as it'll be used in emails/logs rather than direct browser output.
                $string = wp_kses($string, array(
                    'a' => array(
                        'href' => true
                    ),
                    'br' => true,
                    'em' => true,
                    'strong' => true,
                        ));

                if (empty($string))
                    return;

                $this->messages[] = $string;
            }

            /**
             * @access public
             */
            public function header() {
            }

            /**
             * @access public
             */
            public function footer() {
                $output = ob_get_clean();
                if (!empty($output))
                    $this->feedback($output);
            }

        }

    }
}

if (!class_exists('WP_Ajax_Upgrader_Skin')) {
    if (version_compare($GLOBALS['wp_version'], '4.6', '<'))
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
    else
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader-skin.php';
    if (version_compare($GLOBALS['wp_version'], '4.6', '<')) {

        class WP_Ajax_Upgrader_Skin extends Automatic_Upgrader_Skin {

            /**
             * Holds the WP_Error object.
             *
             * @since 4.6.0
             * @access protected
             * @var null|WP_Error
             */
            protected $errors = null;

            /**
             * Constructor.
             *
             * @since 4.6.0
             * @access public
             *
             * @param array $args Options for the upgrader, see WP_Upgrader_Skin::__construct().
             */
            public function __construct($args = array()) {
                parent::__construct($args);

                $this->errors = new WP_Error();
            }

            /**
             * Retrieves the list of errors.
             *
             * @since 4.6.0
             * @access public
             *
             * @return WP_Error Errors during an upgrade.
             */
            public function get_errors() {
                return $this->errors;
            }

            /**
             * Retrieves a string for error messages.
             *
             * @since 4.6.0
             * @access public
             *
             * @return string Error messages during an upgrade.
             */
            public function get_error_messages() {
                $messages = array();

                foreach ($this->errors->get_error_codes() as $error_code) {
                    if ($this->errors->get_error_data($error_code) && is_string($this->errors->get_error_data($error_code))) {
                        $messages[] = $this->errors->get_error_message($error_code) . ' ' . esc_html(strip_tags($this->errors->get_error_data($error_code)));
                    } else {
                        $messages[] = $this->errors->get_error_message($error_code);
                    }
                }

                return implode(', ', $messages);
            }

            /**
             * Stores a log entry for an error.
             *
             * @since 4.6.0
             * @access public
             *
             * @param string|WP_Error $errors Errors.
             */
            public function error($errors) {
                if (is_string($errors)) {
                    $string = $errors;
                    if (!empty($this->upgrader->strings[$string])) {
                        $string = $this->upgrader->strings[$string];
                    }

                    if (false !== strpos($string, '%')) {
                        $args = func_get_args();
                        $args = array_splice($args, 1);
                        if (!empty($args)) {
                            $string = vsprintf($string, $args);
                        }
                    }

                    // Count existing errors to generate an unique error code.
                    $errors_count = count($errors->get_error_codes());
                    $errors_count_plus_one = $errors_count + 1;
                    $this->errors->add('unknown_upgrade_error_'.$errors_count_plus_one, $string);
                } elseif (is_wp_error($errors)) {
                    foreach ($errors->get_error_codes() as $error_code) {
                        $this->errors->add($error_code, $errors->get_error_message($error_code), $errors->get_error_data($error_code));
                    }
                }

                $args = func_get_args();
                call_user_func_array(array($this, 'parent::error'), $args);
            }

            /**
             * Stores a log entry.
             *
             * @since 4.6.0
             * @access public
             *
             * @param string|array|WP_Error $data Log entry data.
             */
            public function feedback($data) {
                if (is_wp_error($data)) {
                    foreach ($data->get_error_codes() as $error_code) {
                        $this->errors->add($error_code, $data->get_error_message($error_code), $data->get_error_data($error_code));
                    }
                }

                $args = func_get_args();
                call_user_func_array(array($this, 'parent::feedback'), $args);
            }

        }

    }
}

if (!function_exists('wp_register_plugin_realpath')) {

    function wp_register_plugin_realpath($file) {
        global $wp_plugin_paths;
        // Normalize, but store as static to avoid recalculation of a constant value
        static $wp_plugin_path = null, $wpmu_plugin_path = null;
        if (!isset($wp_plugin_path)) {
            $wp_plugin_path = wp_normalize_path(WP_PLUGIN_DIR);
            $wpmu_plugin_path = wp_normalize_path(WPMU_PLUGIN_DIR);
        }

        $plugin_path = wp_normalize_path(dirname($file));
        $plugin_realpath = wp_normalize_path(dirname(realpath($file)));

        if ($plugin_path === $wp_plugin_path || $plugin_path === $wpmu_plugin_path) {
            return false;
        }

        if ($plugin_path !== $plugin_realpath) {
            $wp_plugin_paths[$plugin_path] = $plugin_realpath;
        }
        return true;
    }

}

if (!function_exists('wp_normalize_path')) {

    function wp_normalize_path($path) {
        $path = str_replace('\\', '/', $path);
        $path = preg_replace('|(?<=.)/+|', '/', $path);
        if (':' === substr($path, 1, 1)) {
            $path = ucfirst($path);
        }
        return $path;
    }

}