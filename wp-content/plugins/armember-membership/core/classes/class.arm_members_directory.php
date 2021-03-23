<?php
if (!class_exists('ARM_members_directory')) {

    class ARM_members_directory {

        function __construct() {
            global $wpdb, $ARMember, $arm_slugs;
            add_action('wp_ajax_arm_set_default_template', array(&$this, 'arm_set_default_template_func'));
           
            add_action('wp_ajax_arm_update_template_options', array(&$this, 'arm_update_template_options_func'));
            
            add_action('wp_ajax_arm_template_preview', array(&$this, 'arm_template_preview_func'));
            add_action('wp_ajax_arm_template_edit_popup', array(&$this, 'arm_template_edit_popup_func'));
            add_action('wp_ajax_arm_save_profile_template', array(&$this, 'arm_save_profile_template_func'));
            add_action('wp_ajax_arm_ajax_generate_profile_styles', array(&$this, 'arm_template_style'));
            add_action('wp_ajax_arm_load_profile_template_preview', array(&$this, 'arm_load_profile_template_preview_func'));

            /* update user meta while uploading cover and avatar from profile page */
            add_action('wp_ajax_arm_update_user_meta', array(&$this, 'arm_update_user_meta'));
            add_action('wp_ajax_nopriv_arm_update_user_meta', array(&$this, 'arm_update_user_meta'));

            add_action('wp_ajax_arm_change_profile_template',array(&$this,'arm_change_profile_template'));

            add_filter( 'tiny_mce_before_init', array(&$this,'arm_tinymce_plugin') );
        }

        function arm_tinymce_plugin($init){
            $pattern = '/(arm_before_profile_fields_content|arm_after_profile_fields_content)/';
            if( isset($init['body_class']) && preg_match($pattern,$init['body_class']) ){
                $init['setup'] = 'function(ed) { ed.onKeyUp.add( function(ed) { if( ed.id == "arm_before_profile_fields_content" ){jQuery(".arm_profile_field_before_content_wrapper").html(ed.getContent());}else{jQuery(".arm_profile_field_after_content_wrapper").html(ed.getContent());} } ); }';
			}
            return $init;
        }

        function arm_save_profile_template_func() {
            global $wpdb,$ARMember, $arm_capabilities_global;
            
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_member_templates'], '1');

            $arm_title = "";
            $arm_slug = 'profiletemplate3';
            $arm_type = "profile";
            $arm_subscription_plans = isset($_POST['template_options']['plans']) ? implode(',',$_POST['template_options']['plans']) : '';
            $arm_before_profile_field = isset($_POST['arm_before_profile_fields_content']) ? $_POST['arm_before_profile_fields_content'] : '';
            $display_admin_users = isset($_POST['show_admin_users']) ? intval($_POST['show_admin_users']) : 0;
            $arm_after_profile_field = isset($_POST['arm_after_profile_fields_content']) ? $_POST['arm_after_profile_fields_content'] : '';
            $arm_ref_template = isset($_POST['arm_profile_template_id']) ? $_POST['arm_profile_template_id'] : 1;
            $options = $_POST['template_options'];
            $options['hide_empty_profile_fields'] = isset($options['hide_empty_profile_fields']) ? intval($options['hide_empty_profile_fields']) : 0;
            unset($options['plans']);
            if( isset($_POST['profile_fields']) ){
                foreach($_POST['profile_fields'] as $key => $profile_field ){
                    $options['profile_fields'][$key] = $key;
                    $options['label'][$key] = $profile_field;
                }
            }
            
                $arm_template_html = '<div class="arm_profile_detail_wrapper">
                        <div class="arm_profile_picture_block armCoverPhoto" style="{ARM_Profile_Cover_Image}">
                            <div class="arm_profile_picture_block_inner">
                                <div class="arm_profile_header_info">
                                    <span class="arm_profile_name_link">{ARM_Profile_User_Name}</span>
                                    
                                        {ARM_Profile_Badges}
                                    
                                    <div class="armclear"></div>
                                        <span class="arm_user_last_active_text">{ARM_Profile_Join_Date}</span>
                                    </div>
                                    <div class="social_profile_fields">
                                        {ARM_Profile_Social_Icons}
                                    </div>
                                    <div class="armclear"></div>
                                </div>
                                <div class="arm_user_avatar">
                                    {ARM_Profile_Avatar_Image}
                                </div>
                                {ARM_Cover_Upload_Button}
                            </div>
                            <div class="arm_profile_defail_container arm_profile_tabs_container">
                                {ARM_PROFILE_FIELDS_BEFORE_CONTENT}
                                <div class="arm_profile_field_before_content_wrapper">'.$arm_before_profile_field.'</div>
                                <div class="arm_profile_tab_detail" data-tab="general">
                                    <div class="arm_general_info_container">
                                        <table class="arm_profile_detail_tbl">
                                            <tbody>';
                                            foreach($options['profile_fields'] as $k => $value ){
                                                $arm_template_html .= "<tr>";
                                                    $arm_template_html .= "<td>".$options['label'][$k]."</td>";
                                                    $arm_template_html .= "<td>[arm_usermeta meta='".$k."']</td>";
                                                $arm_template_html .= "</tr>";
                                            }
                                      $arm_template_html .= '</tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="arm_profile_field_after_content_wrapper">'.$arm_after_profile_field.'</div>
                                {ARM_PROFILE_FIELDS_AFTER_CONTENT}
                            </div>
                    </div><div class="armclear"></div>';
            
            $options = arm_array_map($options);
            $options = maybe_serialize($options);
            $arguments = array(
                'arm_title' => $arm_title,
                'arm_slug' => $arm_slug,
                'arm_type' => $arm_type,
                'arm_subscription_plan' => $arm_subscription_plans,
                'arm_template_html' => $arm_template_html,
                'arm_ref_template' => $arm_ref_template,
                'arm_options' => $options,
                'arm_html_before_fields' => $arm_before_profile_field,
                'arm_html_after_fields' => $arm_after_profile_field,
                'arm_enable_admin_profile' => $display_admin_users,
                'arm_created_date' => date('Y-m-d H:i:s')
            );
            $default_data = $arguments;
            $default_data['arm_options'] = maybe_unserialize($options);
            if( $_POST['arf_profile_action'] == 'add_profile' ){
                if( $wpdb->insert($ARMember->tbl_arm_member_templates,$arguments) ){
                    echo json_encode(array('type' => 'success','id' => $wpdb->insert_id, 'message' => __('Template Saved Successfully','ARMember'), 'default_data' => $default_data));
                } else {
                    echo json_encode(array('type' => 'error', 'message' => __('There is an error while saving template, please try again','ARMember')));
                }
            } else if( $_POST['arf_profile_action'] == 'edit_profile' ) {
                $id = isset($_POST['template_id'] ) ? intval($_POST['template_id']) : 0;
                if( $id > 0 && $wpdb->update($ARMember->tbl_arm_member_templates,$arguments,array('arm_id' => $id) ) ){
                    echo json_encode(array('type' => 'success','id' => $id, 'message' => __('Template Updated Successfully','ARMember'), 'default_data' => $default_data));
                } else {
                    echo json_encode(array('type' => 'error', 'message' => __('There is an error while updating template, please try again','ARMember')));    
                }
            } else {
                echo json_encode(array('type' => 'error', 'message' => __('There is an error while saving template, please try again','ARMember')));
            }
            die;
        }

        function arm_load_profile_template_preview_func() {
            global $wpdb, $ARMember, $arm_slugs;
            $status = 'error';
            $message = __('There is a error while updating settings, please try again.', 'ARMember');
            $response = array('type' => 'error', 'message' => __('There is a error while adding profile template, please try again.', 'ARMember'));
            if (isset($_POST['action']) && $_POST['action'] == 'arm_load_profile_template_preview') {
                $temp_id = $_POST['temp_id'];
                
               $profile_template = $_POST['profile_slug'];
                $tempOptions = shortcode_atts(array(
                    'plans' => array(),
                    'show_admin_users' => 0,
                    'show_badges' => 0,
                    'show_joining' => 0,
                    'redirect_to_author' => 0,
                    'redirect_to_buddypress_profile' => 0,
                    'hide_empty_profile_fields' => 0,
                    'arm_social_fields' => array(),

            ), $_POST['template_options']); 
                
                
                $plans = !empty($tempOptions['plans']) ? $tempOptions['plans'] : 0;
                $show_admin_users = !empty($tempOptions['show_admin_users'])?$tempOptions['show_admin_users'] : 0;
              
                $show_joining = !empty($tempOptions['show_joining'])?$tempOptions['show_joining'] : 0;
                $redirect_to_author = !empty($tempOptions['redirect_to_author'])?$tempOptions['redirect_to_author'] : 0;
               
                $hide_empty_profile_fields = !empty($tempOptions['hide_empty_profile_fields'])?$tempOptions['hide_empty_profile_fields'] : 0;
                $arm_social_fields = !empty($tempOptions['arm_social_fields'])?$tempOptions['arm_social_fields'] : 0;
                
                
                $arm_url_array = array(
                    'p'=>$plans,
                    'a'=>$show_admin_users,
                   
                    'j'=>$show_joining,
                    'ra'=>$redirect_to_author,
                  
                    'h'=>$hide_empty_profile_fields,
                    's'=>$arm_social_fields,
                    
                    
                );
                
                
            
                
                $edit_profile_link = admin_url('admin.php?page=' . $arm_slugs->profiles_directories . '&action=add_profile&' . http_build_query($arm_url_array)); 
                $response = array('type' => 'success', 'url' => $edit_profile_link);
            }
           
            echo json_encode($response);
            die();
        }
        

        function arm_update_user_meta() {
            $userID = get_current_user_id();
            $posted_url = $_POST['image_url'];
            $type = $_POST['type'];
            if ($type == 'cover') {
                update_user_meta($userID, 'profile_cover', $posted_url);
            } else if ('avatar') {
                update_user_meta($userID, 'avatar', $posted_url);
            }
        }

        function arm_get_all_member_templates() {
            global $wp, $wpdb, $current_user, $ARMember, $arm_global_settings;
            $result_temps = array();
            $temps = $wpdb->get_results("SELECT * FROM `" . $ARMember->tbl_arm_member_templates . "`");
            if (!empty($temps)) {
                foreach ($temps as $t) {
                    $result_temps[$t->arm_type][$t->arm_id] = (array) $t;
                }
            }
            return $result_temps;
        }

        function arm_get_default_template_by_type($type = 'directory') {
            global $wp, $wpdb, $current_user, $ARMember, $arm_global_settings;
            $result_temp = $wpdb->get_row("SELECT * FROM `" . $ARMember->tbl_arm_member_templates . "` WHERE `arm_type`='{$type}' AND `arm_default`='1'");
            return $result_temp;
        }

        function arm_get_template_by_id($tempID = '0') {
            global $wp, $wpdb, $current_user, $ARMember, $arm_global_settings;
            $tempData = array();
            if (!empty($tempID) && $tempID != 0) {
	    
                /* Query Monitor Change */
                if( isset($GLOBALS['arm_template_data']) && isset($GLOBALS['arm_template_data'][$tempID]) ){
                    $tempData = $GLOBALS['arm_template_data'][$tempID];
                } else {
                    $tempData = $wpdb->get_row("SELECT * FROM `" . $ARMember->tbl_arm_member_templates . "` WHERE `arm_id`='{$tempID}'", ARRAY_A);
                    $GLOBALS['arm_template_data'] = array();
                    $GLOBALS['arm_template_data'][$tempID] = $tempData;
                }
                if (!empty($tempData)) {
                    $tempData['options'] = maybe_unserialize($tempData['arm_options']);
                    $tempData['arm_options'] = maybe_unserialize($tempData['arm_options']);
                }
            }
            return $tempData;
        }

        function arm_set_default_template_func() {
            global $wpdb, $ARMember;
            $response = array('type' => 'error', 'message' => __('There is a error while updating settings, please try again.', 'ARMember'));
            if (isset($_POST['action']) && $_POST['action'] == 'arm_set_default_template') {
                $temp_id = $_POST['temp_id'];
                $temp_type = $_POST['temp_type'];
                $update_old_data = $wpdb->update($ARMember->tbl_arm_member_templates, array('arm_default' => 0), array('arm_type' => $temp_type));
                $update_new = $wpdb->update($ARMember->tbl_arm_member_templates, array('arm_default' => 1), array('arm_id' => $temp_id, 'arm_type' => $temp_type));
                if ($update_new) {
                    $response = array('type' => 'success', 'message' => __('Settings has been saved successfully.', 'ARMember'));
                }
            }
            echo json_encode($response);
            die();
        }

     
	
		function arm_update_template_options_func()
		{
            global $wpdb, $ARMember, $arm_slugs, $arm_capabilities_global;

            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_member_templates'], '1');

            $status = 'error';
            $message = __('There is a error while updating settings, please try again.', 'ARMember');
            $response = array('type' => 'error', 'message' => __('There is a error while updating settings, please try again.', 'ARMember'));
            if (isset($_POST['action']) && $_POST['action'] == 'arm_update_template_options') {
                $temp_id = intval($_POST['temp_id']);
                $temp_options = maybe_serialize($_POST['template_options']);
                $templateData = array('arm_options' => $temp_options);
                if (isset($_POST['profile_slug']) && !empty($_POST['profile_slug'])) {
                    $templateData['arm_slug'] = $_POST['profile_slug'];
                }
                
                $update_temp = $wpdb->update($ARMember->tbl_arm_member_templates, $templateData, array('arm_id' => $temp_id));
                if ($update_temp !== false) {
                    $status = 'success';
                    $message = __('Template options has been saved successfully.', 'ARMember');
                    $response = array('type' => 'success', 'message' => __('Template options has been saved successfully.', 'ARMember'));
                }
            }
            
            $redirect_link = admin_url('admin.php?page=' . $arm_slugs->profiles_directories);
            $response['redirect_to'] = $redirect_link;
            if ($status == 'success') {
                $ARMember->arm_set_message($status, $message);
            }
            echo json_encode($response);
            die();
        }

        function arm_prepare_users_detail_for_template($_users = array(), $args = array()) {
       
            global $wpdb, $ARMember, $arm_global_settings, $arm_members_class, $arm_subscription_plans, $arm_social_feature, $arm_load_tipso;
            $users = array();
            $allRoles = $arm_global_settings->arm_get_all_roles();
            $all_alert_message = $ARMember->arm_front_alert_messages();
            $common_messages = $arm_global_settings->arm_get_all_common_message_settings();
            $uploadCoverPhotoTxt = (!empty($common_messages['profile_directory_upload_cover_photo'])) ? $common_messages['profile_directory_upload_cover_photo'] : __('Upload Cover Photo', 'ARMember');
            $removeCoverPhotoTxt = (!empty($common_messages['profile_directory_remove_cover_photo'])) ? $common_messages['profile_directory_remove_cover_photo'] : __('Remove Cover Photo', 'ARMember');
            $upload_profile_text = (!empty($common_messages['profile_template_upload_profile_photo'])) ? $common_messages['profile_template_upload_profile_photo'] : __('Upload Profile Photo', 'ARMember');
            $removeProfilePhotoTxt = (!empty($common_messages['profile_template_remove_profile_photo'])) ? $common_messages['profile_template_remove_profile_photo'] : __('Remove Profile Photo', 'ARMember');
            $removecoverPhotoAlert = (!empty($all_alert_message['coverRemoveConfirm'])) ? $all_alert_message['coverRemoveConfirm'] : __('Are you sure you want to remove cover photo?', 'ARMember');
            $removeprofilePhotoAlert = (!empty($all_alert_message['profileRemoveConfirm'])) ? $all_alert_message['profileRemoveConfirm'] : __('Are you sure you want to remove profile photo?', 'ARMember');
            if (!empty($_users)) {
                $defaultKeys = array(
                    'ID' => '', 'user_login' => '', 'user_pass' => '', 'user_nicename' => '', 'user_email' => '', 'user_url' => '',
                    'user_registered' => '', 'user_status' => 0, 'user_activation_key' => '', 'display_name' => '', 'roles' => array(), 'role' => '',
                    'nickname' => '', 'first_name' => '', 'last_name' => '', 'full_name' => '', 'biography' => '', 'description' => '', 'gender' => '',
                    'profile_cover' => '', 'cover_upload_btn' => '', 'avatar' => '', 'profile_picture' => '',
                    'arm_last_login_date' => '', 'arm_last_login_ip' => '', 'last_activity' => '',
                    'arm_user_plan_ids' => '', 'subscription' => '', 'membership' => '', 'subscription_detail' => '', 'transactions' => '',
                    'user_link' => '', 'profile_link' => '', 'home_url' => '', 'website' => '', 'arm_facebook_id' => '', 'arm_linkedin_id' => '',
                    'arm_twitter_id' => '', 'arm_pinterest_id' => '', 'arm_instagram_id' => '', 'arm_vk_id' => '',
                    'rich_editing' => '', 'comment_shortcuts' => '', 'use_ssl' => '', 'social_profile_fields' => ''
                );

                
                
                  $show_admin_users = (isset($args['show_admin_users']) && $args['show_admin_users'] == 1) ? $args['show_admin_users'] : 0;
                  $redirect_to_author = (isset($args['template_options']['redirect_to_author']) && $args['template_options']['redirect_to_author'] == '1') ? $args['template_options']['redirect_to_author'] : 0;
                 
                foreach ($_users as $k => $guser) {
                    $user = get_user_by('id',$guser->ID);
                   
                    if($show_admin_users == 0)
                    {
                        if (user_can($user->ID, 'administrator') && $args['sample'] != 1) {
                            continue;
                        }
                    }
                    $users[$user->ID] = $defaultKeys;
                    $users[$user->ID] = array_merge($users[$user->ID], (array) $user->data);
                    /* Prepare User Meta Details */
                    $user_metas = get_user_meta($user->ID);
                  
                    if (!empty($user_metas)) {
                        foreach ($user_metas as $key => $val) {
                            $meta_value = maybe_unserialize($val[0]);
                            switch ($key) {
                                case 'description':
                                    $users[$user->ID]['description'] = ($meta_value) ? $meta_value : '';
                                    $users[$user->ID]['biography'] = ($meta_value) ? $meta_value : '';
                                    break;
                                case 'arm_user_plan_ids':
                                    $plan_names = array();
                                    if(!empty($meta_value) && is_array($meta_value)){
                                        $plan_name_array= $arm_subscription_plans->arm_get_plan_name_by_id_from_array();
                                        foreach($meta_value as $pid){
                                            if(!empty($plan_name_array[$pid])){
                                                $plan_names[] = $plan_name_array[$pid];
                                            }
                                        }
                                    }
                                    $plan_name = !empty($plan_names) ? implode(',', $plan_names) : '';
                                    $users[$user->ID]['subscription'] = $plan_name;
                                    $users[$user->ID]['membership'] = $plan_name;
                                    break;
                                case 'profile_picture':
                                case 'avatar':
                                    $users[$user->ID][$key] = $meta_value;
                                    break;
                                case 'profile_cover':
                                    $users[$user->ID][$key] = $meta_value;
                                    break;
                                case 'first_name':
                                    $users[$user->ID][$key] = $meta_value;
                                    break;
                                case 'arm_last_login_date':
                                    $users[$user->ID][$key] = $meta_value;
                                    if (!empty($meta_value)) {
                                        $users[$user->ID][$key] = $arm_global_settings->arm_time_elapsed(strtotime($meta_value));
                                    }
                                    break;
                                case 'arm_achievements':
                                    $users[$user->ID][$key] = $meta_value;
                                    break;
                                default:
                                    
                                    $meta_value = maybe_unserialize($meta_value); 
                                    if (is_array($meta_value) || $meta_value == '') {
                                        $users[$user->ID][$key] = $meta_value;
                                    } else {
                                     
                                        $users[$user->ID][$key] = '<span class="arm_user_meta_' . $key . '">' . $meta_value . '</span>';
                                    }
                                    break;
                            }
                        }
                    }
         
                    if (!function_exists('is_plugin_active')) {
                                            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                                        }
                   
                    /* Prepare Other Details */
                    $users[$user->ID]['full_name'] = $user->first_name . ' ' . $user->last_name;
                    if (empty($user->first_name) && empty($user->last_name)) {
                        $users[$user->ID]['full_name'] = $user->user_login;
                    }
                    
                    $profile_link = $arm_global_settings->arm_get_user_profile_url($user->ID, $show_admin_users);
                    if($redirect_to_author == 1 && count_user_posts( $user->ID ) > 0) { $profile_link = get_author_posts_url($user->ID); }
                    
                    $user_all_status = arm_get_all_member_status($user->ID);
                    $users[$user->ID]['primary_status'] = $user_all_status['arm_primary_status'];
                    $users[$user->ID]['secondary_status'] = $user_all_status['arm_secondary_status'];
                    $users[$user->ID]['user_link'] = $users[$user->ID]['profile_link'] = $profile_link;
                    $users[$user->ID]['home_url'] = ARMLITE_HOME_URL;
                    $users[$user->ID]['website'] = $user->user_url;
                    $role = array_shift($user->roles);
                    $users[$user->ID]['role'] = (!empty($role) && isset($allRoles[$role])) ? $allRoles[$role] : '-';
                    $users[$user->ID]['roles'] = (!empty($role) && isset($allRoles[$role])) ? $allRoles[$role] : '-';

                    $avatar = get_avatar($user->user_email, '200');

                    $users[$user->ID]['last_login'] = '';
                    $users[$user->ID]['last_active'] = '';
                    if (!empty($users[$user->ID]['arm_last_login_date'])) {
                        $users[$user->ID]['last_login'] = $users[$user->ID]['arm_last_login_date'];
                        $users[$user->ID]['last_active'] = __('active', 'ARMember') . ' ' . $arm_global_settings->arm_time_elapsed(strtotime($users[$user->ID]['arm_last_login_date']));
                    } else {
                        $users[$user->ID]['last_active'] = __('active', 'ARMember') . ' ' . $arm_global_settings->arm_time_elapsed(strtotime($user->user_registered));
                    }
                    $users[$user->ID]['user_join_date'] = date_i18n(get_option('date_format'),strtotime($user->user_registered));
                            
                    $profileCover = (!empty($users[$user->ID]['profile_cover'])) ? $users[$user->ID]['profile_cover'] : '';
                    $users[$user->ID]['profile_cover'] = '';
                    if (!empty($profileCover) && file_exists(MEMBERSHIPLITE_UPLOAD_DIR . '/' . basename($profileCover))) {
                        $users[$user->ID]['profile_cover'] = $profileCover;
                    } else {
                        if (isset($args['template_options']['default_cover']) && !empty($args['template_options']['default_cover'])) {
                            if (file_exists(MEMBERSHIPLITE_UPLOAD_DIR . '/' . basename($args['template_options']['default_cover']))) {
                                $users[$user->ID]['profile_cover'] = $args['template_options']['default_cover'];
                            }
                        }
                    }

                    if( $args['type'] == 'directory' && $users[$user->ID]['profile_cover'] == '' ){
                        $plansForQuery = "";
                        $user_plans = get_user_meta($user->ID,'arm_user_plan_ids',true);

                        /* Query Monitor Change ONlY VARIABLE */
                        $arm_qm_plans = "";
                        if( !empty($user_plans) && count($user_plans) > 1 ){
                            $x = 0;
                            foreach($user_plans as $k => $uplan ){
                                if( $x == 0 ){
                                    $plansForQuery .= " AND `arm_subscription_plan` LIKE '%{$uplan}%' ";
                                } else {
                                    $plansForQuery .= " OR `arm_subscription_plan` LIKE '%{$uplan}%' ";
                                }
                                $arm_qm_plans .= $uplan;
                                $x++;
                            }
                        } else {
                            if(isset($user_plans[0])){
                                $plansForQuery = "AND `arm_subscription_plan` LIKE '%{$user_plans[0]}%' ";
                                $arm_qm_plans .= $user_plans[0];
                            }
                        }
                        /* Query Monitor Change */
                        if( $arm_qm_plans == '' ){
                            $arm_qm_plans = 'arm_blank_template';
                        }

                        /* Query Monitor Change */
                        if( isset($GLOBALS['arm_template_options']) && isset($GLOBALS['arm_template_options'][$arm_qm_plans])){
                            $result = $GLOBALS['arm_template_options'][$arm_qm_plans];
                        } else {
                            $result = $wpdb->get_row("SELECT `arm_options` FROM `$ARMember->tbl_arm_member_templates` WHERE 1=1 {$plansForQuery} ORDER BY `arm_id` LIMIT 1");
                            if( !isset($GLOBALS['arm_template_options']) ){
                                $GLOBALS['arm_template_options'] = array();
                            }
                            $GLOBALS['arm_template_options'][$arm_qm_plans] = $result;
                        }
                        if(isset($result)){

                        $templateOpt = maybe_unserialize($result->arm_options);
                    }

                        if( isset($templateOpt['default_cover_photo']) && $templateOpt['default_cover_photo'] == 1 && isset($templateOpt['default_cover']) && $templateOpt['default_cover'] != '' ){
                            $users[$user->ID]['profile_cover'] = $templateOpt['default_cover'];
                        }
                    }

                    $arm_default_cover = isset($args['template_options']['default_cover']) ? $args['template_options']['default_cover'] : '';
                    $users[$user->ID]['cover_upload_btn'] = '';
                    $users[$user->ID]['profile_upload_btn'] = '';



                    preg_match_all('/src="([^"]+)"/', $avatar, $images);
                    $users[$user->ID]['profile_pictuer_url'] = isset($images[1][0]) ? $images[1][0] : '';
                    $users[$user->ID]['subscription_detail'] = '';
                    $users[$user->ID]['transactions'] = $users[$user->ID]['activity'] = '';


                    if ($user->ID == get_current_user_id() && !(isset($_POST['action']) && $_POST['action']=='arm_template_preview')) {
                        $browser_info = $ARMember->getBrowser($_SERVER['HTTP_USER_AGENT']);



                        $uploaderID = 'arm_profile_cover' . wp_generate_password(5, false, false);
                        $users[$user->ID]['cover_upload_btn'] .= '<div class="arm_cover_upload_container">';
                        if (isset($browser_info) and $browser_info != "" && $browser_info['name'] == 'Internet Explorer' && $browser_info['version'] <= '9') {
                            $users[$user->ID]['cover_upload_btn'] .= '<div id="' . $uploaderID . '_iframe_div" class="arm_iframe_wrapper" style="display:none;"><iframe id="' . $uploaderID . '_iframe" src="' . MEMBERSHIPLITE_VIEWS_URL . '/iframeupload.php"></iframe></div>';
                            $users[$user->ID]['cover_upload_btn'] .= '<div class="armCoverUploadBtnContainer">
								<label class="armCoverUploadBtn armhelptip" title="' . $uploadCoverPhotoTxt . '">
									<input type="text" name="arm_profile_cover" id="' . $uploaderID . '" class="arm_profile_cover armCoverUpload armIEFileUpload_profile"  accept=".jpg,.jpeg,.png,.bmp"  data-iframe="' . $uploaderID . '" data-type="cover" data-file_size="5" data-upload-url="' . MEMBERSHIPLITE_UPLOAD_URL . '">
								</label>
							</div>';
                        } else {
                            $users[$user->ID]['cover_upload_btn'] .= '<div class="armCoverUploadBtnContainer">
								<label class="armCoverUploadBtn armhelptip" title="' . $uploadCoverPhotoTxt . '">
									<input type="file" name="arm_profile_cover" id="' . $uploaderID . '" class="arm_profile_cover armCoverUpload"  data-type="cover">
								</label>
							</div>';
                        }

                        if (!empty($profileCover)) {
                            $cover_pic_style = 'style="display:block;"';
                        } else {
                            $cover_pic_style = 'style="display:none;"';
                        }
                        $arm_load_tipso = 1;
                        $users[$user->ID]['cover_upload_btn'] .= '<div class="armCoverUploadBtnContainer">
								<label id="armRemoveCover" class="armRemoveCover armhelptip" data-cover="' . basename($profileCover) . '" data-default-cover="' . $arm_default_cover . '" title="' . $removeCoverPhotoTxt . '" ' . $cover_pic_style . '></label>
							</div>';

                        $users[$user->ID]['cover_upload_btn'] .='<div id="arm_cover_delete_confirm" class="arm_confirm_box arm_delete_cover_popup" style="display: none;"><div class="arm_confirm_box_body"><div class="arm_confirm_box_arrow"></div><div class="arm_confirm_box_text">' . $removecoverPhotoAlert . '</div><div class="arm_confirm_box_btn_container"><button class="arm_confirm_box_btn armok arm_member_delete_btn" type="button" onclick="arm_remove_cover();">' . __('Delete', 'ARMember') . '</button><button onclick="hideConfirmBoxCallback();" class="arm_confirm_box_btn armcancel" type="button">' . __('Cancel', 'ARMember') . '</button></div></div></div>';

                        $users[$user->ID]['cover_upload_btn'] .= '</div>';
                        $uploaderID_profile = 'arm_profile_' . wp_generate_password(5, false, false);
                        $users[$user->ID]['profile_upload_btn'] .= '<div class="arm_cover_upload_container arm_profile">';

                        if (isset($browser_info) and $browser_info != "" && $browser_info['name'] == 'Internet Explorer' && $browser_info['version'] <= '9') {

                            $users[$user->ID]['profile_upload_btn'] .= '<div id="' . $uploaderID_profile . '_iframe_div" class="arm_iframe_wrapper" style="display:none;"><iframe id="' . $uploaderID_profile . '_iframe" src="' . MEMBERSHIPLITE_VIEWS_URL . '/iframeupload.php"></iframe></div>';
                            $users[$user->ID]['profile_upload_btn'] .= '<div class="armCoverUploadBtnContainer">
								<label class="armCoverUploadBtn armhelptip" title="' . $upload_profile_text . '">
									<input type="text" name="arm_profile_cover" id="' . $uploaderID_profile . '" class="arm_profile_cover armCoverUpload armIEFileUpload_profile" data-type="profile"   accept=".jpg,.jpeg,.png,.bmp"  data-iframe="' . $uploaderID_profile . '" data-type="cover" data-file_size="5" data-upload-url="' . MEMBERSHIPLITE_UPLOAD_URL . '">
								</label>
							</div>';
                        } else {
                            $users[$user->ID]['profile_upload_btn'] .= '<div class="armCoverUploadBtnContainer">
								<label class="armCoverUploadBtn armhelptip" title="' . $upload_profile_text . '">
									<input type="file" name="arm_profile_cover" id="' . $uploaderID_profile . '" class="arm_profile_cover armCoverUpload" data-type="profile">
								</label>
							</div>';
                        }


                        /* 23aug 2016  */
                        if (!empty($users[$user->ID]['profile_pictuer_url']) && file_exists(MEMBERSHIPLITE_UPLOAD_DIR . '/' . basename($users[$user->ID]['profile_pictuer_url']))) {
                            $pro_pic_style = ' style="display:block;"';
                        } else {
                            $pro_pic_style = ' style="display:none;"';
                        }
                        $arm_load_tipso = 1;
                        $users[$user->ID]['profile_upload_btn'] .= '<div class="armCoverUploadBtnContainer">
								<label id="armRemoveProfilePic" class="armRemoveCover armhelptip" data-cover="' . basename($users[$user->ID]['profile_pictuer_url']) . '" title="' . $removeProfilePhotoTxt . '"' . $pro_pic_style . '"></label>
							</div>';

                        $users[$user->ID]['profile_upload_btn'] .='<div id="arm_profile_delete_confirm" class="arm_confirm_box arm_delete_profile_popup" style="display: none;"><div class="arm_confirm_box_body"><div class="arm_confirm_box_arrow"></div><div class="arm_confirm_box_text">' . $removeprofilePhotoAlert . '</div><div class="arm_confirm_box_btn_container"><button class="arm_confirm_box_btn armok arm_member_delete_btn" type="button" onclick="arm_remove_profile();">' . __('Delete', 'ARMember') . '</button><button onclick="hideConfirmBoxCallbackprofile();" class="arm_confirm_box_btn armcancel" type="button">' . __('Cancel', 'ARMember') . '</button></div></div></div>';

                        $users[$user->ID]['profile_upload_btn'] .= '</div>';
                    }

                    $users[$user->ID]['profile_picture'] = $users[$user->ID]['avatar'] = $avatar . $users[$user->ID]['profile_upload_btn'];
                    /* Social Profile  Details Start */
                    if (isset($args['template_options']['arm_social_fields'])) {
                        foreach ($args['template_options']['arm_social_fields'] as $key => $value) {
                            $users[$user->ID]['social_profile_fields'] .= $value . ',';
                        }
                    }
                    /* Social Profile  Details End */


                    if (isset($args['show_transaction']) && $args['show_transaction'] == true) {
                        $users[$user->ID]['transactions'] = '<div class="arm_user_transactions">[arm_member_transaction user_id="' . $user->ID . '" title="" message_no_record=""]</div>';
                    }

                }
            }
            return $users;
        }

        function arm_template_profile_fields() {
            global $wpdb, $ARMember, $arm_member_forms, $arm_global_settings;
            $profileFields = array();
            $dbFormFields = $arm_member_forms->arm_get_db_form_fields(true);
            if (!empty($dbFormFields)) {
                $profileFields = $profileFields + $dbFormFields;
            }
            
            return $dbFormFields;
        }

        function arm_profile_template_blocks($template_data = array(), $user_detail = array(), $args = array()) {
            global $wpdb, $ARMember, $arm_member_forms,  $arm_social_feature, $arm_global_settings, $arm_ajaxurl;
            $template = '';
            
           $user = array_shift($user_detail);
           $user_id = $user['ID'];
            if (!empty($user)) {
                if( !wp_script_is('arm_file_upload_js','enqueued')){
                    wp_enqueue_script('arm_file_upload_js');
                }
                wp_enqueue_style('arm_croppic_css');
                global $templateOpt, $tempProfileFields, $socialProfileFields;
                $tempProfileFields = $this->arm_template_profile_fields();
                $socialProfileFields = $arm_member_forms->arm_social_profile_field_types();
				$templateOpt = $template_data;
                                
                             
                                
                                $tempopt = $templateOpt['arm_options'];
				$templateOpt['arm_options'] = maybe_unserialize($templateOpt['arm_options']);

                $hide_empty_profile_fields = isset($tempopt['hide_empty_profile_fields']) ? $tempopt['hide_empty_profile_fields'] : 0;
                $common_messages = $arm_global_settings->arm_get_all_common_message_settings();
                $arm_member_since_label = (isset($common_messages['arm_profile_member_since']) && $common_messages['arm_profile_member_since'] != '' ) ? $common_messages['arm_profile_member_since'] : __('Member Since', 'ARMember');
                $profileTabTxt =  __('Profile', 'ARMember');                
					
					$fileContent = '';
                                        
                                        $slected_social_profiles = isset($tempopt['arm_social_fields']) ? $tempopt['arm_social_fields'] : array();
                                        $social_fields = '';
                                        if (!empty($slected_social_profiles)) {
                                            foreach ($slected_social_profiles as $skey) {
                                                if (isset($args['is_preview']) && $args['is_preview'] == 1) {
                                                    $fileContent .= "<div class='arm_social_prof_div arm_user_social_fields arm_social_field_{$skey}'><a target='_blank' href='#'></a></div>";
                                                } else {
                                                    $spfMetaKey = 'arm_social_field_' . $skey;
                                                    if (in_array($skey, $slected_social_profiles)) {
                                                        $skey_field = get_user_meta($user['ID'], $spfMetaKey, true);
                                                        if (isset($skey_field) && !empty($skey_field)) {
                                                            $social_fields .= "<div class='arm_social_prof_div arm_user_social_fields arm_social_field_{$skey}'><a target='_blank' href='{$skey_field}'></a></div>";
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                        
                                        
                                        $social_fields_arr = array();
                                        $selected_social_profiles = isset($tempopt['arm_social_fields']) ? $tempopt['arm_social_fields'] : array();
                                        if (!empty($selected_social_profiles)) {
                                            foreach ($selected_social_profiles as $skey) {
                                                if (isset($args['is_preview']) && $args['is_preview'] == 1) {
                                                    $social_fields_arr[] = "<div class='arm_social_prof_div arm_user_social_fields arm_social_field_{$skey}'><a target='_blank' href='#'></a></div>";
                                                } else {
                                                    $spfMetaKey = 'arm_social_field_' . $skey;
                                                    if (in_array($skey, $selected_social_profiles)) {
                                                        $skey_field = get_user_meta($user['ID'], $spfMetaKey, true);
                                                        if (isset($skey_field) && !empty($skey_field)) {
                                                            $social_fields_arr[] = "<div class='arm_social_prof_div arm_user_social_fields arm_social_field_{$skey}'><a target='_blank' href='{$skey_field}'></a></div>";
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                        $mobile_device_social_fields = '';
                                        if (!empty($social_fields_arr)) {
                                            $mobile_device_social_fields = implode('', $social_fields_arr);
                                            $social_fields_chunked = array_chunk($social_fields_arr, ceil(count($social_fields_arr) / 2));
                                            $socialfields = array();
                                            $n = 0;
                                            foreach ($social_fields_chunked as $key => $sfields) {
                                                $socialfields[$n] = '';
                                                $socialfields[$n] .= '<div class="social_profile_fields">';
                                                foreach ($sfields as $key => $value) {
                                                    $socialfields[$n] .= $value;
                                                }
                                                $socialfields[$n] .= "</div>";
                                                $n++;
                                            }
                                        }
                                        $socialfields_left = isset($socialfields) && !empty($socialfields[0]) ? $socialfields[0] : '';
                                        $socialfields_right = isset($socialfields) && !empty($socialfields[1]) ? $socialfields[1] : '';
                                        
                                        $arm_user_join_date = '';
                                        if (isset($tempopt['show_joining']) && $tempopt['show_joining'] == true) {

                                            $arm_user_join_date = $arm_member_since_label." ".$user['user_join_date'];
                                        }
                                        $arm_cover_image = "";
                                        if( isset($tempopt['default_cover_photo']) && $tempopt['default_cover_photo'] == 1 ){
                                                $arm_cover_image = "background-image: url('". $user['profile_cover'] . "')";
                                        } else {
                                            if( isset($user['profile_cover']) && $user['profile_cover'] != '' && $user['profile_cover'] != $tempopt['default_cover'] ){
                                                $arm_cover_image = "background-image: url('". $user['profile_cover'] . "')";
                                            }
                                        }

                        $arm_template_html = stripslashes_deep($template_data['arm_template_html']);
                        

                                        $arm_template_html = preg_replace('/(\[arm_usermeta\s+(.*?)\])/','[arm_usermeta $2 id="'.$user_id.'"]',$arm_template_html);
                                        if( $hide_empty_profile_fields ){
                                            $pattern = '/(\<tr\>\<td\>(.*?)\<\/td>\<td\>(.*?)\<\/td\>\<\/tr\>)/';
                                            preg_match_all($pattern,do_shortcode($arm_template_html),$matches);
                                            if( isset($matches) && isset($matches[2]) && isset($matches[3]) && count($matches[2]) > 0 && count($matches[3]) > 0){
                                                foreach($matches[2] as $k => $val ){
                                                    if( $matches[3][$k] == '' ){
                                                        $pat_val = str_replace('/','\\/',$val);
                                                        $pat_val = str_replace('(','\\(',$pat_val);
                                                        $pat_val = str_replace(')','\\)',$pat_val);
                                                        $pattern_d = "\<tr\>\<td\>{$pat_val}\<\/td>\<td\>(.*?)\<\/td\>\<\/tr\>";
                                                        preg_match("/$pattern_d/",$arm_template_html,$match);
                                                        if( isset($match[0]) && count($match) > 0 ){
                                                            $arm_template_html = preg_replace("/$pattern_d/m",'',$arm_template_html);
                                                        }
                                                    }
                                                }
                                            }
                                        }

                                        $profile_link_name  ='<a href="' . $user['user_link'] . '">' . $user['full_name'] . '</a>';
                                        $arm_template_html = str_replace('{ARM_Profile_Cover_Image}',$arm_cover_image , $arm_template_html);
                                        $arm_template_html = str_replace('{ARM_Profile_User_Name}', $profile_link_name, $arm_template_html);
                                        $arm_template_html = str_replace('{ARM_Profile_Avatar_Image}', $user['avatar'], $arm_template_html);
                                      
                                        $arm_template_html = str_replace('{ARM_Profile_Join_Date}', $arm_user_join_date, $arm_template_html);
                                        $arm_template_html = str_replace('{ARM_Profile_Social_Icons}', $social_fields, $arm_template_html);
                                        $arm_template_html = str_replace('{ARM_Cover_Upload_Button}', $user['cover_upload_btn'] ,$arm_template_html);
                                        $arm_template_html = str_replace('{ARM_Profile_Social_Icons_Mobile}', $mobile_device_social_fields ,$arm_template_html);
                                        $arm_template_html = str_replace('{ARM_Profile_Social_Icons_Left}', $socialfields_left ,$arm_template_html);
                                        $arm_template_html = str_replace('{ARM_Profile_Social_Icons_Right}', $socialfields_right ,$arm_template_html);


                                        $arm_arguments = func_get_args();
                                        $arm_profile_before_content = '';
                                        $arm_profile_after_content = '';
                                        $arm_template_html = str_replace('{ARM_PROFILE_FIELDS_BEFORE_CONTENT}',$arm_profile_before_content,$arm_template_html);
                                        $arm_template_html = str_replace('{ARM_PROFILE_FIELDS_AFTER_CONTENT}',$arm_profile_after_content,$arm_template_html);

					                   $template .= $arm_template_html;
				$template = preg_replace('|{(\w+)}|', '', $template);
			}
			return do_shortcode($template);
		}
		function arm_get_directory_members($tempData, $opts = array())
		{
			global $wpdb, $ARMember, $arm_global_settings, $arm_members_directory, $arm_members_class, $arm_social_feature;
			extract($opts);
			$orderby = isset($opts['orderby']) ? $opts['orderby'] : 'user_registered';
			$order = isset($opts['order']) ? $opts['order'] : 'DESC';
                        $show_admin_users = (isset($opts['show_admin_users']) && $opts['show_admin_users'] == 1 )? $opts['show_admin_users'] : 0;
                        if($orderby == 'user_registered')
                        {
                            $order = 'DESC';
                        }
			$per_page = isset($opts['per_page']) ? $opts['per_page'] : 10;
			$offset = (!empty($current_page) && $current_page > 1) ? (($current_page - 1) * $per_page) : 0;
            $content = '';
            $user_table = $wpdb->users;
            $usermeta_table = $wpdb->usermeta;
            $capability_column = $wpdb->get_blog_prefix($GLOBALS['blog_id']) . 'capabilities';

            $user_where = " WHERE 1=1 ";
            if( $orderby === 'login' ){
                $orderby = 'user_login';
            }
            $order_by_keyword = "u.{$orderby}";
            $order_by = ' ORDER BY '.$order_by_keyword.' '.$order;
            if($orderby === 'arm_last_login_date'){
                $order_by = "um.arm_last_login_date {$order}";
            }
            $user_limit = " LIMIT {$offset},{$per_page} ";

            $searchStr = isset($opts['search']) ? esc_attr($opts['search']) : '';
            if($show_admin_users == 0)
            {
                $super_admin_ids = array();
                if( is_multisite() ){
                    $super_admin = get_super_admins();
                    if( !empty($super_admin) ){
                        foreach( $super_admin as $skey => $sadmin ){
                            if( $sadmin != '' ){
                                $user_obj = get_user_by('login',$sadmin);
                                if( $user_obj->ID != '' ){
                                    $super_admin_ids[] = $user_obj->ID;
                                }
                            }
                        }
                    }
                }

                $admin_user_where = " WHERE 1=1 ";

                if( !empty($super_admin_ids ) ){
                    $admin_user_where .= " AND u.ID IN (".implode(',',$super_admin_ids).")";
                }
                $operator = " AND ";

                if( !empty($super_admin_ids ) ){
                    $operator = " OR ";
                }

                $admin_user_where .= " {$operator} um.meta_key = '{$capability_column}' AND um.meta_value LIKE '%administrator%' ";

                $admin_user_query = " SELECT u.ID FROM `{$user_table}` u LEFT JOIN `{$usermeta_table}` um ON um.user_id = u.ID {$admin_user_where} ";

                $admin_users = $wpdb->get_results($admin_user_query);
                $admin_user_ids = array();
                if( !empty($admin_users) ){
                    foreach( $admin_users as $key => $admin ){
                        array_push($admin_user_ids,$admin->ID);
                    }
                }
                $admin_user_ids = array_unique($admin_user_ids);
                if( !empty($admin_user_ids)):
                    $user_where .= " AND u.ID NOT IN (".implode(',',$admin_user_ids).") ";
                endif;
            }
                
            
            $user_search = "";

            if( $searchStr !== '' ){
                $arm_template_options = $opts['template_options'];
                        $arm_search_field_array = $arm_template_options['profile_fields'];
                       
                        if(!empty($arm_search_field_array))
                        {
                            
                            $user_search .= ' AND (';
                            $is_next = 0;
                            if(in_array('user_login', $arm_search_field_array))
                            {
                                $user_search.= "u.user_login LIKE '%{$searchStr}%'";
                                $is_next = 1;
                                unset($arm_search_field_array['user_login']);
                            }
                            if(in_array('user_email', $arm_search_field_array))
                            {
                                if($is_next == 1)
                                {
                                    $serach_operator = " OR";
                                }
                                else
                                {
                                    $serach_operator = '';
                                }
                                $user_search.= $serach_operator." u.user_email LIKE '%{$searchStr}%'";
                                $is_next = 1;
                                unset($arm_search_field_array['user_email']);
                            }
                            if(in_array('display_name', $arm_search_field_array))
                            {
                                if($is_next == 1)
                                {
                                    $serach_operator = " OR";
                                }
                                else
                                {
                                    $serach_operator = '';
                                }
                                $user_search.= $serach_operator." u.display_name LIKE '%{$searchStr}%'";
                                $is_next = 1;
                                unset($arm_search_field_array['display_name']);
                            }
                            if(in_array('user_url', $arm_search_field_array))
                            {
                                if($is_next == 1)
                                {
                                    $serach_operator = " OR";
                                }
                                else
                                {
                                    $serach_operator = '';
                                }
                                $user_search.= $serach_operator." u.user_url LIKE '%{$searchStr}%'";
                                $is_next = 1;
                                unset($arm_search_field_array['user_url']);
                            }
                            $total_search_fields = count($arm_search_field_array);
                            
                            
                     
                            if($total_search_fields > 0)
                            {
                                if($is_next == 1)
                                {
                                    $serach_operator = " OR";
                                }
                                else
                                {
                                    $serach_operator = '';
                                }
                                $i = 0;
                                
                                foreach($arm_search_field_array as $key => $value)
                                {
                                    $i++;
                                    if($i == 1)
                                    {
                                         $user_search .= $serach_operator ." (um.meta_key = '{$key}' AND um.meta_value LIKE '%{$searchStr}%')";
                                    }
                                    else
                                    {
                                        $user_search .= " OR (um.meta_key = '{$key}' AND um.meta_value LIKE '%{$searchStr}%')";
                                    }
                                }
                            }
                            
                             $user_search .= ')';
                        }
                        else
                        {
                            $user_search = " AND ( u.display_name LIKE '%{$searchStr}%' OR (um.meta_key = 'first_name' AND um.meta_value LIKE '%{$searchStr}%') OR (um.meta_key = 'last_name' AND um.meta_value LIKE '%{$searchStr}%') ) ";
                        }
                        
                    }
            $selected_plans = "";
            $filter = 0;
           

            if( isset($opts['template_options']['plans']) && !empty($opts['template_options']['plans'])) {
                $template_opt_plans = $opts['template_options']['plans'];
                $template_opt_plans_filter_qur = "";
                foreach ($template_opt_plans as $template_opt_plan_val) {
                    if(empty($template_opt_plans_filter_qur))
                    {
                        $template_opt_plans_filter_qur .= " ( ";
                        $template_opt_plans_filter_qur .= " um.meta_value like '%\"".$template_opt_plan_val."\"%' ";
                        $template_opt_plans_filter_qur .= " OR um.meta_value like '%i:0;i:".$template_opt_plan_val."%' ";
                        $template_opt_plans_filter_qur .= " ) ";
                    }
                    else {
                        $template_opt_plans_filter_qur .= " OR ( ";
                        $template_opt_plans_filter_qur .= " um.meta_value like '%\"".$template_opt_plan_val."\"%' ";
                        $template_opt_plans_filter_qur .= " OR um.meta_value like '%i:0;i:".$template_opt_plan_val."%' ";
                        $template_opt_plans_filter_qur .= " ) ";
                    }
                }
                $user_search .= " AND u.ID IN (SELECT u.ID FROM {$user_table} u INNER JOIN `{$usermeta_table}` um ON u.ID = um.user_id INNER JOIN `" . $ARMember->tbl_arm_members . "` am ON um.user_id = am.arm_user_id WHERE (um.meta_key = 'arm_user_plan_ids' AND um.meta_value != '' AND (".$template_opt_plans_filter_qur.")))";
                $filter = 1;
            }
            
            if( is_multisite()){
               if($searchStr == '' && $filter == 0){
                    $user_where .= "AND um.meta_key = '{$capability_column}'";
               }
               else
               {

                   $user_where .= "AND um.user_id IN (SELECT `user_id` FROM `{$usermeta_table}` WHERE 1=1 AND `meta_key` = '{$capability_column}')";
               }
            }
            else
            {
                if($searchStr == '' && $filter == 0){
                 $user_where .= "AND um.meta_key = '{$capability_column}'";
                }
            }
            $user_where .= ' AND am.arm_primary_status = 1';
            $user_query_total = "SELECT u.ID FROM `{$user_table}` u INNER JOIN `{$usermeta_table}` um  ON u.ID = um.user_id INNER JOIN `" . $ARMember->tbl_arm_members . "` am  ON um.user_id = am.arm_user_id {$user_where} {$user_search} GROUP BY u.ID {$order_by}";

            $total_users_res = $wpdb->get_results($user_query_total);
            
            if( isset($opts['template_options']['plans']) && !empty($opts['template_options']['plans'])) {
                 
                foreach($total_users_res as $tkey => $tuser){
                    
                    $plan_ids = get_user_meta($tuser->ID, 'arm_user_plan_ids', true);
                    if(!empty($plan_ids) && is_array($plan_ids)){
                        $return_array = array_intersect($plan_ids, $opts['template_options']['plans']);
                        if(empty($return_array)){
                            unset($total_users_res[$tkey]);
                        }
                    }
                }
            }
            
            
        
            $total_users = (!empty($total_users_res)) ? count( $total_users_res ) : 0;

            $user_query = " SELECT u.ID FROM `{$user_table}` u INNER JOIN `{$usermeta_table}` um  ON u.ID = um.user_id INNER JOIN `" . $ARMember->tbl_arm_members . "` am  ON um.user_id = am.arm_user_id {$user_where} {$user_search} GROUP BY u.ID {$order_by} {$user_limit} ";

            $users = $wpdb->get_results($user_query);
            
            if( isset($opts['template_options']['plans']) && !empty($opts['template_options']['plans'])) {
                 
                foreach($users as $key => $user){
                    
                    $plan_ids = get_user_meta($user->ID, 'arm_user_plan_ids', true);
                    if(!empty($plan_ids) && is_array($plan_ids)){
                        $treturn_array = array_intersect($plan_ids, $opts['template_options']['plans']);
                        if(empty($treturn_array)){
                            unset($users[$key]);
                        }
                    }
                }
                
                
            }
        

            if (!empty($users)) {
				$_data = $this->arm_prepare_users_detail_for_template($users, $opts);
                             
                                
				$_data = apply_filters('arm_change_user_detail_before_display_in_profile_and_directory', $_data, $opts);
				$content .= $this->arm_directory_template_blocks((array) $tempData, $_data, $opts);
				if (!empty($_data)) {
                    /* For Pagination */
                    if (isset($opts['template_options']['pagination']) && $opts['template_options']['pagination'] == 'infinite') {
						if ($total_users > ($current_page * $per_page)) {
							$paging = '<a class="arm_directory_load_more_btn arm_directory_load_more_link" href="javascript:void(0)" data-page="' . ($current_page + 1) . '" data-type="infinite">' . __('Load More', 'ARMember') . '</a>';
                            $paging .= '<img class="arm_load_more_loader" src="'.MEMBERSHIPLITE_IMAGES_URL.'/arm_loader.gif" alt="' . __('Load More', 'ARMember') . '" style="display:none;">';
							$content .= '<div class="arm_directory_paging_container arm_directory_paging_container_infinite">' . $paging . '</div>';
						}
					 } else {
						$paging = $arm_global_settings->arm_get_paging_links($current_page, $total_users, $per_page, 'directory');
                            $content .= '<div class="arm_directory_paging_container arm_directory_paging_container_numeric">' . $paging . '</div>';
                    }
                } else {
                    $err_msg = __('No Users Found.', 'ARMember');
                    $content .= '<div class="arm_directory_paging_container arm_directory_empty_list">' . $err_msg . '</div>';
                }
            } else {
                 if (!empty($searchStr)) {
                        $err_msg = $arm_global_settings->common_message['arm_search_result_found'];
                        $err_msg = (!empty($err_msg)) ? $err_msg : __('No Search Result Found.', 'ARMember');
                        $content .= '<div class="arm_directory_paging_container arm_directory_empty_list">' . $err_msg . '</div>';
                    } else {
                        $err_msg =  __('No Users Found.', 'ARMember');
                        $content .= '<div class="arm_directory_paging_container arm_directory_empty_list">' . $err_msg . '</div>';
                    }
            }
            return $content;
		}
		function arm_directory_template_blocks($template_data = array(), $user_data = array(), $args = array())
		{
			global $wpdb, $ARMember,$arm_social_feature, $arm_member_forms, $arm_global_settings;
			$template = '';
			if (!empty($user_data))
			{
                if (is_file(MEMBERSHIPLITE_VIEWS_DIR . '/templates/' . $template_data['arm_slug'].'.php')) {
                    global $templateOpt, $socialProfileFields;
                    $socialProfileFields = $arm_member_forms->arm_social_profile_field_types();
                    $common_messages = $arm_global_settings->arm_get_all_common_message_settings();
                    $arm_member_since_label = (isset($common_messages['arm_profile_member_since']) && $common_messages['arm_profile_member_since'] != '' ) ? $common_messages['arm_profile_member_since'] : __('Member Since', 'ARMember');
                    $arm_view_profile_label = (isset($common_messages['arm_profile_view_profile']) && $common_messages['arm_profile_member_since'] != '' ) ? $common_messages['arm_profile_view_profile'] : __('Member Since', 'ARMember');
                    $templateOpt = $template_data;
                    $templateOpt['arm_options'] = maybe_unserialize($templateOpt['arm_options']);
					$fileContent = '';
					$n = 1;
					$f = 0;
					foreach ($user_data as $user) {
						include (MEMBERSHIPLITE_VIEWS_DIR . '/templates/' . $template_data['arm_slug'].'.php');
						$n++;
						$f++;
					}
					$template .= $fileContent;
				}
				$template = preg_replace('|{(\w+)}|', '', $template);
			}
			return do_shortcode($template);
		}
		function arm_template_edit_popup_func()
		{
			global $wpdb, $ARMember, $arm_member_forms, $arm_capabilities_global;

            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_member_templates'], '1');

			$return = array('status' => 'error', 'message' => __('There is a error while updating template, please try again.', 'ARMember'), 'popup' => '');
			if (isset($_POST['action']) && $_POST['action'] == 'arm_template_edit_popup')
			{
				$temp_id = isset($_POST['temp_id']) ? intval($_POST['temp_id']) : '';
				$tempType = isset($_POST['temp_type']) ? sanitize_text_field($_POST['temp_type']) : '';
				if (!empty($temp_id) && $temp_id != 0) {
					$tempDetails = $this->arm_get_template_by_id($temp_id);
					if (!empty($tempDetails)) {
						$tempType = isset($tempDetails['arm_type']) ? $tempDetails['arm_type'] : 'directory';
						$tempOptions = $tempDetails['arm_options'];
						$popup = '<div class="arm_pdtemp_edit_popup_wrapper popup_wrapper" style="width: 750px;">';
						$popup .= '<form action="#" method="post" onsubmit="return false;" class="arm_template_edit_form arm_admin_form" id="arm_template_edit_form" data-temp_id="'.$temp_id.'">';
							if($tempType == 'directory')
                                                        {
                                                            $popup .= '<input type="hidden" id="arm_template_slug" name="arm_template_slug" value="'.$tempDetails['arm_slug'].'">';
                                                        } 
                                                
                                                        $popup .= '<table cellspacing="0">';
							$popup .= '<tr class="popup_wrapper_inner">';
								$popup .= '<td class="popup_header">';
									$popup .= '<span class="popup_close_btn arm_popup_close_btn arm_pdtemp_edit_close_btn"></span>';
									$popup .= '<span>' . __('Edit Template Options', 'ARMember') . '</span>';
								$popup .= '</td>';
								$popup .= '<td class="popup_content_text">';
									$popup .= $this->arm_template_options($temp_id, $tempType, $tempDetails);
								$popup .= '</td>';
								$popup .= '<td class="popup_content_btn popup_footer">';
									$popup .= '<input type="hidden" name="id" id="arm_pdtemp_edit_id" value="'.$temp_id.'">';
									$popup .= '<div class="popup_content_btn_wrapper arm_temp_option_wrapper">';
									$popup .= '<button class="arm_save_btn arm_pdtemp_edit_submit" id="arm_pdtemp_edit_submit" data-id="'.$temp_id.'" type="submit">'.__('Save', 'ARMember').'</button>';
									$popup .= '<button class="arm_cancel_btn arm_pdtemp_edit_close_btn" type="button">'.__('Cancel', 'ARMember').'</button>';
									$popup .= '</div>';
									$popup .= '<div class="popup_content_btn_wrapper arm_temp_custom_class_btn hidden_section">';
									$backToListingIcon = MEMBERSHIPLITE_IMAGES_URL.'/back_to_listing_arrow.png';
									$popup .= '<a href="javascript:void(0)" class="arm_section_custom_css_detail_hide_template armemailaddbtn"><img src="' . $backToListingIcon . '"/>' . __('Back to template options', 'ARMember') . '</a>';
									$popup .= '</div>';
								$popup .= '</td>';
							$popup .= '</tr>';
							$popup .= '</table>';
						$popup .= '</form>';
						$popup .= '</div>';
						$return = array('status' => 'success', 'message' => __('Template found.', 'ARMember'), 'popup' => $popup);
					} else {
						$return = array('status' => 'error', 'message' => __('Template not found.', 'ARMember'));
					}
				}
			}
			echo json_encode($return);
			exit;
		}
		function arm_template_options($tempID = 0, $tempType = 'directory', $tempDetails = array())
		{
			global $wpdb, $ARMember, $arm_member_forms, $arm_subscription_plans;
                        if (!function_exists('is_plugin_active')) {
                            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                        }                        
			$tempOptions = $tempDetails['arm_options'];
			$tempSlug = $tempDetails['arm_slug'];
			$tempOptions = shortcode_atts(array(
				'plans' => array(),
                'per_page_users' => 10,
				'pagination' => 'numeric',
                            'show_admin_users' => '',
				'show_badges' => '',
                                'show_joining' => '',
                                'redirect_to_author' => '',
                                'redirect_to_buddypress_profile' => '',
                                'hide_empty_profile_fields' => '',
				'color_scheme' => '',
				'title_color' => '',
				'subtitle_color' => '',
				'border_color' => '',
				'button_color' => '',
				'button_font_color' => '',
				'tab_bg_color' => '',
				'tab_link_color' => '',
				'tab_link_hover_color' => '',
				'tab_link_bg_color' => '',
				'tab_link_hover_bg_color' => '',
				'link_color' => '',
				'link_hover_color' => '',
				'content_font_color' => '',
				'box_bg_color' => '',
				'title_font' => array(),
				'subtitle_font' => array(),
				'button_font' => array(),
				'tab_link_font' => array(),
				'content_font' => array(),
				'searchbox' => '',
				'sortbox' => '',
				'grouping' => '',
				'profile_fields' => array(),
                                'labels' => array(),
				'arm_social_fields' => array(),
				'default_cover' => '',
				'custom_css' => '',
			), $tempDetails['arm_options']);


			
            $defaultTemplates = $this->arm_default_member_templates();
			$tempColorSchemes = $this->getTemplateColorSchemes();
			if ($tempType == 'profile') {
				$colorOptions = array(
					'title_color' => __('Title Color', 'ARMember'),
					'subtitle_color' => __('Sub Title Color', 'ARMember'),
					'border_color' => __('Border Color', 'ARMember'),
					'content_font_color' => __('Body Content Color', 'ARMember'),
				);
				$fontOptions = array(
					'title_font' => __('Title Font', 'ARMember'),
					'subtitle_font' => __('Sub Title Font', 'ARMember'),
					'content_font' => __('Content Font', 'ARMember'),
				);
			} else {
				$colorOptions = array(
					'border_color' => __('Box Hover Effect', 'ARMember'),
					'title_color' => __('Title Color', 'ARMember'),
					'subtitle_color' => __('Sub Title Color', 'ARMember'),
					'button_color' => __('Button Color', 'ARMember'),
					'button_font_color' => __('Button Font Color', 'ARMember'),
					'box_bg_color' => __('Background Color', 'ARMember'),
                    'link_color' => __('Link Color', 'ARMember'),
					'link_hover_color' => __('Link Hover Color', 'ARMember'),
				);
				$fontOptions = array(
					'title_font' => __('Title Font', 'ARMember'),
					'subtitle_font' => __('Sub Title Font', 'ARMember'),
					'button_font' => __('Button Font', 'ARMember'),
                    'content_font' => __('Content Font', 'ARMember'),
				);
			}
			$tempOptHtml = '';
			$tempOptHtml .= '<div class="arm_temp_option_wrapper">';
				$tempOptHtml .= '<table class="arm_table_label_on_top">';
					if ($tempType == 'profile')
					{
                        $tempOptHtml .= '<tr>';
                            $tempOptHtml .= '<th>'.__('Select Template','ARMember').'</th>';
                            $tempOptHtml .= '<td>';
                            $tempOptHtml .= '<div class="arm_profile_template_selection">';
                            if (!empty($defaultTemplates)) {
                                foreach($defaultTemplates as $temp) {
                                    if ($temp['arm_type'] == 'profile') {
                                        $checked = ($temp['arm_slug'] == $tempSlug) ? 'checked="checked"' : '';
                                        $activeClass = ($temp['arm_slug'] == $tempSlug) ? 'arm_active_temp' : '';
                            $tempOptHtml .= '<label class="arm_tempalte_type_box arm_temp_' . $temp['arm_type'] . '_options ' . $activeClass . '" data-type="' . $temp['arm_type'] . '" for="arm_profile_temp_type_' . $temp['arm_slug'] . '">';
                            $tempOptHtml .= '<input type="radio" name="profile_slug" value="' . $temp['arm_slug'] . '" id="arm_profile_temp_type_' . $temp['arm_slug'] . '" class="arm_temp_type_radio ' . $temp['arm_type'] . '" data-type="' . $temp['arm_type'] . '" ' . $checked . '>';
                                        $tempOptHtml .= '<img alt="" src="'.MEMBERSHIPLITE_VIEWS_URL. '/templates/' . $temp['arm_slug'] . '.png"/>';
                                        $tempOptHtml .= '<span class="arm_temp_selected_text">'.__('Selected', 'ARMember').'</span>';
                                        $tempOptHtml .= '</label>';
                                        
                                    }
                                }
                            }
                            $tempOptHtml .= '</div>';
                            $tempOptHtml .= '</td>';
						$tempOptHtml .= '</tr>';
					}
                                      
                                        $tempOptions['show_admin_users'] = (isset($tempOptions['show_admin_users']) && $tempOptions['show_admin_users'] == 1) ? $tempOptions['show_admin_users'] : 0;
                                        $tempOptHtml .= '<tr>';
						$tempOptHtml .= '<td colspan="2">';
						$tempOptHtml .= '<div class="arm_temp_switch_wrapper" style="width: auto;margin: 5px 0;">';
						$tempOptHtml .= '<div class="armswitch arm_global_setting_switch"><input type="checkbox" id="arm_template_show_admin_users" value="1" class="armswitch_input" name="template_options[show_admin_users]" '.checked($tempOptions['show_admin_users'], 1, false).'/><label for="arm_template_show_admin_users" class="armswitch_label"></label></div>';
						$tempOptHtml .= '</div>';
						$tempOptHtml .= '<label for="arm_template_show_admin_users" class="arm_temp_form_label">' . __('Display Administrator Users?', 'ARMember') . '</label>';
						$tempOptHtml .= '</td>';
					$tempOptHtml .= '</tr>';
                                       
				
                                        
                                        $tempOptHtml .= '<tr>';
						$tempOptHtml .= '<td colspan="2">';
						$tempOptHtml .= '<div class="arm_temp_switch_wrapper" style="width: auto;margin: 5px 0;">';
						$tempOptHtml .= '<div class="armswitch arm_global_setting_switch"><input type="checkbox" id="arm_template_show_joining" value="1" class="armswitch_input" name="template_options[show_joining]" '.checked($tempOptions['show_joining'], 1, false).'/><label for="arm_template_show_joining" class="armswitch_label"></label></div>';
						$tempOptHtml .= '</div>';
						$tempOptHtml .= '<label for="arm_template_show_joining" class="arm_temp_form_label">' . __('Display Joining Date?', 'ARMember') . '</label>';
						$tempOptHtml .= '</td>';
					$tempOptHtml .= '</tr>';
                                        
                                        if ($tempType == 'directory')
					{
                                            $tempOptHtml .= '<tr>';
						$tempOptHtml .= '<td colspan="2">';
						$tempOptHtml .= '<div class="arm_temp_switch_wrapper" style="width: auto;margin: 5px 0;">';
						$tempOptHtml .= '<div class="armswitch arm_global_setting_switch"><input type="checkbox" id="arm_template_redirect_to_author" value="1" class="armswitch_input" name="template_options[redirect_to_author]" '.checked($tempOptions['redirect_to_author'], 1, false).'/><label for="arm_template_redirect_to_author" class="armswitch_label"></label></div>';
						$tempOptHtml .= '</div>';
						$tempOptHtml .= '<label for="arm_template_redirect_to_author" class="arm_temp_form_label">' . __('Redirect To Author Archive Page', 'ARMember') . '</label>';
                                                $tempOptHtml .= '<div class="armclear" style="height: 1px;"></div>';
                                                $tempOptHtml .= '<span class="arm_info_text" style="width:450px;">( '.__("If Author have no any post than user will be redirect to ARMember Profile Page", 'ARMember').' )</span>';
						$tempOptHtml .= '</td>';
                                            $tempOptHtml .= '</tr>';
                                            
                                                                                      
                                        }
                                        
					if ($tempType == 'profile')
					{
                                            
                                             $tempOptHtml .= '<tr>';
						$tempOptHtml .= '<td colspan="2">';
						$tempOptHtml .= '<div class="arm_temp_switch_wrapper" style="width: auto;margin: 5px 0;">';
						$tempOptHtml .= '<div class="armswitch arm_global_setting_switch"><input type="checkbox" id="arm_template_hide_empty_profile_fields" value="1" class="armswitch_input" name="template_options[hide_empty_profile_fields]" '.checked($tempOptions['hide_empty_profile_fields'], 1, false).'/><label for="arm_template_hide_empty_profile_fields" class="armswitch_label"></label></div>';
						$tempOptHtml .= '</div>';
						$tempOptHtml .= '<label for="arm_template_hide_empty_profile_fields" class="arm_temp_form_label">' . __('Hide empty profile fields?', 'ARMember') . '</label>';
						$tempOptHtml .= '</td>';
					$tempOptHtml .= '</tr>';
						$tempOptHtml .= '<tr>';
							$tempOptHtml .= '<th>' . __('Profile Fields', 'ARMember') . '</th>';
							$tempOptHtml .= '<td>';
							$tempOptHtml .= '<div class="arm_profile_fields_selection_wrapper">';
                                $dbProfileFields = $this->arm_template_profile_fields();
                                $orderedFields = array();
                                if (!empty($tempOptions['profile_fields'])) {
                                   foreach($tempOptions['profile_fields'] as $fieldK) {
                                       if (isset($dbProfileFields[$fieldK])) {
                                            $orderedFields[$fieldK] = $dbProfileFields[$fieldK];
                                            unset($dbProfileFields[$fieldK]);
                                       }
                                   }
                                }
                                $orderedFields = $orderedFields + $dbProfileFields;
                                
                            
                                
                                if (!empty($orderedFields)) {
                                    $tempOptHtml .= '<ul class="arm_profile_fields_sortable_popup">';
                                    foreach ($orderedFields as $fieldMetaKey => $fieldOpt) {
                                        if (empty($fieldMetaKey) || $fieldMetaKey == 'user_pass' || in_array($fieldOpt['type'], array('hidden', 'html', 'section', 'rememberme'))) {
                                            continue;
                                        }
                                        $fchecked = $fdisabled = '';
                                        if (in_array($fieldMetaKey, $tempOptions['profile_fields'])) {
                                            $fchecked = 'checked="checked"';
                                        }
                                        
                                        $field_label = (isset($tempOptions['labels']) && !empty($tempOptions['labels']) && !empty($tempOptions['labels'][$fieldMetaKey])) ? $tempOptions['labels'][$fieldMetaKey] : $fieldOpt['label'];
                                        $tempOptHtml .= '<li class="arm_profile_fields_li">';
                                        $tempOptHtml .= '<input type="checkbox" value="'.$fieldMetaKey.'" class="arm_icheckbox" name="template_options[profile_fields]['.$fieldMetaKey.']" id="arm_profile_temp_field_input_'.$fieldMetaKey.'" '.$fchecked.' '.$fdisabled.'/>';
                                        $tempOptHtml .= '';
                                        $tempOptHtml .= '<input type="hidden" name="template_options[labels]['.$fieldMetaKey.']" id="arm_profile_firld_label_'.$fieldMetaKey.'" value="'.$field_label.'" />';
                                        $tempOptHtml .= '<label class="arm_profile_temp_field_input" data-id="arm_profile_firld_label_'.$fieldMetaKey.'" style="margin-left: 5px;">'.$field_label.'</label>';
                                        $tempOptHtml .= '<div class="arm_list_sortable_icon"></div>';
                                        $tempOptHtml .= '</li>';
                                    }
                                    $tempOptHtml .= '</ul>';
                                }
							$tempOptHtml .= '</div>';
							$tempOptHtml .= '</td>';
						$tempOptHtml .= '</tr>';
					} else {
                $tempOptHtml .= '<tr>';
                $tempOptHtml .= '<th>' . __('Select Membership Plans', 'ARMember') . '</th>';
                $tempOptHtml .= '<td>';
                $tempOptHtml .= '<div style="width: auto;margin: 5px 0;">';
                $subs_data = $arm_subscription_plans->arm_get_all_subscription_plans('arm_subscription_plan_id, arm_subscription_plan_name');
                $tempPlans = isset($tempOptions['plans']) ? $tempOptions['plans'] : array();
                $tempOptHtml .= '<select id="arm_template_plans" class="arm_chosen_selectbox arm_template_plans_select" name="template_options[plans][]" data-placeholder="' . __('Select Plan(s)..', 'ARMember') . '" multiple="multiple">';
                if (!empty($subs_data)) {
                    foreach ($subs_data as $sd) {
                        $tempOptHtml .= '<option value="' . $sd['arm_subscription_plan_id'] . '" ' . (in_array($sd['arm_subscription_plan_id'], $tempPlans) ? 'selected="selected"' : "" ) . '>' . stripslashes($sd['arm_subscription_plan_name']) . '</option>';
                    }
                }
                $tempOptHtml .= '</select>';
                $tempOptHtml .= '<div class="armclear" style="max-height: 1px;"></div>';
                $tempOptHtml .= '<span class="arm_info_text">(' . __("Leave blank to display all plan's members.", 'ARMember') . ')</span>';
                $tempOptHtml .= '</div>';
                $tempOptHtml .= '</td>';
                $tempOptHtml .= '</tr>';
						$tempOptHtml .= '<tr>';
							$tempOptHtml .= '<th>' . __('Filter Options', 'ARMember') . '</th>';
							$tempOptHtml .= '<td>';
							$tempOptions['searchbox'] = isset($tempOptions['searchbox']) ? $tempOptions['searchbox'] : '0';
							$tempOptions['sortbox'] = isset($tempOptions['sortbox']) ? $tempOptions['sortbox'] : '0';
							$tempOptHtml .= '<div class="arm_temp_switch_wrapper">';
								$tempOptHtml .= '<div class="armswitch arm_global_setting_switch"><input type="checkbox" id="arm_template_searchbox" value="1" class="armswitch_input" name="template_options[searchbox]" ' . (checked($tempOptions['searchbox'], '1', false)) . '/><label for="arm_template_searchbox" class="armswitch_label"></label></div>';
								$tempOptHtml .= '<label for="arm_template_searchbox" class="arm_temp_form_label">' . __('Display Search Box?', 'ARMember') . '</label>';
							$tempOptHtml .= '</div>';
							$tempOptHtml .= '<div class="arm_temp_switch_wrapper" class="arm_temp_form_label">';
								$tempOptHtml .= '<div class="armswitch arm_global_setting_switch"><input type="checkbox" id="arm_template_sortbox" value="1" class="armswitch_input" name="template_options[sortbox]" ' . (checked($tempOptions['sortbox'], '1', false)) . '/><label for="arm_template_sortbox" class="armswitch_label"></label></div>';
								$tempOptHtml .= '<label for="arm_template_sortbox" class="arm_temp_form_label">' . __('Display Sorting Options?', 'ARMember') . '</label>';
							$tempOptHtml .= '</div>';
							$tempOptHtml .= '</td>';
						$tempOptHtml .= '</tr>';
                        $tempOptHtml .= '<tr>';
						$tempOptHtml .= '<th>' . __('No. Of Members Per Page', 'ARMember') . '</th>';
							$tempOptHtml .= '<td>';
							$tempOptHtml .= '<div style="width: auto;margin: 5px 0;">';
								$tempOptions['per_page_users'] = isset($tempOptions['per_page_users']) ? $tempOptions['per_page_users'] : 10;
								$tempOptHtml .= '<input type="TEXT" name="template_options[per_page_users]" value="'.$tempOptions['per_page_users'].'" id="arm_temp_per_page_users" onkeydown="javascript:return checkNumber(event)" style="width:70px;">';
                            $tempOptHtml .= '</div>';
							$tempOptHtml .= '</td>';
						$tempOptHtml .= '</tr>';
						$tempOptHtml .= '<tr>';
						$tempOptHtml .= '<th>' . __('Pagination Style', 'ARMember') . '</th>';
							$tempOptHtml .= '<td>';
							$tempOptHtml .= '<div style="width: auto;margin: 5px 0;">';
								$tempOptions['pagination'] = isset($tempOptions['pagination']) ? $tempOptions['pagination'] : 'numeric';
								$tempOptHtml .= '<input type="radio" name="template_options[pagination]" value="numeric" id="arm_template_pagination_numeric" class="arm_iradio" ' . ($tempOptions['pagination'] == 'numeric' ? 'checked="checked"' : '') . '><label for="arm_template_pagination_numeric" class="arm_temp_form_label">' . __('Numeric', 'ARMember') . '</label>';
								$tempOptHtml .= '<input type="radio" name="template_options[pagination]" value="infinite" id="arm_template_pagination_infinite" class="arm_iradio" ' . ($tempOptions['pagination'] == 'infinite' ? 'checked="checked"' : '') . '><label for="arm_template_pagination_infinite" class="arm_temp_form_label">' . __('Load More Link', 'ARMember') . '</label>';
							$tempOptHtml .= '</div>';
							$tempOptHtml .= '</td>';
						$tempOptHtml .= '</tr>';
                                                
                                                
                                                 
                                        }
                    $tempOptHtml .= '<tr>';
                        $tempOptHtml .= '<th>' . __('Social Profile Fields', 'ARMember') . '</th>';
                        $tempOptHtml .= '<td>';
            $tempOptHtml .= '<div class="arm_profile_fields_selection_wrapper arm_social_profile_fields_wrap">';
                        $socialProfileFields = $arm_member_forms->arm_social_profile_field_types();
                        $activeSPF = array();
                        $orderedFields = array();
                        if (!empty($tempOptions['arm_social_fields'])) {
                           foreach($tempOptions['arm_social_fields'] as $fieldK) {
                               if (isset($socialProfileFields[$fieldK])) {
                                    $activeSPF[$fieldK] = $socialProfileFields[$fieldK];
                                    unset($socialProfileFields[$fieldK]);
                               }
                           }
                        }
                        $activeSPF = $activeSPF + $socialProfileFields;
                        if (!empty($activeSPF)) {
                            $tempOptHtml .='<div class="social_profile_fields"><div class="arm_social_profile_fields_list_wrapper">';
                            foreach ($activeSPF as $spfKey => $spfLabel):
                                $tempOptHtml .=	'<div class="arm_social_profile_field_item">';
                                    $tempOptHtml .= '<input type="checkbox" class="arm_icheckbox arm_spf_active_checkbox" value="'. $spfKey .'" name="template_options[arm_social_fields]['.$spfKey .']" id="arm_spf_'.$spfKey.'_status" '. ($val = (in_array($spfKey, $tempOptions['arm_social_fields'])) ? 'checked="checked"' : '') .'>';
                                $tempOptHtml .=	'<label for="arm_spf_'.$spfKey.'_status">'.$spfLabel.'</label>';
                                $tempOptHtml .=	'</div>';
                            endforeach;
                            $tempOptHtml .='</div></div>';
                        }
                        $tempOptHtml .= '</div>';
                        $tempOptHtml .= '</td>';
                    $tempOptHtml .= '</tr>';
					$tempOptHtml .= '<tr>';
						$tempOptHtml .= '<th>' . __('Color Scheme', 'ARMember') . '</th>';
						$tempOptHtml .= '<td>';
							$tempCS = ((!empty($tempOptions['color_scheme'])) ? $tempOptions['color_scheme'] : 'blue');
							$tempOptHtml .= '<div class="c_schemes" style="padding-left: 5px;">';
								foreach ($tempColorSchemes as $color => $color_opt) {
									$tempOptHtml .= '<label class="arm_temp_color_scheme_block arm_temp_color_scheme_block_'.$color.' '.(($tempCS == $color) ? 'arm_color_box_active' : '').'">';
									$tempOptHtml .= '<span style="background-color:'.$color_opt['button_color'].'"></span>';
									$tempOptHtml .= '<span style="background-color:'.$color_opt['tab_bg_color'].'"></span>';
									$tempOptHtml .= '<input type="radio" id="arm_temp_color_radio_'.$color.'" name="template_options[color_scheme]" value="'.$color.'" class="arm_temp_color_radio" '.checked($tempCS, $color, false).' data-type="'.$tempType.'"/>';
									$tempOptHtml .= '</label>';
								}
								$tempOptHtml .= '<label class="arm_temp_color_scheme_block arm_temp_color_scheme_block_custom '.(($tempCS == 'custom') ? 'arm_color_box_active' : '').'">';
								$tempOptHtml .= '<input type="radio" id="arm_temp_color_radio_custom" name="template_options[color_scheme]" value="custom" class="arm_temp_color_radio" '.checked($tempCS, 'custom', false).' data-type="'.$tempType.'"/>';
								$tempOptHtml .= '</label>';
							$tempOptHtml .= '</div>';
							$tempOptHtml .= '<div class="armclear" style="height: 1px;"></div>';
							$tempOptHtml .= '<div class="arm_temp_color_options" id="arm_temp_color_options" style="'.(($tempCS == 'custom') ? '' : 'display:none;').'">';
								foreach ($colorOptions as $key => $title) {
									$preVal = ((!empty($tempOptions[$key])) ? $tempOptions[$key] : '');
									$preVal = (empty($preVal) && isset($tempColorSchemes[$tempCS][$key])) ? $tempColorSchemes[$tempCS][$key] : $preVal;
									if ($key == 'box_bg_color' && $tempSlug != 'directorytemplate3') {
										continue;
									}
									$tempOptHtml .= '<div class="arm_pdtemp_color_opts">';
										$tempOptHtml .= '<span class="arm_temp_form_label">' . $title . '</span>';
										$tempOptHtml .= '<input type="text" name="template_options['.$key.']" id="arm_'.$key.'" class="arm_colorpicker" value="'.$preVal.'">';
									$tempOptHtml .= '</div>';
								}
							$tempOptHtml .= '</div>';
						$tempOptHtml .= '</td>';
					$tempOptHtml .= '</tr>';
					$tempOptHtml .= '<tr>';
						$tempOptHtml .= '<th>' . __('Font Settings', 'ARMember') . '</th>';
						$tempOptHtml .= '<td>';
						foreach ($fontOptions as $key => $title) {
							$fontVal = ((!empty($tempOptions[$key])) ? $tempOptions[$key] : array());
							$font_bold = (isset($fontVal['font_bold']) && $fontVal['font_bold'] == '1') ? 1 : 0;
							$font_italic = (isset($fontVal['font_italic']) && $fontVal['font_italic'] == '1') ? 1 : 0;
							$font_decoration = (isset($fontVal['font_decoration'])) ? $fontVal['font_decoration'] : '';
							$tempOptHtml .= '<div class="arm_temp_font_settings_wrapper">';
								$tempOptHtml .= '<label class="arm_temp_font_setting_label arm_temp_form_label">'.$title.'</label>';

								$tempOptHtml .= '<input type="hidden" id="arm_temp_font_family_'.$key.'" name="template_options['.$key.'][font_family]" value="' . ((!empty($fontVal['font_family'])) ? $fontVal['font_family'] : 'Helvetica') . '"/>';
								$tempOptHtml .= '<dl class="arm_selectbox column_level_dd">';
									$tempOptHtml .= '<dt style="width:160px;"><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"  /><i class="armfa armfa-caret-down armfa-lg"></i></dt>';
									$tempOptHtml .= '<dd><ul data-id="arm_temp_font_family_'.$key.'">';
										$tempOptHtml .= $arm_member_forms->arm_fonts_list();
									$tempOptHtml .= '</ul></dd>';
								$tempOptHtml .= '</dl>';
                                if ($key == 'content_font' && empty($fontVal['font_size'])) {
                                    $fontVal['font_size'] = '16';
                                }
								$tempOptHtml .= '<input type="hidden" id="arm_temp_font_size_'.$key.'" name="template_options['.$key.'][font_size]" value="' . (!empty($fontVal['font_size']) ? $fontVal['font_size'] : '14') . '"/>';
								$tempOptHtml .= '<dl class="arm_selectbox column_level_dd">';
									$tempOptHtml .= '<dt style="width:70px;"><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"  /><i class="armfa armfa-caret-down armfa-lg"></i></dt>';
									$tempOptHtml .= '<dd><ul data-id="arm_temp_font_size_'.$key.'">';
										for ($i = 8; $i < 41; $i++) {
											$tempOptHtml .= '<li data-label="' . $i . ' px" data-value="' . $i . '">' . $i . ' px</li>';
										}
									$tempOptHtml .= '</ul></dd>';
								$tempOptHtml .= '</dl>';
								$tempOptHtml .= '<div class="arm_font_style_options arm_template_font_style_options">';
									$tempOptHtml .= '<label class="arm_font_style_label '.(($font_bold == '1') ? 'arm_style_active' : '').'" data-value="bold" data-field="arm_temp_font_bold_'.$key.'"><i class="armfa armfa-bold"></i></label>';
									$tempOptHtml .= '<input type="hidden" name="template_options['.$key.'][font_bold]" id="arm_temp_font_bold_'.$key.'" class="arm_temp_font_bold_'.$key.'" value="'.$font_bold.'" />';
									$tempOptHtml .= '<label class="arm_font_style_label '.(($font_italic == '1') ? 'arm_style_active' : '').'" data-value="italic" data-field="arm_temp_font_italic_'.$key.'"><i class="armfa armfa-italic"></i></label>';
									$tempOptHtml .= '<input type="hidden" name="template_options['.$key.'][font_italic]" id="arm_temp_font_italic_'.$key.'" class="arm_temp_font_italic_'.$key.'" value="'.$font_italic.'" />';

									$tempOptHtml .= '<label class="arm_font_style_label arm_decoration_label '.(($font_decoration=='underline')? 'arm_style_active' : '').'" data-value="underline" data-field="arm_temp_font_decoration_'.$key.'"><i class="armfa armfa-underline"></i></label>';
									$tempOptHtml .= '<label class="arm_font_style_label arm_decoration_label '.(($font_decoration=='line-through')? 'arm_style_active' : '').'" data-value="line-through" data-field="arm_temp_font_decoration_'.$key.'"><i class="armfa armfa-strikethrough"></i></label>';
									$tempOptHtml .= '<input type="hidden" name="template_options['.$key.'][font_decoration]" id="arm_temp_font_decoration_'.$key.'" class="arm_temp_font_decoration_'.$key.'" value="'.$font_decoration.'" />';
								$tempOptHtml .= '</div>';
							$tempOptHtml .= '</div>';
						}
						$tempOptHtml .= '</td>';
					$tempOptHtml .= '</tr>';
					if ($tempType == 'profile') {
						$tempOptHtml .= '<tr>';
							$tempOptHtml .= '<th>' . __('Default Cover', 'ARMember') . ' <i class="arm_helptip_icon armfa armfa-question-circle" title="'.__('Image size should be approx 900x300.', 'ARMember').'"></i></th>';
							$tempOptHtml .= '<td>';
								$defaultCover = (!empty($tempOptions['default_cover'])) ? $tempOptions['default_cover'] : '';
								$display_file = !empty($defaultCover) && file_exists(MEMBERSHIPLITE_UPLOAD_DIR.'/'.basename($defaultCover)) ? true : false;
								$tempOptHtml .= '<div class="arm_default_cover_upload_container armFileUploadWrapper">';
									$tempOptHtml .= '<div class="armFileUploadContainer" style="'.(($display_file) ? 'display:none;': '').'">';
										$tempOptHtml .= '<div class="armFileUpload-icon"></div>'.__('Upload', 'ARMember');
                $tempOptHtml .= '<input id="armTempEditFileUpload" class="armFileUpload arm_default_cover_image_url" name="template_options[default_cover]" type="file" value="' . $defaultCover . '" accept=".jpg,.jpeg,.png,.bmp" data-file_size="5"/>';
									$tempOptHtml .= '</div>';
									$tempOptHtml .= '<div class="armFileRemoveContainer" style="'.(($display_file) ? 'display:inline-block;': '').'"><div class="armFileRemove-icon"></div>'.__('Remove', 'ARMember').'</div>';
										$tempOptHtml .= '<div class="arm_old_uploaded_file">';
										if ($display_file) {
                            if(file_exists(strstr($defaultCover, "//"))){
                                $defaultCover =strstr($defaultCover, "//");
                            }else if(file_exists($defaultCover)){
                               $defaultCover = $defaultCover;
                            }else{
                                $defaultCover = $defaultCover;
                            }
											$tempOptHtml .= '<img alt="" src="' . ($defaultCover) . '" height="100px"/>';
										}
										$tempOptHtml .= '</div>';
									$tempOptHtml .= '<div class="armFileUploadProgressBar" style="display: none;"><div class="armbar" style="width:0%;"></div></div>';
									$tempOptHtml .= '<div class="armFileUploadProgressInfo"></div>';
									$tempOptHtml .= '<div class="armFileMessages" id="armFileUploadMsg"></div>';
									$tempOptHtml .= '<input class="arm_file_url arm_default_cover_image_url" type="hidden" name="template_options[default_cover]" value="' . $defaultCover . '" data-file_type="directory_cover">';
								$tempOptHtml .= '</div>';
							$tempOptHtml .= '</td>';
						$tempOptHtml .= '</tr>';
					}
					
				$tempOptHtml .= '</table>';
			$tempOptHtml .= '</div>';
			
			$tempOptHtml .= '<script type="text/javascript" src="'.MEMBERSHIPLITE_URL . '/js/arm_file_upload_js.js"></script>';
			return $tempOptHtml;
		}
                
                
                
                function arm_profile_template_options($tempType = 'profile')
		{
			global $wpdb, $ARMember, $arm_member_forms, $arm_subscription_plans;
                        if (!function_exists('is_plugin_active')) {
                            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                        }                        
			$tempSlug = 'profiletemplate3';
			$tempOptions =array(
				'plans' => array(),
                'per_page_users' => 10,
				'pagination' => 'numeric',
                            'show_admin_users' => 1,
				'show_badges' => 1,
                                'show_joining' => 1,
                                'redirect_to_author' => '',
                                'redirect_to_buddypress_profile' => '',
                                'hide_empty_profile_fields' => '',
				'color_scheme' => '',
				'title_color' => '',
				'subtitle_color' => '',
				'border_color' => '',
				'button_color' => '',
				'button_font_color' => '',
				'tab_bg_color' => '',
				'tab_link_color' => '',
				'tab_link_hover_color' => '',
				'tab_link_bg_color' => '',
				'tab_link_hover_bg_color' => '',
				'link_color' => '',
				'link_hover_color' => '',
				'content_font_color' => '',
				'box_bg_color' => '',
				'title_font' => array(),
				'subtitle_font' => array(),
				'button_font' => array(),
				'tab_link_font' => array(),
				'content_font' => array(),
				'searchbox' => '',
				'sortbox' => '',
				'grouping' => '',
				'profile_fields' => array(),
                                'labels' => array(),
				'arm_social_fields' => array(),
				'default_cover' => '',
				'custom_css' => '',
			);
			
            $defaultTemplates = $this->arm_default_member_templates();
			$tempColorSchemes = $this->getTemplateColorSchemes();
			if ($tempType == 'profile') {
				$colorOptions = array(
					'title_color' => __('Title Color', 'ARMember'),
					'subtitle_color' => __('Sub Title Color', 'ARMember'),
					'border_color' => __('Border Color', 'ARMember'),
					'content_font_color' => __('Body Content Color', 'ARMember'),
				);
				$fontOptions = array(
					'title_font' => __('Title Font', 'ARMember'),
					'subtitle_font' => __('Sub Title Font', 'ARMember'),
					'content_font' => __('Content Font', 'ARMember'),
				);
			}
			$tempOptHtml = '';
			$tempOptHtml .= '<div class="arm_temp_option_wrapper">';
				$tempOptHtml .= '<table class="arm_table_label_on_top">';
					if ($tempType == 'profile')
					{
                        $tempOptHtml .= '<tr>';
                            $tempOptHtml .= '<th>'.__('Select Template','ARMember').'</th>';
                            $tempOptHtml .= '<td>';
                            $tempOptHtml .= '<div class="arm_profile_template_selection">';
                            if (!empty($defaultTemplates)) {
                                foreach($defaultTemplates as $temp) {
                                    if ($temp['arm_type'] == 'profile') {
                                        $checked = ($temp['arm_slug'] == $tempSlug) ? 'checked="checked"' : '';
                                        $activeClass = ($temp['arm_slug'] == $tempSlug) ? 'arm_active_temp' : '';
                                        $tempOptHtml .= '<label class="arm_tempalte_type_box arm_temp_'.$temp['arm_type'].'_options_add '.$activeClass.'" data-type="'.$temp['arm_type'].'" for="arm_temp_type_'.$temp['arm_slug'].'_label" id="arm_tempalte_type_box">';
                                        $tempOptHtml .= '<input type="radio" name="profile_slug" value="' . $temp['arm_slug'] . '" id="arm_temp_type_' . $temp['arm_slug'] . '_label" class="arm_temp_profile_radio '.$temp['arm_type'].'" data-type="'.$temp['arm_type'].'" '.$checked.'>';
                                        $tempOptHtml .= '<img alt="" src="'.MEMBERSHIPLITE_VIEWS_URL. '/templates/' . $temp['arm_slug'] . '.png"/>';
                                        $tempOptHtml .= '<span class="arm_temp_selected_text">'.__('Selected', 'ARMember').'</span>';
                                        $tempOptHtml .= '</label>';
                                        
                                    }
                                }
                            }
                            $tempOptHtml .= '</div>';
                            $tempOptHtml .= '</td>';
						$tempOptHtml .= '</tr>';
					}
            
            $tempOptHtml .= '</table>';
            $tempOptHtml .= '</div>';

            $tempOptHtml .= '<script type="text/javascript" src="' . MEMBERSHIPLITE_URL . '/js/arm_admin_file_upload_js.js"></script>';
            return $tempOptHtml;
        }

        function arm_template_preview_func() {
            global $wpdb, $ARMember, $arm_capabilities_global;

            if (isset($_POST['action']) && $_POST['action'] == 'arm_template_preview') {
                $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_member_templates'], '1');
                $temp_id = intval($_POST['temp_id']);
                $temp_type = sanitize_text_field($_POST['temp_type']);
                $extraVars = '';
                if (!empty($temp_id) && !empty($temp_type)) {
                    if (isset($_POST['template_options'])) {

                        $tempSlug = $_POST['arm_slug'];
                        $tempData = array(
                            'arm_type' => $temp_type,
                            'arm_slug' => $tempSlug,
                            'arm_options' => $_POST['template_options'],
                        );
                        $tempData = maybe_serialize($tempData);
                        $extraVars .= " temp_data='$tempData'";
                    }
                    ?>
                    <div class="arm_template_preview_popup popup_wrapper" style="width:960px;">
                        <div class="popup_wrapper_inner">
                            <div class="popup_header">
                                <span class="popup_close_btn arm_popup_close_btn arm_template_preview_close_btn"></span>
                                <div class="arm_responsive_icons">
                                    <a href="javascript:void(0)" class="arm_responsive_link arm_desktop active" data-type="desktop"><i class="armfa armfa-2x armfa-desktop"></i></a>
                                    <a href="javascript:void(0)" class="arm_responsive_link arm_tablet" data-type="tablet"><i class="armfa armfa-2x armfa-tablet"></i></a>
                                    <a href="javascript:void(0)" class="arm_responsive_link arm_mobile" data-type="mobile"><i class="armfa armfa-2x armfa-mobile"></i></a>
                                </div>
                            </div>
                            <div class="popup_content_text">
                    <?php
                    switch ($temp_type) {
                        case 'profile':
                            echo do_shortcode("[arm_template type='profile' id='$temp_id' sample='true' is_preview='1' $extraVars]");
                            break;
                        case 'directory':
                            echo do_shortcode("[arm_template type='directory' id='$temp_id' sample='true' is_preview='1' $extraVars]");
                            break;
                        default:
                            break;
                    }
                    ?>
                                <link rel="stylesheet" type="text/css" href="<?php echo MEMBERSHIPLITE_URL; ?>/css/arm_front.css"/>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            }
            exit;
        }

        function getTemplateColorSchemes() {
            global $wpdb, $ARMember;
            $color_schemes = array(
                'blue' => array(
                    "main_color" => '#0c7cd5',
                    "title_color" => '#0c7cd5',
                    "subtitle_color" => '#575d70',
                    "border_color" => '#0c7cd5',
                    "button_color" => '#0c7cd5',
                    "button_font_color" => '#ffffff',
                    "tab_bg_color" => '#0c7cd5',
                    "tab_link_color" => '#ffffff',
                    "tab_link_hover_color" => '#0c7cd5',
                    'tab_link_bg_color' => '#0c7cd5',
                    'tab_link_hover_bg_color' => '#ffffff',
                    "link_color" => '#0c7cd5',
                    "link_hover_color" => '#41474c',
                    "content_font_color" => '#616175',
                    "box_bg_color" => '#F4F4F4',
                ),
                'red' => array(
                    "main_color" => '#fc5468',
                    "title_color" => '#fc5468',
                    "subtitle_color" => '#635859',
                    "border_color" => '#fc5468',
                    "button_color" => '#fc5468',
                    "button_font_color" => '#FFFFFF',
                    "tab_bg_color" => '#5a52a7',
                    "tab_link_color" => '#a9a9e5',
                    "tab_link_hover_color" => '#ffffff',
                    'tab_link_bg_color' => '#5a52a7',
                    'tab_link_hover_bg_color' => '#a9a9e5',
                    "link_color" => '#fc5468',
                    "link_hover_color" => '#5a52a7',
                    "content_font_color" => '#616175',
                    "box_bg_color" => '#F4F4F4',
                ),
                'orange' => array(
                    "main_color" => '#ff7612',
                    "title_color" => '#ff7612',
                    "subtitle_color" => '#615d59',
                    "border_color" => '#ff7612',
                    "button_color" => '#ff7612',
                    "button_font_color" => '#FFFFFF',
                    "tab_bg_color" => '#312f2d',
                    "tab_link_color" => '#aa9c91',
                    "tab_link_hover_color" => '#ff7612',
                    'tab_link_bg_color' => '#312f2d',
                    'tab_link_hover_bg_color' => '#ffffff',
                    "link_color" => '#ff7612',
                    "link_hover_color" => '#312f2d',
                    "content_font_color" => '#616175',
                    "box_bg_color" => '#F4F4F4',
                ),
                'light_green' => array(
                    "main_color" => '#17c9ab',
                    "title_color" => '#1e1e28',
                    "subtitle_color" => '#464d4c',
                    "border_color" => '#17c9ab',
                    "button_color" => '#17c9ab',
                    "button_font_color" => '#FFFFFF',
                    "tab_bg_color" => '#15b69b',
                    "tab_link_color" => '#016554',
                    "tab_link_hover_color" => '#FFFFFF',
                    'tab_link_bg_color' => '#15b69b',
                    'tab_link_hover_bg_color' => '#016554',
                    "link_color" => '#17c9ab',
                    "link_hover_color" => '#1e1e28',
                    "content_font_color" => '#616175',
                    "box_bg_color" => '#F4F4F4',
                ),
                'purple' => array(
                    "main_color" => '#7955d3',
                    "title_color" => '#191d2e',
                    "subtitle_color" => '#514d5a',
                    "border_color" => '#7955d3',
                    "button_color" => '#7955d3',
                    "button_font_color" => '#FFFFFF',
                    "tab_bg_color" => '#4f446c',
                    "tab_link_color" => '#a695d1',
                    "tab_link_hover_color" => '#ffffff',
                    'tab_link_bg_color' => '#4f446c',
                    'tab_link_hover_bg_color' => '#a695d1',
                    "link_color" => '#7955d3',
                    "link_hover_color" => '#191d2e',
                    "content_font_color" => '#616175',
                    "box_bg_color" => '#F4F4F4',
                ),
                'green' => array(
                    "main_color" => '#8ebd7e',
                    "title_color" => '#1e1e28',
                    "subtitle_color" => '#71776f',
                    "border_color" => '#8ebd7e',
                    "button_color" => '#8ebd7e',
                    "button_font_color" => '#FFFFFF',
                    "tab_bg_color" => '#e9eae9',
                    "tab_link_color" => '#8b8b8b',
                    "tab_link_hover_color" => '#303030',
                    'tab_link_bg_color' => '#e9eae9',
                    'tab_link_hover_bg_color' => '#ffffff',
                    "link_color" => '#7dbc68',
                    "link_hover_color" => '#4b4b5d',
                    "content_font_color" => '#616175',
                    "box_bg_color" => '#F4F4F4',
                ),
                'light_blue' => array(
                    "main_color" => '#32c5fc',
                    "title_color" => '#32c5fc',
                    "subtitle_color" => '#6b7275',
                    "border_color" => '#32c5fc',
                    "button_color" => '#32c5fc',
                    "button_font_color" => '#FFFFFF',
                    "tab_bg_color" => '#ecf3f9',
                    "tab_link_color" => '#73808b',
                    "tab_link_hover_color" => '#1f1f1f',
                    'tab_link_bg_color' => '#ecf3f9',
                    'tab_link_hover_bg_color' => '#ffffff',
                    "link_color" => '#32c5fc',
                    "link_hover_color" => '#1e1e28',
                    "content_font_color" => '#616175',
                    "box_bg_color" => '#F4F4F4',
                ),
            );
            return $color_schemes;
        }

        function getTemplateColorSchemes1() {

			global $wpdb, $ARMember;
			$color_schemes = array('directorytemplate1' => array(
				'blue' => array(
					"main_color" => '#0c7cd5',
					"title_color" => '#0c7cd5',
					"subtitle_color" => '#575d70',
					"border_color" => '#0c7cd5',
					"button_color" => '#0c7cd5',
					"button_font_color" => '#ffffff',
					"tab_bg_color" => '#0c7cd5',
					"tab_link_color" => '#ffffff',
					"tab_link_hover_color" => '#0c7cd5',
					'tab_link_bg_color' => '#0c7cd5',
					'tab_link_hover_bg_color' => '#ffffff',
					"link_color" => '#0c7cd5',
					"link_hover_color" => '#41474c',
					"content_font_color" => '#616175',
					"box_bg_color" => '#F4F4F4',
				),
				'red' => array(
					"main_color" => '#fc5468',
					"title_color" => '#fc5468',
					"subtitle_color" => '#635859',
					"border_color" => '#fc5468',
					"button_color" => '#fc5468',
					"button_font_color" => '#FFFFFF',
					"tab_bg_color" => '#5a52a7',
					"tab_link_color" => '#a9a9e5',
					"tab_link_hover_color" => '#ffffff',
					'tab_link_bg_color' => '#5a52a7',
					'tab_link_hover_bg_color' => '#a9a9e5',
					"link_color" => '#fc5468',
					"link_hover_color" => '#5a52a7',
					"content_font_color" => '#616175',
					"box_bg_color" => '#F4F4F4',
				),
				'orange' => array(
					"main_color" => '#ff7612',
					"title_color" => '#ff7612',
					"subtitle_color" => '#615d59',
					"border_color" => '#ff7612',
					"button_color" => '#ff7612',
					"button_font_color" => '#FFFFFF',
					"tab_bg_color" => '#312f2d',
					"tab_link_color" => '#aa9c91',
					"tab_link_hover_color" => '#ff7612',
					'tab_link_bg_color' => '#312f2d',
					'tab_link_hover_bg_color' => '#ffffff',
					"link_color" => '#ff7612',
					"link_hover_color" => '#312f2d',
					"content_font_color" => '#616175',
					"box_bg_color" => '#F4F4F4',
				),
				'light_green' => array(
					"main_color" => '#17c9ab',
					"title_color" => '#1e1e28',
					"subtitle_color" => '#464d4c',
					"border_color" => '#17c9ab',
					"button_color" => '#17c9ab',
					"button_font_color" => '#FFFFFF',
					"tab_bg_color" => '#15b69b',
					"tab_link_color" => '#016554',
					"tab_link_hover_color" => '#FFFFFF',
					'tab_link_bg_color' => '#15b69b',
					'tab_link_hover_bg_color' => '#016554',
					"link_color" => '#17c9ab',
					"link_hover_color" => '#1e1e28',
					"content_font_color" => '#616175',
					"box_bg_color" => '#F4F4F4',
				),
				'purple' => array(
					"main_color" => '#7955d3',
					"title_color" => '#191d2e',
					"subtitle_color" => '#514d5a',
					"border_color" => '#7955d3',
					"button_color" => '#7955d3',
					"button_font_color" => '#FFFFFF',
					"tab_bg_color" => '#4f446c',
					"tab_link_color" => '#a695d1',
					"tab_link_hover_color" => '#ffffff',
					'tab_link_bg_color' => '#4f446c',
					'tab_link_hover_bg_color' => '#a695d1',
					"link_color" => '#7955d3',
					"link_hover_color" => '#191d2e',
					"content_font_color" => '#616175',
					"box_bg_color" => '#F4F4F4',
				),
				'green' => array(
					"main_color" => '#8ebd7e',
					"title_color" => '#1e1e28',
					"subtitle_color" => '#71776f',
					"border_color" => '#8ebd7e',
					"button_color" => '#8ebd7e',
					"button_font_color" => '#FFFFFF',
					"tab_bg_color" => '#e9eae9',
					"tab_link_color" => '#8b8b8b',
					"tab_link_hover_color" => '#303030',
					'tab_link_bg_color' => '#e9eae9',
					'tab_link_hover_bg_color' => '#ffffff',
					"link_color" => '#7dbc68',
					"link_hover_color" => '#4b4b5d',
					"content_font_color" => '#616175',
					"box_bg_color" => '#F4F4F4',
				),
				'light_blue' => array(
					"main_color" => '#32c5fc',
					"title_color" => '#32c5fc',
					"subtitle_color" => '#6b7275',
					"border_color" => '#32c5fc',
					"button_color" => '#32c5fc',
					"button_font_color" => '#FFFFFF',
					"tab_bg_color" => '#ecf3f9',
					"tab_link_color" => '#73808b',
					"tab_link_hover_color" => '#1f1f1f',
					'tab_link_bg_color' => '#ecf3f9',
					'tab_link_hover_bg_color' => '#ffffff',
					"link_color" => '#32c5fc',
					"link_hover_color" => '#1e1e28',
					"content_font_color" => '#616175',
					"box_bg_color" => '#F4F4F4',
				),
    			),
                'directorytemplate2' => array(
    				'blue' => array(
    					"main_color" => '#0c7cd5',
    					"title_color" => '#0c7cd5',
    					"subtitle_color" => '#575d70',
    					"border_color" => '#0c7cd5',
    					"button_color" => '#0c7cd5',
    					"button_font_color" => '#ffffff',
    					"tab_bg_color" => '#0c7cd5',
    					"tab_link_color" => '#ffffff',
    					"tab_link_hover_color" => '#0c7cd5',
    					'tab_link_bg_color' => '#0c7cd5',
    					'tab_link_hover_bg_color' => '#ffffff',
    					"link_color" => '#0c7cd5',
    					"link_hover_color" => '#41474c',
    					"content_font_color" => '#616175',
    					"box_bg_color" => '#F4F4F4',
    				),
    				'red' => array(
    					"main_color" => '#fc5468',
    					"title_color" => '#fc5468',
    					"subtitle_color" => '#635859',
    					"border_color" => '#fc5468',
    					"button_color" => '#fc5468',
    					"button_font_color" => '#FFFFFF',
    					"tab_bg_color" => '#5a52a7',
    					"tab_link_color" => '#a9a9e5',
    					"tab_link_hover_color" => '#ffffff',
    					'tab_link_bg_color' => '#5a52a7',
    					'tab_link_hover_bg_color' => '#a9a9e5',
    					"link_color" => '#fc5468',
    					"link_hover_color" => '#5a52a7',
    					"content_font_color" => '#616175',
    					"box_bg_color" => '#F4F4F4',
    				),
    				'orange' => array(
    					"main_color" => '#ff7612',
    					"title_color" => '#ff7612',
    					"subtitle_color" => '#615d59',
    					"border_color" => '#ff7612',
    					"button_color" => '#ff7612',
    					"button_font_color" => '#FFFFFF',
    					"tab_bg_color" => '#312f2d',
    					"tab_link_color" => '#aa9c91',
    					"tab_link_hover_color" => '#ff7612',
    					'tab_link_bg_color' => '#312f2d',
    					'tab_link_hover_bg_color' => '#ffffff',
    					"link_color" => '#ff7612',
    					"link_hover_color" => '#312f2d',
    					"content_font_color" => '#616175',
    					"box_bg_color" => '#F4F4F4',
    				),
    				'light_green' => array(
    					"main_color" => '#17c9ab',
    					"title_color" => '#1e1e28',
    					"subtitle_color" => '#464d4c',
    					"border_color" => '#17c9ab',
    					"button_color" => '#17c9ab',
    					"button_font_color" => '#FFFFFF',
    					"tab_bg_color" => '#15b69b',
    					"tab_link_color" => '#016554',
    					"tab_link_hover_color" => '#FFFFFF',
    					'tab_link_bg_color' => '#15b69b',
    					'tab_link_hover_bg_color' => '#016554',
    					"link_color" => '#17c9ab',
    					"link_hover_color" => '#1e1e28',
    					"content_font_color" => '#616175',
    					"box_bg_color" => '#F4F4F4',
    				),
    				'purple' => array(
    					"main_color" => '#7955d3',
    					"title_color" => '#191d2e',
    					"subtitle_color" => '#514d5a',
    					"border_color" => '#7955d3',
    					"button_color" => '#7955d3',
    					"button_font_color" => '#FFFFFF',
    					"tab_bg_color" => '#4f446c',
    					"tab_link_color" => '#a695d1',
    					"tab_link_hover_color" => '#ffffff',
    					'tab_link_bg_color' => '#4f446c',
    					'tab_link_hover_bg_color' => '#a695d1',
    					"link_color" => '#7955d3',
    					"link_hover_color" => '#191d2e',
    					"content_font_color" => '#616175',
    					"box_bg_color" => '#F4F4F4',
    				),
    				'green' => array(
    					"main_color" => '#8ebd7e',
    					"title_color" => '#1e1e28',
    					"subtitle_color" => '#71776f',
    					"border_color" => '#8ebd7e',
    					"button_color" => '#8ebd7e',
    					"button_font_color" => '#FFFFFF',
    					"tab_bg_color" => '#e9eae9',
    					"tab_link_color" => '#8b8b8b',
    					"tab_link_hover_color" => '#303030',
    					'tab_link_bg_color' => '#e9eae9',
    					'tab_link_hover_bg_color' => '#ffffff',
    					"link_color" => '#7dbc68',
    					"link_hover_color" => '#4b4b5d',
    					"content_font_color" => '#616175',
    					"box_bg_color" => '#F4F4F4',
    				),
    				'light_blue' => array(
    					"main_color" => '#32c5fc',
    					"title_color" => '#32c5fc',
    					"subtitle_color" => '#6b7275',
    					"border_color" => '#32c5fc',
    					"button_color" => '#32c5fc',
    					"button_font_color" => '#FFFFFF',
    					"tab_bg_color" => '#ecf3f9',
    					"tab_link_color" => '#73808b',
    					"tab_link_hover_color" => '#1f1f1f',
    					'tab_link_bg_color' => '#ecf3f9',
    					'tab_link_hover_bg_color' => '#ffffff',
    					"link_color" => '#32c5fc',
    					"link_hover_color" => '#1e1e28',
    					"content_font_color" => '#616175',
    					"box_bg_color" => '#F4F4F4',
    				),
    			),
    			'directorytemplate4' => array(
    				'blue' => array(
    					"main_color" => '#0c7cd5',
    					"title_color" => '#ffffff',
    					"subtitle_color" => '#575d70',
    					"border_color" => '#0c7cd5',
    					"button_color" => '#0c7cd5',
    					"button_font_color" => '#ffffff',
    					"tab_bg_color" => '#0c7cd5',
    					"tab_link_color" => '#ffffff',
    					"tab_link_hover_color" => '#0c7cd5',
    					'tab_link_bg_color' => '#0c7cd5',
    					'tab_link_hover_bg_color' => '#ffffff',
    					"link_color" => '#0c7cd5',
    					"link_hover_color" => '#41474c',
    					"content_font_color" => '#616175',
    					"box_bg_color" => '#F4F4F4',
    				),
    				'red' => array(
    					"main_color" => '#fc5468',
    					"title_color" => '#ffffff',
    					"subtitle_color" => '#635859',
    					"border_color" => '#fc5468',
    					"button_color" => '#fc5468',
    					"button_font_color" => '#FFFFFF',
    					"tab_bg_color" => '#5a52a7',
    					"tab_link_color" => '#a9a9e5',
    					"tab_link_hover_color" => '#ffffff',
    					'tab_link_bg_color' => '#5a52a7',
    					'tab_link_hover_bg_color' => '#a9a9e5',
    					"link_color" => '#fc5468',
    					"link_hover_color" => '#5a52a7',
    					"content_font_color" => '#616175',
    					"box_bg_color" => '#F4F4F4',
    				),
    				'orange' => array(
    					"main_color" => '#ff7612',
    					"title_color" => '#ffffff',
    					"subtitle_color" => '#615d59',
    					"border_color" => '#ff7612',
    					"button_color" => '#ff7612',
    					"button_font_color" => '#FFFFFF',
    					"tab_bg_color" => '#312f2d',
    					"tab_link_color" => '#aa9c91',
    					"tab_link_hover_color" => '#ff7612',
    					'tab_link_bg_color' => '#312f2d',
    					'tab_link_hover_bg_color' => '#ffffff',
    					"link_color" => '#ff7612',
    					"link_hover_color" => '#312f2d',
    					"content_font_color" => '#616175',
    					"box_bg_color" => '#F4F4F4',
    				),
    				'light_green' => array(
    					"main_color" => '#17c9ab',
    					"title_color" => '#ffffff',
    					"subtitle_color" => '#464d4c',
    					"border_color" => '#17c9ab',
    					"button_color" => '#17c9ab',
    					"button_font_color" => '#FFFFFF',
    					"tab_bg_color" => '#15b69b',
    					"tab_link_color" => '#016554',
    					"tab_link_hover_color" => '#FFFFFF',
    					'tab_link_bg_color' => '#15b69b',
    					'tab_link_hover_bg_color' => '#016554',
    					"link_color" => '#17c9ab',
    					"link_hover_color" => '#1e1e28',
    					"content_font_color" => '#616175',
    					"box_bg_color" => '#F4F4F4',
    				),
    				'purple' => array(
    					"main_color" => '#7955d3',
    					"title_color" => '#ffffff',
    					"subtitle_color" => '#514d5a',
    					"border_color" => '#7955d3',
    					"button_color" => '#7955d3',
    					"button_font_color" => '#FFFFFF',
    					"tab_bg_color" => '#4f446c',
    					"tab_link_color" => '#a695d1',
    					"tab_link_hover_color" => '#ffffff',
    					'tab_link_bg_color' => '#4f446c',
    					'tab_link_hover_bg_color' => '#a695d1',
    					"link_color" => '#7955d3',
    					"link_hover_color" => '#191d2e',
    					"content_font_color" => '#616175',
    					"box_bg_color" => '#F4F4F4',
    				),
    				'green' => array(
    					"main_color" => '#8ebd7e',
    					"title_color" => '#ffffff',
    					"subtitle_color" => '#71776f',
    					"border_color" => '#8ebd7e',
    					"button_color" => '#8ebd7e',
    					"button_font_color" => '#FFFFFF',
    					"tab_bg_color" => '#e9eae9',
    					"tab_link_color" => '#8b8b8b',
    					"tab_link_hover_color" => '#303030',
    					'tab_link_bg_color' => '#e9eae9',
    					'tab_link_hover_bg_color' => '#ffffff',
    					"link_color" => '#8ebd7e',
    					"link_hover_color" => '#4b4b5d',
    					"content_font_color" => '#616175',
    					"box_bg_color" => '#F4F4F4',
    				),
    				'light_blue' => array(
    					"main_color" => '#32c5fc',
    					"title_color" => '#ffffff',
    					"subtitle_color" => '#6b7275',
    					"border_color" => '#32c5fc',
    					"button_color" => '#32c5fc',
    					"button_font_color" => '#FFFFFF',
    					"tab_bg_color" => '#ecf3f9',
    					"tab_link_color" => '#73808b',
    					"tab_link_hover_color" => '#1f1f1f',
    					'tab_link_bg_color' => '#ecf3f9',
    					'tab_link_hover_bg_color' => '#ffffff',
    					"link_color" => '#32c5fc',
    					"link_hover_color" => '#1e1e28',
    					"content_font_color" => '#616175',
    					"box_bg_color" => '#F4F4F4',
    				),
    			),
                'directorytemplate3' => array(
    				'blue' => array(
    					"main_color" => '#0c7cd5',
    					"title_color" => '#0c7cd5',
    					"subtitle_color" => '#575d70',
    					"border_color" => '#0c7cd5',
    					"button_color" => '#0c7cd5',
    					"button_font_color" => '#ffffff',
    					"tab_bg_color" => '#0c7cd5',
    					"tab_link_color" => '#ffffff',
    					"tab_link_hover_color" => '#0c7cd5',
    					'tab_link_bg_color' => '#0c7cd5',
    					'tab_link_hover_bg_color' => '#ffffff',
    					"link_color" => '#0c7cd5',
    					"link_hover_color" => '#41474c',
    					"content_font_color" => '#616175',
    					"box_bg_color" => '#F4F4F4',
    				),
    				'red' => array(
    					"main_color" => '#fc5468',
    					"title_color" => '#fc5468',
    					"subtitle_color" => '#635859',
    					"border_color" => '#fc5468',
    					"button_color" => '#fc5468',
    					"button_font_color" => '#FFFFFF',
    					"tab_bg_color" => '#5a52a7',
    					"tab_link_color" => '#a9a9e5',
    					"tab_link_hover_color" => '#ffffff',
    					'tab_link_bg_color' => '#5a52a7',
    					'tab_link_hover_bg_color' => '#a9a9e5',
    					"link_color" => '#fc5468',
    					"link_hover_color" => '#5a52a7',
    					"content_font_color" => '#616175',
    					"box_bg_color" => '#F4F4F4',
    				),
    				'orange' => array(
    					"main_color" => '#ff7612',
    					"title_color" => '#ff7612',
    					"subtitle_color" => '#615d59',
    					"border_color" => '#ff7612',
    					"button_color" => '#ff7612',
    					"button_font_color" => '#FFFFFF',
    					"tab_bg_color" => '#312f2d',
    					"tab_link_color" => '#aa9c91',
    					"tab_link_hover_color" => '#ff7612',
    					'tab_link_bg_color' => '#312f2d',
    					'tab_link_hover_bg_color' => '#ffffff',
    					"link_color" => '#ff7612',
    					"link_hover_color" => '#312f2d',
    					"content_font_color" => '#616175',
    					"box_bg_color" => '#F4F4F4',
    				),
    				'light_green' => array(
    					"main_color" => '#17c9ab',
    					"title_color" => '#1e1e28',
    					"subtitle_color" => '#464d4c',
    					"border_color" => '#17c9ab',
    					"button_color" => '#17c9ab',
    					"button_font_color" => '#FFFFFF',
    					"tab_bg_color" => '#15b69b',
    					"tab_link_color" => '#016554',
    					"tab_link_hover_color" => '#FFFFFF',
    					'tab_link_bg_color' => '#15b69b',
    					'tab_link_hover_bg_color' => '#016554',
    					"link_color" => '#17c9ab',
    					"link_hover_color" => '#1e1e28',
    					"content_font_color" => '#616175',
    					"box_bg_color" => '#F4F4F4',
    				),
    				'purple' => array(
    					"main_color" => '#7955d3',
    					"title_color" => '#191d2e',
    					"subtitle_color" => '#514d5a',
    					"border_color" => '#7955d3',
    					"button_color" => '#7955d3',
    					"button_font_color" => '#FFFFFF',
    					"tab_bg_color" => '#4f446c',
    					"tab_link_color" => '#a695d1',
    					"tab_link_hover_color" => '#ffffff',
    					'tab_link_bg_color' => '#4f446c',
    					'tab_link_hover_bg_color' => '#a695d1',
    					"link_color" => '#7955d3',
    					"link_hover_color" => '#191d2e',
    					"content_font_color" => '#616175',
    					"box_bg_color" => '#F4F4F4',
    				),
    				'green' => array(
    					"main_color" => '#8ebd7e',
    					"title_color" => '#1e1e28',
    					"subtitle_color" => '#71776f',
    					"border_color" => '#8ebd7e',
    					"button_color" => '#8ebd7e',
    					"button_font_color" => '#FFFFFF',
    					"tab_bg_color" => '#e9eae9',
    					"tab_link_color" => '#8b8b8b',
    					"tab_link_hover_color" => '#303030',
    					'tab_link_bg_color' => '#e9eae9',
    					'tab_link_hover_bg_color' => '#ffffff',
    					"link_color" => '#7dbc68',
    					"link_hover_color" => '#4b4b5d',
    					"content_font_color" => '#616175',
    					"box_bg_color" => '#F4F4F4',
    				),
    				'light_blue' => array(
    					"main_color" => '#32c5fc',
    					"title_color" => '#32c5fc',
    					"subtitle_color" => '#6b7275',
    					"border_color" => '#32c5fc',
    					"button_color" => '#32c5fc',
    					"button_font_color" => '#FFFFFF',
    					"tab_bg_color" => '#ecf3f9',
    					"tab_link_color" => '#73808b',
    					"tab_link_hover_color" => '#1f1f1f',
    					'tab_link_bg_color' => '#ecf3f9',
    					'tab_link_hover_bg_color' => '#ffffff',
    					"link_color" => '#32c5fc',
    					"link_hover_color" => '#1e1e28',
    					"content_font_color" => '#616175',
    					"box_bg_color" => '#F4F4F4',
    				),
    			),
                'directorytemplate5' => array(
                    'blue' => array(
                        "main_color" => '#0c7cd5',
                        "title_color" => '#0c7cd5',
                        "subtitle_color" => '#575d70',
                        "border_color" => '#0c7cd5',
                        "button_color" => '#0c7cd5',
                        "button_font_color" => '#ffffff',
                        "tab_bg_color" => '#0c7cd5',
                        "tab_link_color" => '#ffffff',
                        "tab_link_hover_color" => '#0c7cd5',
                        'tab_link_bg_color' => '#0c7cd5',
                        'tab_link_hover_bg_color' => '#ffffff',
                        "link_color" => '#0c7cd5',
                        "link_hover_color" => '#41474c',
                        "content_font_color" => '#616175',
                        "box_bg_color" => '#F4F4F4',
                    ),
                    'red' => array(
                        "main_color" => '#fc5468',
                        "title_color" => '#fc5468',
                        "subtitle_color" => '#635859',
                        "border_color" => '#fc5468',
                        "button_color" => '#fc5468',
                        "button_font_color" => '#FFFFFF',
                        "tab_bg_color" => '#5a52a7',
                        "tab_link_color" => '#a9a9e5',
                        "tab_link_hover_color" => '#ffffff',
                        'tab_link_bg_color' => '#5a52a7',
                        'tab_link_hover_bg_color' => '#a9a9e5',
                        "link_color" => '#fc5468',
                        "link_hover_color" => '#5a52a7',
                        "content_font_color" => '#616175',
                        "box_bg_color" => '#F4F4F4',
                    ),
                    'orange' => array(
                        "main_color" => '#ff7612',
                        "title_color" => '#ff7612',
                        "subtitle_color" => '#615d59',
                        "border_color" => '#ff7612',
                        "button_color" => '#ff7612',
                        "button_font_color" => '#FFFFFF',
                        "tab_bg_color" => '#312f2d',
                        "tab_link_color" => '#aa9c91',
                        "tab_link_hover_color" => '#ff7612',
                        'tab_link_bg_color' => '#312f2d',
                        'tab_link_hover_bg_color' => '#ffffff',
                        "link_color" => '#ff7612',
                        "link_hover_color" => '#312f2d',
                        "content_font_color" => '#616175',
                        "box_bg_color" => '#F4F4F4',
                    ),
                    'light_green' => array(
                        "main_color" => '#17c9ab',
                        "title_color" => '#1e1e28',
                        "subtitle_color" => '#464d4c',
                        "border_color" => '#17c9ab',
                        "button_color" => '#17c9ab',
                        "button_font_color" => '#FFFFFF',
                        "tab_bg_color" => '#15b69b',
                        "tab_link_color" => '#016554',
                        "tab_link_hover_color" => '#FFFFFF',
                        'tab_link_bg_color' => '#15b69b',
                        'tab_link_hover_bg_color' => '#016554',
                        "link_color" => '#17c9ab',
                        "link_hover_color" => '#1e1e28',
                        "content_font_color" => '#616175',
                        "box_bg_color" => '#F4F4F4',
                    ),
                    'purple' => array(
                        "main_color" => '#7955d3',
                        "title_color" => '#191d2e',
                        "subtitle_color" => '#514d5a',
                        "border_color" => '#7955d3',
                        "button_color" => '#7955d3',
                        "button_font_color" => '#FFFFFF',
                        "tab_bg_color" => '#4f446c',
                        "tab_link_color" => '#a695d1',
                        "tab_link_hover_color" => '#ffffff',
                        'tab_link_bg_color' => '#4f446c',
                        'tab_link_hover_bg_color' => '#a695d1',
                        "link_color" => '#7955d3',
                        "link_hover_color" => '#191d2e',
                        "content_font_color" => '#616175',
                        "box_bg_color" => '#F4F4F4',
                    ),
                    'green' => array(
                        "main_color" => '#8ebd7e',
                        "title_color" => '#1e1e28',
                        "subtitle_color" => '#71776f',
                        "border_color" => '#8ebd7e',
                        "button_color" => '#8ebd7e',
                        "button_font_color" => '#FFFFFF',
                        "tab_bg_color" => '#e9eae9',
                        "tab_link_color" => '#8b8b8b',
                        "tab_link_hover_color" => '#303030',
                        'tab_link_bg_color' => '#e9eae9',
                        'tab_link_hover_bg_color' => '#ffffff',
                        "link_color" => '#7dbc68',
                        "link_hover_color" => '#4b4b5d',
                        "content_font_color" => '#616175',
                        "box_bg_color" => '#F4F4F4',
                    ),
                    'light_blue' => array(
                        "main_color" => '#32c5fc',
                        "title_color" => '#32c5fc',
                        "subtitle_color" => '#6b7275',
                        "border_color" => '#32c5fc',
                        "button_color" => '#32c5fc',
                        "button_font_color" => '#FFFFFF',
                        "tab_bg_color" => '#ecf3f9',
                        "tab_link_color" => '#73808b',
                        "tab_link_hover_color" => '#1f1f1f',
                        'tab_link_bg_color' => '#ecf3f9',
                        'tab_link_hover_bg_color' => '#ffffff',
                        "link_color" => '#32c5fc',
                        "link_hover_color" => '#1e1e28',
                        "content_font_color" => '#616175',
                        "box_bg_color" => '#F4F4F4',
                    ),
                ),
                            );
			return $color_schemes;
		}
		function arm_template_style($tempID = 0, $tempOptions = array())
		{
			global $ARMember, $arm_member_forms;
			$templateStyle = '';
            $tempID = isset($_POST['id']) ? intval($_POST['id']) : $tempID;
            $tempOptions = isset($_POST['template_options']) ? $_POST['template_options'] : $tempOptions;


            if (!empty($tempOptions)) {
                $tempOptions = shortcode_atts(array(
                    'pagination' => 'numeric',
                    'show_admin_users' => '',
                    'show_badges' => '',
                    'show_joining' => '',
                    'hide_empty_profile_fields' => '',
                    'color_scheme' => '',
                    'title_color' => '',
                    'subtitle_color' => '',
                    'border_color' => '',
                    'button_color' => '',
                    'button_font_color' => '',
                    'tab_bg_color' => '',
                    'tab_link_color' => '',
                    'tab_link_hover_color' => '',
                    'tab_link_bg_color' => '',
                    'tab_link_hover_bg_color' => '',
                    'link_color' => '',
                    'link_hover_color' => '',
                    'content_font_color' => '',
                    'box_bg_color' => '',
                    'title_font' => array(
                        'font_family' => 'Open Sans Semibold',
                        'font_size' => '26',
                        'font_bold' => 1,
                        'font_italic' => 0,
                        'font_decoration' => '',
                    ),
                    'subtitle_font' => array(
                        'font_family' => 'Open Sans Semibold',
                        'font_size' => '16',
                        'font_bold' => 0,
                        'font_italic' => 0,
                        'font_decoration' => '',
                    ),
                    'button_font' => array(
                        'font_family' => 'Open Sans Semibold',
                        'font_size' => '16',
                        'font_bold' => 0,
                        'font_italic' => 0,
                        'font_decoration' => '',
                    ),
                    'tab_link_font' => array(
                        'font_family' => 'Open Sans Semibold',
                        'font_size' => '16',
                        'font_bold' => 1,
                        'font_italic' => 0,
                        'font_decoration' => '',
                    ),
                    'content_font' => array(
                        'font_family' => 'Open Sans Semibold',
                        'font_size' => '16',
                        'font_bold' => 0,
                        'font_italic' => 0,
                        'font_decoration' => '',
                    ),
                    'custom_css' => '',
                        ), $tempOptions);

                $tempFontFamilys = array();
                $fontOptions = array('title_font', 'subtitle_font', 'button_font', 'tab_link_font', 'content_font');
                foreach ($fontOptions as $key) {
                    $tfont_family = (isset($tempOptions[$key]['font_family'])) ? $tempOptions[$key]['font_family'] : "Helvetica";
                    $tfont_family = ($tfont_family == "inherit") ? '' : $tfont_family;
                    $tempFontFamilys[] = $tfont_family;
					$tfont_size = (isset($tempOptions[$key]['font_size'])) ? $tempOptions[$key]['font_size'] : "";
					$tfont_bold = (isset($tempOptions[$key]['font_bold']) && $tempOptions[$key]['font_bold'] == '1') ? "font-weight: bold !important;" : "font-weight: normal !important;";
					$tfont_italic = (isset($tempOptions[$key]['font_italic']) && $tempOptions[$key]['font_italic'] == '1') ? "font-style: italic !important;" : "font-style: normal !important;";
					$tfont_decoration = (!empty($tempOptions[$key]['font_decoration'])) ? "text-decoration: ".$tempOptions[$key]['font_decoration']." !important;" : "text-decoration: none !important;";

                    $tfront_font_family = (!empty($tfont_family)) ? "font-family: ".$tfont_family.", sans-serif, 'Trebuchet MS' !important;" : "";
                    $tempOptions[$key]['font'] = "{$tfront_font_family} font-size: {$tfont_size}px !important;{$tfont_bold}{$tfont_italic}{$tfont_decoration}";
                    $tempOptions[$key]['font_family'] = "{$tfront_font_family}";
                    $tempOptions[$key]['font_size'] = "font-size:{$tfont_size}px !important;";
				}
                $gFontUrl = $arm_member_forms->arm_get_google_fonts_url($tempFontFamilys);
                if (!empty($gFontUrl)) {
					//$templateStyle .= '<link id="google-font-' . $tempID . '" rel="stylesheet" type="text/css" href="' . $gFontUrl . '" />';
                    wp_enqueue_style( 'google-font-' . $tempID, $gFontUrl, array(), MEMBERSHIPLITE_VERSION );
                }
                $custom_css = (!empty($tempOptions['custom_css'])) ? $tempOptions['custom_css'] : '';
                $borderRGB = $arm_member_forms->armHexToRGB($tempOptions['border_color']);
                $borderRGB['r'] = (!empty($borderRGB['r'])) ? $borderRGB['r'] : 0;
                $borderRGB['g'] = (!empty($borderRGB['g'])) ? $borderRGB['g'] : 0;
                $borderRGB['b'] = (!empty($borderRGB['b'])) ? $borderRGB['b'] : 0;
                if (is_admin()) {
                    $templateStyle .= '<style type="text/css" id="arm_profile_runtime_css">';
                } else {
                    $templateStyle .= '<style type="text/css">';
                }


                $tempWrapperClass = ".arm_template_wrapper_{$tempID}";
                $templateStyle .= "
					$tempWrapperClass .arm_profile_name_link,
					$tempWrapperClass .arm_profile_name_link a,
					$tempWrapperClass .arm_directory_container .arm_user_link{
						color: {$tempOptions['title_color']} !important;
						{$tempOptions['title_font']['font']}
					}
                    $tempWrapperClass .arm_template_container .arm_user_link span{
                        color: {$tempOptions['title_color']} !important;
                        {$tempOptions['title_font']['font']}
                    }
					$tempWrapperClass .arm_profile_container .arm_profile_tabs{
						background-color: {$tempOptions['tab_bg_color']} !important;
					}
					$tempWrapperClass .arm_profile_container .arm_user_last_login_time,
					$tempWrapperClass .arm_profile_container .arm_user_last_active_text,
					$tempWrapperClass .arm_profile_container .arm_user_about_me{
						color: {$tempOptions['subtitle_color']} !important;
						{$tempOptions['subtitle_font']['font']}
					}
					$tempWrapperClass.arm_template_wrapper_directorytemplate3 .arm_user_link:before{
						background-color: {$tempOptions['title_color']} !important;
					}
					$tempWrapperClass.arm_template_wrapper_profiletemplate1 .arm_profile_picture_block .arm_user_avatar,
					$tempWrapperClass.arm_template_wrapper_profiletemplate2 .arm_profile_picture_block .arm_user_avatar,
                    $tempWrapperClass.arm_template_wrapper_profiletemplate3 .arm_profile_picture_block .arm_user_avatar,
					$tempWrapperClass.arm_template_wrapper_profiletemplate4 .arm_profile_picture_block .arm_user_avatar{
						
						border-color: {$tempOptions['border_color']} !important;
                        display: none;
					}
                    
					$tempWrapperClass .arm_directory_container .arm_user_desc_box,
					$tempWrapperClass .arm_directory_container .arm_last_active_text,
					$tempWrapperClass .arm_directory_container .arm_paging_wrapper .arm_paging_info,
					$tempWrapperClass .arm_directory_container .arm_paging_wrapper .arm_page_numbers{
						color: {$tempOptions['subtitle_color']} !important;
						{$tempOptions['subtitle_font']['font']}
					}

					$tempWrapperClass .arm_directory_container .arm_paging_wrapper .arm_page_numbers.current,
					$tempWrapperClass .arm_directory_container .arm_paging_wrapper .arm_page_numbers:hover{
						color: {$tempOptions['border_color']} !important;
						border-bottom-color: {$tempOptions['border_color']};
					}
					$tempWrapperClass .arm_directory_container .arm_paging_wrapper .arm_page_numbers.arm_prev,
					$tempWrapperClass .arm_directory_container .arm_paging_wrapper .arm_page_numbers.arm_next{
						border-color: #FFF;
					}
					$tempWrapperClass .arm_directory_list_by_filters select,
					$tempWrapperClass .arm_directory_list_of_filters label{
                        {$tempOptions['subtitle_font']['font_family']}
                        {$tempOptions['subtitle_font']['font_size']}
					}
					$tempWrapperClass .arm_directory_list_of_filters label.arm_active{
						color: {$tempOptions['button_color']} !important;
						border-color: {$tempOptions['button_color']};
					}
					$tempWrapperClass .arm_profile_tabs .arm_profile_tab_link{
						background-color: {$tempOptions['tab_link_bg_color']} !important;
						color: {$tempOptions['tab_link_color']} !important;
						{$tempOptions['tab_link_font']['font']}
					}
					$tempWrapperClass.arm_template_wrapper_profiletemplate1 .arm_profile_picture_block,
					$tempWrapperClass.arm_template_wrapper_profiletemplate2 .arm_profile_picture_block,
					$tempWrapperClass.arm_template_wrapper_profiletemplate3 .arm_profile_picture_block,
                    $tempWrapperClass.arm_template_wrapper_profiletemplate4 .arm_profile_picture_block{
						border-color:{$tempOptions['border_color']} !important;
					}
					$tempWrapperClass .arm_profile_tabs .arm_profile_tab_link:hover,
					$tempWrapperClass .arm_profile_tabs .arm_profile_tab_link.arm_profile_tab_link_active{
						background-color: {$tempOptions['tab_link_hover_bg_color']} !important;
						color: {$tempOptions['tab_link_hover_color']} !important;
						{$tempOptions['tab_link_font']['font']}
					}
					$tempWrapperClass .arm_profile_tabs_container .arm_profile_tab_detail,
					$tempWrapperClass .arm_profile_tab_detail,
					$tempWrapperClass .arm_profile_tabs_container .arm_profile_tab_detail *:not(i){
						color: {$tempOptions['content_font_color']} !important;
					}
                    $tempWrapperClass .arm_profile_tab_detail table tr td{
                        {$tempOptions['content_font']['font']} 
                    }
                    $tempWrapperClass .arm_confirm_box .arm_confirm_box_text,
                    $tempWrapperClass .arm_confirm_box .arm_confirm_box_btn{
                        {$tempOptions['content_font']['font_family']};
                    }

					$tempWrapperClass .arm_profile_defail_container .arm_profile_tab_detail a{
						color: {$tempOptions['link_color']} !important;
					}
					$tempWrapperClass .arm_profile_defail_container .arm_profile_tab_detail a:hover{
						color: {$tempOptions['link_hover_color']} !important;
					}
					$tempWrapperClass .arm_directory_list_by_filters select:focus,
					$tempWrapperClass .arm_directory_search_wrapper .arm_directory_search_box:focus{
						border-color: {$tempOptions['button_color']} !important;
					}
					$tempWrapperClass.arm_template_wrapper_directorytemplate3 .arm_directory_container .arm_view_profile_btn_wrapper a,
					$tempWrapperClass.arm_template_wrapper_directorytemplate1 .arm_directory_container .arm_view_profile_btn_wrapper a,
					$tempWrapperClass.arm_template_wrapper_directorytemplate4 .arm_directory_container .arm_view_profile_btn_wrapper a{
						background-color: {$tempOptions['button_color']} !important;
						border-color: {$tempOptions['button_color']} !important;
						color: {$tempOptions['button_font_color']} !important;
						{$tempOptions['button_font']['font']}
					}
					$tempWrapperClass.arm_template_wrapper_directorytemplate4 .arm_directory_container .arm_user_link{
						background-color: {$tempOptions['button_color']} !important;
						
					}
					$tempWrapperClass .arm_directory_load_more_link{
						color: {$tempOptions['link_color']} !important;
						{$tempOptions['content_font']['font']}
					}
					$tempWrapperClass .arm_directory_load_more_link:hover{
						color: {$tempOptions['link_hover_color']} !important;
					}
					
					$tempWrapperClass.arm_template_wrapper_directorytemplate2 .arm_user_block:hover{
						box-shadow: 0px 0px 25px 0px rgba(".$borderRGB['r'].", ".$borderRGB['g'].", ".$borderRGB['b'].", 0.15);
						-webkit-box-shadow: 0px 0px 25px 0px rgba(".$borderRGB['r'].", ".$borderRGB['g'].", ".$borderRGB['b'].", 0.15);
						-moz-box-shadow: 0px 0px 25px 0px rgba(".$borderRGB['r'].", ".$borderRGB['g'].", ".$borderRGB['b'].", 0.15);
						-o-box-shadow: 0px 0px 25px 0px rgba(".$borderRGB['r'].", ".$borderRGB['g'].", ".$borderRGB['b'].", 0.15);
					}
					$tempWrapperClass.arm_template_wrapper_directorytemplate3 .arm_cover_bg_wrapper{
						background-color: {$tempOptions['box_bg_color']};
					}
                    $tempWrapperClass.arm_template_wrapper_directorytemplate5 .arm_user_avatar:hover:after{
                        background-color: rgba(".$borderRGB['r'].", ".$borderRGB['g'].", ".$borderRGB['b'].", 0.5);
                    }

                                        /* Ripple Out */
					@-webkit-keyframes hvr-ripple-out {
						100% {
							top: -20px;
							right: -20px;
							bottom: -20px;
							left: -20px;
							opacity: 0;
							border: 4px solid {$tempOptions['border_color']};
						}
					}
					@keyframes hvr-ripple-out {
						100% {
							top: -20px;
							right: -20px;
							bottom: -20px;
							left: -20px;
							opacity: 0;
							border: 4px solid {$tempOptions['border_color']};
						}
					}
					{$custom_css}
				";

                if (is_admin()) {
                    $templateStyle .= "$tempWrapperClass .arm_profile_tabs_container .arm_profile_tab_detail .arm_slider_box_heading{
						color: #32323a !important;
                                                font-size: 16px !important;
                                                font-weight: bold !important;
                                                line-height: 40px !important;
                                                text-align: left !important;
					}
                                        
                                        $tempWrapperClass .arm_profile_tabs_container .arm_profile_tab_detail .arm_form_field_settings_menu_inner{
						color: #32323a !important;
                                                font-size: 16px !important;
                                                font-weight: bold !important;
                                                line-height: 40px !important;
                                                text-align: left !important;
					}";
                }


                $templateStyle .= apply_filters('arm_change_profile_directory_style_outside','',$tempOptions);

                $templateStyle .= '</style>';
            }

            $arm_response = array('arm_link' => '', 'arm_css' => $templateStyle);
            if (isset($_POST['action']) && $_POST['action'] == 'arm_ajax_generate_profile_styles') {
                echo json_encode($arm_response);
                exit;
            }
            return $templateStyle;
        }

        function arm_default_member_templates() {
            global $wpdb, $ARMember;
            $templates = array(
                /**
                 * Profile Templates
                 */
                array(
                    'arm_title' => __('Profile Template 3', 'ARMember'),
                    'arm_slug' => 'profiletemplate3',
                    'arm_type' => 'profile',
                    'arm_core' => 1,
                ),
				/**
				 * Directory Templates
				 */
				array(
					'arm_title' => __('Directory Template 2', 'ARMember'),
					'arm_slug' => 'directorytemplate2',
					'arm_type' => 'directory',
					'arm_core' => 1,
				),
			);
			$templates = apply_filters('arm_change_profile_and_directory_settings', $templates);
			return $templates;
		}
		function arm_insert_default_member_templates()
		{
			global $wpdb, $ARMember, $arm_members_activity;
            $oldTemps = $this->arm_get_all_member_templates();
            if (!empty($oldTemps)) {
                return;
            }

            $defaultCoverSource = MEMBERSHIPLITE_IMAGES_DIR.'/profile_default_cover.png';
			$profileCoverDir = MEMBERSHIPLITE_UPLOAD_DIR.'/profile_default_cover.png';
			$profileCoverUrl = MEMBERSHIPLITE_UPLOAD_URL.'/profile_default_cover.png';
            if( !$arm_members_activity->arm_upload_file_function($defaultCoverSource, $profileCoverDir) ){
                $profileCoverUrl = MEMBERSHIPLITE_IMAGES_URL.'/profile_default_cover.png';
            }
			$profileTemplateOptions = array(
                                'show_admin_users' => 0,
				'show_badges' => 1,
                                'show_joining' => 1,
                            'hide_empty_profile_fields' => 0,
				'color_scheme' => 'blue',
				"title_color" => '#0c7cd5',
				"subtitle_color" => '#575d70',
				"border_color" => '#0c7cd5',
				"button_color" => '#0c7cd5',
				"button_font_color" => '#ffffff',
				"tab_bg_color" => '#0c7cd5',
				"tab_link_color" => '#ffffff',
				"tab_link_hover_color" => '#0c7cd5',
				'tab_link_bg_color' => '#0c7cd5',
				'tab_link_hover_bg_color' => '#ffffff',
				"link_color" => '#0c7cd5',
				"link_hover_color" => '#41474c',
				'content_font_color' => '#616175',
				"box_bg_color" => '#F4F4F4',
				'title_font' => array(
					'font_family' => 'Open Sans Semibold',
					'font_size' => '26',
					'font_bold' => 1,
					'font_italic' => 0,
					'font_decoration' => '',
				),
				'subtitle_font' => array(
					'font_family' => 'Open Sans Semibold',
					'font_size' => '16',
					'font_bold' => 0,
					'font_italic' => 0,
					'font_decoration' => '',
				),
				'button_font' => array(
					'font_family' => 'Open Sans Semibold',
					'font_size' => '16',
					'font_bold' => 0,
					'font_italic' => 0,
					'font_decoration' => '',
				),
				'tab_link_font' => array(
					'font_family' => 'Open Sans Semibold',
					'font_size' => '16',
					'font_bold' => 1,
					'font_italic' => 0,
					'font_decoration' => '',
				),
				'content_font' => array(
					'font_family' => 'Open Sans Semibold',
					'font_size' => '16',
					'font_bold' => 0,
					'font_italic' => 0,
					'font_decoration' => '',
				),
                'profile_fields' => array(
                    'user_login' => 'user_login',
                    'user_email' => 'user_email',
                    'first_name' => 'first_name',
                    'last_name' => 'last_name',
                ),
                'default_cover' => $profileCoverUrl,
                'custom_css' => '',
            );
            $dbProfileFields = $this->arm_template_profile_fields();
            $labels = array();
            foreach($profileTemplateOptions['profile_fields'] as $k => $v ){
                $labels[$k] = isset($dbProfileFields[$k]) ? $dbProfileFields[$k]['label'] : '';
            }
            $profileTemplateOptions['label'] = $labels;
            $profileTemplate = array(
                'arm_title' => __('Default Profile Template', 'ARMember'),
                'arm_slug' => 'profiletemplate3',
                'arm_type' => 'profile',
                'arm_default' => 1,
                'arm_core' => 1,
                'arm_options' => maybe_serialize($profileTemplateOptions),
                'arm_created_date' => date('Y-m-d H:i:s')
            );

            $arm_template_html = '<div class="arm_profile_detail_wrapper">
        <div class="arm_profile_picture_block armCoverPhoto" style="{ARM_Profile_Cover_Image}">
            <div class="arm_profile_picture_block_inner">
                                <div class="armclear"></div>
                                <div class="arm_profile_header_info arm_profile_header_bottom_box">
                                    <span class="arm_profile_name_link">
                                        {ARM_Profile_User_Name}
                                    </span>
                                    {ARM_Profile_Badges}
                                </div>
                                <span class="arm_user_last_active_text">Member Since {ARM_Profile_Join_Date}</span>
                      
                    <div class="armclear"></div>
                                <div class="arm_user_social_icons_all social_profile_fields arm_mobile">
                                    {ARM_Profile_Social_Icons_Mobile}
                </div>
                                <div class="arm_profile_header_top_box">
                                    <div class="arm_user_social_icons_left arm_desktop">
                                            {ARM_Profile_Social_Icons_Left}
                </div>
                                    <div class="arm_user_avatar">
                {ARM_Profile_Avatar_Image}
                                    </div>
                                    <div class="arm_user_social_icons_right arm_desktop">
                                            {ARM_Profile_Social_Icons_Right}
                                    </div>
                                </div>
                            </div>
                            {ARM_Cover_Upload_Button}
                        </div>
                        <div class="arm_profile_defail_container arm_profile_tabs_container">
                <div class="arm_profile_field_before_content_wrapper"></div>
                <div class="arm_profile_tab_detail" data-tab="general">
                    <div class="arm_general_info_container">
                        <table class="arm_profile_detail_tbl">
                             <tbody>';
                                        foreach($profileTemplateOptions['profile_fields'] as $k => $value ){
                                            $arm_template_html .= "<tr>";
                                                $arm_template_html .= "<td>".$profileTemplateOptions['label'][$k]."</td>";
                                                $arm_template_html .= "<td>[arm_usermeta meta='".$k."']</td>";
                                            $arm_template_html .= "</tr>";
                                          }
                                      $arm_template_html .= '</tbody>
                        </table>
                    </div>
                </div>
                <div class="arm_profile_field_after_content_wrapper"></div>
            </div>
        </div><div class="armclear"></div>';

            $profileTemplate['arm_template_html'] = $arm_template_html;
            $insrt = $wpdb->insert($ARMember->tbl_arm_member_templates, $profileTemplate);





            $directoryTemplateOptions = array(
                                'color_scheme' => 'blue',
                'title_color' => '#0c7cd5',
                                'subtitle_color' => '#575d70',
                            'button_color' => '#0c7cd5',
                'button_font_color' => '#ffffff',
        'border_color' => '#0c7cd5',
        'box_bg_color' => '#F4F4F4',
        'tab_bg_color' => '#0c7cd5',
        'tab_link_color' => '#ffffff',
        'tab_link_bg_color' => '#0c7cd5',
        'tab_link_hover_color' => '#0c7cd5',
        'tab_link_hover_bg_color] => #ffffff',
        'link_color' => '#0c7cd5',
        'link_hover_color' => '#41474c',
        'content_font_color' => '#616175',
        'title_font' => array
            (
                'font_family' => 'Helvetica',
                'font_size' => '14',
                'font_bold' => '',
                'font_italic' => '',
                'font_decoration' => '',
            ),

        'subtitle_font' => array
            (
                'font_family' => 'Helvetica',
                'font_size' => '14',
                'font_bold' => '',
                'font_italic' => '',
                'font_decoration' => '',
            ),

        'button_font' => array
            (
                'font_family' => 'Helvetica',
                'font_size' => '1',
                'font_bold' => '',
                'font_italic' => '',
                'font_decoration' => '',
            ),

        'content_font' => array
            (
                'font_family' => 'Helvetica',
                'font_size' => '16',
                'font_bold' => '',
                'font_italic' => '',
                'font_decoration' => '',
            ),

      
        'show_joining' => 1,
        'searchbox' => 1,
        'sortbox' => 1,
        'per_page_users' => 10,
        'pagination' => 'infinite',
        'arm_social_fields' => array
            (
                'facebook' => 'facebook',
                'twitter' => 'twitter',
                'linkedin' => 'linkedin',
            ),

        'profile_fields' => array
            (
                'first_name' => 'first_name',
                'last_name' => 'last_name',
            ),

        'custom_css' => '',
                );

                $directoryTemplate = array(
                    'arm_title' => __('Default Directory Template', 'ARMember'),
                    'arm_slug' => 'directorytemplate2',
                    'arm_type' => 'directory',
                    'arm_default' => 1,
                    'arm_core' => 1,
                    'arm_template_html'=>'',
                    'arm_options' => maybe_serialize($directoryTemplateOptions),
                    'arm_created_date' => date('Y-m-d H:i:s')
                );

            $insrt_dir = $wpdb->insert($ARMember->tbl_arm_member_templates, $directoryTemplate);

                return;
        }

        function arm_get_profile_dummy_data(){
            $profile_fields_data = array(
                'user_login' => 'willsmith',
                'user_email' => 'will.smith@armember.com',
                'first_name' => 'Will',
                'last_name' => 'Smith',
                'display_name' => 'Will Smith',
                'gender' => 'male',
                'user_url' => 'https://www.willsmith.example.com',
                'country' => 'United States',
                'description' => 'Hello, I am Will Smith. I am a professional web developer. I am expertise in PHP, WordPress, JavaScript, HTML and CSS.'
            );
            return apply_filters('arm_change_dummy_profile_data_outside',$profile_fields_data);
        }

        function arm_get_profile_editor_template($template,$profile_fields_data,$options,$template_id,$ajax = false,$profile_before_content = '',$profile_after_content = '',$data_type='desktop'){
            if( !isset($template) || $template == '' || empty($profile_fields_data)  ){
                return '';
            }

         

            global $arm_global_settings;
            $template_data = "";
            $randomTempID = $template_id . '_' . arm_generate_random_code();
            $arm_profile_form_rtl = '';
            if (is_rtl()) {
                $arm_profile_form_rtl = 'arm_profile_form_rtl';
            }
            $template_data .= $this->arm_template_style($template_id, $options);
            if( $ajax == false ){
                wp_enqueue_style('arm_template_style_' . $template, MEMBERSHIPLITE_VIEWS_URL . '/templates/' . $template . '.css', array(),MEMBERSHIPLITE_VERSION );
            } else {
                $template_data .= "<link rel='stylesheet' id='arm_template_style_{$template}-css' type='text/css' href='".MEMBERSHIPLITE_VIEWS_URL."/templates/{$template}.css' />";
            }

            $social_fields_array = array(
                'facebook' => 'Facebook',
                'twitter' => 'Twitter',
                'linkedin' => 'LinkedIn',
                'vk' => 'VK',
                'instagram' => 'Instagram',
                'pinterest' => 'Pinterest',
                'youtube' => 'Youtube',
                'dribbble' => 'Dribbble',
                'delicious' => 'Delicious',
                'tumblr' => 'Tumblr',
                'vine' => 'Vine',
                'skype' => 'Skype',
                'whatsapp' => 'WhatsApp',
                'tiktok' => 'Tiktok'
            );
            $display_cover_photo = isset($options['default_cover_photo']) ? $options['default_cover_photo'] : 0;
            $cover_photo_bg = "";
            if( $display_cover_photo == 1 ){
                $cover_photo_url = isset($options['default_cover']) ? $options['default_cover'] : MEMBERSHIPLITE_IMAGES_URL.'/profile_default_cover.png';
                $cover_photo_bg = "background:url({$cover_photo_url}) no-repeat center center;";
            }

            $default_avatar_photo = MEMBERSHIPLITE_VIEWS_URL.'/templates/profile_default_avatar.png';
            $dbSocialFields = isset($options['arm_social_fields']) ? $options['arm_social_fields'] : array();


            $template_data .= "<div class='arm_template_wrapper {$data_type} arm_template_wrapper_{$template_id} arm_template_wrapper_{$template}'>";
            $template_data .= "<div class='arm_template_container arm_profile_container {$arm_profile_form_rtl}' id='arm_template_container_{$randomTempID}'>";

            $arm_args = func_get_args();
            $arm_profile_before_content_outside = apply_filters('arm_profile_dummy_content_before_fields_outside','',$arm_args);
            $arm_profile_after_content_outside = apply_filters('arm_profile_dummy_content_after_fields_outside','',$arm_args);

            if( $template == 'profiletemplate1' ){

                $template_data .= "<div class='arm_profile_defail_container arm_profile_tabs_container'>";
                
                $template_data .= "<div class='arm_profile_detail_wrapper'>";
                
                $template_data .= "<div class='arm_profile_picture_block armCoverPhoto' style='{$cover_photo_bg}'>";

                $template_data .= "<div class='arm_template_loading'><img src='".MEMBERSHIPLITE_IMAGES_URL."/loader.gif' alt='".__('Loading','ARMember')."..' /></div>";
                
                $template_data .= "<div class='arm_profile_picture_block_inner'>";
                    $template_data .= "<div class='arm_user_avatar'><img class='avatar arm_grid_avatar arm-avatar avatar-200 photo' src='{$default_avatar_photo}' height='200' width='200' /></div>";
                    $template_data .= "<div class='arm_profile_separator'></div>";
                    $template_data .= "<div class='arm_profile_header_info'>";
                        $template_data .= "<span class='arm_profile_name_link'>Will Smith</span>";
             
                        
                        $display_joining_date = ( isset($options['show_joining']) && $options['show_joining'] == 1 ) ? '' : 'hidden_section';
                        $template_data .= "<div class='arm_user_last_active_text {$display_joining_date}'>".__('Member Since','ARMember').' '.date($arm_global_settings->arm_get_wp_date_format())."</div>";
                        $template_data .= "<div class='social_profile_fields'>";
                            foreach($social_fields_array as $fk => $val ){
                                $k = array_keys($dbSocialFields,$fk);
                                $cls = isset($k[0]) && ($dbSocialFields[$k[0]] == $fk) ? '' : 'hidden_section';
                                $template_data .= "<div class='arm_social_prof_div {$cls} arm_user_social_fields arm_social_field_{$fk}'>";
                                    $template_data .= "<a href='#'></a>";
                                $template_data .= "</div>";
                            }
                        $template_data .= "</div>";
                    $template_data .= "</div>";
                $template_data .= "</div>";
                
                $template_data .= "</div>";

                $template_data .= "<div class='armclear'></div>";
                
                $template_data .= $arm_profile_before_content_outside;

                $template_data .= "<div class='arm_profile_field_before_content_wrapper'>";
                    $template_data .= stripslashes_deep($profile_before_content);
                $template_data .= "</div>";

                $template_data .= "<div class='arm_profile_tab_detail'>";
                    $template_data .= "<div class='arm_general_info_container'>";
                        
                        $template_data .= "<table class='arm_profile_detail_tbl'>";
                            $template_data .= "<tbody>";
                                foreach($profile_fields_data['profile_fields'] as $meta_key => $meta_val ){
                                    $template_data .= "<tr id='".$meta_key."'>";
                                        $user_value = isset($profile_fields_data['default_values'][$meta_key]) ? $profile_fields_data['default_values'][$meta_key] : '';
                                        $template_data .= "<td>".$profile_fields_data['label'][$meta_key]."</td>";
                                        $template_data .= "<td>".$user_value."</td>";
                                    $template_data .= "</tr>";
                                }
                            $template_data .= "</tbody>";
                        $template_data .= "</table>";
                    $template_data .= "</div>";
                $template_data .= "</div>";

                $template_data .= "<div class='arm_profile_field_after_content_wrapper'>";
                    $template_data .= stripslashes_deep($profile_after_content);
                $template_data .= "</div>";

                $template_data .= $arm_profile_after_content_outside;

                $template_data .= "</div>";

                $template_data .= "</div>";
            } else if( $template == 'profiletemplate2' ){
                $template_data .= "<div class='arm_template_container arm_profile_container '>";
                
                $template_data .= "<div class='arm_profile_detail_wrapper'>";
                
                $template_data .= "<div class='arm_profile_picture_block armCoverPhoto' style='{$cover_photo_bg}'>";

                $template_data .= "<div class='arm_template_loading'><img src='".MEMBERSHIPLITE_IMAGES_URL."/loader.gif' alt='".__('Loading','ARMember')."..' /></div>";
                
                $template_data .= "<div class='arm_profile_picture_block_inner'>";
                    $template_data .= "<div class='arm_profile_header_info arm_profile_header_bottom_box'>";
                        $template_data .= "<span class='arm_profile_name_link'>Will Smith</span>";
                       
                        $display_joining_date = ( isset($options['show_joining']) && $options['show_joining'] == 1 ) ? '' : 'hidden_section';
                        $template_data .= "<div class='arm_user_last_active_text {$display_joining_date}'>".__('Member Since','ARMember').' '.date($arm_global_settings->arm_get_wp_date_format())."</div>";
                    $template_data .= "</div>";
                    $template_data .= "<div class='armclear'></div>";
                    $template_data .= "<div class='arm_user_social_icons_all social_profile_fields  arm_{$data_type}'>";
                    foreach($social_fields_array as $fk => $val ){
                        $k = array_keys($dbSocialFields,$fk);
                        $cls = isset($k[0]) && ($dbSocialFields[$k[0]] == $fk) ? '' : 'hidden_section';
                        $template_data .= "<div class='arm_social_prof_div {$cls} arm_user_social_fields arm_social_field_{$fk}'>";
                            $template_data .= "<a href='#'></a>";
                        $template_data .= "</div>";
                    }
                    $template_data .= "</div>";
                    $template_data .= "<div class='arm_profile_header_top_box'>";
                    
                    $template_data .= "<div class='arm_social_profile_hidden' id='arm_social_profile_hidden' style='width: !important0;height: !important0;padding: !important0;overflow: !importanthidden;visibility: !importanthidden;display:none !important;'>";
                        foreach($social_fields_array as $key => $spf ){
                            $template_data .= "<div class='arm_social_prof_div arm_user_social_fields arm_social_field_{$key}'>";
                                $template_data .= "<a href='#'></a>";
                            $template_data .= "</div>";
                        }
                    $template_data .= "</div>";

                    $template_data .= "<div class='arm_user_social_icons_left arm_{$data_type}'>";
                    $array_size = ceil(count($dbSocialFields)/2);
                    if( $array_size < 1){
                        $array_size = 1;
                    }
                    $chunked_array = array_chunk($dbSocialFields,$array_size,true);
                        $template_data .= "<div class='social_profile_fields'>";
                        if(isset($chunked_array[1]) ){
                        foreach($chunked_array[0] as $fk => $key ){
                            $template_data .= "<div class='arm_social_prof_div arm_user_social_fields arm_social_field_{$key}'>";
                            $template_data .= "<a href='#'></a>";
                            $template_data .= "</div>";
                        }
                    }
                        $template_data .= "</div>";
                    $template_data .= "</div>";
                    $template_data .= "<div class='arm_user_avatar'><img class='avatar arm_grid_avatar arm-avatar avatar-200 photo' src='{$default_avatar_photo}' height='200' width='200' /></div>";
                    $template_data .= "<div class='arm_user_social_icons_right arm_{$data_type}'>";
                        $template_data .= "<div class='social_profile_fields'>";
                        if(isset($chunked_array[1]) ){
                            foreach($chunked_array[1] as $fk => $key ){
                                $template_data .= "<div class='arm_social_prof_div arm_user_social_fields arm_social_field_{$key}'>";
                                $template_data .= "<a href='#'></a>";
                                $template_data .= "</div>";
                            }
                            }
                        $template_data .= "</div>";
                    $template_data .= "</div>";
                    $template_data .= "</div>";
                    $template_data .= "<div class='arm_profile_separator'></div>";
                $template_data .= "</div>";
                
                $template_data .= "</div>";

                $template_data .= "<div class='armclear'></div>";

                $template_data .= $arm_profile_before_content_outside;
                
                $template_data .= "<div class='arm_profile_field_before_content_wrapper'>";
                    $template_data .= stripslashes_deep($profile_before_content);
                $template_data .= "</div>";

                $template_data .= "<div class='arm_profile_tab_detail'>";
                    $template_data .= "<div class='arm_general_info_container'>";
                        $template_data .= "<table class='arm_profile_detail_tbl'>";
                            $template_data .= "<tbody>";
                                foreach($profile_fields_data['profile_fields'] as $meta_key => $meta_val ){
                                    $template_data .= "<tr id='".$meta_key."'>";
                                        $user_value = isset($profile_fields_data['default_values'][$meta_key]) ? $profile_fields_data['default_values'][$meta_key] : '';
                                        $template_data .= "<td>".$profile_fields_data['label'][$meta_key]."</td>";
                                        $template_data .= "<td>".$user_value."</td>";
                                    $template_data .= "</tr>";
                                }
                            $template_data .= "</tbody>";
                        $template_data .= "</table>";
                    $template_data .= "</div>";
                $template_data .= "</div>";

                $template_data .= "<div class='arm_profile_field_after_content_wrapper'>";
                    $template_data .= stripslashes_deep($profile_after_content);
                $template_data .= "</div>";

                $template_data .= $arm_profile_after_content_outside;

                $template_data .= "</div>";

                $template_data .= "</div>";
            } else if( $template == 'profiletemplate3' ){
                $template_data .= "<div class='arm_profile_detail_wrapper'>";

                $template_data .= "<div class='arm_profile_picture_block armCoverPhoto' style='{$cover_photo_bg}'>";

                $template_data .= "<div class='arm_template_loading'><img src='".MEMBERSHIPLITE_IMAGES_URL."/loader.gif' alt='".__('Loading','ARMember')."..' /></div>";

                $template_data .= "<div class='arm_profile_picture_block_inner'>";
                    
                    $template_data .= "<div class='arm_profile_header_info'>";

                        $template_data .= "<span class='arm_profile_name_link'>Will Smith</span>";

                        

                        $template_data .= "<div class='armclear'></div>";

                        $display_joining_date = ( isset($options['show_joining']) && $options['show_joining'] == 1 ) ? '' : 'hidden_section';
                        $template_data .= "<span class='arm_user_last_active_text {$display_joining_date}'>".__('Member Since','ARMember').' '.date($arm_global_settings->arm_get_wp_date_format())."</span>";

                    $template_data .= "</div>";

                    $template_data .= "<div class='armclear'></div>";

                    $template_data .= "<div class='social_profile_fields'>";
                        foreach($social_fields_array as $fk => $val ){
                            $k = array_keys($dbSocialFields,$fk);
                            $cls = isset($k[0]) && ($dbSocialFields[$k[0]] == $fk) ? '' : 'hidden_section';
                            $template_data .= "<div class='arm_social_prof_div {$cls} arm_user_social_fields arm_social_field_{$fk}'>";
                                $template_data .= "<a href='#'></a>";
                            $template_data .= "</div>";
                        }
                    $template_data .= "</div>";

                $template_data .= "</div>";

                $template_data .= "<div class='arm_user_avatar'><img class='avatar arm_grid_avatar arm-avatar avatar-200 photo' src='{$default_avatar_photo}' height='200' width='200' /></div>";

                $template_data .= "</div>";

                $template_data .= $arm_profile_before_content_outside;

                $template_data .= "<div class='arm_profile_field_before_content_wrapper'>";
                    $template_data .= stripslashes_deep($profile_before_content);
                $template_data .= "</div>";

                $template_data .= "<div class='arm_profile_tab_detail'>";
                    $template_data .= "<div class='arm_general_info_container'>";
                        
                        $template_data .= "<table class='arm_profile_detail_tbl'>";
                            $template_data .= "<tbody>";
                                foreach($profile_fields_data['profile_fields'] as $meta_key => $meta_val ){
                                    $template_data .= "<tr id='".$meta_key."'>";
                                        $user_value = isset($profile_fields_data['default_values'][$meta_key]) ? $profile_fields_data['default_values'][$meta_key] : '';
                                        $template_data .= "<td>".$profile_fields_data['label'][$meta_key]."</td>";
                                        $template_data .= "<td>".$user_value."</td>";
                                    $template_data .= "</tr>";
                                }
                            $template_data .= "</tbody>";
                        $template_data .= "</table>";
                    $template_data .= "</div>";
                $template_data .= "</div>";

                $template_data .= "<div class='arm_profile_field_after_content_wrapper'>";
                    $template_data .= stripslashes_deep($profile_after_content);
                $template_data .= "</div>";

                $template_data .= $arm_profile_after_content_outside;

                $template_data .= "</div>";    
            } else if( $template == 'profiletemplate4' ){
                $template_data .= "<div class='arm_profile_defail_container arm_profile_tabs_container'>";
                $template_data .= "<div class='arm_profile_detail_wrapper'>";
                    $template_data .= "<div class='arm_profile_picture_block armCoverPhoto' style='{$cover_photo_bg}'>";
                        $template_data .= "<div class='arm_template_loading'><img src='".MEMBERSHIPLITE_IMAGES_URL."/loader.gif' alt='".__('Loading','ARMember')."..' /></div>";
                        $template_data .= "<div class='arm_profile_picture_block_inner'>";
                            $template_data .= "<div class='arm_user_avatar'><img class='avatar arm_grid_avatar arm-avatar avatar-200 photo' src='{$default_avatar_photo}' height='200' width='200' /></div>";
                            $template_data .= "<div class='arm_profile_separator'></div>";
                            $template_data .= "<div class='arm_profile_header_info'>";
                                $template_data .= "<span class='arm_profile_name_link'>Will Smith</span>";
                               
                                $display_joining_date = ( isset($options['show_joining']) && $options['show_joining'] == 1 ) ? '' : 'hidden_section';
                                $template_data .= "<div class='arm_user_last_active_text {$display_joining_date}'>".__('Member Since','ARMember').' '.date($arm_global_settings->arm_get_wp_date_format())."</div>";
                                $template_data .= "<div class='social_profile_fields'>";
                                    foreach($social_fields_array as $fk => $val ){
                                        $k = array_keys($dbSocialFields,$fk);
                                        $cls = isset($k[0]) && ($dbSocialFields[$k[0]] == $fk) ? '' : 'hidden_section';
                                        $template_data .= "<div class='arm_social_prof_div {$cls} arm_user_social_fields arm_social_field_{$fk}'>";
                                            $template_data .= "<a href='#'></a>";
                                        $template_data .= "</div>";
                                    }
                                $template_data .= "</div>";
                            $template_data .= "</div>";
                        $template_data .= "</div>";
                    $template_data .= "</div>";
                    $template_data .= "<div class='armclear'></div>";
                        $template_data .= $arm_profile_before_content_outside;
                    
                    $template_data .= "<div class='arm_profile_field_before_content_wrapper'>";
                        $template_data .= stripslashes_deep($profile_before_content);
                    $template_data .= "</div>";

                    $template_data .= "<div class='arm_profile_tab_detail'>";
                        $template_data .= "<div class='arm_general_info_container'>";
                            $template_data .= "<table class='arm_profile_detail_tbl'>";
                                $template_data .= "<tbody>";
                                    foreach($profile_fields_data['profile_fields'] as $meta_key => $meta_val ){
                                        $template_data .= "<tr id='".$meta_key."'>";
                                            $user_value = isset($profile_fields_data['default_values'][$meta_key]) ? $profile_fields_data['default_values'][$meta_key] : '';
                                            $template_data .= "<td>".$profile_fields_data['label'][$meta_key]."</td>";
                                            $template_data .= "<td>".$user_value."</td>";
                                        $template_data .= "</tr>";
                                    }
                                $template_data .= "</tbody>";
                            $template_data .= "</table>";
                        $template_data .= "</div>";
                    $template_data .= "</div>";
                    
                    $template_data .= "<div class='arm_profile_field_after_content_wrapper'>";
                        $template_data .= stripslashes_deep($profile_after_content);
                    $template_data .= "</div>";

                        $template_data .= $arm_profile_after_content_outside;

                    $template_data .= "</div>";
                $template_data .= "</div>";
            } else {
                $template_data = apply_filters('arm_profile_template_data_outside',$template_data,$template,$dbProfileFields,$options,$profile_before_content,$profile_after_content,$arm_profile_before_content_outside,$arm_profile_after_content_outside);
            }
            $template_data .= "</div>";
            $template_data .= "</div>";

            return $template_data;
        }

        function arm_change_profile_template(){
            $options = $_POST['template_options'];
            $data_type = $_POST['data_type'];
            if( isset($_POST['profile_fields']) ){
                foreach($_POST['profile_fields'] as $key => $profile_field ){
                    $options['profile_fields'][$key] = $key;
                    $options['label'][$key] = $profile_field;
                }
            }
            $profile_fields = array();
            $profile_fields['profile_fields'] = $options['profile_fields'];
            $profile_fields['label'] = $options['label'];
            $profile_fields['default_values'] = $this->arm_get_profile_dummy_data();
            $profile_template = isset($_POST['arm_profile_template']) ? $_POST['arm_profile_template'] : '';
            $before_content = isset($_POST['arm_before_profile_fields_content']) ? $_POST['arm_before_profile_fields_content'] : '';
            $after_content = isset($_POST['arm_after_profile_fields_content']) ? $_POST['arm_after_profile_fields_content'] : '';

            $POST_ID = intval($_POST['id']);
            $template = $this->arm_get_profile_editor_template($profile_template,$profile_fields,$options,$POST_ID,true,$before_content,$after_content,$data_type);
            echo json_encode(array('template' => $template) );
            exit;
        }

    }

}
global $arm_members_directory;
$arm_members_directory = new ARM_members_directory();