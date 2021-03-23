<?php 
global $wpdb, $ARMember, $arm_slugs, $arm_social_feature,$myplugarr;
$ARMember->arm_session_start();
$social_feature = get_option('arm_is_social_feature');
$user_private_content = 0;
$social_login_feature = 0;
$drip_content_feature = 0;
$opt_ins_feature = 0;
$coupon_feature = 0;
$buddypress_feature = 0;
$invoice_tax_feature = 0;
$multiple_membership_feature = 0;
$arm_is_mycred_active = 0;
$woocommerce_feature = 0;
$arm_pay_per_post = 0;
$arm_admin_mycred_feature = 0;




$featureActiveIcon = MEMBERSHIPLITE_IMAGES_URL . '/feature_active_icon.png';
if (is_rtl()) {
	$featureActiveIcon = MEMBERSHIPLITE_IMAGES_URL . '/feature_active_icon_rtl.png';
}
?>
<style>
    .purchased_info{
        color:#7cba6c;
        font-weight:bold;
        font-size: 15px;
    }
	.arperrmessage{color:red;}
    #wpcontent{
        background: #EEF2F8;
    }
	.arfnewmodalclose
    {
        font-size: 15px;
        font-weight: bold;
        height: 19px;
        position: absolute;
        right: 3px;
        top:5px;
        width: 19px;
        cursor:pointer;
        color:#D1D6E5;
    }
	.newform_modal_title { font-size:25px; line-height:25px; margin-bottom: 10px; }
	.newmodal_field_title { font-size: 16px;
    line-height: 16px;
    margin-bottom: 10px; }
</style>
<div class="wrap arm_page arm_feature_settings_main_wrapper">
    <div class="content_wrapper arm_feature_settings_content" id="content_wrapper">
        <div class="page_title"><?php _e('Additional Membership Modules', 'ARMember'); ?></div>
        <div class="armclear"></div>
        <div class="arm_feature_settings_wrapper">            
            <div class="arm_feature_settings_container">
                <div class="arm_feature_list social_enable <?php echo ($social_feature == 1) ? 'active':'';?>">
                    <div class="arm_feature_icon"></div>
					<div class="arm_feature_active_icon"><div class="arm_check_mark"></div></div>
					<div class="arm_feature_content">
						<div class="arm_feature_title"><?php _e('Social Feature','ARMember'); ?></div>
						<div class="arm_feature_text"><?php _e("With this feature, enable social activities like Member Directory/Public Profile, Social Profile Fields etc.", 'ARMember');?></div>
                        
                        <div class="arm_feature_button_activate_wrapper <?php echo ($social_feature == 1) ? 'hidden_section':'';?>">
							<a href="javascript:void(0)" class="arm_feature_activate_btn arm_feature_settings_switch" data-feature_val="1" data-feature="social"><?php _e('Activate','ARMember'); ?></a>
							<a href="javascript:void(0)" class="arm_feature_configure_btn"><?php _e('Configure','ARMember'); ?></a>
                            <img src="<?php echo MEMBERSHIPLITE_IMAGES_URL.'/arm_loader.gif' ?>" class="arm_addon_loader_img" width="24" height="24" />
						</div>
                        
						<div class="arm_feature_button_deactivate_wrapper <?php echo ($social_feature == 1) ? '':'hidden_section';?>">
							<a href="javascript:void(0)" class="arm_feature_deactivate_btn arm_feature_settings_switch" data-feature_val="0" data-feature="social"><?php _e('Deactivate','ARMember'); ?></a>
							<a href="<?php echo admin_url('admin.php?page=' . $arm_slugs->profiles_directories);?>" class="arm_feature_configure_btn"><?php _e('Configure','ARMember'); ?></a>
                            <img src="<?php echo MEMBERSHIPLITE_IMAGES_URL.'/arm_loader.gif' ?>" class="arm_addon_loader_img" width="24" height="24" />
						</div>
					</div>
                    <a class="arm_ref_info_links arm_feature_link" target="_blank" href="https://www.armemberplugin.com/documents/brief-of-social-features/"><?php _e('More Info', 'ARMember'); ?></a>
				</div>

				<div class="arm_feature_list opt_ins_enable <?php echo ($opt_ins_feature == 1) ? 'active':'';?>">
                    <div class="arm_feature_icon"></div>
					<div class="arm_feature_active_icon"><div class="arm_check_mark"></div></div>
					<div class="arm_feature_content">
						<div class="arm_feature_title"><?php _e('Opt-ins','ARMember'); ?></div>
						<div class="arm_feature_text"><?php _e("build you subscription list with external list builder like Aweber, Mailchimp while user registration.", 'ARMember');?></div>
                        
						<div class="arm_feature_button_activate_wrapper <?php echo ($opt_ins_feature == 1) ? 'hidden_section':'';?>">
							<a href="javascript:void(0)" class="arm_feature_activate_btn arm_feature_settings_switch" data-feature_val="1" data-feature="opt_ins"><?php _e('Activate','ARMember'); ?></a>
							<a href="javascript:void(0)" class="arm_feature_configure_btn"><?php _e('Configure','ARMember'); ?></a>
                            <img src="<?php echo MEMBERSHIPLITE_IMAGES_URL.'/arm_loader.gif' ?>" class="arm_addon_loader_img" width="24" height="24" />
						</div>
                        <div class="arm_feature_button_deactivate_wrapper <?php echo ($opt_ins_feature == 1) ? '':'hidden_section';?>">
							<a href="javascript:void(0)" class="arm_feature_deactivate_btn arm_feature_settings_switch" data-feature_val="0" data-feature="opt_ins"><?php _e('Deactivate','ARMember'); ?></a>
							<a href="#" class="arm_feature_configure_btn"><?php _e('Configure','ARMember'); ?></a>
                            <img src="<?php echo MEMBERSHIPLITE_IMAGES_URL.'/arm_loader.gif' ?>" class="arm_addon_loader_img" width="24" height="24" />
						</div>
					</div>
                    <a class="arm_ref_info_links arm_feature_link" target="_blank" href="https://www.armemberplugin.com/documents/armember-opt-ins-provide-ease-of-email-marketing/"><?php _e('More Info', 'ARMember'); ?></a>
                </div>

                <div class="arm_feature_list drip_content_enable <?php echo ($drip_content_feature == 1) ? 'active':'';?>">
                    <div class="arm_feature_icon"></div>
					<div class="arm_feature_active_icon"><div class="arm_check_mark"></div></div>
					<div class="arm_feature_content">
						<div class="arm_feature_title"><?php _e('Drip Content','ARMember'); ?></div>
						<div class="arm_feature_text"><?php _e("Publish your site content based on different time intervals by enabling this feature.", 'ARMember');?></div>
                        
						<div class="arm_feature_button_activate_wrapper <?php echo ($drip_content_feature == 1) ? 'hidden_section':'';?>">
							<a href="javascript:void(0)" class="arm_feature_activate_btn arm_feature_settings_switch" data-feature_val="1" data-feature="drip_content"><?php _e('Activate','ARMember'); ?></a>
							<a href="javascript:void(0)" class="arm_feature_configure_btn"><?php _e('Configure','ARMember'); ?></a>
                            <img src="<?php echo MEMBERSHIPLITE_IMAGES_URL.'/arm_loader.gif' ?>" class="arm_addon_loader_img" width="24" height="24" />
						</div>
                        <div class="arm_feature_button_deactivate_wrapper <?php echo ($drip_content_feature == 1) ? '':'hidden_section';?>">
							<a href="javascript:void(0)" class="arm_feature_deactivate_btn arm_feature_settings_switch" data-feature_val="0" data-feature="drip_content"><?php _e('Deactivate','ARMember'); ?></a>
							<a href="#"><?php _e('Configure','ARMember'); ?></a>
                            <img src="<?php echo MEMBERSHIPLITE_IMAGES_URL.'/arm_loader.gif' ?>" class="arm_addon_loader_img" width="24" height="24" />
						</div>
					</div>
                    <a class="arm_ref_info_links arm_feature_link" target="_blank" href="https://www.armemberplugin.com/documents/enable-drip-content-for-your-site/"><?php _e('More Info', 'ARMember'); ?></a>
                </div>

				<div class="arm_feature_list social_login_enable <?php echo ($social_login_feature == 1) ? 'active':'';?>">
                    <div class="arm_feature_icon"></div>
					<div class="arm_feature_active_icon"><div class="arm_check_mark"></div></div>
					<div class="arm_feature_content">
						<div class="arm_feature_title"><?php _e('Social Connect','ARMember'); ?></div>
						<div class="arm_feature_text"><?php _e("Allow users to sign up / login with their social accounts by enabling this feature.", 'ARMember');?></div>
                        <div class="arm_feature_button_activate_wrapper <?php echo ($social_login_feature == 1) ? 'hidden_section':'';?>">
							<a href="javascript:void(0)" class="arm_feature_activate_btn arm_feature_settings_switch" data-feature_val="1" data-feature="social_login"><?php _e('Activate','ARMember'); ?></a>
							<a href="javascript:void(0)" class="arm_feature_configure_btn"><?php _e('Configure','ARMember'); ?></a>
                            <img src="<?php echo MEMBERSHIPLITE_IMAGES_URL.'/arm_loader.gif' ?>" class="arm_addon_loader_img" width="24" height="24" />
						</div>
                        <div class="arm_feature_button_deactivate_wrapper <?php echo ($social_login_feature == 1) ? '':'hidden_section';?>">
							<a href="javascript:void(0)" class="arm_feature_deactivate_btn arm_feature_settings_switch" data-feature_val="0" data-feature="social_login"><?php _e('Deactivate','ARMember'); ?></a>
							<a href="#" class="arm_feature_configure_btn"><?php _e('Configure','ARMember'); ?></a>
                            <img src="<?php echo MEMBERSHIPLITE_IMAGES_URL.'/arm_loader.gif' ?>" class="arm_addon_loader_img" width="24" height="24" />
						</div>
					</div>
                    <a class="arm_ref_info_links arm_feature_link arm_advanced_link" target="_blank" href="https://www.armemberplugin.com/documents/basic-information-for-social-login/"><?php _e('More Info', 'ARMember'); ?></a>
                </div>

                <div class="arm_feature_list pay_per_post_enable <?php echo ($arm_pay_per_post == 1) ? 'active':'';?>">
                    <div class="arm_feature_icon"></div>
                    <div class="arm_feature_active_icon"><div class="arm_check_mark"></div></div>
                    <div class="arm_feature_content">
                        <div class="arm_feature_title"><?php _e('Pay Per Post','ARMember'); ?></div>
                        <div class="arm_feature_text"><?php _e("With this feature, you can sell post separately without creating plan(s).", 'ARMember');?></div>
                        <div class="arm_feature_button_activate_wrapper <?php echo ($arm_pay_per_post == 1) ? 'hidden_section':'';?>">
                            <a href="javascript:void(0)" class="arm_feature_activate_btn arm_feature_settings_switch" data-feature_val="1" data-feature="arm_pay_per_post"><?php _e('Activate','ARMember'); ?></a>
                            <a href="javascript:void(0)" class="arm_feature_configure_btn"><?php _e('Configure','ARMember'); ?></a>
                            <img src="<?php echo MEMBERSHIPLITE_IMAGES_URL.'/arm_loader.gif' ?>" class="arm_addon_loader_img" width="24" height="24" />
                        </div>
                        <div class="arm_feature_button_deactivate_wrapper <?php echo ($arm_pay_per_post == 1) ? '':'hidden_section';?>">
                            <a href="javascript:void(0)" class="arm_feature_deactivate_btn arm_feature_settings_switch" data-feature_val="0" data-feature="coupon"><?php _e('Deactivate','ARMember'); ?></a>
                            <a href="#" class="arm_feature_configure_btn"><?php _e('Configure','ARMember'); ?></a>
                            <img src="<?php echo MEMBERSHIPLITE_IMAGES_URL.'/arm_loader.gif' ?>" class="arm_addon_loader_img" width="24" height="24" />
                        </div>
                    </div>
                    <a class="arm_ref_info_links arm_feature_link" target="_blank" href="https://www.armemberplugin.com/documents/pay-per-post/"><?php _e('More Info', 'ARMember'); ?></a>
                </div>

                <div class="arm_feature_list coupon_enable <?php echo ($coupon_feature == 1) ? 'active':'';?>">
                    <div class="arm_feature_icon"></div>
                    <div class="arm_feature_active_icon"><div class="arm_check_mark"></div></div>
                    <div class="arm_feature_content">
                        <div class="arm_feature_title"><?php _e('Coupon','ARMember'); ?></div>
                        <div class="arm_feature_text"><?php _e("Let users get benefit of discounts coupons while making payment with your site.", 'ARMember');?></div>
                        <div class="arm_feature_button_activate_wrapper <?php echo ($coupon_feature == 1) ? 'hidden_section':'';?>">
                            <a href="javascript:void(0)" class="arm_feature_activate_btn arm_feature_settings_switch" data-feature_val="1" data-feature="coupon"><?php _e('Activate','ARMember'); ?></a>
                            <a href="javascript:void(0)" class="arm_feature_configure_btn"><?php _e('Configure','ARMember'); ?></a>
                            <img src="<?php echo MEMBERSHIPLITE_IMAGES_URL.'/arm_loader.gif' ?>" class="arm_addon_loader_img" width="24" height="24" />
                        </div>
                        <div class="arm_feature_button_deactivate_wrapper <?php echo ($coupon_feature == 1) ? '':'hidden_section';?>">
                            <a href="javascript:void(0)" class="arm_feature_deactivate_btn arm_feature_settings_switch" data-feature_val="0" data-feature="coupon"><?php _e('Deactivate','ARMember'); ?></a>
                            <a href="#" class="arm_feature_configure_btn"><?php _e('Configure','ARMember'); ?></a>
                            <img src="<?php echo MEMBERSHIPLITE_IMAGES_URL.'/arm_loader.gif' ?>" class="arm_addon_loader_img" width="24" height="24" />
                        </div>
                    </div>
                    <a class="arm_ref_info_links arm_feature_link arm_advanced_link" target="_blank" href="https://www.armemberplugin.com/documents/how-to-do-coupon-management/"><?php _e('More Info', 'ARMember'); ?></a>
                </div>

                <div class="arm_feature_list invoice_tax_enable <?php echo ($invoice_tax_feature == 1) ? 'active':'';?>">
                    <div class="arm_feature_icon"></div>
                    <div class="arm_feature_active_icon"><div class="arm_check_mark"></div></div>
                    <div class="arm_feature_content">
                        <div class="arm_feature_title"><?php _e('Invoice and Tax','ARMember'); ?></div>
                        <div class="arm_feature_text"><?php _e("Enable facility to send Invoice and apply Sales Tax on membership plans.", 'ARMember');?></div>
                        <div class="arm_feature_button_activate_wrapper <?php echo ($invoice_tax_feature == 1) ? 'hidden_section':'';?>">
                            <a href="javascript:void(0)" class="arm_feature_activate_btn arm_feature_settings_switch" data-feature_val="1" data-feature="invoice_tax"><?php _e('Activate','ARMember'); ?></a>
                            <a href="javascript:void(0)" class="arm_feature_configure_btn"><?php _e('Configure','ARMember'); ?></a>
                            <img src="<?php echo MEMBERSHIPLITE_IMAGES_URL.'/arm_loader.gif' ?>" class="arm_addon_loader_img" width="24" height="24" />
                        </div>
                        <div class="arm_feature_button_deactivate_wrapper <?php echo ($invoice_tax_feature == 1) ? '':'hidden_section';?>">
                            <a href="javascript:void(0)" class="arm_feature_deactivate_btn arm_feature_settings_switch" data-feature_val="0" data-feature="coupon"><?php _e('Deactivate','ARMember'); ?></a>
                            <a href="#" class="arm_feature_configure_btn"><?php _e('Configure','ARMember'); ?></a>
                            <img src="<?php echo MEMBERSHIPLITE_IMAGES_URL.'/arm_loader.gif' ?>" class="arm_addon_loader_img" width="24" height="24" />
                        </div>
                    </div>
                    <a class="arm_ref_info_links arm_feature_link arm_advanced_link" target="_blank" href="https://www.armemberplugin.com/documents/invoice-and-tax"><?php _e('More Info', 'ARMember'); ?></a>
                </div>

                <div class="arm_feature_list user_private_content_enable <?php echo ($user_private_content == 1) ? 'active':'';?>">
                    <div class="arm_feature_icon"></div>
                    <div class="arm_feature_active_icon"><div class="arm_check_mark"></div></div>
                    <div class="arm_feature_content">
                        <div class="arm_feature_title"><?php _e('User Private Content','ARMember'); ?></div>
                        <div class="arm_feature_text"><?php _e("With this feature, you can set different content for different user.", 'ARMember');?></div>
                        <div class="arm_feature_button_activate_wrapper <?php echo ($user_private_content == 1) ? 'hidden_section':'';?>">
                            <a href="javascript:void(0)" class="arm_feature_activate_btn arm_feature_settings_switch" data-feature_val="1" data-feature="user_private_content"><?php _e('Activate','ARMember'); ?></a>
                            <a href="javascript:void(0)" class="arm_feature_configure_btn"><?php _e('Configure','ARMember'); ?></a>
                            <img src="<?php echo MEMBERSHIPLITE_IMAGES_URL.'/arm_loader.gif' ?>" class="arm_addon_loader_img" width="24" height="24" />
                        </div>
                        <div class="arm_feature_button_deactivate_wrapper <?php echo ($user_private_content == 1) ? '':'hidden_section';?>">
                            <a href="javascript:void(0)" class="arm_feature_deactivate_btn arm_feature_settings_switch" data-feature_val="0" data-feature="coupon"><?php _e('Deactivate','ARMember'); ?></a>
                            <a href="#" class="arm_feature_configure_btn"><?php _e('Configure','ARMember'); ?></a>
                            <img src="<?php echo MEMBERSHIPLITE_IMAGES_URL.'/arm_loader.gif' ?>" class="arm_addon_loader_img" width="24" height="24" />
                        </div>
                    </div>
                    <a class="arm_ref_info_links arm_feature_link" target="_blank" href="https://www.armemberplugin.com/documents/user-private-content/"><?php _e('More Info', 'ARMember'); ?></a>
                </div>

                <div class="arm_feature_list multiple_membership_enable <?php echo ($multiple_membership_feature == 1) ? 'active':'';?>">
                    <div class="arm_feature_icon"></div>
                    <div class="arm_feature_active_icon"><div class="arm_check_mark"></div></div>
                    <div class="arm_feature_content">
                        <div class="arm_feature_title"><?php _e('Multiple Membership/Plans','ARMember'); ?></div>
                        <div class="arm_feature_text"><?php _e("Allow members to subscribe multiple plans simultaneously.", 'ARMember');?></div>
                        <div class="arm_feature_button_activate_wrapper <?php echo ($multiple_membership_feature == 1) ? 'hidden_section':'';?>">
                            <a href="javascript:void(0)" class="arm_feature_activate_btn arm_feature_settings_switch" data-feature_val="1" data-feature="multiple_membership"><?php _e('Activate','ARMember'); ?></a>
               
                            <img src="<?php echo MEMBERSHIPLITE_IMAGES_URL.'/arm_loader.gif' ?>" class="arm_addon_loader_img" width="24" height="24" />
                        </div>
                        <div class="arm_feature_button_deactivate_wrapper <?php echo ($multiple_membership_feature == 1) ? '':'hidden_section';?>">
                            <a href="javascript:void(0)" class="arm_feature_deactivate_btn arm_feature_settings_switch" data-feature_val="0" data-feature="multiple_membership"><?php _e('Deactivate','ARMember'); ?></a>
                    
                            <img src="<?php echo MEMBERSHIPLITE_IMAGES_URL.'/arm_loader.gif' ?>" class="arm_addon_loader_img" width="24" height="24" />
                        </div>
                    </div>
                        <a class="arm_ref_info_links arm_feature_link arm_advanced_link" target="_blank" href="https://www.armemberplugin.com/documents/single-vs-multiple-membership/"><?php _e('More Info', 'ARMember'); ?></a>
                </div>

                <div class="arm_feature_list buddypress_enable <?php echo ($buddypress_feature == 1) ? 'active':'';?>">
                    <div class="arm_feature_icon"></div>
                    <div class="arm_feature_active_icon"><div class="arm_check_mark"></div></div>
                    <div class="arm_feature_content">
                        <div class="arm_feature_title"><?php _e('Buddypress/Buddyboss Integration','ARMember'); ?></div>
                        <div class="arm_feature_text"><?php _e("Integrate BuddyPress/Buddyboss with ARMember.", 'ARMember');?></div>
                        <div class="arm_feature_button_activate_wrapper <?php echo ($buddypress_feature == 1) ? 'hidden_section':'';?>">
                            <a href="javascript:void(0)" class="arm_feature_activate_btn arm_feature_settings_switch" data-feature_val="1" data-feature="buddypress"><?php _e('Activate','ARMember'); ?></a>
                            <a href="javascript:void(0)" class="arm_feature_configure_btn"><?php _e('Configure','ARMember'); ?></a>
                            <img src="<?php echo MEMBERSHIPLITE_IMAGES_URL.'/arm_loader.gif' ?>" class="arm_addon_loader_img" width="24" height="24" />
                        </div>
                        <div class="arm_feature_button_deactivate_wrapper <?php echo ($buddypress_feature == 1) ? '':'hidden_section';?>">
                            <a href="javascript:void(0)" class="arm_feature_deactivate_btn arm_feature_settings_switch" data-feature_val="0" data-feature="buddypress"><?php _e('Deactivate','ARMember'); ?></a>
                            <a href="#" class="arm_feature_configure_btn"><?php _e('Configure','ARMember'); ?></a>
                            <img src="<?php echo MEMBERSHIPLITE_IMAGES_URL.'/arm_loader.gif' ?>" class="arm_addon_loader_img" width="24" height="24" />
                        </div>
                    </div>
                    <a class="arm_ref_info_links arm_feature_link arm_advanced_link" target="_blank" href="https://www.armemberplugin.com/documents/buddypress-support/"><?php _e('More Info', 'ARMember'); ?></a>
                </div>

                <div class="arm_feature_list woocommerce_enable <?php echo ($woocommerce_feature == 1) ? 'active':'';?>">
                    <div class="arm_feature_icon"></div>
                    <div class="arm_feature_active_icon"><div class="arm_check_mark"></div></div>
                    <div class="arm_feature_content">
                        <div class="arm_feature_title"><?php _e('Woocommerce Integration','ARMember'); ?></div>
                        <div class="arm_feature_text" style=" min-height: 0;"><?php _e("Integrate Woocommerce with ARMember.", 'ARMember');?></div>
                        <div class="arm_feature_text" style=" font-size: 15px; font-weight: bold;  color: #969699; min-height: 0;"><?php _e('Minimum Required Woocommerce Version: 3.0.2', 'ARMember');?></div>
                        <div class="arm_feature_button_activate_wrapper <?php echo ($woocommerce_feature == 1) ? 'hidden_section':'';?>">
                            <a href="javascript:void(0)" class="arm_feature_activate_btn arm_feature_settings_switch" data-feature_val="1" data-feature="woocommerce"><?php _e('Activate','ARMember'); ?></a>
                            <!--<a href="javascript:void(0)" class="arm_feature_configure_btn"><?php _e('Configure','ARMember'); ?></a>-->
                            <img src="<?php echo MEMBERSHIPLITE_IMAGES_URL.'/arm_loader.gif' ?>" class="arm_addon_loader_img" width="24" height="24" />
                        </div>
                        <div class="arm_feature_button_deactivate_wrapper <?php echo ($woocommerce_feature == 1) ? '':'hidden_section';?>">
                            <a href="javascript:void(0)" class="arm_feature_deactivate_btn arm_feature_settings_switch" data-feature_val="0" data-feature="woocommerce"><?php _e('Deactivate','ARMember'); ?></a>
                            <!--<a href="#" class="arm_feature_configure_btn"><?php _e('Configure','ARMember'); ?></a>-->
                            <img src="<?php echo MEMBERSHIPLITE_IMAGES_URL.'/arm_loader.gif' ?>" class="arm_addon_loader_img" width="24" height="24" />
                        </div>
                    </div>
                    <a class="arm_ref_info_links arm_feature_link arm_advanced_link" target="_blank" href="https://www.armemberplugin.com/documents/woocommerce-support/"><?php _e('More Info', 'ARMember'); ?></a>
                </div>

                <div class="arm_feature_list mycred_enable <?php echo ($arm_admin_mycred_feature == 1) ? 'active':'';?>">
                    <div class="arm_feature_icon"></div>
                    <div class="arm_feature_active_icon"><div class="arm_check_mark"></div></div>
                    <div class="arm_feature_content">
                        <div class="arm_feature_title"><?php _e('myCRED Integration','ARMember'); ?></div>
                        <div class="arm_feature_text"><?php _e("Integrate myCRED adaptive points management system with ARMember.", 'ARMember');?></div>
                        <div class="arm_feature_button_activate_wrapper <?php echo ($arm_admin_mycred_feature == 1) ? 'hidden_section':'';?>">
                            <a href="javascript:void(0)" class="arm_feature_activate_btn arm_feature_settings_switch" data-feature_val="1" data-feature="mycred"><?php _e('Activate','ARMember'); ?></a>
                            <a href="javascript:void(0)" class="arm_feature_configure_btn"><?php _e('Configure','ARMember'); ?></a>
                            <img src="<?php echo MEMBERSHIPLITE_IMAGES_URL.'/arm_loader.gif' ?>" class="arm_addon_loader_img" width="24" height="24" />
                        </div>
                        <div class="arm_feature_button_deactivate_wrapper <?php echo ($arm_admin_mycred_feature == 1) ? '':'hidden_section';?>">
                            <a href="javascript:void(0)" class="arm_feature_deactivate_btn arm_feature_settings_switch" data-feature_val="0" data-feature="coupon"><?php _e('Deactivate','ARMember'); ?></a>
                            <a href="#" class="arm_feature_configure_btn"><?php _e('Configure','ARMember'); ?></a>
                            <img src="<?php echo MEMBERSHIPLITE_IMAGES_URL.'/arm_loader.gif' ?>" class="arm_addon_loader_img" width="24" height="24" />
                        </div>
                    </div>
                    <a class="arm_ref_info_links arm_feature_link arm_advanced_link" target="_blank" href="https://www.armemberplugin.com/documents/mycred-integration/"><?php _e('More Info', 'ARMember'); ?></a>
                </div>
                
                <?php echo do_action('arm_add_new_custom_add_on'); ?>
            </div>
            
            <div class="arm_feature_settings_container" style="margin-top:30px;">
				<?php
				global $arm_social_feature;
				global $arm_version;
				$addon_resp = "";
				$addon_resp = $arm_social_feature->addons_page();

				$plugins = get_plugins();
				$installed_plugins = array();
				foreach ($plugins as $key => $plugin) {
					$is_active = is_plugin_active($key);
					$installed_plugin = array("plugin" => $key, "name" => $plugin["Name"], "is_active" => $is_active);
					$installed_plugin["activation_url"] = $is_active ? "" : wp_nonce_url("plugins.php?action=activate&plugin={$key}", "activate-plugin_{$key}");
					$installed_plugin["deactivation_url"] = !$is_active ? "" : wp_nonce_url("plugins.php?action=deactivate&plugin={$key}", "deactivate-plugin_{$key}");

					$installed_plugins[] = $installed_plugin;
				}

		
		?>
        <?php wp_nonce_field( 'arm_wp_nonce' );?>
	    </div>
        </div>
        <div class="armclear"></div>
    </div>
</div>

<?php
$addon_content = '<span class="arm_confirm_text">'.__("You need to have ARMember version 1.6 OR higher to install this addon.",'ARMember' ).'</span>';
		$addon_content .= '<input type="hidden" value="false" id="bulk_delete_flag"/>';
		$addon_content_popup_arg = array(
			'id' => 'addon_message',
			'class' => 'adddon_message',
                        'title' => __('Confirmation','ARMember'),
			'content' => $addon_content,
			'button_id' => 'addon_ok_btn',
			'button_onclick' => "addon_message();",
		);
		echo $arm_global_settings->arm_get_bpopup_html($addon_content_popup_arg); 



        $addon_not_supported_content = '<span class="arm_confirm_text">'.__("This feature is available only in Pro version.",'ARMember' ).'</span>';
        $popup = '<div id="arm_addon_not_supoported_notice" class="popup_wrapper arm_addon_not_supoported_notice"><div class="popup_wrapper_inner">';
       
            $popup .= '<div class="popup_content_text">' . $addon_not_supported_content . '</div>';
            $popup .= '<div class="armclear"></div>';
            $popup .= '<div class="popup_footer">';
            $popup .= '<div class="popup_content_btn_wrapper">';
           
            $popup .= '<button type="button" class="arm_submit_btn popup_ok_btn" id="addon_not_supported_notices_ok_btn">' . __('Ok', 'ARMember'). '</button>';
            $popup .= '</div>';

            $popup .= '</div>';
            $popup .= '<div class="armclear"></div>';
            $popup .= '</div></div>';


        echo $popup ?>

<div id="arfactnotcompatible" style="display:none; background:white; padding:15px; border-radius:3px; width:400px; height:100px;">
		
		<div class="arfactnotcompatiblemodalclose" style="float:right;text-align:right;cursor:pointer; position:absolute;right:10px; " onclick="javascript:return false;"><img src="<?php echo MEMBERSHIPLITE_IMAGES_URL . '/close-button.png'; ?>" align="absmiddle" /></div>
        
       <table class="form-table">
            <tr class="form-field">
                <th class="arm-form-table-label" style="font-size:16px;">You need to have ARMember version 1.6 OR higher to install this addon.</th>
            </tr>				
		</table>
</div>
<script type="text/javascript">
    var ADDON_NOT_COMPATIBLE_MESSAGE = "<?php _e('This Addon is not compatible with current ARMember version. Please update ARMember to latest version.','ARMember'); ?>";
    </script>
    
<?php
$_SESSION['arm_member_addon'] = $myplugarr;