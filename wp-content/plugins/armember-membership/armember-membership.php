<?php
/*
  Plugin Name: ARMember Lite - Membership Plugin
  Description: The most powerful membership plugin to handle any complex membership wordpress sites with super ease.
  Version: 3.0
  Plugin URI: https://www.armemberplugin.com
  Author: Repute InfoSystems
  Author URI: https://www.armemberplugin.com
  Text Domain: ARMember
 */

function armember_deactivate_plugin() { 
    $armember_plugin_data = get_plugin_data(WP_PLUGIN_DIR.'/armember/armember.php'); 
    $armember_version = $armember_plugin_data['Version'];  
    if($armember_version <= '2.1'){
        wp_die('<div style="background-color: #f7f7f7;padding: 20px;font-size: 20px;color: red;">This plugin can not be acctivated. You are using ARMember Lite. Please use ARMember version greater than 2.1.</div>');
    }
    // make action magic happen here... 
} 
         
// add the action 

add_action( "activate_armember/armember.php", 'armember_deactivate_plugin', 10, 0 ); 




if(!defined('MEMBERSHIPLITE_DIR_NAME')){
define('MEMBERSHIPLITE_DIR_NAME', 'armember-membership');
define('MEMBERSHIPLITE_DIR', WP_PLUGIN_DIR . '/' . MEMBERSHIPLITE_DIR_NAME);

if (is_ssl()) {
    define('MEMBERSHIPLITE_URL', str_replace('http://', 'https://', WP_PLUGIN_URL . '/' . MEMBERSHIPLITE_DIR_NAME));
    define('ARMLITE_HOME_URL', home_url('','https'));
} else {
    define('MEMBERSHIPLITE_URL', WP_PLUGIN_URL . '/' . MEMBERSHIPLITE_DIR_NAME);  
    define('ARMLITE_HOME_URL', home_url());
}

define('MEMBERSHIPLITE_CORE_DIR', MEMBERSHIPLITE_DIR . '/core');
define('MEMBERSHIPLITE_CLASSES_DIR', MEMBERSHIPLITE_DIR . '/core/classes');
define('MEMBERSHIPLITE_CLASSES_URL', MEMBERSHIPLITE_URL . '/core/classes');
define('MEMBERSHIPLITE_WIDGET_DIR', MEMBERSHIPLITE_DIR . '/core/widgets');
define('MEMBERSHIPLITE_WIDGET_URL', MEMBERSHIPLITE_URL . '/core/widgets');
define('MEMBERSHIPLITE_IMAGES_DIR', MEMBERSHIPLITE_DIR . '/images');
define('MEMBERSHIPLITE_IMAGES_URL', MEMBERSHIPLITE_URL . '/images');
define('MEMBERSHIPLITE_LIBRARY_DIR', MEMBERSHIPLITE_DIR . '/lib');
define('MEMBERSHIPLITE_LIBRARY_URL', MEMBERSHIPLITE_URL . '/lib');
define('MEMBERSHIPLITE_INC_DIR', MEMBERSHIPLITE_DIR . '/inc');
define('MEMBERSHIPLITE_VIEWS_DIR', MEMBERSHIPLITE_DIR . '/core/views');
define('MEMBERSHIPLITE_VIEWS_URL', MEMBERSHIPLITE_URL . '/core/views');
define('MEMBERSHIPLITE_VIDEO_URL', 'https://www.youtube.com/embed/8COXGo-NetQ');
define('MEMBERSHIPLITE_DOCUMENTATION_URL', 'https://www.armemberplugin.com/documentation');

}

if(!defined('FS_METHOD')){
    @define('FS_METHOD', 'direct');
}

/* Cornerstone */

global $armPrimaryStatus, $armSecondaryStatus;
$armPrimaryStatus = array(
    '1' => __('Active', 'ARMember'),
    '2' => __('Inactive', 'ARMember'),
    '3' => __('Pending', 'ARMember'),
    '4' => __('Terminated', 'ARMember'),
);
$armSecondaryStatus = array(
    '0' => __('Admin', 'ARMember'),
    '1' => __('Account Closed', 'ARMember'),
    '2' => __('Suspended', 'ARMember'),
    '3' => __('Expired', 'ARMember'),
    '4' => __('User Cancelled', 'ARMember'),
    '5' => __('Payment Failed', 'ARMember'),
    '6' => __('Cancelled', 'ARMember'),
);

/* DEBUG LOG CONSTANTS */
define("MEMBERSHIPLITE_DEBUG_LOG", false); /* true - enable debug log (Default) & false - disable debug log */
define("MEMBERSHIPLITE_DEBUG_LOG_TYPE", "ARM_ALL");
/* Possible Values
  ARM_ALL - Enable Debug Log for All types for restriction & redirection rules (Default).
  ARM_ADMIN_PANEL - Enable Debug Log for wordpress admin panel restriction & redirection rules.
  ARM_POSTS - Enable Debug Log for wordpress default posts for restriction & redirection rules.
  ARM_PAGES - Enable Debug Log for wordpress default pages for restriction & redirection rules.
  ARM_TAXONOMY - Enable Debug Log for all taxonomies for restriction & redirection rules.
  ARM_MENU - Enable Debug Log for wordpress Menu for restriction & redirection rules.
  ARM_CUSTOM - Enable Debug Log for all types of custom posts for restriction & redirection rules.
  ARM_SPECIAL_PAGE - Enable Debug Log for all types of special pages like Archive Page, Author Page, Category Page, etc.
  ARM_SHORTCODE - Enable Debug Log for all types of restriction & redirection rules applied using shortcodes
  ARM_MAIL - Enable Debug Log for all content before mail sent.
 */


global $arm_datepicker_loaded, $arm_avatar_loaded, $arm_file_upload_field, $bpopup_loaded, $arm_load_tipso, $arm_popup_modal_elements, $arm_is_access_rule_applied, $arm_load_icheck, $arm_font_awesome_loaded, $arm_inner_form_modal;

$arm_is_access_rule_applied = 0;
$arm_datepicker_loaded = $arm_avatar_loaded = $arm_file_upload_field = $bpopup_loaded = $arm_load_tipso = $arm_font_awesome_loaded = 0;
$arm_popup_modal_elements = array();
$arm_inner_form_modal = array();
global $arm_case_types;
$arm_case_types = array(
    'admin_panel' => array(
        'protected' => false,
        'type' => 'redirect'
    ),
    'page' => array(
        'protected' => false,
        'type' => 'redirect'
    ),
    'post' => array(
        'protected' => false,
        'type' => 'redirect'
    ),
    'taxonomy' => array(
        'protected' => false,
        'type' => 'redirect'
    ),
    'menu' => array(
        'protected' => false,
        'type' => 'redirect'
    ),
    'custom' => array(
        'protected' => false,
        'type' => 'redirect'
    ),
    'special' => array(
        'protected' => false,
        'type' => 'redirect'
    ),
    'shortcode' => array(
        'protected' => false,
        'type' => 'redirect'
    ),
    'mail' => array(
        'protected' => false,
        'type' => 'redirect'
    )
);

$wpupload_dir = wp_upload_dir();
$upload_dir = $wpupload_dir['basedir'] . '/armember';
$upload_url = $wpupload_dir['baseurl'] . '/armember';
if (!is_dir($upload_dir)) {
    wp_mkdir_p($upload_dir);
}
define('MEMBERSHIPLITE_UPLOAD_DIR', $upload_dir);
define('MEMBERSHIPLITE_UPLOAD_URL', $upload_url);

/* Defining Membership Plugin Version */ 
global $arm_version;
$arm_version = '3.0';
define('MEMBERSHIPLITE_VERSION', $arm_version);

global $arm_ajaxurl;
$arm_ajaxurl = admin_url('admin-ajax.php');

global $arm_errors;
$arm_errors = new WP_Error();

global $arm_widget_effects;
$arm_widget_effects = array(
    'slide' => __('Slide', 'ARMember'),
    'crossfade' => __('Fade', 'ARMember'),
    'directscroll' => __('Direct Scroll', 'ARMember'),
    'cover' => __('Cover', 'ARMember'),
    'uncover' => __('Uncover', 'ARMember')
);


global $armlite_default_user_details_text;
$armlite_default_user_details_text = __('Unknown', 'ARMember');

/**
 * Plugin Main Class
 */
global $ARMember;
$ARMember = new ARMemberlite();

if(file_exists(MEMBERSHIPLITE_CLASSES_DIR . "/class.arm_members.php")){
    require_once( MEMBERSHIPLITE_CLASSES_DIR . "/class.arm_members.php");
}

if(file_exists(MEMBERSHIPLITE_CLASSES_DIR . "/class.arm_modal_view_in_menu.php")){
   require_once( MEMBERSHIPLITE_CLASSES_DIR . "/class.arm_modal_view_in_menu.php");
}

if(file_exists(MEMBERSHIPLITE_CLASSES_DIR . "/class.arm_restriction.php")){
    require_once( MEMBERSHIPLITE_CLASSES_DIR . "/class.arm_restriction.php");
}



if(file_exists(MEMBERSHIPLITE_CLASSES_DIR . "/class.arm_payment_gateways.php")){
    require_once( MEMBERSHIPLITE_CLASSES_DIR . "/class.arm_payment_gateways.php");
}

if(file_exists(MEMBERSHIPLITE_CLASSES_DIR . "/class.arm_shortcodes.php")){
    require_once( MEMBERSHIPLITE_CLASSES_DIR . "/class.arm_shortcodes.php");
}

if(file_exists(MEMBERSHIPLITE_CLASSES_DIR . "/class.arm_gateways_paypal.php")){
    require_once( MEMBERSHIPLITE_CLASSES_DIR . "/class.arm_gateways_paypal.php");
}



if(file_exists(MEMBERSHIPLITE_CLASSES_DIR . "/class.arm_global_settings.php")){
    require_once( MEMBERSHIPLITE_CLASSES_DIR . "/class.arm_global_settings.php");
}

if(file_exists(MEMBERSHIPLITE_CLASSES_DIR . "/class.arm_membership_setup.php")){


   require_once( MEMBERSHIPLITE_CLASSES_DIR . "/class.arm_membership_setup.php");
}

if(file_exists(MEMBERSHIPLITE_CLASSES_DIR . "/class.arm_member_forms.php")){
    require_once( MEMBERSHIPLITE_CLASSES_DIR . "/class.arm_member_forms.php");
}



if(file_exists(MEMBERSHIPLITE_CLASSES_DIR . "/class.arm_members_directory.php")){
   require_once( MEMBERSHIPLITE_CLASSES_DIR . "/class.arm_members_directory.php");
}

if(file_exists(MEMBERSHIPLITE_CLASSES_DIR . "/class.arm_subscription_plans.php")){
    require_once( MEMBERSHIPLITE_CLASSES_DIR . "/class.arm_subscription_plans.php");
}


if(file_exists(MEMBERSHIPLITE_CLASSES_DIR . "/class.arm_transaction.php")){
    require_once( MEMBERSHIPLITE_CLASSES_DIR . "/class.arm_transaction.php");
}

if(file_exists(MEMBERSHIPLITE_CLASSES_DIR . "/class.arm_crons.php")){
   require_once( MEMBERSHIPLITE_CLASSES_DIR . "/class.arm_crons.php");
}

if(file_exists(MEMBERSHIPLITE_CLASSES_DIR . "/class.arm_manage_communication.php")){
    require_once( MEMBERSHIPLITE_CLASSES_DIR . "/class.arm_manage_communication.php");
}

if(file_exists(MEMBERSHIPLITE_CLASSES_DIR . "/class.arm_members_activity.php")){
    require_once( MEMBERSHIPLITE_CLASSES_DIR . "/class.arm_members_activity.php");
}

if(file_exists(MEMBERSHIPLITE_CLASSES_DIR . "/class.arm_social_feature.php")){
    require_once( MEMBERSHIPLITE_CLASSES_DIR . "/class.arm_social_feature.php");
}

if(file_exists(MEMBERSHIPLITE_CLASSES_DIR . "/class.arm_access_rules.php")){
    require_once( MEMBERSHIPLITE_CLASSES_DIR . "/class.arm_access_rules.php");
}

if(file_exists(MEMBERSHIPLITE_CLASSES_DIR . "/class.arm_multiple_membership_feature.php")){
   require_once( MEMBERSHIPLITE_CLASSES_DIR . "/class.arm_multiple_membership_feature.php");
}

if(file_exists(MEMBERSHIPLITE_CLASSES_DIR . "/class.arm_email_settings.php")){
   require_once( MEMBERSHIPLITE_CLASSES_DIR . "/class.arm_email_settings.php");
}

if(file_exists(MEMBERSHIPLITE_CLASSES_DIR . "/class.arm_spam_filter.php")){
  require_once( MEMBERSHIPLITE_CLASSES_DIR . "/class.arm_spam_filter.php");
}

if(file_exists(MEMBERSHIPLITE_WIDGET_DIR . "/class.arm_dashboard_widgets.php")){
   require_once( MEMBERSHIPLITE_WIDGET_DIR . "/class.arm_dashboard_widgets.php");
}

if(file_exists(MEMBERSHIPLITE_WIDGET_DIR . "/class.arm_widgetForm.php")){
  require_once( MEMBERSHIPLITE_WIDGET_DIR . "/class.arm_widgetForm.php");
}

if(file_exists(MEMBERSHIPLITE_WIDGET_DIR . "/class.arm_widgetlatestMembers.php")){
   require_once( MEMBERSHIPLITE_WIDGET_DIR . "/class.arm_widgetlatestMembers.php");
}

if(file_exists(MEMBERSHIPLITE_WIDGET_DIR . "/class.arm_widgetloginwidget.php")){
   require_once( MEMBERSHIPLITE_WIDGET_DIR . "/class.arm_widgetloginwidget.php");
}

global $arm_api_url, $arm_plugin_slug, $wp_version;

//Query monitor
register_uninstall_hook(__FILE__, array('ARMemberlite', 'uninstall'));


    
   
class ARMemberlite {

    var $arm_slugs;
    var $tbl_arm_activity;
    var $tbl_arm_auto_message;
   
    var $tbl_arm_email_templates;
    var $tbl_arm_entries;
    var $tbl_arm_fail_attempts;
    var $tbl_arm_forms;
    var $tbl_arm_form_field;
    var $tbl_arm_lockdown;
    var $tbl_arm_members;
    var $tbl_arm_membership_setup;
    var $tbl_arm_payment_log;
    var $tbl_arm_bank_transfer_log;
    var $tbl_arm_subscription_plans;
    var $tbl_arm_termmeta;
    var $tbl_arm_member_templates;
   
    
    var $tbl_arm_login_history;

    function __construct() {
        global $wp, $wpdb, $arm_db_tables, $arm_access_rules, $arm_capabilities_global;

        $arm_db_tables = array(
            'tbl_arm_activity' => $wpdb->prefix . 'arm_activity',
            'tbl_arm_auto_message' => $wpdb->prefix . 'arm_auto_message',
            
            'tbl_arm_email_templates' => $wpdb->prefix . 'arm_email_templates',
            'tbl_arm_entries' => $wpdb->prefix . 'arm_entries',
            'tbl_arm_fail_attempts' => $wpdb->prefix . 'arm_fail_attempts',
            'tbl_arm_forms' => $wpdb->prefix . 'arm_forms',
            'tbl_arm_form_field' => $wpdb->prefix . 'arm_form_field',
            'tbl_arm_lockdown' => $wpdb->prefix . 'arm_lockdown',
            'tbl_arm_members' => $wpdb->prefix . 'arm_members',
            'tbl_arm_membership_setup' => $wpdb->prefix . 'arm_membership_setup',
            'tbl_arm_payment_log' => $wpdb->prefix . 'arm_payment_log',
            'tbl_arm_bank_transfer_log' => $wpdb->prefix . 'arm_bank_transfer_log',
            'tbl_arm_subscription_plans' => $wpdb->prefix . 'arm_subscription_plans',
            'tbl_arm_termmeta' => $wpdb->prefix . 'arm_termmeta',
            'tbl_arm_member_templates' => $wpdb->prefix . 'arm_member_templates',
            
            
            'tbl_arm_login_history' => $wpdb->prefix . 'arm_login_history',
        );
        /* Set Database Table Variables. */
        foreach ($arm_db_tables as $key => $table) {
            $this->$key = $table;
        }

        /* Set Page Slugs Global */
        $this->arm_slugs = $this->arm_page_slugs();

        /* Set Page Capabilities Global */
        $arm_capabilities_global = array(
            'arm_manage_members' => 'arm_manage_members',
            'arm_manage_plans' => 'arm_manage_plans',
            'arm_manage_setups' => 'arm_manage_setups',
            'arm_manage_forms' => 'arm_manage_forms',
            'arm_manage_access_rules' => 'arm_manage_access_rules',

            'arm_manage_transactions' => 'arm_manage_transactions',
            'arm_manage_email_notifications' => 'arm_manage_email_notifications',
            'arm_manage_communication' => 'arm_manage_communication',
            'arm_manage_member_templates' => 'arm_manage_member_templates',
            'arm_manage_general_settings' => 'arm_manage_general_settings',
            'arm_manage_feature_settings' => 'arm_manage_feature_settings',
            'arm_manage_block_settings' => 'arm_manage_block_settings',
            
            'arm_manage_payment_gateways' => 'arm_manage_payment_gateways',
            'arm_import_export' => 'arm_import_export',
            
        );

        register_activation_hook(__FILE__, array('ARMemberlite', 'armember_check_proversion_active'));
        register_activation_hook(__FILE__, array('ARMemberlite', 'install'));
        register_activation_hook(__FILE__, array('ARMemberlite', 'armember_check_network_activation'));

        
        add_action('admin_notices', array(&$this, 'arm_sample_admin_notice__success'), 1);
		add_action( 'admin_notices', array( $this, 'arm_display_news_notices' ) );
		add_action( 'wp_ajax_arm_dismiss_news', array( $this, 'arm_dismiss_news_notice' ) );
       
        

        /* Load Language TextDomain */
        add_action('plugins_loaded', array(&$this, 'arm_load_textdomain'));
        /* Add 'Addon' link in plugin list */
        add_filter('plugin_action_links', array(&$this, 'armPluginActionLinks'), 10, 2);
        /* Hide Update Notification */
        add_action('admin_init', array(&$this, 'arm_hide_update_notice'), 1);
        /* Init Hook */
        add_action('init', array(&$this, 'arm_init_action'));
        add_action('init', array(&$this, 'wpdbfix'));
        add_action('switch_blog', array(&$this, 'wpdbfix'));
	//Query monitor
        add_action('admin_init', array(&$this, 'arm_install_plugin_data'), 1000);
        


        add_action('admin_body_class', array(&$this, 'arm_admin_body_class'));
        add_action('admin_menu', array(&$this, 'arm_menu'), 27);
        add_action('admin_menu', array(&$this, 'arm_set_last_menu'), 50);
        add_action('admin_bar_menu', array(&$this, 'arm_add_debug_bar_menu'), 999);
        add_action('admin_enqueue_scripts', array(&$this, 'set_css'), 11);
        add_action('admin_enqueue_scripts', array(&$this, 'set_js'), 11);
        add_action('admin_enqueue_scripts', array(&$this, 'set_global_javascript_variables'), 10);

        /* Front end css and js */
        add_action('wp_head', array(&$this, 'set_front_css'), 1);
        add_action('wp_head', array(&$this, 'set_front_js'), 1);
        add_action('wp_head', array(&$this, 'set_global_javascript_variables'));

        /* Add Document Video For First Time */
        add_action('admin_footer', array(&$this, 'arm_add_document_video'), 1);
        add_action('wp_ajax_arm_do_not_show_video', array(&$this, 'arm_do_not_show_video'), 1);

        /* Add what's new popup */
        add_action('admin_footer', array(&$this, 'arm_add_new_version_release_note'), 1);
        add_action('wp_ajax_arm_dont_show_upgrade_notice', array(&$this, 'arm_dont_show_upgrade_notice'), 1);

        /* For Admin Menus. */
        add_action('adminmenu', array(&$this, 'arm_set_adminmenu'));
        add_action('wp_logout', array(&$this, 'ARM_EndSession'));
        add_action('wp_login', array(&$this, 'ARM_EndSession'));

        add_action('arm_admin_messages', array(&$this, 'arm_admin_messages_init'));
        
        /* Include All Class Files. */
	
	//Query Monitor
        if( !function_exists('is_plugin_active') ){
            require(ABSPATH.'/wp-admin/includes/plugin.php');
        }
        if (is_plugin_active('js_composer/js_composer.php') && file_exists(MEMBERSHIPLITE_CORE_DIR . '/vc/class_vc_extend.php')) {
            require_once(MEMBERSHIPLITE_CORE_DIR . '/vc/class_vc_extend.php');
            global $armlite_vcextend;
            $armlite_vcextend = new ARMLITE_VCExtend();
        }


        if( is_plugin_active('wp-rocket/wp-rocket.php') && !is_admin() ){
            add_filter('script_loader_tag', array(&$this, 'arm_prevent_rocket_loader_script'), 10, 2);
        }

        /* Register Element for Cornerstone */
        /* add_action('wp_enqueue_scripts',array(&$this,'armember_cs_enqueue'));
          add_action('cornerstone_register_elements',array(&$this,'armember_cs_register_element'));
          add_filter('cornerstone_icon_map',array(&$this,'armember_cs_icon_map')); */
        /* Register Element for Cornerstone */
        add_action('wp_footer', array(&$this, 'arm_set_js_css_conditionally'), 11);

        add_filter('block_categories',array(&$this,'arm_gutenberg_category'),10,2);

        add_action('enqueue_block_editor_assets',array(&$this,'arm_enqueue_gutenberg_assets'));

        add_action('admin_enqueue_scripts', array(&$this, 'armlite_enqueue_notice_assets'), 10);
        add_action( 'admin_notices', array( &$this, 'armlite_display_notice_for_rating') );
        add_action( 'wp_ajax_armlite_dismiss_rate_notice', array( &$this, 'armlite_reset_ratenow_notice') );
        add_action( 'wp_ajax_armlite_dismiss_rate_notice_no_display', array( &$this, 'armlite_reset_ratenow_notice_never') );
    }

    function armlite_enqueue_notice_assets(){
        global $arm_version;

        wp_register_script( 'armlite-admin-notice-script', MEMBERSHIPLITE_URL . '/js/armlite-admin-notice.js', array(), $arm_version );

        wp_enqueue_script( 'armlite-admin-notice-script' );
    }

    function armlite_reset_ratenow_notice_never(){
        update_option('armlite_display_rating_notice', 'no');
        update_option('armlite_never_display_rating_notice','true');
        die;
    }

    function armlite_reset_ratenow_notice(){

        $nextEvent = strtotime( '+60 days' );

        wp_schedule_single_event( $nextEvent, 'armlite_display_ratenow_popup' );

        update_option( 'armlite_display_rating_notice', 'no' );

        die;
    }

    function armlite_display_notice_for_rating(){
        $display_notice = get_option('armlite_display_rating_notice');
        $display_notice_never = get_option('armlite_never_display_rating_notice');
        //echo "<br>Reputelog : display_notice : ".$display_notice." || display_notice_never : ".$display_notice_never;die;

        if( '' != $display_notice && 'yes' == $display_notice && ( '' == $display_notice_never || 'yes' != $display_notice_never ) ){
            $class = 'notice notice-warning armlite-rate-notice is-dismissible';
            $message = sprintf(addslashes(esc_html__("Hey, you've been using %sARMember Lite%s for a long time. %sCould you please do us a BIG favor and give it a 5-star rating on WordPress to help us spread the word and boost our motivation. %sYour help is much appreciated. Thank you very much - %sRepute InfoSystems%s", "ARMember")), "<strong>", "</strong>", "<br/>", "<br/><br/>", "<strong>", "</strong>");
            $rate_link = 'https://wordpress.org/support/plugin/armember-membership/reviews/';
            $rate_link_text = esc_html__('OK, you deserve it','ARMember');
            $close_btn_text = esc_html__('No, Maybe later','ARMember');
            $rated_link_text = esc_html__('I already did','ARMember');

            printf( '<div class="%1$s"><p>%2$s</p><br/><br/><a href="%3$s" class="armlite_rate_link" target="_blank">%4$s</a><br/><a class="armlite_maybe_later_link" href="javascript:void(0);">%5$s</a><br/><a class="armlite_already_rated_link" href="javascript:void(0)">%6$s</a><br/>&nbsp;</div>', esc_attr( $class ), $message, esc_url( $rate_link ), esc_html( $rate_link_text ), esc_attr( $close_btn_text ), esc_html( $rated_link_text ) );
        }
    }

    function arm_gutenberg_category($category,$post){
        $new_category = array(
            array(
                'slug' => 'armember',
                'title' => 'ARMember Blocks'
            )
        );

        $final_categories = array_merge($category,$new_category);

        return $final_categories;
    }

    function arm_enqueue_gutenberg_assets(){

        global $arm_version;

        wp_register_script('armlite_gutenberg_script',MEMBERSHIPLITE_URL.'/js/arm_gutenberg_script.js',array('wp-blocks','wp-element', 'wp-editor','wp-i18n'),$arm_version);

        wp_enqueue_script('armlite_gutenberg_script');

        wp_register_style('armlite_gutenberg_style',MEMBERSHIPLITE_URL.'/css/arm_gutenberg_style.css',array(), $arm_version);

        wp_enqueue_style('armlite_gutenberg_style');

    }

    function arm_sample_admin_notice__success() {
        $is_arm_admin_notice_shown = "block !important";
        global $ARMember;
        $arm_check_is_gutenberg_page = $ARMember->arm_check_is_gutenberg_page();
        if($arm_check_is_gutenberg_page)
        {
            return true;
        }

        if((isset($_REQUEST["page"]) && $_REQUEST["page"] == "arm_manage_forms" && isset($_REQUEST["action"]) && $_REQUEST["action"] == "edit_form" ))  {
            $is_arm_admin_notice_shown = "none !important";
        }
    
    ?>
        <div class="notice arm_admin_notice_shown" style="display: <?php echo $is_arm_admin_notice_shown;?>;background-color: #faa800;color: #fff; padding: 0; border: none; margin-bottom: 0 !important">

            <p class="arm_admin_notice_shown_icn" style="padding: 13px 2px 13px 0;display: table-cell;width: 60px;text-align: center;vertical-align: middle;background-color: #ffb215;line-height: 24px;margin: 0 15px 0 0;">
                <span class="dashicons dashicons-warning" style="font-size: 25px;"></span>
            </p>
            <p class="arm_admin_notice_shown_msg" style="display: table-cell; padding: 10px 0 0 15px; font-weight: 600; font-size: 16px;">Upgrade to <a href="https://www.armemberplugin.com/product.php?rdt=t11" style="color: #fff;font-size: 18px;text-decoration: none;border-bottom: 1px solid;" target="_blank">ARMember Premium</a> to get access of all premium features and frequent updates.</p>
        </div>
    <?php
	}

	function arm_display_news_notices() {
		$arm_news = get_transient( 'arm_news' );
		if ( false == $arm_news ) {
			$url = 'https://www.armemberplugin.com/armember_addons/armemberlite_notices.php';
			$raw_response = wp_remote_post(
				$url,
				array(
					'timeout' => 5000
				)
			);

			if( !is_wp_error( $raw_response ) && 200 == $raw_response['response']['code'] ){

				$news = json_decode( $raw_response['body'], true );

			} else {
				$news = array();
			}

			set_transient( 'arm_news', json_encode( $news ), DAY_IN_SECONDS );
		} else {
			$news = json_decode( $arm_news, true );
		}
		$current_date = date('Y-m-d');

		foreach( $news as $news_id => $news_data ) {
			$isAlreadyDismissed = get_option( 'arm_' . $news_id . '_is_dismissed' );

			if( '' == $isAlreadyDismissed ) {
				$class = 'notice notice-warning arm-news-notice is-dismissible';
				$message = $news_data['description'];
				$start_date = strtotime( $news_data['start_date'] );
				$end_date = strtotime( $news_data['end_date'] );

				$current_timestamp = strtotime( $current_date );

				if( $current_timestamp >= $start_date && $current_timestamp <= $end_date ){
					$background_color = ( isset( $news_data['background'] )  && '' != $news_data['background'] ) ? 'background:' . $news_data['background'] .';' : '';
					$font_color = ( isset( $news_data['color'] )  && '' != $news_data['color'] ) ? 'color:' . $news_data['color'] .';' : '';
					$border_color = ( isset( $news_data['border'] )  && '' != $news_data['border'] ) ? 'border-left-color:' . $news_data['border'] .';' : '';

					printf(
						'<div class="%1$s" style="%2$s%3$s%4$s" id="%5$s"><p>%6$s</p></div>',
						esc_attr( $class ),
						esc_attr( $background_color ),
						esc_attr( $font_color ),
						esc_attr( $border_color ),
						esc_attr( $news_id ),
						wp_kses( $message, $this->arm_allowed_html_tags() )
					);
				}
			}
		}
	}

	function arm_allowed_html_tags(){
		$arm_allowed_html = array(
			'a' => array_merge(
				$this->arm_global_attributes(),
				$this->arm_visible_tag_attributes(),
				array(
					'href' => array(),
					'rel' => array(),
					'target' => array(),
				)
			),
			'b' => $this->arm_global_attributes(),
			'br' => $this->arm_global_attributes(),
			'button' => array_merge(
				$this->arm_global_attributes(),
				$this->arm_visible_tag_attributes(),
				array(
					'autofocus' => array(),
					'disabled' => array(),
					'formaction' => array(),
					'name' => array(),
					'type' => array(),
					'value' => array()
				)
			),
			'code' => $this->arm_global_attributes(),
			'del' => array_merge(
				$this->arm_global_attributes(),
				array(
					'cite' => array(),
					'datetime' => array()
				)
			),
			'div' => array_merge(
				$this->arm_global_attributes(),
				$this->arm_visible_tag_attributes()
			),
			'embed' => array_merge(
				$this->arm_global_attributes(),
				array(
					'height' => array(),
					'onabort' => array(),
					'oncanplay' => array(),
					'onerror' => array(),
					'src' => array(),
					'type' => array(),
					'width' => array(),
				)
			),
			'font' => array_merge(
				$this->arm_global_attributes(),
				array(
					'color' => array(),
					'face' => array(),
					'size' => array()
				)
			),
			'form' => array_merge(
				$this->arm_global_attributes(),
				array(
					'accept-charset' => array(),
					'action' => array(),
					'autocomplete' => array(),
					'enctype' => array(),
					'method' => array(),
					'name' => array(),
					'novalidate' => array(),
					'onreset' => array(),
					'onsubmit' => array(),
					'target' => array()
				)
			),
			'h1' => array_merge(
				$this->arm_global_attributes(),
				$this->arm_visible_tag_attributes()
			),
			'h2' => array_merge(
				$this->arm_global_attributes(),
				$this->arm_visible_tag_attributes()
			),
			'h3' => array_merge(
				$this->arm_global_attributes(),
				$this->arm_visible_tag_attributes()
			),
			'h4' => array_merge(
				$this->arm_global_attributes(),
				$this->arm_visible_tag_attributes()
			),
			'h5' => array_merge(
				$this->arm_global_attributes(),
				$this->arm_visible_tag_attributes()
			),
			'h6' => array_merge(
				$this->arm_global_attributes(),
				$this->arm_visible_tag_attributes()
			),
			'hr' => $this->arm_global_attributes(),
			'i' => $this->arm_global_attributes(),
			'img' => array_merge(
				$this->arm_global_attributes(),
				$this->arm_visible_tag_attributes(),
				array(
					'alt' => array(),
					'height' => array(),
					'ismap' => array(),
					'onabort' => array(),
					'onerror' => array(),
					'onload' => array(),
					'sizes' => array(),
					'src' => array(),
					'srcset' => array(),
					'usemap' => array(),
					'width' => array()
				)
			),
			'input' => array_merge(
				$this->arm_global_attributes(),
				$this->arm_visible_tag_attributes(),
				array(
					'accept' => array(),
					'alt' => array(),
					'autocomplete' => array(),
					'autofocus' => array(),
					'checked' => array(),
					'dirname' => array(),
					'disabled' => array(),
					'height' => array(),
					'list' => array(),
					'max' => array(),
					'maxlength' => array(),
					'min' => array(),
					'multiple' => array(),
					'name' => array(),
					'onload' => array(),
					'onsearch' => array(),
					'pattern' => array(),
					'placeholder' => array(),
					'readonly' => array(),
					'required' => array(),
					'size' => array(),
					'src' => array(),
					'step' => array(),
					'type' => array(),
					'value' => array(),
					'width' => array()
				)
			),
			'ins' => array_merge(
				$this->arm_global_attributes(),
				array(
					'cite' => array(),
					'datetime' => array()
				)
			),
			'label' => array_merge(
				$this->arm_global_attributes(),
				$this->arm_visible_tag_attributes(),
				array(
					'for' => array(),
				)
			),
			'li' => $this->arm_global_attributes(),
			'object' => array_merge(
				$this->arm_global_attributes(),
				array(
					'data' => array(),
					'height' => array(),
					'name' => array(),
					'onabort' => array(),
					'oncanplay' => array(),
					'onerror' => array(),
					'type' => array(),
					'usemap' => array(),
					'width' => array(),
				)
			),
			'ol' => array_merge(
				$this->arm_global_attributes(),
				array(
					'reversed' => array(),
					'start' => array()
				)
			),
			'optgroup' => array_merge(
				$this->arm_global_attributes(),
				array(
					'disabled' => array(),
					'label' => array()
				)
			),
			'option' => array_merge(
				$this->arm_global_attributes(),
				array(
					'disabled' => array(),
					'label' => array(),
					'selected' => array(),
					'value' => array()
				)
			),
			'p' => $this->arm_global_attributes(),
			'script' => array_merge(
				$this->arm_global_attributes(),
				array(
					'async' => array(),
					'charset' => array(),
					'defer' => array(),
					'onerror' => array(),
					'onload' => array(),
					'src' => array(),
					'type' => array()
				)
			),
			'section' => $this->arm_global_attributes(),
			'select' => array_merge(
				$this->arm_global_attributes(),
				$this->arm_visible_tag_attributes(),
				array(
					'autofocus' => array(),
					'disabled' => array(),
					'multiple' => array(),
					'name' => array(),
					'required' => array(),
					'size' => array()
				)
			),
			'small' => $this->arm_global_attributes(),
			'span' => $this->arm_global_attributes(),
			'strike' => $this->arm_global_attributes(),
			'strike' => $this->arm_global_attributes(),
			'strong' => $this->arm_global_attributes(),
			'sub' => $this->arm_global_attributes(),
			'sup' => $this->arm_global_attributes(),
			'table' => $this->arm_global_attributes(),
			'tbody' => $this->arm_global_attributes(),
			'thead' => $this->arm_global_attributes(),
			'tfooter' => $this->arm_global_attributes(),
			'th' => array_merge(
				$this->arm_global_attributes(),
				array(
					'colspan' => array(),
					'headers' => array(),
					'rowspan' => array(),
					'scope' => array()
				)
			),
			'td' => array_merge(
				$this->arm_global_attributes(),
				array(
					'colspan' => array(),
					'headers' => array(),
					'rowspan' => array()
				)
			),
			'tr' => $this->arm_global_attributes(),
			'textarea' => array_merge(
				$this->arm_global_attributes(),
				$this->arm_visible_tag_attributes(),
				array(
					'autofocus' => array(),
					'cols' => array(),
					'dirname' => array(),
					'disabled' => array(),
					'maxlength' => array(),
					'name' => array(),
					'placeholder' => array(),
					'readonly' => array(),
					'required' => array(),
					'rows' => array(),
					'wrap' => array()
				)
			),
			'time' => array_merge(
				$this->arm_global_attributes(),
				array(
					'datetime' => array()
				)
			),
			'u' => $this->arm_global_attributes(),
			'ul' => $this->arm_global_attributes(),
		);

		return $arm_allowed_html;
	}

	function arm_global_attributes(){
		return array(
			'class' => array(),			
			'id' => array(),
			'title' => array(),
			'tabindex' => array(),
			'lang' => array(),
			'style' => array(),
		);
	}

	function arm_visible_tag_attributes(){
		return array(
			'onblur' => array(),
			'onchange' => array(),
			'onclick' => array(),
			'oncontextmenu' => array(),
			'oncopy' => array(),
			'oncut' => array(),
			'ondblclick' => array(),
			'ondrag' => array(),
			'ondragend' => array(),
			'ondragenter' => array(),
			'ondragleave' => array(),
			'ondragover' => array(),
			'ondragstart' => array(),
			'ondrop' => array(),
			'onfocus' => array(),
			'oninput' => array(),
			'oninvalid' => array(),
			'onkeydown' => array(),
			'onkeypress' => array(),
			'onkeyup' => array(),
			'onmousedown' => array(),
			'onmousemove' => array(),
			'onmouseout' => array(),
			'onmouseover' => array(),
			'onmouseup' => array(),
			'onmousewheel' => array(),
			'onpaste' => array(),
			'onscroll' => array(),
			'onselect' => array(),
			'onwheel' => array()
		);
	}

	function arm_dismiss_news_notice(){
		$noticeId = isset( $_POST['notice_id'] ) ? $_POST['notice_id'] : '';
		if( '' != $noticeId ){
			update_option( 'arm_' . $noticeId . '_is_dismissed', true );
		}
	}

    function arm_is_gutenberg_active() {
        //Check Gutenberg plugin is installed and activated.
        $gutenberg = ! ( false === has_filter( 'replace_editor', 'gutenberg_init' ) );

        //Version Check Block editor since 5.0.
        $block_editor = version_compare( $GLOBALS['wp_version'], '5.0-beta', '>' );

        if ( ! $gutenberg && ! $block_editor ) {
            return false;
        }

        if ( ! function_exists( 'is_plugin_active' ) ) {
            include_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        if ( ! is_plugin_active( 'classic-editor/classic-editor.php' ) ) {
            return true;
        }

        $use_block_editor = get_option( 'classic-editor-replace' ) === 'no-replace';

        return $use_block_editor;
    }

    /**
    * Check is gutenberg active or not function end
    */

     function arm_check_is_gutenberg_page()
     {
        if(function_exists('is_gutenberg_page'))
        {
            if(is_gutenberg_page())
            {
                return true;
            }
        }
        else {
            if ( function_exists( 'get_current_screen' )) {
                $arm_get_current_screen = get_current_screen();
                if(is_object($arm_get_current_screen))
                {
                    if ( isset($arm_get_current_screen->base) && $arm_get_current_screen->base==='post' && $this->arm_is_gutenberg_active() ) {
                        return true;
                    }
                }
            }
        }
        return false;
     }
   

   
    function wpdbfix() {
        global $wpdb, $arm_db_tables, $ARMember;
        $wpdb->arm_termmeta = $ARMember->tbl_arm_termmeta;
    }

    function arm_init_action() {
        global $wp, $wpdb, $arm_db_tables;
        $this->arm_slugs = $this->arm_page_slugs();
        /**
         * Start Session
         */
        ob_start();
        /**
         * Plugin Hook for `Init` Actions
         */
        do_action('arm_init', $this);
    }

    /**
     * Hide WordPress Update Notifications In Plugin's Pages
     */
    function arm_hide_update_notice() {
        global $wp, $wpdb, $arm_errors, $current_user, $ARMember, $pagenow, $arm_slugs;
        if (isset($_REQUEST['page']) && in_array($_REQUEST['page'], (array) $arm_slugs)) {
            remove_action('admin_notices', 'update_nag', 3);
            remove_action('network_admin_notices', 'update_nag', 3);
            remove_action('admin_notices', 'maintenance_nag');
            remove_action('network_admin_notices', 'maintenance_nag');
            remove_action('admin_notices', 'site_admin_notice');
            remove_action('network_admin_notices', 'site_admin_notice');
            remove_action('load-update-core.php', 'wp_update_plugins');
            add_filter('pre_site_transient_update_core', array(&$this, 'arm_remove_core_updates'));
            add_filter('pre_site_transient_update_plugins', array(&$this, 'arm_remove_core_updates'));
            add_filter('pre_site_transient_update_themes', array(&$this, 'arm_remove_core_updates'));
            /* Remove BuddyPress Admin Notices */
            remove_action('bp_admin_init', 'bp_core_activation_notice', 1010);
            if (!in_array($_REQUEST['page'], array($arm_slugs->manage_forms))) {
                add_action('admin_notices', array(&$this, 'arm_admin_notices'));
            }
            global  $arm_social_feature;
            $permissionMessage = __('You do not have sufficient permissions to access this page.', 'ARMember');
            
            if (in_array($_REQUEST['page'], array($arm_slugs->profiles_directories)) && !$arm_social_feature->isSocialFeature) {
                wp_die($permissionMessage);
            }
        }
    }

    function arm_admin_notices() {
        global $wp, $wpdb, $arm_errors, $ARMember, $pagenow, $arm_global_settings;
        $notice_html = '';
        $notices = array();
        $notices = apply_filters('arm_display_admin_notices', $notices);

        if (!empty($notices)) {
            $notice_html .= '<div class="arm_admin_notices_container">';
            $notice_html .= '<ul class="arm_admin_notices">';
            foreach ($notices as $notice) {
                $notice_html .= '<li class="arm_notice arm_notice_' . $notice['type'] . '">' . $notice['message'] . '</li>';
            }
            $notice_html .= '</ul>';
            $notice_html .= '<div class="armclear"></div></div>';
        }

        $arm_get_php_version = (function_exists('phpversion')) ? phpversion() : 0;
        if(version_compare($arm_get_php_version, '5.6', '<')) {
            $notice_html .= '<div class="notice notice-warning" style="display:block;">';
            $notice_html .= '<p>'.esc_html__('ARMember Lite recommend to use Minimum PHP version 5.6 or greater.', 'ARMember').'</p>';
            $notice_html .= '</div>';
        }
        if(!empty($arm_global_settings->global_settings['enable_crop'])) {
            if (!function_exists('gd_info')) {
                $notice_html .= '<div class="notice notice-error" style="display:block;">';
                $notice_html .= '<p>'.esc_html__("ARMember Lite requires PHP GD Extension module at the server. And it seems that it's not installed or activated. Please contact your hosting provider for the same.", "ARMember").'</p>';
                $notice_html .= '</div>';
            }
        }
        echo $notice_html;
    }

    function arm_set_message($type = 'error', $message = '') {
        global $wp, $wpdb, $arm_errors, $ARMember, $pagenow;
        if (!empty($message)) {
            $ARMember->arm_session_start();
            $_SESSION['arm_message'][] = array(
                'type' => $type,
                'message' => $message,
            );
        }
        return;
    }

    function arm_remove_core_updates() {
        global $wp_version;
        return(object) array('last_checked' => time(), 'version_checked' => $wp_version,);
    }

    function arm_set_adminmenu() {
        global $menu, $submenu, $parent_file, $ARMember;
        $ARMember->arm_session_start();
        if(isset($_SESSION['arm_admin_menus']))
        {
            unset($_SESSION['arm_admin_menus']);
        }
        $_SESSION['arm_admin_menus'] = array('main_menu' => $menu, 'submenu' => $submenu);
        if (isset($submenu['arm_manage_members']) && !empty($submenu['arm_manage_members'])) {
            $armAdminMenuScript = '<script type="text/javascript">';
            $armAdminMenuScript .= 'jQuery(document).ready(function ($) {';
            $armAdminMenuScript .= 'jQuery("#toplevel_page_arm_manage_members").find("ul li").each(function(){
                
					var thisLI = jQuery(this);
					thisLI.addClass("arm-submenu-item");
					var thisLinkHref = thisLI.find("a").attr("href");
					if(thisLinkHref != "" && thisLinkHref != undefined){
						var thisLinkClass = thisLinkHref.replace("admin.php?page=","");
						thisLI.addClass(thisLinkClass);
					}
				});
				jQuery(".arm_documentation a, .arm-submenu-item a[href=\"admin.php?page=arm_documentation\"]").attr("target", "_blank");';

            $docLink = MEMBERSHIPLITE_DOCUMENTATION_URL;
            $armAdminMenuScript .= 'jQuery(".arm_documentation a, .arm-submenu-item a[href=\"admin.php?page=arm_documentation\"]").attr("href", "' . $docLink . '");';

            $armAdminMenuScript .= '});';

            $armAdminMenuScript .= '</script>';
            $armAdminMenuScript .= '<style type="text/css">';
            global  $arm_social_feature;
           
            if (!$arm_social_feature->isSocialFeature) {
                $armAdminMenuScript .= '.arm-submenu-item.arm_profiles_directories{display:none;}';
            }
           
           
            $armAdminMenuScript .= '.arm-submenu-item.arm_feature_settings a{color:#ffff00 !important;}';
            $armAdminMenuScript .= '</style>';
            echo $armAdminMenuScript;
        }
    }

    function ARM_EndSession() {
        @session_destroy();
    }

    /**
     * Loading plugin text domain
     */
    function arm_load_textdomain() {
        load_plugin_textdomain('ARMember', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }

    /* Setting Capabilities for user */

    function arm_capabilities() {
        $cap = array(
            'arm_manage_members' => __('Manage Members', 'ARMember'),
            'arm_manage_plans' => __('Manage Plans', 'ARMember'),
            'arm_manage_setups' => __('Manage Setups', 'ARMember'),
            'arm_manage_forms' => __('Manage Form Settings', 'ARMember'),
            'arm_manage_access_rules' => __('Manage Access Rules', 'ARMember'),

            'arm_manage_transactions' => __('Manage Transactions', 'ARMember'),
            'arm_manage_email_notifications' => __('Manage Email Notifications', 'ARMember'),
            'arm_manage_communication' => __('Manage Communication', 'ARMember'),
            'arm_manage_member_templates' => __('Manage Member Templates', 'ARMember'),
            'arm_manage_general_settings' => __('Manage General Settings', 'ARMember'),
            'arm_manage_feature_settings' => __('Manage Feature Settings', 'ARMember'),
            'arm_manage_block_settings' => __('Manage Block Settings', 'ARMember'),
            
            'arm_manage_payment_gateways' => __('Manage Payment Gateways', 'ARMember'),
            'arm_import_export' => __('Manage Import/Export', 'ARMember'),
            
        );
        return $cap;
    }

    function arm_page_slugs() {
        global $ARMember, $arm_slugs;
        $arm_slugs = new stdClass;
        /* Admin-Pages-Slug */
        $arm_slugs->main = 'arm_manage_members';
        $arm_slugs->manage_members = 'arm_manage_members';
        $arm_slugs->manage_plans = 'arm_manage_plans';
        $arm_slugs->membership_setup = 'arm_membership_setup';
        $arm_slugs->manage_forms = 'arm_manage_forms';
        $arm_slugs->access_rules = 'arm_access_rules';
       
        $arm_slugs->transactions = 'arm_transactions';
        $arm_slugs->email_notifications = 'arm_email_notifications';
        
        $arm_slugs->general_settings = 'arm_general_settings';
        $arm_slugs->feature_settings = 'arm_feature_settings';
        $arm_slugs->documentation = 'arm_documentation';
        $arm_slugs->profiles_directories = 'arm_profiles_directories';
       

        return $arm_slugs;
    }

    /**
     * Setting Menu Position
     */
    function get_free_menu_position($start, $increment = 0.1) {
        foreach ($GLOBALS['menu'] as $key => $menu) {
            $menus_positions[] = floatval($key);
        }
        if (!in_array($start, $menus_positions)) {
            $start = strval($start);
            return $start;
        } else {
            $start += $increment;
        }
        /* the position is already reserved find the closet one */
        while (in_array($start, $menus_positions)) {
            $start += $increment;
        }
        $start = strval($start);
        return $start;
    }

    function armPluginActionLinks($links, $file) {
        global $wp, $wpdb, $ARMember, $arm_slugs;
        if ($file == plugin_basename(__FILE__)) {

            if( isset( $links['deactivate'] ) ) {
                $deactivation_link = $links['deactivate'];
                // Insert an onClick action to allow form before deactivating
                $deactivation_link = str_replace( '<a ',
                    '<div class="armlite-deactivate-form-wrapper">
                         <span class="armlite-deactivate-form" id="armlite-deactivate-form-' . esc_attr( 'ARMember' ) . '"></span>
                     </div><a id="armlite-deactivate-link-' . esc_attr( 'ARMember' ) . '" ', $deactivation_link );
                $links['deactivate'] = $deactivation_link;
            }

            $armAddonsLink = admin_url('admin.php?page=' . $arm_slugs->feature_settings);
            $link = '<a title="' . __('Modules', 'ARMember') . '" href="' . esc_url($armAddonsLink) . '">' . __('Modules', 'ARMember') . '</a>';
            array_unshift($links, $link); /* Add Link To First Position */
        }
        return $links;
    }

    function arm_admin_body_class($classes) {
        global $pagenow, $arm_slugs;
        if (isset($_REQUEST['page']) && in_array($_REQUEST['page'], (array) $arm_slugs)) {
            $classes .= ' arm_wpadmin_page ';
        }
        return $classes;
    }

    /**
     * Adding Membership Admin Menu(s)
     */
    function arm_menu() {
        global $wp, $wpdb, $current_user, $arm_errors, $ARMember, $arm_slugs, $arm_global_settings, $arm_social_feature, $arm_membership_setup;

        $place = $this->get_free_menu_position(26.1, 0.3);
        if (version_compare($GLOBALS['wp_version'], '3.8', '<')) {
            echo "<style type='text/css'>.toplevel_page_arm_manage_members .wp-menu-image img{margin-top:-4px !important;}.toplevel_page_arm_manage_members .wp-menu-image .wp-menu-name{padding-left:30px !important;;}</style>";
        }
        $arm_menu_hook = add_menu_page('ARMember Lite', __('ARMember Lite', 'ARMember'), 'arm_manage_members', $arm_slugs->main, array(&$this, 'route'), MEMBERSHIPLITE_IMAGES_URL . '/armember_menu_icon.png', $place);
        $admin_menu_items = array(
            $arm_slugs->manage_members => array(
                'name' => __('Manage Members', 'ARMember'),
                'title' => __('Manage Members', 'ARMember'),
                'capability' => 'arm_manage_members'
            ),
            $arm_slugs->manage_plans => array(
                'name' => __('Manage Plans', 'ARMember'),
                'title' => __('Manage Plans', 'ARMember'),
                'capability' => 'arm_manage_plans'
            ),
            $arm_slugs->membership_setup => array(
                'name' => __('Configure Plan + Signup Page', 'ARMember'),
                'title' => __('Configure Plan + Signup Page', 'ARMember'),
                'capability' => 'arm_manage_setups'
            ),
            $arm_slugs->manage_forms => array(
                'name' => __('Manage Forms', 'ARMember'),
                'title' => __('Manage Forms', 'ARMember'),
                'capability' => 'arm_manage_forms'
            ),
            $arm_slugs->access_rules => array(
                'name' => __('Content Access Rules', 'ARMember'),
                'title' => __('Content Access Rules', 'ARMember'),
                'capability' => 'arm_manage_access_rules'
            ),
           
            $arm_slugs->transactions => array(
                'name' => __('Payment History', 'ARMember'),
                'title' => __('Payment History', 'ARMember'),
                'capability' => 'arm_manage_transactions'
            ),
            $arm_slugs->email_notifications => array(
                'name' => __('Email Notifications', 'ARMember'),
                'title' => __('Email Notifications', 'ARMember'),
                'capability' => 'arm_manage_email_notifications'
            ),
            
            $arm_slugs->profiles_directories => array(
                'name' => __('Profiles & Directories', 'ARMember'),
                'title' => __('Profiles & Directories', 'ARMember'),
                'capability' => 'arm_manage_member_templates'
            ),
            $arm_slugs->general_settings => array(
                'name' => __('General Settings', 'ARMember'),
                'title' => __('General Settings', 'ARMember'),
                'capability' => 'arm_manage_general_settings'
            ),
            
        );
        foreach ($admin_menu_items as $slug => $menu) {

            if ($slug == $arm_slugs->membership_setup) {
                $total_setups = $arm_membership_setup->arm_total_setups();
                if ($total_setups < 1) {
                    $menu['title'] = '<span style="color: #53E2F3">' . $menu['title'] . '</span>';
                }
            }
            $armSubMenuHook = add_submenu_page($arm_slugs->main, $menu['name'], $menu['title'], $menu['capability'], $slug, array(&$this, 'route'));
        }
        do_action('arm_before_last_menu');
    }

   

    
    

    function arm_set_last_menu() {
        global $wp, $wpdb, $ARMember, $arm_slugs, $arm_membership_setup;
        $admin_menu_items = array(
            $arm_slugs->feature_settings => array(
                'name' => __('Modules', 'ARMember'),
                'title' => __('Modules', 'ARMember'),
                'capability' => 'arm_manage_feature_settings'
            ),
            $arm_slugs->documentation => array(
                'name' => __('Documentation', 'ARMember'),
                'title' => __('Documentation', 'ARMember'),
                'capability' => 'arm_manage_members'
            ),
        );
        foreach ($admin_menu_items as $slug => $menu) {
            if ($slug == $arm_slugs->membership_setup) {
                $total_setups = $arm_membership_setup->arm_total_setups();
                if ($total_setups < 1) {
                    $menu['title'] = '<span style="color: #53E2F3">' . $menu['title'] . '</span>';
                }
            }
            $armSubMenuHook = add_submenu_page($arm_slugs->main, $menu['name'], $menu['title'], $menu['capability'], $slug, array(&$this, 'route'));
        }
    }

    function arm_add_debug_bar_menu($wp_admin_bar) {
        /* Admin Bar Menu */
        if (!current_user_can('administrator') || MEMBERSHIPLITE_DEBUG_LOG == false) {
            return;
        }
        $args = array(
            'id' => 'arm_debug_menu',
            'title' => __('ARMember Debug', 'ARMember'),
            'parent' => 'top-secondary',
            'href' => '#',
            'meta' => array(
                'class' => 'armember_admin_bar_debug_menu'
            )
        );
        echo "<style type='text/css'>";
        echo ".armember_admin_bar_debug_menu{
				background:#ff9a8d !Important;
			}";
        echo "</style>";
        $wp_admin_bar->add_menu($args);
    }

    /**
     * Display Admin Page View
     */
    function route() {
        global $wp, $wpdb, $arm_errors, $ARMember, $arm_slugs, $arm_members_class, $arm_member_forms, $arm_global_settings;
        if (isset($_REQUEST['page'])) {
            $pageWrapperClass = '';
            if (is_rtl()) {
                $pageWrapperClass = 'arm_page_rtl';
            }
            echo '<div class="arm_page_wrapper ' . $pageWrapperClass . '" id="arm_page_wrapper">';
            $requested_page = $_REQUEST['page'];
            do_action('arm_admin_messages', $requested_page);
            $GET_ACTION = isset($_GET['action']) ? esc_attr($_GET['action']) : '';
            $GET_id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : '';
            switch ($requested_page) {
                case $arm_slugs->main:
                case $arm_slugs->manage_members:
                    if (isset($GET_ACTION) && in_array($GET_ACTION, array('new', 'edit_member', 'view_member'))) {
                        if ($GET_ACTION == 'view_member' && !empty($GET_id) && file_exists(MEMBERSHIPLITE_VIEWS_DIR . '/arm_view_member.php')) {
                            include( MEMBERSHIPLITE_VIEWS_DIR . '/arm_view_member.php');
                        } elseif (file_exists(MEMBERSHIPLITE_VIEWS_DIR . '/arm_member_add.php')) {
                            include( MEMBERSHIPLITE_VIEWS_DIR . '/arm_member_add.php');
                        }
                    } else {
                        if (file_exists(MEMBERSHIPLITE_VIEWS_DIR . '/arm_members_list.php')) {
                            include( MEMBERSHIPLITE_VIEWS_DIR . '/arm_members_list.php');
                        }
                    }
                    break;
                case $arm_slugs->manage_plans:
                    if (isset($GET_ACTION) && in_array($GET_ACTION, array('new', 'edit_plan'))) {
                        if ($GET_ACTION == 'edit_plan' && !isset($GET_id) && file_exists(MEMBERSHIPLITE_VIEWS_DIR . '/arm_subscription_plans_list.php')) {
                            include( MEMBERSHIPLITE_VIEWS_DIR . '/arm_subscription_plans_list.php');
                        } elseif (file_exists(MEMBERSHIPLITE_VIEWS_DIR . '/arm_subscription_plans_add.php')) {
                            include( MEMBERSHIPLITE_VIEWS_DIR . '/arm_subscription_plans_add.php');
                        }
                    } else {
                        if (file_exists(MEMBERSHIPLITE_VIEWS_DIR . '/arm_subscription_plans_list.php')) {
                            include( MEMBERSHIPLITE_VIEWS_DIR . '/arm_subscription_plans_list.php');
                        }
                    }
                    break;
                case $arm_slugs->membership_setup:
                    if (isset($GET_ACTION) && in_array($GET_ACTION, array('new_setup', 'edit_setup', 'new_setup_old'))) {
                        if ($GET_ACTION == 'new_setup_old') {
                            if (file_exists(MEMBERSHIPLITE_VIEWS_DIR . '/arm_membership_setup_add_old.php')) {
                                include( MEMBERSHIPLITE_VIEWS_DIR . '/arm_membership_setup_add_old.php');
                            }
                        } elseif ($GET_ACTION == 'edit_setup' && isset($GET_id) && !empty($GET_id) && $GET_id != 0) {
                            if (file_exists(MEMBERSHIPLITE_VIEWS_DIR . '/arm_membership_setup_add.php')) {
                                include( MEMBERSHIPLITE_VIEWS_DIR . '/arm_membership_setup_add.php');
                            }
                        } else {
                            if (file_exists(MEMBERSHIPLITE_VIEWS_DIR . '/arm_membership_setup_add.php')) {
                                include( MEMBERSHIPLITE_VIEWS_DIR . '/arm_membership_setup_add.php');
                            }
                        }
                    } else {
                        global $arm_membership_setup;
                        $total_setups = $arm_membership_setup->arm_total_setups();
                        if ($total_setups < 1 && file_exists(MEMBERSHIPLITE_VIEWS_DIR . '/arm_membership_setup_add.php')) {
                            include( MEMBERSHIPLITE_VIEWS_DIR . '/arm_membership_setup_add.php');
                        } else if (file_exists(MEMBERSHIPLITE_VIEWS_DIR . '/arm_membership_setup_list.php')) {
                            include( MEMBERSHIPLITE_VIEWS_DIR . '/arm_membership_setup_list.php');
                        }
                    }
                    break;
                case $arm_slugs->manage_forms:
                    if (isset($GET_ACTION) && ($GET_ACTION == 'edit_form') && is_numeric($_GET['form_id']) && file_exists(MEMBERSHIPLITE_VIEWS_DIR . '/arm_form_editor.php')) {
                        include( MEMBERSHIPLITE_VIEWS_DIR . '/arm_form_editor.php');
                    } else {
                        if (file_exists(MEMBERSHIPLITE_VIEWS_DIR . '/arm_manage_forms.php')) {
                            include( MEMBERSHIPLITE_VIEWS_DIR . '/arm_manage_forms.php');
                        }
                    }
                    break;
                case $arm_slugs->access_rules:
                    if (file_exists(MEMBERSHIPLITE_VIEWS_DIR . '/arm_access_rules.php')) {
                        include( MEMBERSHIPLITE_VIEWS_DIR . '/arm_access_rules.php');
                    }
                    break;
                
                case $arm_slugs->transactions:
                    if (isset($GET_ACTION) && in_array($GET_ACTION, array('new', 'edit_payment'))) {
                        if ($GET_ACTION == 'edit_payment' && !isset($GET_id) && file_exists(MEMBERSHIPLITE_VIEWS_DIR . '/arm_transactions.php')) {
                            include( MEMBERSHIPLITE_VIEWS_DIR . '/arm_transactions.php');
                        } elseif (file_exists(MEMBERSHIPLITE_VIEWS_DIR . '/arm_transactions_add.php')) {
                            include( MEMBERSHIPLITE_VIEWS_DIR . '/arm_transactions_add.php');
                        }
                    } else {
                        if (file_exists(MEMBERSHIPLITE_VIEWS_DIR . '/arm_transactions.php')) {
                            include( MEMBERSHIPLITE_VIEWS_DIR . '/arm_transactions.php');
                        }
                    }
                    break;
                case $arm_slugs->email_notifications:
                    if (file_exists(MEMBERSHIPLITE_VIEWS_DIR . '/arm_email_notification.php')) {
                        include( MEMBERSHIPLITE_VIEWS_DIR . '/arm_email_notification.php');
                    }
                    break;
                
                case $arm_slugs->general_settings:
                    if (file_exists(MEMBERSHIPLITE_VIEWS_DIR . '/arm_general_settings.php')) {
                        include( MEMBERSHIPLITE_VIEWS_DIR . '/arm_general_settings.php');
                    }
                    break;
                case $arm_slugs->feature_settings:
                    if (file_exists(MEMBERSHIPLITE_VIEWS_DIR . '/arm_feature_settings.php')) {
                        include( MEMBERSHIPLITE_VIEWS_DIR . '/arm_feature_settings.php');
                    }
                    break;
                case $arm_slugs->documentation:

                    wp_redirect(MEMBERSHIPLITE_DOCUMENTATION_URL);
                    die();
                    break;
                case $arm_slugs->profiles_directories:
                    if (isset($GET_ACTION) && ($GET_ACTION == 'add_profile' || $GET_ACTION == 'edit_profile') && file_exists(MEMBERSHIPLITE_VIEWS_DIR . '/arm_profile_editor.php')) {
                        include( MEMBERSHIPLITE_VIEWS_DIR . '/arm_profile_editor.php');
                    } else {
                        if (file_exists(MEMBERSHIPLITE_VIEWS_DIR . '/arm_profiles_directories.php')) {
                            include( MEMBERSHIPLITE_VIEWS_DIR . '/arm_profiles_directories.php');
                        }
                    }
                    break;
                
                default:
                    break;
            }
            echo '</div>';
        } else {
            /* No Action */
        }
    }

    /* Setting Admin CSS  */

    function set_css() {
        global $arm_slugs;
        /* Plugin Style */
        wp_register_style('arm_admin_css', MEMBERSHIPLITE_URL . '/css/arm_admin.css', array(), MEMBERSHIPLITE_VERSION);
        wp_register_style('arm_form_style_css', MEMBERSHIPLITE_URL . '/css/arm_form_style.css', array(), MEMBERSHIPLITE_VERSION);
        wp_register_style('arm-font-awesome-css', MEMBERSHIPLITE_URL . '/css/arm-font-awesome.css', array(), MEMBERSHIPLITE_VERSION);

        /* For chosen select box */
        wp_register_style('arm_chosen_selectbox', MEMBERSHIPLITE_URL . '/css/chosen.css', array(), MEMBERSHIPLITE_VERSION);

        /* For bootstrap datetime picker */

        wp_register_style('arm_bootstrap_all_css', MEMBERSHIPLITE_URL . '/bootstrap/css/bootstrap_all.css', array(), MEMBERSHIPLITE_VERSION);

        $arm_admin_page_name = !empty( $_GET['page'] ) ? $_GET['page'] : '';
        if( !empty($arm_admin_page_name) && (preg_match('/arm_*/', $arm_admin_page_name) || $arm_admin_page_name=='badges_achievements') ) 
        {
            wp_deregister_style( 'datatables' );
            wp_dequeue_style( 'datatables' );
            
            wp_register_style( 'datatables', MEMBERSHIPLITE_URL . '/datatables/media/css/datatables.css', array(), MEMBERSHIPLITE_VERSION );
        }
        

        /* Add Style for menu icon image. */
        echo '<style type="text/css"> .toplevel_page_armember .wp-menu-image img, .toplevel_page_arm_manage_members .wp-menu-image img{padding: 5px !important;} .arm_vc_icon{background-image:url(' . MEMBERSHIPLITE_IMAGES_URL . '/armember_menu_icon.png) !important;}</style>';
        /* Add CSS file only for plugin pages. */
        if (isset($_REQUEST['page']) && in_array($_REQUEST['page'], (array) $arm_slugs)) {
            wp_enqueue_style('arm_admin_css');
            wp_enqueue_style('arm_form_style_css');
            wp_enqueue_style('arm-font-awesome-css');
            if (in_array($_REQUEST['page'], array($arm_slugs->general_settings, $arm_slugs->manage_members, $arm_slugs->manage_plans, $arm_slugs->email_notifications,  $arm_slugs->profiles_directories, $arm_slugs->access_rules,$arm_slugs->transactions))) {
                wp_enqueue_style('arm_chosen_selectbox');
                wp_enqueue_style('datatables');
            }
            if (in_array($_REQUEST['page'], array($arm_slugs->general_settings, $arm_slugs->manage_plans, $arm_slugs->manage_members, $arm_slugs->transactions))) {
                wp_enqueue_style('arm_bootstrap_all_css');
            }
            if($_REQUEST['page'] == $arm_slugs->manage_members && (isset($_REQUEST['action']) && $_REQUEST['action'] == 'view_member') && (isset($_REQUEST['view_type']) && $_REQUEST['view_type'] == 'popup')) {
                $inline_style = "html.wp-toolbar { padding-top: 0px !important; }
                #wpcontent{ margin-left: 0 !important; }
                #wpadminbar { display: none !important; }
                #adminmenumain { display: none !important; }
                .arm_view_member_wrapper { max-width: inherit !important; }";
                wp_add_inline_style('arm_admin_css', $inline_style);
            }
        }
        if (is_rtl()) {
            wp_register_style('arm_admin_css-rtl', MEMBERSHIPLITE_URL . '/css/arm_admin_rtl.css', array(), MEMBERSHIPLITE_VERSION);
            wp_enqueue_style('arm_admin_css-rtl');
        }
    }

    /* Setting Admin JavaScript */
    function set_js() {
        global $wp, $wpdb, $ARMember, $arm_slugs, $arm_global_settings, $arm_ajaxurl;

        /* Plugin JS */
        wp_register_script('arm_admin_js', MEMBERSHIPLITE_URL . '/js/arm_admin.js', array(), MEMBERSHIPLITE_VERSION);
        wp_register_script('arm_common_js', MEMBERSHIPLITE_URL . '/js/arm_common.js', array(), MEMBERSHIPLITE_VERSION);
        wp_register_script('arm_bpopup', MEMBERSHIPLITE_URL . '/js/jquery.bpopup.min.js', array('jquery'), MEMBERSHIPLITE_VERSION);
        wp_register_script('arm_jeditable', MEMBERSHIPLITE_URL . '/js/jquery.jeditable.mini.js', array(), MEMBERSHIPLITE_VERSION);
        wp_register_script('arm_icheck-js', MEMBERSHIPLITE_URL . '/js/icheck.js', array('jquery'), MEMBERSHIPLITE_VERSION);
        wp_register_script('arm_colpick-js', MEMBERSHIPLITE_URL . '/js/colpick.min.js', array('jquery'), MEMBERSHIPLITE_VERSION);
        
        /* Tooltip JS */
        wp_register_script('arm_tipso', MEMBERSHIPLITE_URL . '/js/tipso.min.js', array('jquery'), MEMBERSHIPLITE_VERSION);
        /* Form Validation */
        wp_register_script('arm_validate', MEMBERSHIPLITE_URL . '/js/jquery.validate.min.js', array('jquery'), MEMBERSHIPLITE_VERSION);
        wp_register_script('arm_tojson', MEMBERSHIPLITE_URL . '/js/jquery.json.js', array('jquery'), MEMBERSHIPLITE_VERSION);
        /* For chosen select box */
        wp_register_script('arm_chosen_jq_min', MEMBERSHIPLITE_URL . '/js/chosen.jquery.min.js', array(), MEMBERSHIPLITE_VERSION);
        /* File Upload JS */
        wp_register_script('arm_filedrag_import_user_js', MEMBERSHIPLITE_URL . '/js/filedrag/filedrag_import_user.js', array(), MEMBERSHIPLITE_VERSION);

    	wp_register_script('arm_file_upload_js',MEMBERSHIPLITE_URL . '/js/arm_file_upload_js.js',array('jquery'), MEMBERSHIPLITE_VERSION);
        wp_register_script('arm_admin_file_upload_js',MEMBERSHIPLITE_URL . '/js/arm_admin_file_upload_js.js',array('jquery'), MEMBERSHIPLITE_VERSION);
           
        /* For bootstrap datetime picker js */
        wp_register_script('arm_bootstrap_js', MEMBERSHIPLITE_URL . '/bootstrap/js/bootstrap.min.js', array('jquery'), MEMBERSHIPLITE_VERSION);
            
        wp_register_script('arm_bootstrap_datepicker_with_locale', MEMBERSHIPLITE_URL . '/bootstrap/js/bootstrap-datetimepicker-with-locale.js', array('jquery'), MEMBERSHIPLITE_VERSION);

        $arm_admin_page_name = !empty( $_GET['page'] ) ? $_GET['page'] : '';
        if( !empty($arm_admin_page_name) && (preg_match('/arm_*/', $arm_admin_page_name) || $arm_admin_page_name=='badges_achievements') ) 
        {
            wp_deregister_script('datatables');
            wp_dequeue_script( 'datatables' );

            wp_deregister_script('buttons-colvis');
            wp_dequeue_script( 'buttons-colvis' );

            wp_deregister_script('fixedcolumns');
            wp_dequeue_script( 'fixedcolumns' );

            wp_deregister_script('fourbutton');
            wp_dequeue_script( 'fourbutton' );

            wp_register_script('datatables', MEMBERSHIPLITE_URL . '/datatables/media/js/datatables.js', array(), MEMBERSHIPLITE_VERSION);
            wp_register_script('buttons-colvis', MEMBERSHIPLITE_URL . '/datatables/media/js/buttons.colVis.js', array(), MEMBERSHIPLITE_VERSION);
            wp_register_script('fixedcolumns', MEMBERSHIPLITE_URL . '/datatables/media/js/FixedColumns.js', array(), MEMBERSHIPLITE_VERSION);
            wp_register_script('fourbutton', MEMBERSHIPLITE_URL . '/datatables/media/js/four_button.js', array(), MEMBERSHIPLITE_VERSION);
        }
            
        if (isset($_REQUEST['page']) && in_array($_REQUEST['page'], (array) $arm_slugs)) {
            wp_enqueue_script('jquery');
            wp_enqueue_script('jquery-ui-core');
            wp_enqueue_script('jquery-ui-datepicker');
            wp_enqueue_script('arm_tojson');
            wp_enqueue_script('arm_icheck-js');
            wp_enqueue_script('arm_validate');
            /* Main Plugin Back-End JS */
            wp_enqueue_script('arm_bpopup');
            wp_enqueue_script('arm_tipso');
            wp_enqueue_script('arm_admin_js');
            wp_enqueue_script('arm_common_js');

            /* For the Datatable Design. */
            $dataTablePages = array(
                $arm_slugs->main,
                $arm_slugs->manage_members,
                $arm_slugs->manage_plans,
                $arm_slugs->membership_setup,
                $arm_slugs->access_rules,
               
                $arm_slugs->transactions,
                $arm_slugs->email_notifications,
            );
            if (in_array($_REQUEST['page'], $dataTablePages)) {
                wp_enqueue_script('datatables');
                wp_enqueue_script('buttons-colvis');
                wp_enqueue_script('fixedcolumns');
                wp_enqueue_script('fourbutton');
            }
            if (in_array($_REQUEST['page'], array($arm_slugs->general_settings, $arm_slugs->manage_plans, $arm_slugs->membership_setup, $arm_slugs->manage_forms, $arm_slugs->profiles_directories))) {
                wp_enqueue_script('jquery-ui-sortable');
                wp_enqueue_script('jquery-ui-draggable');
            }
            if (in_array($_REQUEST['page'], array($arm_slugs->manage_forms, $arm_slugs->profiles_directories))) {
                wp_enqueue_script('arm_jeditable');
                wp_enqueue_script('arm_colpick-js');
                wp_enqueue_style('arm_colpick-css', MEMBERSHIPLITE_URL . '/css/colpick.css', array(), MEMBERSHIPLITE_VERSION);
            }
            if (in_array($_REQUEST['page'], array($arm_slugs->general_settings, $arm_slugs->membership_setup, $arm_slugs->profiles_directories))) {
                wp_enqueue_script('arm_colpick-js');
                wp_enqueue_style('arm_colpick-css', MEMBERSHIPLITE_URL . '/css/colpick.css', array(), MEMBERSHIPLITE_VERSION);
            }
            if (in_array($_REQUEST['page'], array($arm_slugs->general_settings, $arm_slugs->manage_members, $arm_slugs->manage_forms, $arm_slugs->profiles_directories))) {
                wp_enqueue_script('arm_admin_file_upload_js');
            }
            if (in_array($_REQUEST['page'], array($arm_slugs->general_settings, $arm_slugs->manage_members, $arm_slugs->manage_plans, $arm_slugs->email_notifications, $arm_slugs->profiles_directories))) {
                wp_enqueue_script('arm_chosen_jq_min');
            }
            if (in_array($_REQUEST['page'], array($arm_slugs->general_settings, $arm_slugs->manage_plans, $arm_slugs->manage_members, $arm_slugs->transactions))) {
                wp_enqueue_script('arm_bootstrap_js');
                wp_enqueue_script('arm_bootstrap_datepicker_with_locale');
            }
            if (in_array($_REQUEST['page'], array($arm_slugs->general_settings))) {
                wp_enqueue_script('arm_filedrag_import_user_js');
                wp_enqueue_script('sack');
            }
            if (in_array($_REQUEST['page'], array($arm_slugs->manage_members))) {
                wp_enqueue_script('arm_admin_file_upload_js');
            }
        }
    }
    
    
    /* Setting global javascript variables */
    function set_global_javascript_variables(){
        
        global $arm_ajaxurl;

        echo '<script type="text/javascript" data-cfasync="false">';
            echo '__ARMAJAXURL = "'.$arm_ajaxurl.'";';
            echo '__ARMURL = "'.MEMBERSHIPLITE_URL.'";';
            echo '__ARMVIEWURL = "'.MEMBERSHIPLITE_VIEWS_URL.'";';
            echo '__ARMIMAGEURL = "'.MEMBERSHIPLITE_IMAGES_URL.'";';
            echo '__ARMISADMIN = ['.is_admin().'];';
            echo 'loadActivityError = "'.__("There is an error while loading activities, please try again.", 'ARMember').'";';
            echo 'pinterestPermissionError = "'. __("The user chose not to grant permissions or closed the pop-up", 'ARMember').'";';
            echo 'pinterestError = "'. __("Oops, there was a problem getting your information", 'ARMember').'";';
            echo 'clickToCopyError = "'. __("There is a error while copying, please try again", 'ARMember').'";';
            echo 'fbUserLoginError = "'. __("User cancelled login or did not fully authorize.", 'ARMember').'";';
            echo 'closeAccountError = "'. __("There is a error while closing account, please try again.", 'ARMember').'";';
            echo 'invalidFileTypeError = "'. __("Sorry, this file type is not permitted for security reasons.", 'ARMember').'";';
            echo 'fileSizeError = "'. __("File is not allowed bigger than {SIZE}.", 'ARMember').'";';
            echo 'fileUploadError = "'. __("There is an error in uploading file, Please try again.", 'ARMember').'";';
            echo 'coverRemoveConfirm = "'. __("Are you sure you want to remove cover photo?", 'ARMember').'";';
            echo 'profileRemoveConfirm = "'. __("Are you sure you want to remove profile photo?", 'ARMember').'";';
            echo 'errorPerformingAction = "'. __("There is an error while performing this action, please try again.", 'ARMember').'";';
            echo 'userSubscriptionCancel = "'. __("User's subscription has been canceled", 'ARMember').'";';
            
            echo 'ARM_Loding = "'. __("Loading..", 'ARMember').'";';
            echo 'Post_Publish ="'.__("After certain time of post is published", 'ARMember').'";';
            echo 'Post_Modify ="'.__("After certain time of post is modified", 'ARMember').'";';
            
            echo 'wentwrong ="'. __("Sorry, Something went wrong. Please try again.", 'ARMember').'";';
            echo 'bulkActionError = "'. __("Please select valid action.", 'ARMember').'";';
            echo 'bulkRecordsError ="'. __("Please select one or more records.", 'ARMember').'";';
            echo 'clearLoginAttempts ="'. __("Login attempts cleared successfully.", 'ARMember').'";';
            echo 'clearLoginHistory ="'. __("Login History cleared successfully.", 'ARMember').'";';
            echo 'nopasswordforimport ="'. __("Password can not be left blank.", 'ARMember').'";';
           
           
            echo 'delPlansSuccess ="'. __("Plan(s) has been deleted successfully.", 'ARMember').'";';
            echo 'delPlansError ="'. __("There is a error while deleting Plan(s), please try again.", 'ARMember').'";';
            echo 'delPlanError ="'. __("There is a error while deleting Plan, please try again.", 'ARMember').'";';
           
           
            echo 'delSetupsSuccess ="'. __("Setup(s) has been deleted successfully.", 'ARMember').'";';
            echo 'delSetupsError ="'. __("There is a error while deleting Setup(s), please try again.", 'ARMember').'";';
            echo 'delSetupSuccess ="'. __("Setup has been deleted successfully.", 'ARMember').'";';
            echo 'delSetupError ="'. __("There is a error while deleting Setup, please try again.", 'ARMember').'";';
            echo 'delFormSetSuccess ="'. __("Form Set Deleted Successfully.", 'ARMember').'";';
            echo 'delFormSetError ="'. __("There is a error while deleting form set, please try again.", 'ARMember').'";';
            echo 'delFormSuccess ="'. __("Form deleted successfully.", 'ARMember').'";';
            echo 'delFormError ="'. __("There is a error while deleting form, please try again.", 'ARMember').'";';
            echo 'delRuleSuccess ="'. __("Rule has been deleted successfully.", 'ARMember').'";';
            echo 'delRuleError ="'. __("There is a error while deleting Rule, please try again.", 'ARMember').'";';
            echo 'delRulesSuccess ="'. __("Rule(s) has been deleted successfully.", 'ARMember').'";';
            echo 'delRulesError ="'. __("There is a error while deleting Rule(s), please try again.", 'ARMember').'";';
            echo 'prevTransactionError ="'. __("There is a error while generating preview of transaction detail, Please try again.", 'ARMember').'";';
            echo 'invoiceTransactionError ="'. __("There is a error while generating invoice of transaction detail, Please try again.", 'ARMember').'";';
            echo 'prevMemberDetailError ="'. __("There is a error while generating preview of members detail, Please try again.", 'ARMember').'";';
            echo 'prevMemberActivityError ="'. __("There is a error while displaying members activities detail, Please try again.", 'ARMember').'";';
            echo 'prevCustomCssError ="'. __("There is a error while displaying ARMember CSS Class Information, Please Try Again.", 'ARMember').'";';
            echo 'prevImportMemberDetailError ="'. __("Please upload appropriate file to import users.", 'ARMember').'";';
            echo 'delTransactionSuccess ="'. __("Transaction has been deleted successfully.", 'ARMember').'";';
            echo 'delTransactionsSuccess ="'. __("Transaction(s) has been deleted successfully.", 'ARMember').'";';
            echo 'delAutoMessageSuccess ="'. __("Message has been deleted successfully.", 'ARMember').'";';
            echo 'delAutoMessageError ="'. __("There is a error while deleting Message, please try again.", 'ARMember').'";';
            echo 'delAutoMessagesSuccess ="'. __("Message(s) has been deleted successfully.", 'ARMember').'";';
            echo 'delAutoMessagesError ="'. __("There is a error while deleting Message(s), please try again.", 'ARMember').'";';
       
           
            echo 'saveSettingsSuccess ="'. __("Settings has been saved successfully.", 'ARMember').'";';
            echo 'saveSettingsError ="'. __("There is a error while updating settings, please try again.", 'ARMember').'";';
            echo 'saveDefaultRuleSuccess ="'. __("Default Rules Saved Successfully.", 'ARMember').'";';
            echo 'saveDefaultRuleError ="'. __("There is a error while updating rules, please try again.", 'ARMember').'";';
            echo 'saveOptInsSuccess ="'. __("Opt-ins Settings Saved Successfully.", 'ARMember').'";';
            echo 'saveOptInsError ="'. __("There is a error while updating opt-ins settings, please try again.", 'ARMember').'";';
            echo 'delOptInsConfirm ="'. __("Are you sure to delete configuration?", 'ARMember').'";';
            echo 'delMemberActivityError ="'. __("There is a error while deleting member activities, please try again.", 'ARMember').'";';
            echo 'noTemplateError ="'. __("Template not found.", 'ARMember').'";';
            echo 'saveTemplateSuccess ="'. __("Template options has been saved successfully.", 'ARMember').'";';
            echo 'saveTemplateError ="'. __("There is a error while updating template options, please try again.", 'ARMember').'";';
            echo 'prevTemplateError ="'. __("There is a error while generating preview of template, Please try again.", 'ARMember').'";';
            echo 'addTemplateSuccess ="'. __("Template has been added successfully.", 'ARMember').'";';
            echo 'addTemplateError ="'. __("There is a error while adding template, please try again.", 'ARMember').'";';
            echo 'delTemplateSuccess ="'. __("Template has been deleted successfully.", 'ARMember').'";';
            echo 'delTemplateError ="'. __("There is a error while deleting template, please try again.", 'ARMember').'";';
            echo 'saveEmailTemplateSuccess ="'. __("Email Template Updated Successfully.", 'ARMember').'";';
            echo 'saveAutoMessageSuccess ="'. __("Message Updated Successfully.", 'ARMember').'";';
           
            
            echo 'pastDateError ="'. __("Cannot Set Past Dates.", 'ARMember').'";';
            echo 'pastStartDateError ="'. __("Start date can not be earlier than current date.", 'ARMember').'";';
            echo 'pastExpireDateError ="'. __("Expire date can not be earlier than current date.", 'ARMember').'";';
            
            echo 'uniqueformsetname ="'. __("This Set Name is already exist.", 'ARMember').'";';
            echo 'uniquesignupformname ="'. __("This Form Name is already exist.", 'ARMember').'";';
            echo 'installAddonError ="'. __('There is an error while installing addon, Please try again.', 'ARMember').'";';
            echo 'installAddonSuccess ="'. __('Addon installed successfully.', 'ARMember').'";';
            echo 'activeAddonError ="'. __('There is an error while activating addon, Please try agina.', 'ARMember').'";';
            echo 'activeAddonSuccess ="'. __('Addon activated successfully.', 'ARMember').'";';
            echo 'deactiveAddonSuccess ="'. __('Addon deactivated successfully.', 'ARMember').'";';
            echo 'pwdstrength_vweak ="'. __('Strength: Very Weak', 'ARMember').'";';
            echo 'pwdstrength_weak ="'. __('Strength: Weak', 'ARMember').'";';
            echo 'pwdstrength_good ="'. __('Strength: Good', 'ARMember').'";';
            echo 'pwdstrength_vgood ="'. __('Strength: Strong', 'ARMember').'";';
            echo 'confirmCancelSubscription ="'. __('Are you sure you want to cancel subscription?', 'ARMember').'";';
            echo 'errorPerformingAction ="'. __("There is an error while performing this action, please try again.", 'ARMember').'";';
            echo 'arm_nothing_found ="'. __('Oops, nothing found.', 'ARMember').'";';
            echo 'armEditCurrency ="'.__('Edit', 'ARMember').'";';
            
        echo '</script>';
    }
    

    /* Setting Frond CSS */

    function set_front_css($isFrontSection = false) {
        global $wp, $wpdb, $wp_query, $ARMember, $arm_slugs, $arm_global_settings, $arm_members_directory;
        /* Main Plugin CSS */
        wp_register_style('arm_front_css', MEMBERSHIPLITE_URL . '/css/arm_front.css', array(), MEMBERSHIPLITE_VERSION);
        wp_register_style('arm_form_style_css', MEMBERSHIPLITE_URL . '/css/arm_form_style.css', array(), MEMBERSHIPLITE_VERSION);
        /* Font Awesome CSS */
        wp_register_style('arm_fontawesome_css', MEMBERSHIPLITE_URL . '/css/arm-font-awesome.css', array(), MEMBERSHIPLITE_VERSION);
        /* For bootstrap datetime picker */
        wp_register_style('arm_bootstrap_all_css', MEMBERSHIPLITE_URL . '/bootstrap/css/bootstrap_all.css', array(), MEMBERSHIPLITE_VERSION);
        /* Check Current Front-Page is Membership Page. */
        $is_arm_front_page = $this->is_arm_front_page();
        $isEnqueueAll = $arm_global_settings->arm_get_single_global_settings('enqueue_all_js_css', 0);
        $is_arm_form_in_page = $this->is_arm_form_page();
        if (($is_arm_front_page === TRUE || $isEnqueueAll == '1' || $isFrontSection) && !is_admin()) {
            wp_enqueue_style('arm_front_css');
            if ($is_arm_form_in_page || $isFrontSection || $isEnqueueAll == '1') {
                wp_enqueue_style('arm_form_style_css');
                wp_enqueue_style('arm_fontawesome_css');
            }
            wp_enqueue_style('arm_bootstrap_all_css');

         
            /**
             * Directory & Profile Templates Style
             */
            if ($isEnqueueAll == '1') {
                wp_enqueue_style('arm_form_style_css');

                $templates = $arm_members_directory->arm_default_member_templates();
                if (!empty($templates)) {
                    foreach ($templates as $tmp) {
                        if (is_file(MEMBERSHIPLITE_VIEWS_DIR . '/templates/' . $tmp['arm_slug'] . '.css')) {
                            wp_enqueue_style('arm_template_style_' . $tmp['arm_slug'], MEMBERSHIPLITE_VIEWS_URL . '/templates/' . $tmp['arm_slug'] . '.css', array(), MEMBERSHIPLITE_VERSION);
                        }
                    }
                }
            } else { 
                $found_matches = array();
                $pattern = '\[(\[?)(arm_template)(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)';
                $posts = $wp_query->posts;
                if (is_array($posts)) {
                    foreach ($posts as $post) {
                        if (preg_match_all('/' . $pattern . '/s', $post->post_content, $matches) > 0) {
                            $found_matches[] = $matches;
                        }
                    }
                    $tempids = array();
                    if (is_array($found_matches) && count($found_matches) > 0) {
                        foreach ($found_matches as $mat) {
                            if (is_array($mat) and count($mat) > 0) {
                                foreach ($mat as $k => $v) {
                                    foreach ($v as $key => $val) {
                                        $parts = explode("id=", $val);
                                        if ($parts > 0 && isset($parts[1])) {
                                            if (stripos(@$parts[1], ']') !== false) {
                                                $partsnew = explode("]", $parts[1]);
                                                $tempids[] = str_replace("'", "", str_replace('"', '', $partsnew[0]));
                                            } else if (stripos(@$parts[1], ' ') !== false) {
                                                $partsnew = explode(" ", $parts[1]);
                                                $tempids[] = str_replace("'", "", str_replace('"', '', $partsnew[0]));
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                if (!empty($tempids) && count($tempids) > 0) {
                    $tempids = $this->arm_array_unique($tempids);
                    foreach ($tempids as $tid) {
                        $tid = trim($tid);
                        /* Query Monitor Change */
			
			
                        if( isset($GLOBALS['arm_profile_template']) && isset($GLOBALS['arm_profile_template'][$tid])){
                            $tempSlug = $GLOBALS['arm_profile_template'][$tid];
                        } else {
                            $tempSlug = $wpdb->get_var("SELECT `arm_slug` FROM `" . $this->tbl_arm_member_templates . "` WHERE `arm_id`='{$tid}' AND `arm_type` != 'profile'");
                            if( !isset($GLOBALS['arm_profile_template']) ){
                                $GLOBALS['arm_profile_template'] = array();
                            }
                            $GLOBALS['arm_profile_template'][$tid] = $tempSlug;
                        }
                        
                        if (is_file(MEMBERSHIPLITE_VIEWS_DIR . '/templates/' . $tempSlug . '.css')) {
                            wp_enqueue_style('arm_template_style_' . $tempSlug, MEMBERSHIPLITE_VIEWS_URL . '/templates/' . $tempSlug . '.css', array(), MEMBERSHIPLITE_VERSION);
                        }
                    }
                }
            }
        }
    }

    /* Setting Front Side JavaScript */

    function set_front_js($isFrontSection = false) {
        global $wp, $wpdb, $post, $wp_scripts, $ARMember, $arm_ajaxurl, $arm_slugs, $arm_global_settings;
        /* Check Current Front-Page is Membership Page. */
       
        
        
        $is_arm_front_page = $this->is_arm_front_page();
        $isEnqueueAll = $arm_global_settings->arm_get_single_global_settings('enqueue_all_js_css', 0);
        if (($is_arm_front_page === TRUE || $isEnqueueAll == '1' || $isFrontSection) && !is_admin()) {
            wp_enqueue_script('jquery');
            
            /* Main Plugin Front-End JS */
            wp_register_script('arm_common_js', MEMBERSHIPLITE_URL . '/js/arm_common.js', array('jquery'), MEMBERSHIPLITE_VERSION);
            wp_register_script('arm_bpopup', MEMBERSHIPLITE_URL . '/js/jquery.bpopup.min.js', array('jquery'), MEMBERSHIPLITE_VERSION);
            /* Tooltip JS */
            wp_register_script('arm_tipso_front', MEMBERSHIPLITE_URL . '/js/tipso.min.js', array('jquery'), MEMBERSHIPLITE_VERSION);
            /* File Upload JS */
            wp_register_script('arm_file_upload_js', MEMBERSHIPLITE_URL . '/js/arm_file_upload_js.js', array('jquery'), MEMBERSHIPLITE_VERSION);
            
            /* For bootstrap datetime picker js */
            wp_register_script('arm_bootstrap_js', MEMBERSHIPLITE_URL . '/bootstrap/js/bootstrap.min.js', array('jquery'), MEMBERSHIPLITE_VERSION);

            wp_register_script('arm_bootstrap_datepicker_with_locale_js', MEMBERSHIPLITE_URL . '/bootstrap/js/bootstrap-datetimepicker-with-locale.js', array('jquery'), MEMBERSHIPLITE_VERSION);
           
            /* Enqueue Javascripts */
            wp_enqueue_script('jquery-ui-core');
            if (!wp_script_is('arm_bpopup', 'enqueued')) {
                wp_enqueue_script('arm_bpopup');
            }
            
            if (!wp_script_is('arm_bootstrap_js', 'enqueued')) {
                wp_enqueue_script('arm_bootstrap_js');
            }

            if ($isEnqueueAll == '1') {
                if (!wp_script_is('arm_bootstrap_datepicker_with_locale_js', 'enqueued')) {
                    wp_enqueue_script('arm_bootstrap_datepicker_with_locale_js');
                }
                if (!wp_script_is('arm_bpopup', 'enqueued')) {
                    wp_enqueue_script('arm_bpopup');
                }
                if (!wp_script_is('arm_file_upload_js', 'enqueued')) {
                    wp_enqueue_script('arm_file_upload_js');
                }
                if (!wp_script_is('arm_tipso_front', 'enqueued')) {
                    wp_enqueue_script('arm_tipso_front');
                }
            }

            if (!wp_script_is('arm_common_js', 'enqueued')) {
                wp_enqueue_script('arm_common_js');
            }
            /* Load Angular Assets */
            if ($isEnqueueAll == '1') {
                $this->enqueue_angular_script();
            }
        }

      
    }

    function enqueue_angular_script($include_card_validation = false) {
        global $wp, $wpdb, $post, $arm_errors, $ARMember, $arm_ajaxurl;
        /* Angular Design CSS */
        wp_register_style('arm_angular_material_css', MEMBERSHIPLITE_URL . '/css/arm_angular_material.css', array(), MEMBERSHIPLITE_VERSION);
        wp_enqueue_style('arm_angular_material_css');
        /* Angular JS */
        $angularJSFiles = array(
            'arm_angular_with_material' => MEMBERSHIPLITE_URL . '/js/angular/arm_angular_with_material.js',
            'arm_form_angular' => MEMBERSHIPLITE_URL . '/js/angular/arm_form_angular.js',
        );
        foreach ($angularJSFiles as $handle => $src) {
            if (!wp_script_is($handle, 'registered')) {
                wp_register_script($handle, $src, array(), MEMBERSHIPLITE_VERSION, true);
            }
            if (!wp_script_is($handle, 'enqueued')) {
                wp_enqueue_script($handle);
            }
        }

        if($include_card_validation){
            if (!wp_script_is('arm_angular_credit_card', 'registered')) {
                    wp_register_script('arm_angular_credit_card', MEMBERSHIPLITE_URL . '/js/angular/arm_angular_credit_card.js', array(), MEMBERSHIPLITE_VERSION, true);
            }
            if (!wp_script_is('arm_angular_credit_card', 'enqueued')) {
                    wp_enqueue_script('arm_angular_credit_card');
            }
        }
        
        
    }

    /**
     * Check front page has plugin content.
     */
    function is_arm_front_page() {
        global $wp, $wpdb, $wp_query, $post, $arm_errors, $ARMember, $arm_global_settings;
        if (!is_admin()) {
            $found_matches = array();
            $pattern = '\[(\[?)(arm.*)(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)';
            $posts = $wp_query->posts;
            if (is_array($posts)) {
                foreach ($posts as $post) {
                    if (preg_match_all('/' . $pattern . '/s', $post->post_content, $matches) > 0) {
                        $found_matches[] = $matches;
                    }
                }
            }
            /* Remove empty array values. */
            $found_matches = $this->arm_array_trim($found_matches);
            if (!empty($found_matches) && count($found_matches) > 0) {
                return TRUE;
            }
        }
        return FALSE;
    }
    
    function is_arm_setup_page() {
        global $wp, $wpdb, $wp_query, $post, $arm_errors, $ARMember, $arm_global_settings;
        if (!is_admin()) {
            $found_matches = array();
            $pattern = '\[(\[?)(arm_setup)(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)';
            $posts = $wp_query->posts;
            if (is_array($posts)) {
                foreach ($posts as $post) {
                    if (preg_match_all('/' . $pattern . '/s', $post->post_content, $matches) > 0) {
                        $found_matches[] = $matches;
                    }
                }
            }
            /* Remove empty array values. */
            $found_matches = $this->arm_array_trim($found_matches);
            if (!empty($found_matches) && count($found_matches) > 0) {
                return TRUE;
            }
        }
        return FALSE;
    }

    /**
     * Check if front page content has plugin shortcode and has form.
     */
    function is_arm_form_page() {
        global $wp, $wpdb, $wp_query, $post, $ARMember, $arm_global_settings;
        if (!is_admin()) {
            $found_matches = array();
            $pattern = '\[(\[?)(arm_form|arm_edit_profile|arm_close_account|arm_setup|arm_template)(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)';
            $posts = $wp_query->posts;
            if (is_array($posts) && !empty($posts)) {
                foreach ($posts as $key => $post) {
                    if (preg_match_all('/' . $pattern . '/s', $post->post_content, $matches) > 0) {
                        $found_matches[] = $matches;
                    }
                }
            }

            $found_matches = $this->arm_array_trim($found_matches);
            if (!empty($found_matches) && count($found_matches) > 0) {
                return true;
            }
        }
        return FALSE;
    }

    /*
     * Trim Array Values.
     */

    function arm_array_trim($array) {
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                if (is_array($value)) {
                    $array[$key] = $this->arm_array_trim($value);
                } else {
                    $array[$key] = trim($value);
                }
                if (empty($array[$key]))
                    unset($array[$key]);
            }
        } else {
            $array = trim($array);
        }
        return $array;
    }

    /**
     * Removes duplicate values from multidimensional array 
     */
    function arm_array_unique($array) {
        $result = array_map("unserialize", array_unique(array_map("serialize", $array)));
        if (is_array($result)) {
            foreach ($result as $key => $value) {
                if (is_array($value)) {
                    $result[$key] = $this->arm_array_unique($value);
                }
            }
        }
        return $result;
    }

    /**
     * Restrict Network Activation
     */
    public static function armember_check_network_activation($network_wide) {
        if (!$network_wide)
            return;

        deactivate_plugins(plugin_basename(__FILE__), TRUE, TRUE);

        header('Location: ' . network_admin_url('plugins.php?deactivate=true'));
        exit;
    }

    public static function armember_check_proversion_active(){
        if( !function_exists('is_plugin_active') ){
            require(ABSPATH.'/wp-admin/includes/plugin.php');
        }

        if(file_exists(WP_PLUGIN_DIR.'/armember/armember.php')){

            if ( is_plugin_active( 'armember/armember.php' ) ) {
                    deactivate_plugins(plugin_basename(__FILE__), TRUE, TRUE);
                     wp_die('<div style="background-color: #f7f7f7;padding: 20px;color: red;"><p style="margin: 0; font-size: 20px;">Pro version of ARMember Lite is already active, so you can not activate ARMember Lite plugin.<p style="margin: 10px 0; font-size: 16px;">Please <a href="javascript:void(0)" onclick="window.location.href=\''.network_admin_url('plugins.php?deactivate=true').'\'">Click Here</a> to go back.</p></div>');

                  
            }
           /* else{
                wp_die('<div style="background-color: #f7f7f7;padding: 20px;color: red;"><p style="margin: 0; font-size: 20px;">Pro version of ARMember Lite is already installed, so you can not activate ARMember Lite plugin. You must need to delete Pro version to activate Lite version.<p style="margin: 10px 0; font-size: 16px;">Please <a href="javascript:void(0)" onclick="window.location.href=\''.network_admin_url('plugins.php?deactivate=true').'\'">Click Here</a> to go back.</p></div>');
            }*/
        }
        else{
           /* $armember_version = get_option('arm_version'); 
            if (!empty($armember_version) && $armember_version != '') {
                wp_die('<div style="background-color: #f7f7f7;padding: 20px;color: red;"><p style="margin: 0; font-size: 20px;">Pro version of ARMember Lite is already installed, so you can not activate ARMember Lite plugin. You must need to delete Pro version to activate Lite version.<p style="margin: 10px 0; font-size: 16px;">Please <a href="javascript:void(0)" onclick="window.location.href=\''.network_admin_url('plugins.php?deactivate=true').'\'">Click Here</a> to go back.</p></div>');
            }*/

        }
    }

    public static function install() {

        


        global $ARMember, $arm_version;
        



        $armember_exists = 0;
        if(file_exists(WP_PLUGIN_DIR.'/armember/armember.php')){
            $armember_exists = 1;
        }
        $armember_version = get_option('arm_version', '');

        if( $armember_version != '' && $armember_exists == 1){
            $_version = get_option('armlite_version'); 

            if (empty($_version) || $_version == '') {
                update_option('armlite_version', $arm_version);
            } else {
                $ARMember->wpdbfix();
                do_action('arm_reactivate_plugin');
            }
        }
        else{
            $_version = get_option('armlite_version'); 

            if (empty($_version) || $_version == '') {


                require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                @set_time_limit(0);
                global $wpdb, $arm_version, $arm_global_settings;
                $arm_global_settings->arm_set_ini_for_access_rules();
                $charset_collate = '';
                if ($wpdb->has_cap('collation')) {
                    if (!empty($wpdb->charset)) {
                        $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
                    }
                    if (!empty($wpdb->collate)) {
                        $charset_collate .= " COLLATE $wpdb->collate";
                    }
                }

                update_option('armlite_version', $arm_version);
                update_option('arm_plugin_activated', 1);
                update_option('arm_show_document_video', 1);
                update_option('arm_is_social_feature', 0);
                

                $arm_dbtbl_create = array();
                /* Table structure for `Members activity` */
                $tbl_arm_members_activity = $wpdb->prefix . 'arm_activity';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_members_activity}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_members_activity}`(
                    `arm_activity_id` INT(11) NOT NULL AUTO_INCREMENT,
                    `arm_user_id` bigint(20) NOT NULL DEFAULT '0',
                    `arm_type` VARCHAR(50) NOT NULL,
                    `arm_action` VARCHAR(50) NOT NULL,
                    `arm_content` LONGTEXT NOT NULL,
                    `arm_item_id` bigint(20) NOT NULL DEFAULT '0',
                    `arm_paid_post_id` bigint(20) NOT NULL DEFAULT '0',
                    `arm_link` VARCHAR(255) DEFAULT NULL,
                    `arm_ip_address` VARCHAR(50) NOT NULL,
                    `arm_date_recorded` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    PRIMARY KEY (`arm_activity_id`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_members_activity] = dbDelta($sql_table);

               

                /* Table structure for `email settings` */
                $tbl_arm_email_settings = $wpdb->prefix . 'arm_email_templates';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_email_settings}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_email_settings}`(
                    `arm_template_id` INT(11) NOT NULL AUTO_INCREMENT,
                    `arm_template_name` VARCHAR(255) NOT NULL,
                    `arm_template_slug` VARCHAR(255) NOT NULL ,
                    `arm_template_subject` VARCHAR(255) NOT NULL,
                    `arm_template_content` longtext NOT NULL,
                    `arm_template_status` INT(1) NOT NULL DEFAULT '1',
                    PRIMARY KEY (`arm_template_id`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_email_settings] = dbDelta($sql_table);

                /* Table structure for `Entries` */
                $tbl_arm_entries = $wpdb->prefix . 'arm_entries';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_entries}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_entries}` (
                    `arm_entry_id` bigint(20) NOT NULL AUTO_INCREMENT,
                    `arm_entry_email` varchar(255) DEFAULT NULL,
                    `arm_name` varchar(255) DEFAULT NULL,
                    `arm_description` LONGTEXT,
                    `arm_ip_address` text,
                    `arm_browser_info` text,
                    `arm_entry_value` LONGTEXT,
                    `arm_form_id` int(11) DEFAULT NULL,
                    `arm_user_id` bigint(20) DEFAULT NULL,
                    `arm_plan_id` int(11) DEFAULT NULL,
                    `arm_is_post_entry` TINYINT(1) NOT NULL DEFAULT '0',
                    `arm_paid_post_id` BIGINT(20) NOT NULL DEFAULT '0',
                    `arm_created_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    PRIMARY KEY (`arm_entry_id`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_entries] = dbDelta($sql_table);

                /* Table structure for `failed login` */
                $tbl_arm_fail_attempts = $wpdb->prefix . 'arm_fail_attempts';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_fail_attempts}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_fail_attempts}`(
                    `arm_fail_attempts_id` bigint(20) NOT NULL AUTO_INCREMENT,
                    `arm_user_id` bigint(20) NOT NULL,
                    `arm_fail_attempts_detail` text,
                    `arm_fail_attempts_ip` varchar(200) DEFAULT NULL,
                    `arm_is_block` int(1) NOT NULL DEFAULT '0',
                    `arm_fail_attempts_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    `arm_fail_attempts_release_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    PRIMARY KEY (`arm_fail_attempts_id`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_fail_attempts] = dbDelta($sql_table);

                /* Table structure for `arm_forms` */
                $tbl_arm_forms = $wpdb->prefix . 'arm_forms';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_forms}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_forms}` (
                    `arm_form_id` INT(11) NOT NULL AUTO_INCREMENT,
                    `arm_form_label` VARCHAR(255) DEFAULT NULL,
                    `arm_form_title` VARCHAR(255) DEFAULT NULL,
                    `arm_form_type` VARCHAR(100) DEFAULT NULL,
                    `arm_form_slug` VARCHAR(255) DEFAULT NULL,
                    `arm_is_default` INT(1) NOT NULL DEFAULT '0',
                    `arm_set_name` VARCHAR(255) DEFAULT NULL,
                    `arm_set_id` INT(11) NOT NULL DEFAULT '0',
                    `arm_is_template` INT(11) NOT NULL DEFAULT '0',
                    `arm_ref_template` INT(11) NOT NULL DEFAULT '0',
                    `arm_form_settings` LONGTEXT,
                    `arm_form_updated_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    `arm_form_created_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    PRIMARY KEY (`arm_form_id`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_forms] = dbDelta($sql_table);

                /* Table structure for `arm_form_field` */
                $tbl_arm_form_field = $wpdb->prefix . 'arm_form_field';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_form_field}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_form_field}`(
                    `arm_form_field_id` INT(11) NOT NULL AUTO_INCREMENT,
                    `arm_form_field_form_id` INT(11) NOT NULL,
                    `arm_form_field_order` INT(11) NOT NULL DEFAULT '0',
                    `arm_form_field_slug` VARCHAR(255) DEFAULT NULL,
                    `arm_form_field_option` LONGTEXT,
                                    `arm_form_field_bp_field_id` INT(11) NOT NULL DEFAULT '0',
                    `arm_form_field_status` INT(1) NOT NULL DEFAULT '1',
                    `arm_form_field_created_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    PRIMARY KEY (`arm_form_field_id`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_form_field] = dbDelta($sql_table);

                /* Table structure for `lockdown` */
                $tbl_arm_lockdown = $wpdb->prefix . 'arm_lockdown';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_lockdown}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_lockdown}`(
                    `arm_lockdown_ID` bigint(20) NOT NULL AUTO_INCREMENT,
                    `arm_user_id` bigint(20) NOT NULL,
                    `arm_lockdown_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    `arm_release_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    `arm_lockdown_IP` VARCHAR(255) DEFAULT NULL,
                    PRIMARY KEY  (`arm_lockdown_ID`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_lockdown] = dbDelta($sql_table);

                /* Table structure for `arm_members` */
                $tbl_arm_members = $wpdb->prefix . 'arm_members';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_members}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_members}` (
                  `arm_member_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                  `arm_user_id` bigint(20) unsigned NOT NULL,
                  `arm_user_login` VARCHAR(60) NOT NULL DEFAULT '',
                  `arm_user_pass` VARCHAR(64) NOT NULL DEFAULT '',
                  `arm_user_nicename` VARCHAR(50) NOT NULL DEFAULT '',
                  `arm_user_email` VARCHAR(100) NOT NULL DEFAULT '',
                  `arm_user_url` VARCHAR(100) NOT NULL DEFAULT '',
                  `arm_user_registered` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                  `arm_user_activation_key` VARCHAR(60) NOT NULL DEFAULT '',
                  `arm_user_status` INT(11) NOT NULL DEFAULT '0',
                  `arm_display_name` VARCHAR(250) NOT NULL DEFAULT '',
                  `arm_user_type` int(1) NOT NULL DEFAULT '0',
                  `arm_primary_status` int(1) NOT NULL DEFAULT '1',
                  `arm_secondary_status` int(1) NOT NULL DEFAULT '0',
                  PRIMARY KEY (`arm_member_id`),
                  KEY `arm_user_login_key` (`arm_user_login`),
                  KEY `arm_user_nicename` (`arm_user_nicename`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_members] = dbDelta($sql_table);

                /* Table structure for `Membership Setup Wizard` */
                $tbl_arm_membership_setup = $wpdb->prefix . 'arm_membership_setup';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_membership_setup}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_membership_setup}`(
                    `arm_setup_id` INT(11) NOT NULL AUTO_INCREMENT,
                    `arm_setup_name` VARCHAR(255) NOT NULL,
                    `arm_setup_type` TINYINT(1) NOT NULL DEFAULT '0',
                    `arm_setup_modules` LONGTEXT,
                    `arm_setup_labels` LONGTEXT,
                    `arm_status` INT(1) NOT NULL DEFAULT '1',
                    `arm_created_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    PRIMARY KEY (`arm_setup_id`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_membership_setup] = dbDelta($sql_table);

                /* Table structure for `Payment Log` */
                $tbl_arm_payment_log = $wpdb->prefix . 'arm_payment_log';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_payment_log}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_payment_log}`(
                    `arm_log_id` INT(11) NOT NULL AUTO_INCREMENT,
                    `arm_invoice_id` INT(11) NOT NULL DEFAULT '0',
                    `arm_user_id` bigint(20) NOT NULL DEFAULT '0',
                    `arm_first_name` VARCHAR(255) DEFAULT NULL,
                    `arm_last_name` VARCHAR(255) DEFAULT NULL,
                    `arm_plan_id` bigint(20) NOT NULL DEFAULT '0',
                    `arm_old_plan_id` bigint(20) NOT NULL DEFAULT '0',
                    `arm_payment_gateway` VARCHAR(50) NOT NULL,
                    `arm_payment_type` VARCHAR(50) NOT NULL,
                    `arm_token` TEXT,
                    `arm_payer_email` VARCHAR(255) DEFAULT NULL,
                    `arm_receiver_email` VARCHAR(255) DEFAULT NULL,
                    `arm_transaction_id` TEXT,
                    `arm_transaction_payment_type` VARCHAR(100) DEFAULT NULL,
                    `arm_transaction_status` TEXT,
                    `arm_payment_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    `arm_payment_mode` VARCHAR(255),
                    `arm_payment_cycle` INT(11) NOT NULL DEFAULT '0',
                    `arm_bank_name` VARCHAR(255) DEFAULT NULL,
                    `arm_account_name` VARCHAR(255) DEFAULT NULL,
                    `arm_additional_info` LONGTEXT,
                    `arm_payment_transfer_mode` VARCHAR(255) DEFAULT NULL,
                    `arm_amount` double NOT NULL DEFAULT '0',
                    `arm_currency` VARCHAR(50) DEFAULT NULL,
                    `arm_extra_vars` LONGTEXT,
                    `arm_response_text` LONGTEXT,
                    `arm_coupon_code` VARCHAR(255) DEFAULT NULL,
                    `arm_coupon_discount` double NOT NULL DEFAULT '0',
                    `arm_coupon_discount_type` VARCHAR(50) DEFAULT NULL,
                    `arm_coupon_on_each_subscriptions` TINYINT(1) NULL DEFAULT '0',
                    `arm_is_post_payment` TINYINT(1) NOT NULL DEFAULT '0',
                    `arm_paid_post_id` BIGINT(20) NOT NULL DEFAULT '0',
                    `arm_is_trial` INT(1) NOT NULL DEFAULT '0',
                    `arm_display_log` INT(1) NOT NULL DEFAULT '1',
                    `arm_created_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    PRIMARY KEY (`arm_log_id`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_payment_log] = dbDelta($sql_table);

                
                /* Table structure for `arm_subscription_plans` */
                $tbl_arm_subscription_plans = $wpdb->prefix . 'arm_subscription_plans';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_subscription_plans}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_subscription_plans}`(
                    `arm_subscription_plan_id` INT(11) NOT NULL AUTO_INCREMENT,
                    `arm_subscription_plan_name` VARCHAR(255) NOT NULL,
                    `arm_subscription_plan_description` TEXT,
                    `arm_subscription_plan_type` VARCHAR(50) NOT NULL,
                    `arm_subscription_plan_options` LONGTEXT,
                    `arm_subscription_plan_amount` double NOT NULL DEFAULT '0',
                    `arm_subscription_plan_status` INT(1) NOT NULL DEFAULT '1',
                    `arm_subscription_plan_role` VARCHAR(100) DEFAULT NULL,
                    `arm_subscription_plan_post_id` BIGINT(20) NOT NULL DEFAULT '0',
                    `arm_subscription_plan_is_delete` INT(1) NOT NULL DEFAULT '0',
                    `arm_subscription_plan_created_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    PRIMARY KEY (`arm_subscription_plan_id`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_subscription_plans] = dbDelta($sql_table);

                /* Table structure for `Taxonomy Term Meta` */
                $tbl_arm_termmeta = $wpdb->prefix . 'arm_termmeta';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_termmeta}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_termmeta}`(
                    `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    `arm_term_id` bigint(20) unsigned NOT NULL DEFAULT '0',
                    `meta_key` VARCHAR(255) DEFAULT NULL,
                    `meta_value` longtext,
                    PRIMARY KEY (`meta_id`),
                    KEY `arm_term_id` (`arm_term_id`),
                    KEY `meta_key` (`meta_key`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_termmeta] = dbDelta($sql_table);

                /* Table structure for `Member Templates` */
                $tbl_arm_member_templates = $wpdb->prefix . 'arm_member_templates';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_member_templates}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_member_templates}`(
                    `arm_id` int(11) NOT NULL AUTO_INCREMENT,
                    `arm_title` text,
                    `arm_slug` varchar(255) DEFAULT NULL,
                    `arm_type` varchar(50) DEFAULT NULL,
                    `arm_default` int(1) NOT NULL DEFAULT '0',
                                    `arm_subscription_plan` text NULL,
                    `arm_core` int(1) NOT NULL DEFAULT '0',
                                    `arm_template_html` longtext,
                                    `arm_ref_template` int(11) NOT NULL DEFAULT '0',
                    `arm_options` longtext,
                    `arm_html_before_fields` longtext,
                    `arm_html_after_fields` longtext,
                    `arm_enable_admin_profile` int(1) NOT NULL DEFAULT '0',
                    `arm_created_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    PRIMARY KEY (`arm_id`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_member_templates] = dbDelta($sql_table);

               


                $tbl_arm_login_history = $wpdb->prefix . 'arm_login_history';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_login_history}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_login_history}`(
                    `arm_history_id` int(11) NOT NULL AUTO_INCREMENT,
                    `arm_user_id` int(11) NOT NULL,
                    `arm_logged_in_ip` varchar(255) NOT NULL,
                    `arm_logged_in_date` DATETIME NOT NULL,
                    `arm_logout_date` DATETIME NOT NULL,
                    `arm_login_duration` TIME NOT NULL,
                    `arm_history_browser` VARCHAR(255) NOT NULL,
                    `arm_history_session` VARCHAR(255) NOT NULL,
                    `arm_login_country` VARCHAR(255) NOT NULL,
                                    `arm_user_current_status` int(1) NOT NULL DEFAULT '0',
                    PRIMARY KEY (`arm_history_id`)
                ){$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_login_history] = dbDelta($sql_table);

                /* Install Default Template Forms & Fields */
                $ARMember->install_default_templates();
                $wpdb->query("ALTER TABLE `{$tbl_arm_forms}` AUTO_INCREMENT = 101");
                /* Install Default Member Forms & Fields. */
                $ARMember->install_member_form_fields();
                /* Install Default Pages. */
                $ARMember->install_default_pages();
                /* Update Page in default template */
                $ARMember->update_default_pages_for_templates();
                /* Create Custom User Role & Capabilities. */
                $ARMember->add_user_role_and_capabilities();
                
                /* Plugin Action Hook After Install Process */
                do_action('arm_after_activation_hook');
                do_action('arm_after_install');
                
            } else {
                
                $ARMember->wpdbfix();
                do_action('arm_reactivate_plugin');
            }
        }



        $args = array(
            'role' => 'administrator',
            'fields' => 'id'
        );
        $users = get_users($args);
        if (count($users) > 0) {
            foreach ($users as $key => $user_id) {
                $armroles = $ARMember->arm_capabilities();
                $userObj = new WP_User($user_id);
                foreach ($armroles as $armrole => $armroledescription) {
                    $userObj->add_cap($armrole);
                }
                unset($armrole);
                unset($armroles);
                unset($armroledescription);
            }
        }
    }

    function install_default_templates() {
        include(MEMBERSHIPLITE_CLASSES_DIR . '/templates.arm_member_forms_templates.php');
    }

    function update_default_pages_for_templates() {
        global $wpdb, $ARMember;
        $global_settings = get_option('arm_global_settings');
        $arm_settings = maybe_unserialize($global_settings);
        $page_settings = $arm_settings['page_settings'];
        $template_slugs_query = " WHERE (`arm_form_slug` LIKE 'template-login%' OR `arm_form_slug` LIKE 'template-registration%' OR `arm_form_slug` LIKE 'template-forgot%' OR `arm_form_slug` LIKE 'template-change%') AND arm_is_template = 1";
        $forms = $wpdb->get_results("SELECT * FROM `" . $ARMember->tbl_arm_forms . "` {$template_slugs_query}");
        if (count($forms) > 0) {
            foreach ($forms as $key => $value) {
                $form_id = $value->arm_form_id;
                $form_settings = maybe_unserialize($value->arm_form_settings);
                $form_settings['redirect_page'] = $page_settings['edit_profile_page_id'];
                $form_settings['registration_link_type_page'] = $page_settings['register_page_id'];
                $form_settings['forgot_password_link_type_page'] = $page_settings['forgot_password_page_id'];
                $form_settings = maybe_serialize($form_settings);
                $formData = array('arm_form_settings' => $form_settings);
                $form_update = $wpdb->update($ARMember->tbl_arm_forms, $formData, array('arm_form_id' => $form_id));
            }
        }
    }

    function arm_install_plugin_data() {
        global $wp, $wpdb, $arm_members_directory, $arm_access_rules, $arm_email_settings, $arm_subscription_plans;
        $is_activate = get_option('arm_plugin_activated', 0);
        if ($is_activate == '1') {
            delete_option('arm_plugin_activated');
            /**
             * Install Plugin Default Data For The First Time.
             */
            /* Create Free Plan. */
            $arm_subscription_plans->arm_insert_sample_subscription_plan();
            /* Install default templates */
            $arm_email_settings->arm_insert_default_email_templates();
            /* Install Default Profile Template */
            $arm_members_directory->arm_insert_default_member_templates();
           
            /* Install Default Rules */
            $arm_access_rules->install_rule_data();
            
            $arm_access_rules->install_redirection_settings();
        }
    }

    /**
     * Add Custom User Role & Capabilities
     */
    function add_user_role_and_capabilities() {
        global $wp, $wpdb, $wp_roles, $ARMember, $arm_members_class, $arm_global_settings;
        $role_name = "ARMember";
        $role_slug = sanitize_title($role_name);
        $basic_caps = array(
            $role_slug => true,
            'read' => true,
            'level_0' => true,
        );

        $wp_roles->add_role($role_slug, $role_name, $basic_caps);
        $arm_user_role = $wp_roles->get_role($role_slug);

        $wpdb->query("DELETE FROM `$ARMember->tbl_arm_members`");

        $user_table = $wpdb->users;
        $usermeta_table = $wpdb->usermeta;
        if (is_multisite()) {
            $capability_column = $wpdb->get_blog_prefix($GLOBALS['blog_id']) . 'capabilities';
            $query_to_get_remainig_users = "SELECT * FROM `{$user_table}` u INNER JOIN `{$usermeta_table}` um  ON u.ID = um.user_id WHERE 1=1 AND um.meta_key = '{$capability_column}'";
        } else {
            $query_to_get_remainig_users = "SELECT * FROM $wpdb->users";
        }
        $allMembers = $wpdb->get_results($query_to_get_remainig_users);
        $chunk_size = 100;
        if (!empty($allMembers)) {

            $arm_total_users = count($allMembers);

            if ($arm_total_users <= 15000) {
                $chunk_size = 100;
            } else if ($arm_total_users > 15000 && $arm_total_users <= 25000) {
                $chunk_size = 200;
            } else if ($arm_total_users > 25000 && $arm_total_users <= 50000) {
                $chunk_size = 300;
            } else if ($arm_total_users > 50000 && $arm_total_users <= 100000) {
                $chunk_size = 400;
            } else {
                $chunk_size = 500;
            }

            $i = 0;
            $chunked_values = '';
            foreach ($allMembers as $member) {
                $i++;
                $user_id = $member->ID;
                $arm_user_id = $user_id;
                $arm_user_login = $member->user_login;
                $arm_user_pass = $member->user_pass;
                $arm_user_nicename = $member->user_nicename;
                $arm_user_email = $member->user_email;
                $arm_user_url = $member->user_url;
                $arm_user_registered = $member->user_registered;
                $arm_user_activation_key = $member->user_activation_key;
                $arm_user_status = $member->user_status;
                $arm_display_name = $member->display_name;
                $arm_user_type = 0;
                $arm_primary_status = 1;
                $arm_secondary_status = 0;
                if ($i == 1) {
                    $chunked_values .= "(" . $arm_user_id . ",\"" . $arm_user_login . "\",\"" . $arm_user_pass . "\",\"" . $arm_user_nicename . "\",\"" . $arm_user_email . "\",\"\",\"" . $arm_user_registered . "\",\"" . $arm_user_activation_key . "\"," . $arm_user_status . ",\"" . $arm_display_name . "\",0,1,0)";
                } else {
                    $chunked_values .= ",(" . $arm_user_id . ",\"" . $arm_user_login . "\",\"" . $arm_user_pass . "\",\"" . $arm_user_nicename . "\",\"" . $arm_user_email . "\",\"\",\"" . $arm_user_registered . "\",\"" . $arm_user_activation_key . "\"," . $arm_user_status . ",\"" . $arm_display_name . "\",0,1,0)";
                }
                if ($i == $chunk_size && (!empty($chunked_values) || $chunked_values != '')) {
                    $wpdb->query('INSERT INTO `' . $ARMember->tbl_arm_members . '` (arm_user_id, arm_user_login, arm_user_pass,arm_user_nicename, arm_user_email, arm_user_url,arm_user_registered, arm_user_activation_key, arm_user_status,arm_display_name, arm_user_type, arm_primary_status,arm_secondary_status) VALUES ' . $chunked_values);
                    $i = 0;
                    $chunked_values = '';
                }
            }
            if (!empty($chunked_values) || $chunked_values != '') {
                $wpdb->query('INSERT INTO `' . $ARMember->tbl_arm_members . '` (arm_user_id, arm_user_login, arm_user_pass,arm_user_nicename, arm_user_email, arm_user_url,arm_user_registered, arm_user_activation_key, arm_user_status,arm_display_name, arm_user_type, arm_primary_status,arm_secondary_status) VALUES ' . $chunked_values);
            }
        }
    }

    /**
     * Check and Add Custom User Role & Capabilities for new users - after plugin reactivation
     */
    
    function check_new_users_after_plugin_reactivation() {

        global $wpdb, $ARMember;
        $user_table = $wpdb->users;
        $usermeta_table = $wpdb->usermeta;

        $get_all_armembers = $wpdb->get_results("select * from $ARMember->tbl_arm_members", ARRAY_A);
        $push_user_ids = array();
        $where = "WHERE 1=1";
        $where1 = '';
        foreach ($get_all_armembers as $new_user_id) {
            $push_user_ids[] = $new_user_id['arm_user_id'];
        }
        if (!empty($push_user_ids)) {
            if (is_multisite()) {
                $where1 = " AND u.ID NOT IN (" . implode(", ", $push_user_ids) . ") ";
            } else {
                $where .= " AND `ID` NOT IN (" . implode(", ", $push_user_ids) . ") ";
            }
        }

        if (is_multisite()) {
            $capability_column = $wpdb->get_blog_prefix($GLOBALS['blog_id']) . 'capabilities';
            $query_to_get_remainig_users = "SELECT * FROM `{$user_table}` u INNER JOIN `{$usermeta_table}` um  ON u.ID = um.user_id WHERE 1=1 AND um.meta_key = '{$capability_column}' {$where1}";
        } else {
            $query_to_get_remainig_users = "SELECT * FROM $wpdb->users {$where}";
        }
        
        $list_to_include_new_users = $wpdb->get_results($query_to_get_remainig_users, ARRAY_A);

        if (!empty($list_to_include_new_users)) {

            $arm_total_users = count($list_to_include_new_users);

            if ($arm_total_users <= 15000) {
                $chunk_size = 100;
            } else if ($arm_total_users > 15000 && $arm_total_users <= 25000) {
                $chunk_size = 200;
            } else if ($arm_total_users > 25000 && $arm_total_users <= 50000) {
                $chunk_size = 300;
            } else if ($arm_total_users > 50000 && $arm_total_users <= 100000) {
                $chunk_size = 400;
            } else {
                $chunk_size = 500;
            }

            $chunked_values = '';
            $i = 0;
            foreach ($list_to_include_new_users as $key => $new_users_data) {
                $i++;
                $arm_user_id = $new_users_data['ID'];
                $arm_user_login = $new_users_data['user_login'];
                $arm_user_pass = $new_users_data['user_pass'];
                $arm_user_nicename = $new_users_data['user_nicename'];
                $arm_user_email = $new_users_data['user_email'];
                $arm_user_url = $new_users_data['user_url'];
                $arm_user_registered = $new_users_data['user_registered'];
                $arm_user_activation_key = $new_users_data['user_activation_key'];
                $arm_user_status = $new_users_data['user_status'];
                $arm_display_name = $new_users_data['display_name'];
                $arm_user_type = 0;
                $arm_primary_status = 1;
                $arm_secondary_status = 0;
                if ($i == 1) {
                    $chunked_values .= "(" . $arm_user_id . ",\"" . $arm_user_login . "\",\"" . $arm_user_pass . "\",\"" . $arm_user_nicename . "\",\"" . $arm_user_email . "\",\"\",\"" . $arm_user_registered . "\",\"" . $arm_user_activation_key . "\"," . $arm_user_status . ",\"" . $arm_display_name . "\",0,1,0)";
                } else {
                    $chunked_values .= ",(" . $arm_user_id . ",\"" . $arm_user_login . "\",\"" . $arm_user_pass . "\",\"" . $arm_user_nicename . "\",\"" . $arm_user_email . "\",\"\",\"" . $arm_user_registered . "\",\"" . $arm_user_activation_key . "\"," . $arm_user_status . ",\"" . $arm_display_name . "\",0,1,0)";
                }
                if ($i == $chunk_size && $chunked_values != '') {
                    $wpdb->query('INSERT INTO `' . $ARMember->tbl_arm_members . '` (arm_user_id, arm_user_login, arm_user_pass,arm_user_nicename, arm_user_email, arm_user_url,arm_user_registered, arm_user_activation_key, arm_user_status,arm_display_name, arm_user_type, arm_primary_status,arm_secondary_status) VALUES ' . $chunked_values);
                    $i = 0;
                    $chunked_values = '';
                }
            }


            if (!empty($chunked_values) || $chunked_values != '') {
                $wpdb->query('INSERT INTO `' . $ARMember->tbl_arm_members . '` (arm_user_id, arm_user_login, arm_user_pass,arm_user_nicename, arm_user_email, arm_user_url,arm_user_registered, arm_user_activation_key, arm_user_status,arm_display_name, arm_user_type, arm_primary_status,arm_secondary_status) VALUES ' . $chunked_values);
            }
        }
    }

    /**
     * Install Default Member Forms & thier fields into Database
     */
    function install_member_form_fields() {
        global $wp, $wpdb, $arm_errors, $ARMember, $arm_members_class, $arm_member_forms, $arm_global_settings;
        /* Add Default Preset Fields */
        $defaultFields = $arm_member_forms->arm_default_preset_user_fields();
        unset($defaultFields['social_fields']);
        $defaultPresetFields = array('default' => $defaultFields);
        update_option('arm_preset_form_fields', $defaultPresetFields);
        /* Add Default Forms */
        $tbl_arm_forms = $wpdb->prefix . 'arm_forms';
        $tbl_arm_form_field = $wpdb->prefix . 'arm_form_field';

        $default_member_forms_data = $arm_member_forms->arm_default_member_forms_data();
        $insertedFields = array();
        foreach ($default_member_forms_data as $key => $val) {
            $arm_set_id = 0;
            $arm_set_name = '';
            if (in_array($key, array('login', 'forgot_password', 'change_password'))) {
                $arm_set_name = __('Default Set', 'ARMember');
                $arm_set_id = 1;
            }
            $form_data = array(
                'arm_form_label' => $val['name'],
                'arm_form_title' => $val['name'],
                'arm_form_type' => $key,
                'arm_form_slug' => sanitize_title($val['name']),
                'arm_is_default' => '1',
                'arm_set_name' => $arm_set_name,
                'arm_set_id' => $arm_set_id,
                'arm_ref_template' => '1',
                'arm_form_updated_date' => date('Y-m-d H:i:s'),
                'arm_form_created_date' => date('Y-m-d H:i:s'),
                'arm_form_settings' => maybe_serialize($val['settings'])
            );
            /* Insert Form Data */
            $wpdb->insert($tbl_arm_forms, $form_data);
            $form_id = $wpdb->insert_id;
            if (!empty($val['fields'])) {
                $i = 1;
                foreach ($val['fields'] as $field) {
                    $fid = isset($field['id']) ? $field['id'] : $field['meta_key'];
                    if ($fid == 'repeat_pass') {
                        $field['ref_field_id'] = $insertedFields[$key]['user_pass'];
                    }
                    $form_field_data = array(
                        'arm_form_field_form_id' => $form_id,
                        'arm_form_field_order' => $i,
                        'arm_form_field_slug' => isset($field['meta_key']) ? $field['meta_key'] : '',
                        'arm_form_field_created_date' => date('Y-m-d H:i:s'),
                        'arm_form_field_option' => maybe_serialize($field)
                    );
                    /* Insert Form Fields. */
                    $wpdb->insert($tbl_arm_form_field, $form_field_data);
                    $insert_field_id = $wpdb->insert_id;
                    $insertedFields[$key][$fid] = $insert_field_id;
                    $i++;
                }
            }
        }
    }

    /**
     * Install Default Plugin Pages into Database
     */
    function install_default_pages() {
        global $wp, $wpdb, $ARMember, $arm_members_class, $arm_member_forms, $arm_global_settings;
        /* Default Global Settings */
        $arm_settings = $arm_global_settings->arm_default_global_settings();
        /* Default Pages */
        $arm_pages = $arm_global_settings->arm_default_pages_content();
        if (!empty($arm_pages)) {
            foreach ($arm_pages as $pageIDKey => $page) {
                $page_id = wp_insert_post($page);
                if ($page_id != 0) {
                    $arm_settings['page_settings'][$pageIDKey] = $page_id;
                }
            }
        }
        /* Store Global Setting into DB */
        if (!empty($arm_settings)) {
            $new_global_settings = $arm_settings;
            update_option('arm_global_settings', $new_global_settings);
            /**
             * Update Redirection pages in member forms
             */
            $allForms = $arm_member_forms->arm_get_all_member_forms('`arm_form_id`, `arm_form_type`, `arm_form_settings`');
            if (!empty($allForms)) {
                foreach ($allForms as $form) {
                    $form_id = $form['arm_form_id'];
                    $form_settings = $form['arm_form_settings'];
                    $isFormUpdate = false;
                    switch ($form['arm_form_type']) {
                        case 'registration':
                            $isFormUpdate = true;
                            $form_settings['redirect_type'] = 'page';
                            $form_settings['redirect_page'] = $arm_settings['page_settings']['edit_profile_page_id'];
                            break;
                        case 'login':
                            $isFormUpdate = true;
                            $form_settings['redirect_type'] = 'page';
                            $form_settings['redirect_page'] = $arm_settings['page_settings']['edit_profile_page_id'];
                            $form_settings['registration_link_type'] = 'page';
                            $form_settings['registration_link_type_page'] = $arm_settings['page_settings']['register_page_id'];
                            $form_settings['forgot_password_link_type_page'] = $arm_settings['page_settings']['forgot_password_page_id'];
                            break;
                    }
                    if ($isFormUpdate) {
                        $formData = array('arm_form_settings' => maybe_serialize($form_settings));
                        $form_update = $wpdb->update($ARMember->tbl_arm_forms, $formData, array('arm_form_id' => $form_id));
                    }
                }
            }
        }
        /* Update Security Settings */
        $securitySettings = $arm_global_settings->arm_get_all_block_settings();
        update_option('arm_block_settings', $securitySettings);
    }

    public static function uninstall() {
        global $wpdb;
        $arm_uninstall = false;
         if ( !is_plugin_active( 'armember/armember.php' ) ) {
                $arm_uninstall = true;
                
        }
        if (is_multisite()) {
            $blogs = $wpdb->get_results("SELECT blog_id FROM {$wpdb->blogs}", ARRAY_A);
            if ($blogs) {
                foreach ($blogs as $blog) {
                    switch_to_blog($blog['blog_id']);
                    delete_option("armlite_version");
                    if( $arm_uninstall ){
                        self::arm_uninstall();
                    }
                    
                }
                restore_current_blog();
            }
        } else {
            if( $arm_uninstall ){
                        self::arm_uninstall();
                    }
        }
        /* Plugin Action Hook After Uninstall Process */
        do_action('arm_after_uninstall');
    }

    public static function arm_uninstall() {
        global $wpdb, $arm_members_class;
        /**
         * To Cancel User's Recurring Subscription from Payment Gateway
         */

        $select_member_users = "SELECT arm_user_id FROM ". $wpdb->prefix . 'arm_members';
        $query_member_users = $wpdb->get_results($select_member_users);
        if(!empty($query_member_users))
        {
            foreach ($query_member_users as $query_member_user) {
                $chk_subscription_arm_user_id = $query_member_user->arm_user_id;
                $arm_members_class->arm_before_delete_user_action($chk_subscription_arm_user_id);
            }
        }
        
        /**
         * Delete Meta Values
         */
        $wpdb->query("DELETE FROM `" . $wpdb->options . "` WHERE  `option_name` LIKE  '%arm\_%'");
        $wpdb->query("DELETE FROM `" . $wpdb->postmeta . "` WHERE  `meta_key` LIKE  '%arm\_%'");
        $wpdb->query("DELETE FROM `" . $wpdb->usermeta . "` WHERE  `meta_key` LIKE  '%arm\_%'");


        delete_option("armlite_version");
        delete_option("armIsSorted");
        delete_option("armSortOrder");
        delete_option("armSortId");
        delete_option("armSortInfo");
        delete_option("arm_new_version_installed");

        delete_site_option("armIsSorted");
        delete_site_option("armSortOrder");
        delete_site_option("armSortId");
        delete_site_option("armSortInfo");
        delete_site_option("arm_version_1_7_installed");

        /**
         * Delete Plugin DB Tables
         */
        $blog_tables = array(
           $wpdb->prefix . 'arm_activity',
            $wpdb->prefix . 'arm_auto_message',
            $wpdb->prefix . 'arm_coupons',
            $wpdb->prefix . 'arm_email_templates',
            $wpdb->prefix . 'arm_entries',
            $wpdb->prefix . 'arm_fail_attempts',
            $wpdb->prefix . 'arm_forms',
            $wpdb->prefix . 'arm_form_field',
            $wpdb->prefix . 'arm_lockdown',
            $wpdb->prefix . 'arm_members',
            $wpdb->prefix . 'arm_membership_setup',
            $wpdb->prefix . 'arm_payment_log',
            $wpdb->prefix . 'arm_payment_log_temp',
            $wpdb->prefix . 'arm_bank_transfer_log',
            $wpdb->prefix . 'arm_subscription_plans',
            $wpdb->prefix . 'arm_termmeta',
            $wpdb->prefix . 'arm_member_templates',
            $wpdb->prefix . 'arm_drip_rules',
            $wpdb->prefix . 'arm_badges_achievements',
            $wpdb->prefix . 'arm_login_history'
        );
        foreach ($blog_tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS $table ");
        }
        return true;
    }

    /**
     * Get Current Browser Info
     */
    function getBrowser($user_agent) {
        $u_agent = $user_agent;
        $bname = 'Unknown';
        $platform = 'Unknown';
        $version = "";
        $ub = "";

        /* First get the platform? */
        if (@preg_match('/linux/i', $u_agent)) {
            $platform = 'linux';
        } elseif (@preg_match('/macintosh|mac os x/i', $u_agent)) {
            $platform = 'mac';
        } elseif (@preg_match('/windows|win32/i', $u_agent)) {
            $platform = 'windows';
        }

        /* Next get the name of the useragent yes seperately and for good reason */
        if (@preg_match('/MSIE/i', $u_agent) && !@preg_match('/Opera/i', $u_agent)) {
            $bname = 'Internet Explorer';
            $ub = "MSIE";
        } elseif (@preg_match('/Firefox/i', $u_agent)) {
            $bname = 'Mozilla Firefox';
            $ub = "Firefox";
        } elseif (@preg_match('/OPR/i', $u_agent)) {
            $bname = 'Opera';
            $ub = "OPR";
        } elseif (@preg_match('/Edge/i', $u_agent)) {
            $bname = 'Edge';
            $ub = "Edge";
        } elseif (@preg_match('/Chrome/i', $u_agent)) {
            $bname = 'Google Chrome';
            $ub = "Chrome";
        } elseif (@preg_match('/Safari/i', $u_agent)) {
            $bname = 'Apple Safari';
            $ub = "Safari";
        } elseif (@preg_match('/Opera/i', $u_agent)) {
            $bname = 'Opera';
            $ub = "Opera";
        } elseif (@preg_match('/Netscape/i', $u_agent)) {
            $bname = 'Netscape';
            $ub = "Netscape";
        } elseif (@preg_match('/Trident/', $u_agent)) {
            $bname = 'Internet Explorer';
            $ub = "rv";
        }
        /* finally get the correct version number */
        $known = array('Version', $ub, 'other');
        $pattern = '#(?<browser>' . join('|', $known) . ')[/ |:]+(?<version>[0-9.|a-zA-Z.]*)#';

        if (!@preg_match_all($pattern, $u_agent, $matches)) {
            /* we have no matching number just continue */
        }

        /* see how many we have */
        $i = count($matches['browser']);
        if ($i != 1) {
            /* we will have two since we are not using 'other' argument yet */
            /* see if version is before or after the name */
            if (strripos($u_agent, "Version") < strripos($u_agent, $ub)) {
                $version = $matches['version'][0];
            } else {
                $version = $matches['version'][1];
            }
        } else {
            $version = $matches['version'][0];
        }

        /* check if we have a number */
        if ($version == null || $version == "") {
            $version = "?";
        }

        return array(
            'userAgent' => $u_agent,
            'name' => $bname,
            'version' => $version,
            'platform' => $platform,
            'pattern' => $pattern
        );
    }

    /**
     * Get Current IP Address of User/Guest
     */
    function arm_get_ip_address() {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']) && !empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        } else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else if (isset($_SERVER['HTTP_X_FORWARDED']) && !empty($_SERVER['HTTP_X_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        } else if (isset($_SERVER['HTTP_FORWARDED_FOR']) && !empty($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        } else if (isset($_SERVER['HTTP_FORWARDED']) && !empty($_SERVER['HTTP_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        } else if (isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR'])) {
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        } else {
            $ipaddress = 'UNKNOWN';
        }
        /* For Public IP Address. */
        /* $publicIP = trim(shell_exec("dig +short myip.opendns.com @resolver1.opendns.com")); */
        return $ipaddress;
    }

    function arm_write_response($response_data, $file_name = '') {
        global $wp, $wpdb, $wp_filesystem;
        if (!empty($file_name)) {
            $file_path = MEMBERSHIPLITE_DIR . '/log/' . $file_name;
        } else {
            $file_path = MEMBERSHIPLITE_DIR . '/log/response.txt';
        }
        if (file_exists(ABSPATH . 'wp-admin/includes/file.php')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            if (false === ($creds = request_filesystem_credentials($file_path, '', false, false) )) {
                /**
                 * if we get here, then we don't have credentials yet,
                 * but have just produced a form for the user to fill in,
                 * so stop processing for now
                 */
                return true; /* stop the normal page form from displaying */
            }
            /* now we have some credentials, try to get the wp_filesystem running */
            if (!WP_Filesystem($creds)) {
                /* our credentials were no good, ask the user for them again */
                request_filesystem_credentials($file_path, $method, true, false);
                return true;
            }
            @$file_data = $wp_filesystem->get_contents($file_path);
            $file_data .= $response_data;
            $file_data .= "\r\n===========================================================================\r\n";
            $breaks = array("<br />", "<br>", "<br/>");
            $file_data = str_ireplace($breaks, "\r\n", $file_data);
            
            @$write_file = $wp_filesystem->put_contents($file_path, $file_data, 0755);
            if (!$write_file) {
                /* _e('Error Saving Log.', 'ARMember'); */
            }
        }
        return;
    }

    /**
     * Function for Write Degug Log
     */
    function arm_debug_response_log($callback = '', $arm_restricted_cases = array(), $query_obj = array(), $executed_query = '', $is_mail_log = false) {
        global $wp, $wpdb, $wp_filesystem;
        if (!defined('MEMBERSHIPLITE_DEBUG_LOG') || MEMBERSHIPLITE_DEBUG_LOG == false) {
            return;
        }
        $arm_restricted_cases_filtered = "";
        if ($executed_query == "") {
            $executed_query = $wpdb->last_query;
        }
        $arm_restriction_type = 'redirect';
        if (!empty($arm_restricted_cases)) {
            foreach ($arm_restricted_cases as $key => $restricted_case) {
                if ($restricted_case['protected'] == true) {
                    $arm_restricted_cases_filtered = $arm_restricted_cases[$key]["message"];
                    $arm_restriction_type = $arm_restricted_cases[$key]['type'];
                }
            }
        }
        $arm_debug_file_path = MEMBERSHIPLITE_DIR . '/log/restriction_response.txt';
        $date = "[ " . date(get_option('date_format') . ' ' . get_option('time_format')) . " ]";
        if (file_exists(ABSPATH . 'wp-admin/includes/file.php')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            if (false === ($creds = request_filesystem_credentials($arm_debug_file_path, '', false, false) )) {
                return true;
            }
            if (!WP_Filesystem($creds)) {
                request_filesystem_credentials($arm_debug_file_path, $method, true, false);
                return true;
            }
            $debug_log_type = MEMBERSHIPLITE_DEBUG_LOG_TYPE;
            $content = " Date: " . $date . "\r\n";
            $content .= "\r\n Function :" . $callback . "\r\n";
            if ($is_mail_log == true) {
                $content .= "\r\n Log Type : Mail Notification Log \r\n";
                $content .= "\r\n Mail Content : " . $arm_restricted_cases_filtered . " \r\n";
            } else {
                $content .= "\r\n Log Type : " . $debug_log_type . "\r\n";
                $content .= "\r\n Content : " . $arm_restricted_cases_filtered . "\r\n";
                
            }
            $content .= "\r\n Last Executed Query:" . $executed_query . "\r\n";
            $arm_debug_file_data = $wp_filesystem->get_contents($arm_debug_file_path);
            $arm_debug_file_data .= $content;
            $arm_debug_file_data .= "\r\n===========================================================================\r\n";
            $breaks = array("<br />", "<br>", "<br/>");
            $arm_debug_file_data = str_ireplace($breaks, "\r\n", $arm_debug_file_data);
            
            @$write_file = $wp_filesystem->put_contents($arm_debug_file_path, $arm_debug_file_data, 0755);
            if (!$write_file) {
                /* _e('Error Saving Log.', 'ARMember'); */
            }
        }
    }

    function arm_admin_messages_init($page = '') {
        global $wp, $wpdb, $arm_errors, $ARMember, $pagenow, $arm_slugs;
        $success_msgs = '';
        $error_msgs = '';
        $ARMember->arm_session_start();
        if (isset($_SESSION['arm_message']) && !empty($_SESSION['arm_message'])) {
            foreach ($_SESSION['arm_message'] as $snotice) {
                if ($snotice['type'] == 'success') {
                    $success_msgs .= $snotice['message'];
                } else {
                    $error_msgs .= $snotice['message'];
                }
            }
            if (!empty($success_msgs)) {
                ?>
                <script type="text/javascript">jQuery(window).on("load", function () {
                        armToast('<?php echo $snotice['message']; ?>', 'success');
                    });</script>
                <?php
            } elseif (!empty($error_msgs)) {
                ?>
                <script type="text/javascript">jQuery(window).on("load", function () {
                        armToast('<?php echo $snotice['message']; ?>', 'error');
                    });</script>
                <?php
            }
            unset($_SESSION['arm_message']);
        }
        ?>
        <div class="armclear"></div>
        <div class="arm_message arm_success_message" id="arm_success_message">
            <div class="arm_message_text"><?php echo $success_msgs; ?></div>
        </div>
        <div class="arm_message arm_error_message" id="arm_error_message">
            <div class="arm_message_text"><?php echo $error_msgs; ?></div>
        </div>
        <div class="armclear"></div>
        <div class="arm_toast_container" id="arm_toast_container"></div>
        <div class="arm_loading" style="display: none;"><img src="<?php echo MEMBERSHIPLITE_IMAGES_URL; ?>/loader.gif" alt="Loading.."></div>
        <?php
    }

    function arm_do_not_show_video() {
        global $wp, $wpdb, $ARMember, $pagenow;
        $isShow = (isset($_POST['isShow']) && $_POST['isShow'] == '0') ? 0 : 1;
        $now = strtotime(current_time('mysql'));
        $time = strtotime('+10 day', $now);
        update_option('arm_show_document_video', $isShow);
        update_option('arm_show_document_video_on', $time);
        exit;
    }

    function arm_add_document_video() {
        global $wp, $wpdb, $ARMember, $pagenow, $arm_slugs;
        $popupData = '';
        if (isset($_REQUEST['page']) && in_array($_REQUEST['page'], (array) $arm_slugs)) {
            $now = strtotime(current_time('mysql'));
            $show_document_video = get_option('arm_show_document_video', 0);
            $show_document_video_on = get_option('arm_show_document_video_on', strtotime(current_time('mysql')));
            if ($show_document_video == '0') {
                return;
            }
            if ($show_document_video_on > $now) {
                return;
            }
            /* Document Video Popup */
            $popupData = '<div id="arm_document_video_popup" class="popup_wrapper arm_document_video_popup"><div class="popup_wrapper_inner">';
            $popupData .= '<div class="popup_header">';
            $popupData .= '<span class="popup_close_btn arm_popup_close_btn" onclick="armHideDocumentVideo();"></span>';
            $popupData .= '<span class="popup_header_text">' . __('Help Tutorial', 'ARMember') . '</span>';
            $popupData .= '</div>';
            $popupData .= '<div class="popup_content_text">';
            $popupData .= '<iframe src="' . MEMBERSHIPLITE_VIDEO_URL . '" allowfullscreen="" frameborder="0"> </iframe> ';
            $popupData .= '</div>';
            $popupData .= '<div class="armclear"></div>';
            $popupData .= '<div class="popup_content_btn popup_footer">';
            $popupData .= '<label><input type="checkbox" id="arm_do_not_show_video" class="arm_do_not_show_video arm_icheckbox"><span>' . __('Do not show again.', 'ARMember') . '</span></label>';
            $popupData .= '<div class="popup_content_btn_wrapper">';
            $popupData .= '<button class="arm_cancel_btn popup_close_btn" onclick="armHideDocumentVideo();" type="button">' . __('Close', 'ARMember') . '</button>';
            $popupData .= '</div>';
            $popupData .= '<div class="armclear"></div>';
            $popupData .= '</div>';
            $popupData .= '<div class="armclear"></div>';
            $popupData .= '</div></div>';
            $popupData .= '<script type="text/javascript">jQuery(window).on("load", function(){
				var v_width = jQuery( window ).width();
				if(v_width <= "1350")
		        {
		          var poup_width = "720";
		          var poup_height = "400";
		          jQuery("#arm_document_video_popup").css("width","760");
		          jQuery(".popup_content_text iframe").css("width",poup_width);
		          jQuery(".popup_content_text iframe").css("height",poup_height);
		          
		        }
		        if(v_width > "1350" && v_width <= "1600")
		        {
		          var poup_width = "750";
		          var poup_height = "430";

		          jQuery("#arm_document_video_popup").css("width","790");
		          jQuery(".popup_content_text iframe").css("width",poup_width);
		          jQuery(".popup_content_text iframe").css("height",poup_height);
		        }
		        if(v_width > "1600")
		        {
		          var poup_width = "800";
		          var poup_height = "450";
		          jQuery("#arm_document_video_popup").css("width","840");
		          jQuery(".popup_content_text iframe").css("width",poup_width);
		          jQuery(".popup_content_text iframe").css("height",poup_height);
		        }
				jQuery("#arm_document_video_popup").bPopup({
					modalClose: false,
					closeClass: "popup_close_btn",
					onClose: function(){
               			 jQuery(this).find(".popup_wrapper_inner .popup_content_text").html("");
         			},
				});
			});</script>';
            echo $popupData;
        }
    }

    function arm_add_new_version_release_note() {
        global $wp, $wpdb, $ARMember, $pagenow, $arm_slugs;
        $popupData = '';
        if (isset($_REQUEST['page']) && in_array($_REQUEST['page'], (array) $arm_slugs)) {

            $show_document_video = get_option('arm_new_version_installed', 0);

            if ($show_document_video == '0') {
                return;
            }

            $urltopost = 'https://www.armemberplugin.com/armember_addons/addon_whatsnew_list.php';

            $raw_response = wp_remote_post($urltopost, array(
                'method' => 'POST',
                'timeout' => 45,
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking' => true,
                'headers' => array(),
                //'body' => array('plugins' => urlencode(serialize($installed_plugins)), 'wpversion' => $encodedval),
                'cookies' => array()
                    )
            );
            $addon_list_html = "";
            if (is_wp_error($raw_response) || $raw_response['response']['code'] != 200) {
                $addon_list_html .= "<div class='error_message' style='margin-top:100px; padding:20px;'>" . __("Add-On listing is currently unavailable. Please try again later.", 'ARMember') . "</div>";
            } else {
                $addon_list = json_decode($raw_response['body']);
                $addon_count = count($addon_list);
                $arm_whtsnew_wrapper_width = $addon_count * 141;
                foreach ( $addon_list as $list) {

                    $addon_list_html .= '<div class="arm_add_on"><a href="'.$list->addon_url.'" target="_blank"><img src="' . $list->addon_icon_url . '" /></a><div class="arm_add_on_text"><a href="'.$list->addon_url.'" target="_blank">'.$list->addon_name.'</a></div></div>';
                }
            }

            $popupData = '<div id="arm_update_note" class="popup_wrapper arm_update_note">'
                    . '<div class="popup_wrapper_inner">';
            $popupData .= '<div class="popup_header">';
            $popupData .= '<img src="' . MEMBERSHIPLITE_IMAGES_URL . '/logo_addon.png" />';
            $popupData .= '</div>';
            $popupData .= '<div class="popup_content_text">';
            $i = 1;
            $major_changes = false;
            $change_log = $this->arm_new_version_changelog();

            if (isset($change_log) && !empty($change_log)) {



                $arm_show_critical_change_title = isset($change_log['show_critical_title']) ? $change_log['show_critical_title'] : 0;
                $arm_critical_title = isset($change_log['critical_title']) ? $change_log['critical_title'] : '';
                $arm_critical_changes = (isset($change_log['critical']) && !empty($change_log['critical'])) ? $change_log['critical'] : array();

                $arm_show_major_change_title = isset($change_log['show_major_title']) ? $change_log['show_major_title'] : 0;
                $arm_major_title = isset($change_log['major_title']) ? $change_log['major_title'] : '';
                $arm_major_changes = (isset($change_log['major']) && !empty($change_log['major'])) ? $change_log['major'] : array();

                $arm_show_other_change_title = isset($change_log['show_other_title']) ? $change_log['show_other_title'] : 0;
                $arm_other_title = isset($change_log['other_title']) ? $change_log['other_title'] : '';
                $arm_other_changes = (isset($change_log['other']) && !empty($change_log['other'])) ? $change_log['other'] : array();


                if (!empty($arm_critical_changes)) {
                    if ($arm_show_critical_change_title == 1) {
                        $popupData .= '<div class="arm_critical_change_title">' . __($arm_critical_title, 'ARMember') . '</div>';
                    }
                    $popupData .= '<div class="arm_critical_change_list"><ul>';
                    foreach ($arm_critical_changes as $value) {
                        $popupData .='<li>' . __($value, 'ARMember') . '</li>';
                    }
                    $popupData .= '</ul></div>';
                }

                if (!empty($arm_major_changes)) {
                    if ($arm_show_major_change_title == 1) {
                        $popupData .= '<div class="arm_major_change_title">' . __($arm_major_title, 'ARMember') . '</div>';
                    }
                    $popupData .= '<div class="arm_major_change_list"><ul>';
                    foreach ($arm_major_changes as $value) {
                        $popupData .='<li>' . __($value, 'ARMember') . '</li>';
                    }
                    $popupData .= '</ul></div>';
                }

                if (!empty($arm_other_changes)) {
                    if ($arm_show_other_change_title == 1) {
                        $popupData .= '<div class="arm_other_change_title">' . __($arm_other_title, 'ARMember') . '</div>';
                    }
                    $popupData .= '<div class="arm_other_change_list"><ul>';
                    foreach ($arm_other_changes as $value) {
                        $popupData .='<li>' . __($value, 'ARMember') . '</li>';
                    }
                    $popupData .= '</ul></div>';
                }
            }

            $popupData .= '</div>';
            $popupData .= '<div class="arm_addons_list_title">' . __('Available Modules', 'ARMember') . '</div>';

            
            $popupData .= '<div class="arm_addons_list_div">';
            $popupData .= '<div class="arm_addons_list" style="width:'.$arm_whtsnew_wrapper_width.'px;">';


            $popupData .= $addon_list_html;
            $popupData .= '</div>';
            $popupData .= '</div>';



            $popupData .= '<div class="armclear"></div>';
            $popupData .= '<div class="popup_content_btn popup_footer">';
            if (!empty($arm_critical_changes)) {
                $popupData .= '<label><input type="checkbox" id="arm_hide_update_notice" class="arm_icheckbox"><span>' . __('I agree', 'ARMember') . '</span></label>';
                $popupData .= '<div class="popup_content_btn_wrapper">';
                $popupData .= '<button class="arm_cancel_btn popup_close_btn" onclick="arm_hide_update_notice();" type="button">' . __('Close', 'ARMember') . '</button>';
                $popupData .= '</div>';
                $popupData .= '<div class="armclear"></div>';
            } else {
                $popupData .= '<div style="display: none;"><input type="checkbox" id="arm_hide_update_notice" class="arm_icheckbox" value="1" checked="checked"></div>';
            }
            $popupData .= '</div>';
            $popupData .= '<div class="armclear"></div>';
            $popupData .= '</div></div>';
            $popupData .= '<script type="text/javascript">jQuery(window).on("load", function(){
				
				jQuery("#arm_update_note").bPopup({
					modalClose: false,  
escClose : false                                        
				});

			});
                                                        function arm_hide_update_notice()
{
    var ishide = 0;
    if (jQuery("#arm_hide_update_notice").is(":checked")) {
	var ishide = 1;                   
	    jQuery("#arm_update_note").bPopup().close(); 
    }else{
        return;
    }
    jQuery.ajax({
	type: "POST",
	url: __ARMAJAXURL,
	data: "action=arm_dont_show_upgrade_notice&is_hide=" + ishide,
	success: function (res) {

            return false;
            
	}
    });
    return false;
}
</script>';
            echo $popupData;
        }
    }

    /*
     * for red color note `|^|Use coupon for invitation link`
     * Add important note to `major`
     * Add normal changelog to `other`  
     */

    function arm_new_version_changelog() {
        $arm_change_log = array();
        global $arm_payment_gateways, $arm_global_settings, $arm_slugs;
        $active_gateways = $arm_payment_gateways->arm_get_active_payment_gateways();


        $arm_change_log = array(
            'show_critical_title' => 1,
            'critical_title' =>'Version 3.0 Changes',
            'critical' =>array(
		      'Minor bug fixes.',
             ),
            'show_major_title' => 0,
            'major_title' =>'Major Changes',
            'major' => array( ),
            'show_other_title' =>0,
            'other_title' => 'Other Changes',
            'other' => array(
            )
        );


        return $arm_change_log;
    }

    function arm_dont_show_upgrade_notice() {
        global $wp, $wpdb, $ARMember, $pagenow;
        $is_hide = (isset($_POST['is_hide']) && $_POST['is_hide'] == '1') ? 1 : 0;
        if ($is_hide == 1) {
            delete_option('arm_new_version_installed');
        }
        die();
    }

    /* Cornerstone Methods */

    function arm_front_alert_messages() {
        $alertMessages = array(
            'loadActivityError' => __("There is an error while loading activities, please try again.", 'ARMember'),
            'pinterestPermissionError' => __("The user chose not to grant permissions or closed the pop-up", 'ARMember'),
            'pinterestError' => __("Oops, there was a problem getting your information", 'ARMember'),
            'clickToCopyError' => __("There is a error while copying, please try again", 'ARMember'),
            'fbUserLoginError' => __("User cancelled login or did not fully authorize.", 'ARMember'),
            'closeAccountError' => __("There is a error while closing account, please try again.", 'ARMember'),
            'invalidFileTypeError' => __("Sorry, this file type is not permitted for security reasons.", 'ARMember'),
            'fileSizeError' => __("File is not allowed bigger than {SIZE}.", 'ARMember'),
            'fileUploadError' => __("There is an error in uploading file, Please try again.", 'ARMember'),
            'coverRemoveConfirm' => __("Are you sure you want to remove cover photo?", 'ARMember'),
            'profileRemoveConfirm' => __("Are you sure you want to remove profile photo?", 'ARMember'),
            'errorPerformingAction' => __("There is an error while performing this action, please try again.", 'ARMember'),
            'userSubscriptionCancel' => __("User's subscription has been canceled", 'ARMember'),
            'cancelSubscriptionAlert' => __("Are you sure you want to cancel subscription?", 'ARMember'),
            'ARM_Loding' => __("Loading..", 'ARMember')
        );
        return $alertMessages;
    }

    function arm_alert_messages() {
        $alertMessages = array(
            'wentwrong' => __("Sorry, Something went wrong. Please try again.", 'ARMember'),
            'bulkActionError' => __("Please select valid action.", 'ARMember'),
            'bulkRecordsError' => __("Please select one or more records.", 'ARMember'),
            'clearLoginAttempts' => __("Login attempts cleared successfully.", 'ARMember'),
            'clearLoginHistory' => __("Login History cleared successfully.", 'ARMember'),
           
            'delPlansSuccess' => __("Plan(s) has been deleted successfully.", 'ARMember'),
            'delPlansError' => __("There is a error while deleting Plan(s), please try again.", 'ARMember'),
            'delPlanSuccess' => __("Plan has been deleted successfully.", 'ARMember'),
            'delPlanError' => __("There is a error while deleting Plan, please try again.", 'ARMember'),
            
            'delSetupsSuccess' => __("Setup(s) has been deleted successfully.", 'ARMember'),
            'delSetupsError' => __("There is a error while deleting Setup(s), please try again.", 'ARMember'),
            'delSetupSuccess' => __("Setup has been deleted successfully.", 'ARMember'),
            'delSetupError' => __("There is a error while deleting Setup, please try again.", 'ARMember'),
            'delFormSetSuccess' => __("Form Set Deleted Successfully.", 'ARMember'),
            'delFormSetError' => __("There is a error while deleting form set, please try again.", 'ARMember'),
            'delFormSuccess' => __("Form deleted successfully.", 'ARMember'),
            'delFormError' => __("There is a error while deleting form, please try again.", 'ARMember'),
            'delRuleSuccess' => __("Rule has been deleted successfully.", 'ARMember'),
            'delRuleError' => __("There is a error while deleting Rule, please try again.", 'ARMember'),
            'delRulesSuccess' => __("Rule(s) has been deleted successfully.", 'ARMember'),
            'delRulesError' => __("There is a error while deleting Rule(s), please try again.", 'ARMember'),
            'prevTransactionError' => __("There is a error while generating preview of transaction detail, Please try again.", 'ARMember'),
            'invoiceTransactionError' => __("There is a error while generating invoice of transaction detail, Please try again.", 'ARMember'),
            'prevMemberDetailError' => __("There is a error while generating preview of members detail, Please try again.", 'ARMember'),
            'prevMemberActivityError' => __("There is a error while displaying members activities detail, Please try again.", 'ARMember'),
            'prevCustomCssError' => __("There is a error while displaying ARMember CSS Class Information, Please Try Again.", 'ARMember'),
            'prevImportMemberDetailError' => __("Please upload appropriate file to import users.", 'ARMember'),
            'delTransactionSuccess' => __("Transaction has been deleted successfully.", 'ARMember'),
            'delTransactionsSuccess' => __("Transaction(s) has been deleted successfully.", 'ARMember'),
            'delAutoMessageSuccess' => __("Message has been deleted successfully.", 'ARMember'),
            'delAutoMessageError' => __("There is a error while deleting Message, please try again.", 'ARMember'),
            'delAutoMessagesSuccess' => __("Message(s) has been deleted successfully.", 'ARMember'),
            'delAutoMessagesError' => __("There is a error while deleting Message(s), please try again.", 'ARMember'),
           
           
            'saveSettingsSuccess' => __("Settings has been saved successfully.", 'ARMember'),
            'saveSettingsError' => __("There is a error while updating settings, please try again.", 'ARMember'),
            'saveDefaultRuleSuccess' => __("Default Rules Saved Successfully.", 'ARMember'),
            'saveDefaultRuleError' => __("There is a error while updating rules, please try again.", 'ARMember'),
            
            'delMemberActivityError' => __("There is a error while deleting member activities, please try again.", 'ARMember'),
            'noTemplateError' => __("Template not found.", 'ARMember'),
            'saveTemplateSuccess' => __("Template options has been saved successfully.", 'ARMember'),
            'saveTemplateError' => __("There is a error while updating template options, please try again.", 'ARMember'),
            'prevTemplateError' => __("There is a error while generating preview of template, Please try again.", 'ARMember'),
            'addTemplateSuccess' => __("Template has been added successfully.", 'ARMember'),
            'addTemplateError' => __("There is a error while adding template, please try again.", 'ARMember'),
            'delTemplateSuccess' => __("Template has been deleted successfully.", 'ARMember'),
            'delTemplateError' => __("There is a error while deleting template, please try again.", 'ARMember'),
            'saveEmailTemplateSuccess' => __("Email Template Updated Successfully.", 'ARMember'),
            'saveAutoMessageSuccess' => __("Message Updated Successfully.", 'ARMember'),
            
            'addAchievementSuccess' => __("Achievements Added Successfully.", 'ARMember'),
            'saveAchievementSuccess' => __("Achievements Updated Successfully.", 'ARMember'),
           
            'pastDateError' => __("Cannot Set Past Dates.", 'ARMember'),
            'pastStartDateError' => __("Start date can not be earlier than current date.", 'ARMember'),
            'pastExpireDateError' => __("Expire date can not be earlier than current date.", 'ARMember'),
            
            'uniqueformsetname' => __("This Set Name is already exist.", 'ARMember'),
            'uniquesignupformname' => __("This Form Name is already exist.", 'ARMember'),
            'installAddonError' => __('There is an error while installing addon, Please try again.', 'ARMember'),
            'installAddonSuccess' => __('Addon installed successfully.', 'ARMember'),
            'activeAddonError' => __('There is an error while activating addon, Please try agina.', 'ARMember'),
            'activeAddonSuccess' => __('Addon activated successfully.', 'ARMember'),
            'deactiveAddonSuccess' => __('Addon deactivated successfully.', 'ARMember'),
            'pwdstrength_vweak' => __('Strength: Very Weak', 'ARMember'),
            'pwdstrength_weak' => __('Strength: Weak', 'ARMember'),
            'pwdstrength_good' => __('Strength: Good', 'ARMember'),
            'pwdstrength_vgood' => __('Strength: Strong', 'ARMember'),
            'confirmCancelSubscription' => __('Are you sure you want to cancel subscription?', 'ARMember'),
            'errorPerformingAction' => __("There is an error while performing this action, please try again.", 'ARMember'),
            'userSubscriptionCancel' => __("User's subscription has been canceled", 'ARMember'),
            'cancelSubscriptionAlert' => __("Are you sure you want to cancel subscription?", 'ARMember'),
            'ARM_Loding' => __("Loading..", 'ARMember'),
            'arm_nothing_found' => __('Oops, nothing found.', 'ARMember')
        );
        $frontMessages = $this->arm_front_alert_messages();
        $alertMessages = array_merge($alertMessages, $frontMessages);
        return $alertMessages;
    }

    function arm_prevent_rocket_loader_script($tag, $handle) {
        $script = htmlspecialchars($tag);
        $pattern2 = '/\/(wp\-content\/plugins\/armember-membership)/';
        preg_match($pattern2,$script,$match_script);

        /* Check if current script is loaded from ARMember only */
        if( !isset($match_script[0]) || $match_script[0] == '' ){
            return $tag;
        }

        $pattern = '/(.*?)(data\-cfasync\=)(.*?)/';
        preg_match_all($pattern,$tag,$matches);
        if( !is_array($matches) ){
            return str_replace(' src', ' data-cfasync="false" src', $tag);
        } else if( !empty($matches) && !empty($matches[2]) && !empty($matches[2][0]) && strtolower(trim($matches[2][0])) != 'data-cfasync=' ){
            return str_replace(' src', ' data-cfasync="false" src', $tag);
        } else if( !empty($matches) && empty($matches[2]) ) {
            return str_replace(' src', ' data-cfasync="false" src', $tag);
        } else {
            return $tag;
        }
    }

    function arm_set_js_css_conditionally() {
        global $arm_datepicker_loaded, $arm_avatar_loaded, $arm_file_upload_field, $bpopup_loaded, $arm_load_tipso, $arm_load_icheck, $arm_font_awesome_loaded;
        if (!is_admin()) {
            if ($arm_datepicker_loaded == 1) {
                if (!wp_script_is('arm_bootstrap_datepicker_with_locale_js', 'enqueued')) {
                    wp_enqueue_script('arm_bootstrap_datepicker_with_locale_js');
                }
            }
            if ($arm_avatar_loaded == 1 || $arm_file_upload_field == 1) {
                if (!wp_script_is('arm_file_upload_js', 'enqueued')) {
                    wp_enqueue_script('arm_file_upload_js');
                }
            }
            if ($bpopup_loaded == 1) {
                if (!wp_script_is('arm_bpopup', 'enqueued')) {
                    wp_enqueue_script('arm_bpopup');
                }
            }
            if ($arm_load_tipso == 1) {
                if (!wp_script_is('arm_tipso_front', 'enqueued')) {
                    wp_enqueue_script('arm_tipso_front');
                }
            }
            if ($arm_font_awesome_loaded == 1) {
                wp_enqueue_style('arm_fontawesome_css');
            }
        }
    }

    function arm_check_font_awesome_icons($content) {
        global $arm_font_awesome_loaded;

        $fa_class = "/armfa|arm_user_social_icons|arm_user_social_fields/";
        $matches = array();
        preg_match_all($fa_class, $content, $matches);

        if (count($matches) > 0 && count($matches[0]) > 0) {
            $arm_font_awesome_loaded = 1;
        }

        return $content;
    }

    function arm_check_user_cap($arm_capabilities = '', $is_ajax_call='')
    {
        
        global $arm_global_settings;

        $errors = array();
        $message = "";
        if($is_ajax_call==true)
        {
            if (!current_user_can($arm_capabilities)) 
            {
                $errors[] = __('Sorry, You do not have permission to perform this action.', 'ARMember');
                $return_array = $arm_global_settings->handle_return_messages(@$errors, @$message);
                $return_array['message'] = $return_array['msg'];

                
                    echo json_encode($return_array);    
                    exit;
            }
        }
        
        $wpnonce = isset($_REQUEST['_wpnonce']) ? $_REQUEST['_wpnonce'] : '';
        $arm_verify_nonce_flag = wp_verify_nonce( $wpnonce, 'arm_wp_nonce' );
        if(!$arm_verify_nonce_flag)
            {
            $errors[] = __('Sorry, Your request can not process due to security reason.', 'ARMember');
            $return_array = $arm_global_settings->handle_return_messages(@$errors, @$message);
            $return_array['message'] = $return_array['msg'];
            echo json_encode($return_array);
            exit;
        }
    }

    function arm_session_start( $force = false ) {
        /**
         * Start Session
         */
        $arm_session_id = session_id();
        if( empty($arm_session_id) || $force == true ) {
            @session_start();
        }
    }
}