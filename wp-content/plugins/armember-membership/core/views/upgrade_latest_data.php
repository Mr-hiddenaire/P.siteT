<?php
global $wpdb, $arm_newdbversion;

if (version_compare($arm_newdbversion, '1.5', '<')) {
    global $wpdb, $wp, $ARMember;
    $pt_log_table = $ARMember->tbl_arm_payment_log;
    $bt_log_table = $ARMember->tbl_arm_bank_transfer_log;

    $wpdb->query("ALTER TABLE `{$pt_log_table}` ADD `arm_coupon_on_each_subscriptions` TINYINT(1) NULL DEFAULT '0' AFTER `arm_coupon_discount_type`; ");

    $wpdb->query("ALTER TABLE `{$bt_log_table}` ADD `arm_coupon_on_each_subscriptions` TINYINT(1) NULL DEFAULT '0' AFTER `arm_coupon_discount_type`; ");

}

if (version_compare($arm_newdbversion, '1.8', '<')) {
     global $arm_global_settings, $arm_member_forms;

     $all_global_settings = $arm_global_settings->arm_get_all_global_settings();
     $all_global_settings['general_settings']['spam_protection'] = 1;
     $new_global_settings_result = $all_global_settings;
     update_option('arm_global_settings', $new_global_settings_result);
     
    $old_preset_fields     = get_option("arm_preset_form_fields");
    $old_preset_fields     = maybe_unserialize(maybe_unserialize($old_preset_fields));
    $default_preset_fields = $arm_member_forms->arm_default_preset_user_fields();
    if (isset($default_preset_fields['country']['options']) && !empty($default_preset_fields['country']['options']) && isset($old_preset_fields['default']['country'])) {
        $old_preset_fields['default']['country']['options'] = $default_preset_fields['country']['options'];
	
	$updated_preset_fields = $old_preset_fields;
	update_option("arm_preset_form_fields", $updated_preset_fields);
    }
}
if (version_compare($arm_newdbversion, '2.1', '<')) {
    global $wpdb, $wp, $ARMember;
    $pt_log_table = $ARMember->tbl_arm_payment_log;
    $bt_log_table = $ARMember->tbl_arm_bank_transfer_log;
    $arm_bank_table_log_flag=get_option('arm_bank_table_log_flag');

    $arm_old_plan_row = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' AND TABLE_NAME = '".$pt_log_table."' AND column_name = 'arm_old_plan_id'");
    if(empty($arm_old_plan_row)){
        $wpdb->query("ALTER TABLE `{$pt_log_table}` ADD `arm_old_plan_id` bigint(20) NOT NULL DEFAULT '0' AFTER `arm_plan_id`");
    }    

    $arm_payment_cycle_row = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' AND TABLE_NAME = '".$pt_log_table."' AND column_name = 'arm_payment_cycle'");
    if(empty($arm_payment_cycle_row)){
        $wpdb->query("ALTER TABLE `{$pt_log_table}` ADD `arm_payment_cycle` INT(11) NOT NULL DEFAULT '0' AFTER `arm_payment_mode`");
    }

    $arm_bank_name_row = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' AND TABLE_NAME = '".$pt_log_table."' AND column_name = 'arm_bank_name'");
    if(empty($arm_bank_name_row)){
        $wpdb->query("ALTER TABLE `{$pt_log_table}` ADD `arm_bank_name` VARCHAR(255) DEFAULT NULL AFTER `arm_payment_cycle`");
    }
    $arm_account_name_row = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' AND TABLE_NAME = '".$pt_log_table."' AND column_name = 'arm_account_name'");
    if(empty($arm_account_name_row)){
        $wpdb->query("ALTER TABLE `{$pt_log_table}` ADD `arm_account_name` VARCHAR(255) DEFAULT NULL AFTER `arm_bank_name`");
    }
    $arm_additional_info_row = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' AND TABLE_NAME = '".$pt_log_table."' AND column_name = 'arm_additional_info'");
    if(empty($arm_additional_info_row)){
        $wpdb->query("ALTER TABLE `{$pt_log_table}` ADD `arm_additional_info` LONGTEXT AFTER `arm_account_name`");
    }
    $arm_first_name_row = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' AND TABLE_NAME = '".$pt_log_table."' AND column_name = 'arm_first_name'");
    if(empty($arm_first_name_row)){
        $wpdb->query("ALTER TABLE `{$pt_log_table}` ADD `arm_first_name` VARCHAR(255) DEFAULT NULL AFTER `arm_user_id`");
    }
    $arm_last_name_row = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' AND TABLE_NAME = '".$pt_log_table."' AND column_name = 'arm_last_name'");
    if(empty($arm_last_name_row)){
        $wpdb->query("ALTER TABLE `{$pt_log_table}` ADD `arm_last_name` VARCHAR(255) DEFAULT NULL AFTER `arm_first_name`");
    }

    $arm_payment_transfer_mode_row = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' AND TABLE_NAME = '".$pt_log_table."' AND column_name = 'arm_payment_transfer_mode'");
    if(empty($arm_payment_transfer_mode_row)) {
        $wpdb->query("ALTER TABLE `{$pt_log_table}` ADD `arm_payment_transfer_mode` VARCHAR( 255 ) NULL AFTER `arm_additional_info`");
    }

    if(empty($arm_bank_table_log_flag)){
        
        update_option('arm_bank_table_log_flag','1');

        $btquery = "SELECT * FROM `" . $bt_log_table . "`";
        $bt_payment_log = $wpdb->get_results($btquery, ARRAY_A);
        if(count($bt_payment_log)>0){
            foreach ($bt_payment_log as $bt_payment_log_data) {
                $arm_first_name=get_user_meta($bt_payment_log_data["arm_user_id"],'first_name',true);
                $arm_last_name=get_user_meta($bt_payment_log_data["arm_user_id"],'last_name',true);
                $arm_payment_mode=(!empty($bt_payment_log_data["arm_payment_mode"]))? $bt_payment_log_data["arm_payment_mode"]:'one_time';
                $arm_payment_type=(!empty($bt_payment_log_data["arm_payment_mode"]) && $bt_payment_log_data["arm_payment_mode"]=='manual_subscription')?'subscription':'one_time';
                $bt_insert_result=$wpdb->insert($pt_log_table, array(
                    'arm_invoice_id' => $bt_payment_log_data["arm_invoice_id"],
                    'arm_user_id' => $bt_payment_log_data["arm_user_id"],
                    'arm_first_name' => $arm_first_name,
                    'arm_last_name' => $arm_last_name,
                    'arm_plan_id' => $bt_payment_log_data["arm_plan_id"],
                    'arm_old_plan_id' =>$bt_payment_log_data["arm_old_plan_id"],
                    'arm_payer_email' => $bt_payment_log_data["arm_payer_email"],
                    'arm_transaction_id' => $bt_payment_log_data["arm_transaction_id"],
                    'arm_transaction_payment_type'=>$arm_payment_type,
                    'arm_payment_mode' => $arm_payment_mode,
                    'arm_payment_type' => $arm_payment_type,
                    'arm_payment_gateway' => 'bank_transfer',
                    'arm_payment_cycle' => $bt_payment_log_data["arm_payment_cycle"],
                    'arm_bank_name' => $bt_payment_log_data["arm_bank_name"],
                    'arm_account_name' => $bt_payment_log_data["arm_account_name"],
                    'arm_additional_info' => $bt_payment_log_data["arm_additional_info"],
                    'arm_amount' => $bt_payment_log_data["arm_amount"],
                    'arm_currency' => $bt_payment_log_data["arm_currency"],
                    'arm_extra_vars' => $bt_payment_log_data["arm_extra_vars"],
                    'arm_coupon_code' => $bt_payment_log_data["arm_coupon_code"],
                    'arm_coupon_discount' => $bt_payment_log_data["arm_coupon_discount"],
                    'arm_coupon_discount_type' => $bt_payment_log_data["arm_coupon_discount_type"],
                    'arm_coupon_on_each_subscriptions' => $bt_payment_log_data["arm_coupon_on_each_subscriptions"],
                    'arm_transaction_status' => $bt_payment_log_data["arm_status"],
                    'arm_is_trial' => $bt_payment_log_data["arm_is_trial"],
                    'arm_display_log' => $bt_payment_log_data["arm_display_log"],
                    'arm_payment_date' => $bt_payment_log_data["arm_created_date"],
                    'arm_created_date'=> $bt_payment_log_data["arm_created_date"],
                ));
                
            }
            
        }
    }    
        
}

if(version_compare($arm_newdbversion, '2.4', '<')) {
    
    global $wpdb, $wp, $ARMember;

    $arm_pt_log_table = $ARMember->tbl_arm_payment_log;
    $arm_entries_table = $ARMember->tbl_arm_entries;
    $arm_subscription_plans_table = $ARMember->tbl_arm_subscription_plans;
    $arm_activity_table = $ARMember->tbl_arm_activity;
    $arm_membership_setup_table = $ARMember->tbl_arm_membership_setup;

    $arm_add_payment_log_col = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' AND TABLE_NAME = '".$arm_pt_log_table."' AND column_name = 'arm_is_post_payment'");
    if(empty($arm_add_payment_log_col)){
        $wpdb->query("ALTER TABLE `{$arm_pt_log_table}` ADD `arm_is_post_payment` TINYINT(1) NOT NULL DEFAULT '0' AFTER `arm_is_trial`");
    }
    
    $arm_add_payment_log_col = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' AND TABLE_NAME = '".$arm_pt_log_table."' AND column_name = 'arm_paid_post_id'");
    if(empty($arm_add_payment_log_col)){
        $wpdb->query("ALTER TABLE `{$arm_pt_log_table}` ADD `arm_paid_post_id` BIGINT(20) NOT NULL DEFAULT '0' AFTER `arm_is_post_payment`");
    }
    
    $arm_add_entries_col = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' AND TABLE_NAME = '".$arm_entries_table."' AND column_name = 'arm_is_post_entry'");
    if(empty($arm_add_entries_col)){
        $wpdb->query("ALTER TABLE `{$arm_entries_table}` ADD `arm_is_post_entry` TINYINT(1) NOT NULL DEFAULT '0' AFTER `arm_plan_id`");
    }
    
    $arm_add_entries_col = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' AND TABLE_NAME = '".$arm_entries_table."' AND column_name = 'arm_paid_post_id'");
    if(empty($arm_add_entries_col)){
        $wpdb->query("ALTER TABLE `{$arm_entries_table}` ADD `arm_paid_post_id` BIGINT(20) NOT NULL DEFAULT '0' AFTER `arm_is_post_entry`");
    }

    $arm_add_subscription_plans = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' AND TABLE_NAME = '".$arm_subscription_plans_table."' AND column_name = 'arm_subscription_plan_post_id'");
    if(empty($arm_add_subscription_plans)){
        $wpdb->query("ALTER TABLE `{$arm_subscription_plans_table}` ADD `arm_subscription_plan_post_id` BIGINT(20) NOT NULL DEFAULT '0' AFTER `arm_subscription_plan_role`");
    }

    $arm_add_activity_post_id = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' AND TABLE_NAME = '".$arm_activity_table."' AND column_name = 'arm_paid_post_id'");
    if(empty($arm_add_activity_post_id)){
        $wpdb->query("ALTER TABLE `{$arm_activity_table}` ADD `arm_paid_post_id` BIGINT(20) NOT NULL DEFAULT '0' AFTER `arm_item_id`");
    }

    $arm_add_setup_type = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' AND TABLE_NAME = '".$arm_membership_setup_table."' AND column_name = 'arm_setup_type'");
    if(empty($arm_add_setup_type)){
        $wpdb->query("ALTER TABLE `{$arm_membership_setup_table}` ADD `arm_setup_type` TINYINT(1) NOT NULL DEFAULT '0' AFTER `arm_setup_name`");
    }
}
update_option('arm_new_version_installed',1);
update_option('armlite_version', '3.0');
$arm_newdbversion = '3.0';