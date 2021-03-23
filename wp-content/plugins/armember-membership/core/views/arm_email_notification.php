<?php
global $wpdb, $ARMember, $arm_members_class, $arm_member_forms, $arm_global_settings, $arm_email_settings,  $arm_slugs;
$active = 'arm_general_settings_tab_active';

$_r_action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'email_notification';
?>
<div class="wrap arm_page arm_general_settings_main_wrapper">
    <div class="content_wrapper arm_global_settings_content" id="content_wrapper">
        <div class="page_title" style="margin: 0px;"><?php _e('Email Notification', 'ARMember'); ?></div>
        <div class="armclear"></div>
        <div class="arm_general_settings_wrapper">
            <div class="arm_settings_container" style="border-top: 0px;">
                <?php 
				if (file_exists(MEMBERSHIPLITE_VIEWS_DIR . '/arm_email_templates.php')) {
					include(MEMBERSHIPLITE_VIEWS_DIR . '/arm_email_templates.php');
				}
                ?>
            </div>
        </div>
        <div class="armclear"></div>
    </div>
</div>