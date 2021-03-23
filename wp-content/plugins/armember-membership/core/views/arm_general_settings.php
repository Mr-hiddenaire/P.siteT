<?php
global $wpdb, $ARMember, $arm_members_class, $arm_member_forms, $arm_global_settings, $arm_email_settings,  $arm_slugs, $arm_social_feature;
$active = 'arm_general_settings_tab_active';

$g_action = isset($_REQUEST['action']) ? $_REQUEST['action'] : "general_settings";


?>
<div class="wrap arm_page arm_general_settings_main_wrapper">
    <div class="content_wrapper arm_global_settings_content" id="content_wrapper">
        <div class="page_title"><?php _e('General Settings', 'ARMember'); ?></div>
        <div class="armclear"></div>
        <div class="armember_general_settings_wrapper">
            <div class="arm_general_settings_tab_wrapper">
                <a class="arm_general_settings_tab <?php echo ($g_action == 'general_settings') ? $active : ""; ?>" href="<?php echo admin_url('admin.php?page=' . $arm_slugs->general_settings); ?>"><?php _e('General Options', 'ARMember'); ?></a>
                <a class="arm_general_settings_tab <?php echo ($g_action == 'payment_options' ? $active : ""); ?>" href="<?php echo admin_url('admin.php?page=' . $arm_slugs->general_settings . '&action=payment_options'); ?>"><?php _e('Payment Gateways', 'ARMember'); ?></a>
                <a class="arm_general_settings_tab <?php echo ($g_action == 'page_setup' ? $active : ""); ?>" href="<?php echo admin_url('admin.php?page=' . $arm_slugs->general_settings . '&action=page_setup'); ?>"><?php _e('Page Setup', 'ARMember'); ?></a>
              
                        <a class="arm_general_settings_tab <?php echo ($g_action == 'access_restriction' ? $active : ""); ?>" href="<?php echo admin_url('admin.php?page=' . $arm_slugs->general_settings . '&action=access_restriction'); ?>"><?php _e('Default Restriction Rules', 'ARMember'); ?></a>
                <a class="arm_general_settings_tab <?php echo ($g_action == 'block_options' ? $active : ""); ?>" href="<?php echo admin_url('admin.php?page=' . $arm_slugs->general_settings . '&action=block_options'); ?>"><?php _e('Security Options', 'ARMember'); ?></a>
                <a class="arm_general_settings_tab <?php echo ($g_action == 'import_export' ? $active : ""); ?>" href="<?php echo admin_url('admin.php?page=' . $arm_slugs->general_settings . '&action=import_export'); ?>"><?php _e('Import / Export', 'ARMember'); ?></a>
          
              
                <a class="arm_general_settings_tab <?php echo ($g_action == 'redirection_options' ? $active : ""); ?>" href="<?php echo admin_url('admin.php?page=' . $arm_slugs->general_settings . '&action=redirection_options'); ?>"><?php _e('Redirection Rules', 'ARMember'); ?></a>
                <a class="arm_general_settings_tab <?php echo ($g_action == 'common_messages' ? $active : ""); ?>" href="<?php echo admin_url('admin.php?page=' . $arm_slugs->general_settings . '&action=common_messages'); ?>"><?php _e('Common Messages', 'ARMember'); ?></a>
              
                <div class="armclear"></div>
            </div>
            <div class="arm_settings_container">
                <?php
                    /* if you add any new tab than reset the min height of the box other wise last menu not display in page setup page. */
                    $arm_setting_title = __('General Options', 'ARMember');
                    $arm_setting_tooltip = '';
                    $file_path = MEMBERSHIPLITE_VIEWS_DIR . '/arm_global_settings.php';
                    switch ($g_action)
                    {
                            case 'payment_options':
                                    $file_path = MEMBERSHIPLITE_VIEWS_DIR . '/arm_manage_payment_gateways.php';
                                    $arm_setting_title = __('Payment Gateways', 'ARMember');
                                    break;
                            case 'page_setup':
                                    $file_path = MEMBERSHIPLITE_VIEWS_DIR . '/arm_page_setup.php';
                                    $arm_setting_title = __('Page Setup', 'ARMember');
                                    break;
                           
                            case 'block_options':
                                    $file_path = MEMBERSHIPLITE_VIEWS_DIR . '/arm_block_settings.php';
                                    $arm_setting_title = __('Security Options', 'ARMember');
                                    break;
                            case 'import_export':
                                    $file_path = MEMBERSHIPLITE_VIEWS_DIR . '/arm_import_export.php';
                                    $arm_setting_title = __('Import / Export', 'ARMember');
                                    break;
                           
                           
                           
                            case 'redirection_options':
                                    $file_path = MEMBERSHIPLITE_VIEWS_DIR . '/arm_redirection_settings.php';
                                    $arm_setting_title = __('Page/Post Redirection Rules', 'ARMember');
                                    break;
                            case 'common_messages':
                                    $file_path = MEMBERSHIPLITE_VIEWS_DIR . '/arm_common_messages_settings.php';
                                    $arm_setting_title = __('Common Messages', 'ARMember');
                                    break;
                            
                            case 'access_restriction':
                                    $file_path = MEMBERSHIPLITE_VIEWS_DIR . '/arm_access_restriction_settings.php';
                                    $arm_setting_title = __('Default Restriction Rules', 'ARMember');
                                    break;
                            default:
                                    $file_path = MEMBERSHIPLITE_VIEWS_DIR . '/arm_global_settings.php';
                                    $arm_setting_title = __('General Options', 'ARMember');
                                    break;
                    }
                    if (file_exists($file_path)) {
                            ?>
                            <div class="arm_settings_title_wrapper">
                                <div class="arm_setting_title"><?php echo $arm_setting_title." ".$arm_setting_tooltip; ?></div>
                            </div>
                            <?php
                            include($file_path);
                    }
                ?>
            </div>
        </div>
        <div class="armclear"></div>
    </div>
</div>