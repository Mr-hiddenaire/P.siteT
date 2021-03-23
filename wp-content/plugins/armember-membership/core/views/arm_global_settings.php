<?php
global $wpdb, $ARMember, $arm_global_settings, $arm_email_settings, $arm_payment_gateways, $arm_access_rules, $arm_subscription_plans, $arm_member_forms, $arm_social_feature;
$all_global_settings = $arm_global_settings->arm_get_all_global_settings();

$all_email_settings = $arm_email_settings->arm_get_all_email_settings();
$is_permalink = $arm_global_settings->is_permalink();
$general_settings = $all_global_settings['general_settings'];

$page_settings = $all_global_settings['page_settings'];




$all_plans_data = $arm_subscription_plans->arm_get_all_subscription_plans('arm_subscription_plan_id, arm_subscription_plan_name, arm_subscription_plan_type', ARRAY_A, true);
$defaultRulesTypes = $arm_access_rules->arm_get_access_rule_types();
$default_rules = $arm_access_rules->arm_get_default_access_rules();
$default_schedular_settings = $arm_global_settings->arm_default_global_settings();
$all_roles = $arm_global_settings->arm_get_all_roles();

$currencies = $arm_payment_gateways->currency['paypal'];

?>
<style>
    .purchased_info{
        color:#7cba6c;
        font-weight:bold;
        font-size: 15px;
    }
	.arperrmessage{color:red;}
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
<div class="arm_global_settings_main_wrapper armPageContainer">
	<div class="page_sub_content">
		<form method="post" action="#" id="arm_global_settings" class="arm_global_settings arm_admin_form" onsubmit="return false;">
			<?php do_action('arm_before_global_settings_html', $general_settings);?>
            <div class="page_sub_title"><?php _e('General Settings','ARMember');?></div>
            
            
            
			<table class="form-table">
				<tr class="form-field">
					<th class="arm-form-table-label"><?php _e('Hide admin bar','ARMember');?></th>
					<td class="arm-form-table-content">
						<div class="armswitch arm_global_setting_switch">
							<input type="checkbox" id="hide_admin_bar" <?php checked($general_settings['hide_admin_bar'], '1');?> value="1" class="armswitch_input" name="arm_general_settings[hide_admin_bar]"/>
							<label for="hide_admin_bar" class="armswitch_label"></label>
						</div>
						<label for="hide_admin_bar" class="arm_global_setting_switch_label"><?php _e('Hide admin bar for non-admin users?','ARMember');?></label>
					</td>
				</tr>
				<tr class="form-field arm_exclude_role_for_hide_admin<?php echo ($general_settings['hide_admin_bar'] == '1') ? '' : ' hidden_section' ; ?>">
                    <th class="arm-form-table-label"><?php _e('Exclude role for hide admin bar','ARMember'); ?></th>
                    <td class="arm-form-table-content">
                        <?php $arm_exclude_role_for_hide_admin = array();
                        if(isset($general_settings['arm_exclude_role_for_hide_admin']) && is_array($general_settings['arm_exclude_role_for_hide_admin']))
                        {
                            $arm_exclude_role_for_hide_admin = $general_settings['arm_exclude_role_for_hide_admin'];
                        } else {
                            $arm_exclude_role_for_hide_admin = isset($general_settings['arm_exclude_role_for_hide_admin']) ? explode(',', $general_settings['arm_exclude_role_for_hide_admin']) : array(); 
                        }
                        ?>
                        <select id="arm_access_page_for_restrict_site" class="arm_chosen_selectbox" name="arm_general_settings[arm_exclude_role_for_hide_admin][]" data-placeholder="<?php _e('Select Role(s)..', 'ARMember');?>" multiple="multiple" style="width:500px;">
                                <?php
                                    if (!empty($all_roles)):
                                        foreach ($all_roles as $role_key => $role_value) {
                                            ?><option class="arm_message_selectbox_op" value="<?php echo esc_attr($role_key); ?>" <?php echo (in_array($role_key, $arm_exclude_role_for_hide_admin)) ? ' selected="selected"' : ''; ?>><?php echo stripslashes($role_value);?></option><?php
                                        }
                                    else:
                                ?>
                                        <option value=""><?php _e('No Roles Available', 'ARMember');?></option>
                                <?php endif;?>
                        </select>
                        <span class="arm_info_text" style="margin: 10px 0 0; display:block;">
                            (<?php _e('Admin bar will be displayed to selected roles.','ARMember'); ?>)
                        </span>
                    </td>
                </tr>
				
				
				
				<tr class="form-field">
					<th class="arm-form-table-label"><?php _e('Hide','ARMember');?> wp-login.php <?php _e('page','ARMember');?></th>
					<td class="arm-form-table-content">
						<div class="armswitch arm_global_setting_switch">
							<input type="checkbox" id="hide_wp_login" <?php checked($general_settings['hide_wp_login'], '1');?> value="1" class="armswitch_input" name="arm_general_settings[hide_wp_login]"/>
							<label for="hide_wp_login" class="armswitch_label"></label>
						</div>
						<label for="hide_wp_login" class="arm_global_setting_switch_label"><?php _e('Hide', 'ARMember');?> <strong>wp-login.php</strong> <?php _e('page for all users?','ARMember');?></label>
					</td>
				</tr>
				<tr class="form-field">
					<th class="arm-form-table-label"><?php _e('Hide register link','ARMember');?></th>
					<td class="arm-form-table-content">
						<div class="armswitch arm_global_setting_switch">
							<input type="checkbox" id="hide_register_link" <?php checked($general_settings['hide_register_link'], '1');?> value="1" class="armswitch_input" name="arm_general_settings[hide_register_link]"/>
							<label for="hide_register_link" class="armswitch_label"></label>
						</div>
						<label for="hide_register_link" class="arm_global_setting_switch_label"><?php _e('Hide register link on', 'ARMember');?> <strong>wp-login.php</strong> <?php _e('page?','ARMember');?></label>
					</td>
				</tr>
                                 
               
                                
                <tr class="form-field">
					<th class="arm-form-table-label"><?php _e('Auto Lock Shared Account','ARMember');?></th>
					<td class="arm-form-table-content">						
						<div class="armswitch arm_global_setting_switch">
                                                    <?php $general_settings['autolock_shared_account'] = (isset($general_settings['autolock_shared_account'])) ? $general_settings['autolock_shared_account'] : 0; ?>
							<input type="checkbox" id="autolock_shared_account" <?php checked($general_settings['autolock_shared_account'], '1');?> value="1" class="armswitch_input" name="arm_general_settings[autolock_shared_account]"/>
							<label for="autolock_shared_account" class="armswitch_label"></label>
						</div>
                                                <span class="arm_info_text" style="margin: 10px 0 0; display:block;">(<?php _e('By enabling this feature, you can prevent simultaneous multiple logins using same login details','ARMember'); ?>)</span>
					</td>
				</tr>
				<tr class="form-field">
					<th class="arm-form-table-label"><?php _e('Enable Gravatars?','ARMember');?></th>
					<td class="arm-form-table-content">						
						<div class="armswitch arm_global_setting_switch">
							<input type="checkbox" id="enable_gravatar" <?php checked($general_settings['enable_gravatar'], '1');?> value="1" class="armswitch_input" name="arm_general_settings[enable_gravatar]"/>
							<label for="enable_gravatar" class="armswitch_label"></label>
						</div>
                                                <span class="arm_info_text" style="margin: 10px 0 0; display:block;">(<?php _e('if buddyPress plugin is active then use buddyPress avtars','ARMember'); ?>)</span>
					</td>
				</tr>
                <tr class="form-field">
                    <th class="arm-form-table-label"><?php _e('Allow image cropping','ARMember');?></th>
                    <td class="arm-form-table-content">
                        <div class="armswitch arm_global_setting_switch">
                         <?php $enable_crop = isset($general_settings['enable_crop']) ? $general_settings['enable_crop'] : 0; ?>
                            <input type="checkbox" id="enable_crop" <?php checked($enable_crop, '1');?> value="1" class="armswitch_input" name="arm_general_settings[enable_crop]"/>
                            <label for="enable_crop" class="armswitch_label"></label>
                        </div>
                        <label for="enable_crop" class="arm_global_setting_switch_label"><?php _e('Allow avatar and cover photo cropping', 'ARMember');?> </label>
                    </td>
                </tr>
                <tr class="form-field">
                    <th class="arm-form-table-label"><?php echo esc_html('Enable Spam Protection','ARMember');?></th>
                    <td class="arm-form-table-content">
                        <div class="armswitch arm_global_setting_switch">
                         <?php $spam_protection = isset($general_settings['spam_protection']) ? $general_settings['spam_protection'] : 0; ?>
                            <input type="checkbox" id="spam_protection" <?php checked($spam_protection, '1');?> value="1" class="armswitch_input" name="arm_general_settings[spam_protection]"/>
                            <label for="spam_protection" class="armswitch_label"></label>
                        </div>
			<label for="spam_protection" class="arm_global_setting_switch_label"><?php echo esc_html('Enable hidden spam protection mechanism in signup/login forms','ARMember');?></label>
                    </td>
                </tr>
				<tr class="form-field" id="changeCurrency">
					<th class="arm-form-table-label"><?php _e('New user approval','ARMember');?></th>
					<td class="arm-form-table-content">
                                            <?php $general_settings['user_register_verification'] = isset($general_settings['user_register_verification']) ? $general_settings['user_register_verification'] : ''; ?>
						<input type='hidden' id='arm_new_user_approval' name="arm_general_settings[user_register_verification]" value="<?php echo $general_settings['user_register_verification'];?>" />
						<dl class="arm_selectbox column_level_dd">
							<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
							<dd>
								<ul data-id="arm_new_user_approval">
									<li data-label="<?php _e('Automatic approve','ARMember');?>" data-value="auto"><?php _e('Automatic approve', 'ARMember');?></li>
									<li data-label="<?php _e('Email verified approve','ARMember');?>" data-value="email"><?php _e('Email verified approve', 'ARMember');?></li>
									<li data-label="<?php _e('Manual approve by admin','ARMember');?>" data-value="manual"><?php _e('Manual approve by admin', 'ARMember');?></li>
								</ul>
							</dd>
						</dl>
					</td>
				</tr>



				<tr class="form-field" id="profilePermalinkBase">
					<th class="arm-form-table-label"><?php _e('Default currency','ARMember');?></th>
					<td class="arm-form-table-content">
						<?php 
						$currencies = apply_filters('arm_available_currencies', $currencies);
						$paymentcurrency = $general_settings['paymentcurrency'];
						$custom_currency_status = isset($general_settings['custom_currency']['status']) ? $general_settings['custom_currency']['status'] : '';
						$custom_currency_symbol = isset($general_settings['custom_currency']['symbol']) ? $general_settings['custom_currency']['symbol'] : '';
						$custom_currency_shortname = isset($general_settings['custom_currency']['shortname']) ? $general_settings['custom_currency']['shortname'] : '';
						$custom_currency_place = isset($general_settings['custom_currency']['place']) ? $general_settings['custom_currency']['place'] : '';
						?>
						<input type='hidden' id='arm_payment_currency' name="arm_general_settings[paymentcurrency]" value="<?php echo $paymentcurrency;?>" />
						<dl class="arm_selectbox column_level_dd arm_default_currency_box <?php echo ($custom_currency_status == 1) ? 'disabled' : '';?>">
							<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
							<dd>
								<ul data-id="arm_payment_currency">
									<?php foreach ($currencies as $key => $value): ?>
									<li data-label="<?php echo $key . " ( $value ) ";?>" data-value="<?php echo esc_attr($key);?>"><?php echo $key . " ( $value ) ";?></li>
									<?php endforeach;?>
								</ul>
							</dd>
						</dl>

						<?php
                            $arm_specific_currency_position = isset($general_settings['arm_specific_currency_position']) ? $general_settings['arm_specific_currency_position'] : 'suffix';
                        ?>

                        <div class="arm_currency_prefix_suffix_display" <?php echo ($paymentcurrency != "EUR") ? "style='display: none;'" : ""; ?>>
                            <div>
                                <input type="radio" id="default_currency_prefix_val" name="arm_general_settings[arm_specific_currency_position]" class="arm_general_input arm_iradio default_currency_prefix_suffix_val" <?php checked($arm_specific_currency_position, 'prefix');?> value="prefix" <?php echo ($custom_currency_status == 1) ? 'disabled' : '';?> />
                                <label class="default_currency_prefix_suffix_lbl" for="default_currency_prefix_val" <?php echo ($custom_currency_status == 1) ? 'style="cursor: no-drop;"' : '';?>><?php _e('Prefix','ARMember');?></label>
                            </div>
                            <div>
                                <input type="radio" id="default_currency_suffix_val" name="arm_general_settings[arm_specific_currency_position]" class="arm_general_input arm_iradio default_currency_prefix_suffix_val" <?php checked($arm_specific_currency_position, 'suffix');?> value="suffix" <?php echo ($custom_currency_status == 1) ? 'disabled' : '';?> />
                                <label class="default_currency_prefix_suffix_lbl" for="default_currency_suffix_val" <?php echo ($custom_currency_status == 1) ? 'style="cursor: no-drop;"' : '';?>><?php _e('Suffix','ARMember');?></label>
                            </div>
                        </div>
						<div class="armclear"></div>
						<span style="width: 220px;text-align: center;display: inline-block;font-weight: bold;font-size: 16px;margin: 15px 0 1px;"><?php _e('OR', 'ARMember');?></span>
						<div class="armclear"></div>
						<div class="armGridActionTD arm_custom_currency_options_container">
							<input type="hidden" class="custom_currency_symbol" name="arm_general_settings[custom_currency][symbol]" value="<?php echo $custom_currency_symbol;?>">
							<input type="hidden" class="custom_currency_shortname" name="arm_general_settings[custom_currency][shortname]" value="<?php echo $custom_currency_shortname;?>">
							<input type="hidden" class="custom_currency_place" name="arm_general_settings[custom_currency][place]" value="<?php echo $custom_currency_place;?>">

							<div class="armclear"></div>
							<label class="arm_custom_currency_checkbox_label"><input type="checkbox" class="arm_custom_currency_checkbox arm_icheckbox" value="1" name="arm_general_settings[custom_currency][status]" <?php checked($custom_currency_status, 1)?>><span><?php _e('Set Custom Currency', 'ARMember');?></span></label>
							<div class="arm_confirm_box_custom_currency arm_no_hide" id="arm_confirm_box_custom_currency">
								<div class="arm_confirm_box_body" style="max-width: 100%;">
									<div class="arm_confirm_box_arrow"></div>
									<div class="arm_confirm_box_text arm_custom_currency_fields" style="text-align: left;">
										<table>
											<tr>
												<th><?php _e('Currency Symbol', 'ARMember');?></th>
												<td>
													<input type="text" id="custom_currency_symbol" value="<?php echo (!empty($custom_currency_symbol)) ? "$custom_currency_symbol" : ''; ?>">
													<span class="arm_error_msg symbol_error" style="display:none;"><?php _e('Please enter symbol.', 'ARMember');?></span>
													<span class="arm_error_msg invalid_symbol_error" style="display:none;"><?php _e('Please enter valid symbol.', 'ARMember');?></span>
												</td>
											</tr>
											<tr>
												<th><?php _e('Currency Shortname', 'ARMember');?></th>
												<td>
													<input type="text" id="custom_currency_shortname" value="<?php echo (!empty($custom_currency_shortname)) ? "$custom_currency_shortname" : ''; ?>">
													<span class="arm_error_msg shortname_error" style="display:none;"><?php _e('Please enter shortname.', 'ARMember');?></span>
													<span class="arm_error_msg invalid_shortname_error" style="display:none;"><?php _e('Please enter valid shortname.', 'ARMember');?></span>
												</td>
											</tr>
											<tr>
												<th><?php _e('Symbol will be display as', 'ARMember');?></th>
												<td>
													<input type="hidden" id="custom_currency_place" value="<?php echo (!empty($custom_currency_place)) ? $custom_currency_place : 'prefix'; ?>"/>
													<dl class="arm_selectbox column_level_dd">
														<dt style="width: 110px;"><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
														<dd>
															<ul data-id="custom_currency_place">
																<li data-label="<?php _e('Prefix', 'ARMember');?>" data-value="prefix"><?php _e('Prefix', 'ARMember');?></li>
																<li data-label="<?php _e('Suffix', 'ARMember');?>" data-value="suffix"><?php _e('Suffix', 'ARMember');?></li>
															</ul>
														</dd>
													</dl>
												</td>
											</tr>
										</table>
									</div>
									<div class='arm_confirm_box_btn_container'>
										<button type="button" class="arm_confirm_box_btn armemailaddbtn" id="arm_custom_currency_ok_btn" style="margin-right: 5px;"><?php _e('Add', 'ARMember');?></button>
										<button type="button" class="arm_confirm_box_btn armcancel" onclick="hideCustomCurrencyBox();"><?php _e('Cancel', 'ARMember');?></button>
									</div>
								</div>
							</div>
							<div class="armclear"></div>
							<span class="arm_custom_currency_text"><?php 
	                        if (!empty($custom_currency_symbol) && !empty($custom_currency_shortname)) {
	                            $currency_name = $custom_currency_shortname . " ( $custom_currency_symbol )";
	                            echo '<span>'.__('Custom Currency', 'ARMember') . ": <strong>$currency_name</strong><a href='javascript:void(0)' class='arm_custom_currency_edit'>".__('Edit', 'ARMember')."</a></span>";
	                        }
	                        ?></span>
						</div>						
						<div class="armclear"></div>
						<?php 
						if($custom_currency_status == 1){
							$paymentcurrency = $custom_currency_shortname;
						}
						$currency_warring = $arm_payment_gateways->arm_check_currency_status($paymentcurrency);
						?>
						<span class="arm_global_setting_currency_warring" style="<?php echo (empty($currency_warring)) ? 'display:none;' : '';?>"><?php echo $currency_warring;?></span>
					</td>
				</tr>
                <tr class="form-field" style="<?php echo (!$arm_social_feature->isSocialFeature) ? 'display:none;' : '';?>">
					<th class="arm-form-table-label"><?php _e('Profile Permalink Base','ARMember');?></th>
					<td class="arm-form-table-content">
                        <?php 
                        $permalink_base = (isset($general_settings['profile_permalink_base'])) ? $general_settings['profile_permalink_base'] : 'user_login';
                        if ($is_permalink) {
                            $profileUrl = trailingslashit(untrailingslashit($arm_global_settings->profile_url));
                            $profileUrl_user_login = $profileUrl . '<b>username</b>/';
                            $profileUrl_user_id = $profileUrl . '<b>user_id</b>/';
                        } else {
                            $profileUrl = $arm_global_settings->add_query_arg('arm_user', 'arm_base_slug', $arm_global_settings->profile_url);
                            $profileUrl_user_login = str_replace('arm_base_slug', '<b>username</b>', $profileUrl);
                            $profileUrl_user_id = str_replace('arm_base_slug', '<b>user_id</b>', $profileUrl);
                        }
                        ?>
						<input type='hidden' id="arm_profile_permalink_base" name="arm_general_settings[profile_permalink_base]" value="<?php echo $permalink_base;?>" />
						<dl class="arm_selectbox column_level_dd">
							<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
							<dd>
								<ul data-id="arm_profile_permalink_base">
									<li data-label="<?php _e('Username','ARMember');?>" data-value="user_login"><?php _e('Username', 'ARMember');?></li>
									<li data-label="<?php _e('User ID','ARMember');?>" data-value="user_id"><?php _e('User ID', 'ARMember');?></li>
									
								</ul>
							</dd>
						</dl>
                        <div class="armclear"></div>
                        <span class="arm_info_text arm_profile_user_login" style="<?php echo ($permalink_base == 'user_login')? '' : 'display: none;';?>">e.g. <?php echo $profileUrl_user_login;?></span>
                        <span class="arm_info_text arm_profile_user_id" style="<?php echo ($permalink_base == 'user_id')? '' : 'display: none;';?>">e.g. <?php echo $profileUrl_user_id;?></span>
					</td>
				</tr>
                               
                                
				<tr class="form-field">
					<th class="arm-form-table-label"><?php _e('Load JS & CSS in all pages','ARMember');?></th>
					<td class="arm-form-table-content">						
						<div class="armswitch arm_global_setting_switch" style="margin-top: 5px;">
							<input type="checkbox" id="arm_enqueue_all_js_css" <?php checked($general_settings['enqueue_all_js_css'], '1');?> value="1" class="armswitch_input" name="arm_general_settings[enqueue_all_js_css]"/>
							<label for="arm_enqueue_all_js_css" class="armswitch_label"></label>
						</div>
						<span class="arm_info_text" style="margin: 10px 0 0;display: block;">(<strong><?php _e('Not recommended', 'ARMember');?></strong> - <?php _e('If you have any js/css loading issue in your theme, only in that case you should enable this settings', 'ARMember');?>)</span>
					</td>
				</tr>
				
            </table>
			<div class="arm_solid_divider"></div>
			<div class="page_sub_title"><?php _e('Email Settings','ARMember');?></div>
			<table class="form-table">
				<tr class="form-field">
					<th class="arm-form-table-label"><?php _e('From/Reply to name', 'ARMember'); ?></th>
					<td class="arm-form-table-content">
						<input id="arm_email_from_name" type="text" name="arm_email_from_name" value="<?php echo (!empty($all_email_settings['arm_email_from_name']) ? $all_email_settings['arm_email_from_name'] : get_option('blogname') ); ?>" >
                        <span id="email_from_name_error" class="arm_error_msg email_from_name_error" style="display:none;"><?php _e('Please enter From Name.', 'ARMember');?></span>
                         <span id="invalid_email_from_name_error" class="arm_error_msg invalid_email_from_name_error" style="display:none;"><?php _e('Please enter valid From Name.', 'ARMember');?></span>         
					</td>
				</tr>
				<tr class="form-field">
					<th class="arm-form-table-label"><?php _e('From/Reply to email', 'ARMember'); ?></th>
					<td class="arm-form-table-content">
						<input id="arm_email_from_email" type="email" name="arm_email_from_email" value="<?php echo (!empty($all_email_settings['arm_email_from_email']) ? $all_email_settings['arm_email_from_email'] : get_option('admin_email') ); ?>" >
                                                <span id="email_from_email_error" class="arm_error_msg email_from_email_error" style="display:none;"><?php _e('Please enter From Email ID.', 'ARMember');?></span>
                                        <span id="invalid_email_from_email_error" class="arm_error_msg invalid_email_from_email_error" style="display:none;"><?php _e('Please enter valid From Email ID.', 'ARMember');?></span>
					</td>
				</tr>
                                <tr class="form-field">
					<th class="arm-form-table-label"><?php _e('Admin email', 'ARMember'); ?></th>
					<td class="arm-form-table-content">
						<input id="arm_email_admin_email" type="email" name="arm_email_admin_email" value="<?php echo (!empty($all_email_settings['arm_email_admin_email']) ? $all_email_settings['arm_email_admin_email'] : get_option('admin_email') ); ?>" >
                                                <span id="email_admin_email_error" class="arm_error_msg email_admin_email_error" style="display:none;"><?php _e('Please enter Admin Email ID.', 'ARMember');?></span>
                                        <span id="invalid_email_admin_email_error" class="arm_error_msg invalid_email_admin_email_error" style="display:none;"><?php _e('Please enter valid Admin Email ID.', 'ARMember');?></span>
					<?php $ae_tooltip = __("You can add multiple Admin email address separated by comma in case of you want to send email to more than one email address.", 'ARMember'); ?>
                                        <i class="arm_helptip_icon armfa armfa-question-circle" title="<?php echo $ae_tooltip; ?>"></i>
                                        </td>
				</tr>
				<tr class="form-field">
					<th class="arm_email_settings_content_label"><?php _e('Email notification','ARMember');?></th>
					<td class="arm_email_settings_content_text" style="vertical-align: top;padding: 10px;">
						<div class="arm_email_settings_select_text">
							<div class="arm_email_settings_select_text_inner">
							<?php $all_email_settings['arm_email_server'] = (isset($all_email_settings['arm_email_server'])) ? $all_email_settings['arm_email_server'] : 'wordpress_server';?>
								<input type="radio" id="arm_email_server_ws" class="arm_general_input arm_email_notification_radio arm_iradio" <?php checked($all_email_settings['arm_email_server'], 'wordpress_server');?> name="arm_email_server" value="wordpress_server" />
								<label for="arm_email_server_ws" class="arm_email_settings_help_text"><?php _e('Wordpress Server','ARMember');?></label>
							</div>
							<div class="arm_email_settings_select_text_inner">
								<input type="radio" id="arm_email_server_smtps" class="arm_general_input arm_email_notification_radio arm_iradio" <?php checked($all_email_settings['arm_email_server'], 'smtp_server');?> name="arm_email_server" value="smtp_server" />
								<label for="arm_email_server_smtps" class="arm_email_settings_help_text"><?php _e('SMTP Server','ARMember');?></label>
							</div>
							<div class="arm_email_settings_select_text_inner">
								<input type="radio" id="arm_email_server_phpm" class="arm_general_input arm_email_notification_radio arm_iradio" <?php checked($all_email_settings['arm_email_server'], 'phpmailer');?> name="arm_email_server" value="phpmailer" />
								<label for="arm_email_server_phpm" class="arm_email_settings_help_text"><?php _e('PHP Mailer','ARMember');?></label>
							</div>
						</div>
						<div class="arm_smtp_slide_form">
							<table class="form-sub-table" width="100%">
								<tr>
									<th class="arm_email_settings_content_label" style="min-width: 100px;"><?php _e('Authentication','ARMember');?></th>
									<td class="arm_email_settings_content_text">
										<?php $arm_mail_authentication = (isset($all_email_settings['arm_mail_authentication'])) ? $all_email_settings['arm_mail_authentication'] : '1'; ?>
                                        <label class="arm_custom_currency_checkbox_label"><input type="checkbox" class="arm_icheckbox" value="1" id="arm_mail_authentication" name="arm_mail_authentication" onchange="arm_mail_authentication_func(this.value);" <?php checked($arm_mail_authentication, 1)?>><span><?php _e('Enable SMTP authentication', 'ARMember');?></span></label>
									</td>
								</tr>
								<tr>
									<th class="arm_email_settings_content_label" style="min-width: 100px;"><?php _e('Mail Server','ARMember');?> *</th>
									<td class="arm_email_settings_content_text">
                                                                            <?php $arm_mail_server = (isset($all_email_settings['arm_mail_server'])) ? $all_email_settings['arm_mail_server'] : ''; ?>
										<input type="text" id="arm_mail_server" name="arm_mail_server" value="<?php echo (isset($all_email_settings['arm_mail_server'])) ? $all_email_settings['arm_mail_server'] : '';?>" class="arm_mail_server_input" style="width: 390px;"/>
										<span class="error arm_invalid" id="arm_mail_server_error" style="display: none;"><?php _e('Mail Server can not be left blank.', 'ARMember');?></span>
									</td>
								</tr>
								<tr>
									<th class="arm_email_settings_content_label"><?php _e('Port','ARMember');?> *</th>
									<td class="arm_email_settings_content_text">
                                                                            <?php $arm_mail_port = (isset($all_email_settings['arm_mail_port'])) ? $all_email_settings['arm_mail_port'] : ''; ?>
										<input type="text" id="arm_port" name="arm_mail_port" value="<?php echo (isset($all_email_settings['arm_mail_port'])) ? $all_email_settings['arm_mail_port'] : '';?>" style="width: 390px;"/>
										<span class="error arm_invalid" id="arm_mail_port_error" style="display: none;"><?php _e('Port can not be left blank.', 'ARMember');?></span>
									</td>
								</tr>
								<tr class="arm_email_settings_login_name_main" style="<?php if(empty($arm_mail_authentication)){ echo "display:none;"; }?>">
									<th class="arm_email_settings_content_label"><?php _e('Login Name','ARMember');?> *</th>
									<td class="arm_email_settings_content_text">
                                                                            <?php $arm_mail_login_name = (isset($all_email_settings['arm_mail_login_name'])) ? $all_email_settings['arm_mail_login_name'] : ''; ?>
										<input type="text" id="arm_login_name" value="<?php echo (isset($all_email_settings['arm_mail_login_name'])) ? $all_email_settings['arm_mail_login_name'] : '';?>" name="arm_mail_login_name" style="width: 390px;"/>
										<span class="error arm_invalid" id="arm_mail_login_name_error" style="display: none;"><?php _e('Login Name can not be left blank.', 'ARMember');?></span>
									</td>
								</tr>
								<tr class="arm_email_settings_password_main" style="<?php if(empty($arm_mail_authentication)){ echo "display:none;"; }?>">
									<th class="arm_email_settings_content_label"><?php _e('Password','ARMember');?> *</th>
									<td class="arm_email_settings_content_text">
                                                                            <?php $arm_mail_pssword = (isset($all_email_settings['arm_mail_password'])) ? $all_email_settings['arm_mail_password'] : ''; ?>
										<input type="password" id="arm_password" autocomplete="off"> value="<?php echo (isset($all_email_settings['arm_mail_password'])) ? $all_email_settings['arm_mail_password'] : '';?>" name="arm_mail_password" style="width: 390px;"/>
										<span class="error arm_invalid" id="arm_mail_password_error" style="display: none;"><?php _e('Password can not be left blank.', 'ARMember');?></span>
									</td>
								</tr>
								<tr>
									<th class="arm_email_settings_content_label"><?php _e('Encryption','ARMember');?></th>
									<td class="arm_email_settings_content_text">
										<div class="arm_email_settings_select_text">     	
											<div id="arm_first_enc" class="arm_email_settings_select_text_inner">
<?php
$selected_enc = (isset($all_email_settings['arm_smtp_enc'])) ? (($all_email_settings['arm_smtp_enc'] == 'ssl' || $all_email_settings['arm_smtp_enc'] == 'tls') ? '1' : '0' ) : '0';
												$all_email_settings['arm_smtp_enc'] = (isset($all_email_settings['arm_smtp_enc'])) ? $all_email_settings['arm_smtp_enc'] : '0';
												?>
												<input type="radio" id="arm_smtp_enc_none" class="arm_general_input arm_iradio" <?php checked( $selected_enc, '0' );?>  name="arm_smtp_enc" value="none" />
                                                                                                <label for="arm_smtp_enc_none" class="arm_email_settings_help_text" style="margin-right: 0px;"><?php _e('None','ARMember');?></label>
											</div>
											<div class="arm_email_settings_select_text_inner">
												<input type="radio" id="arm_smtp_enc_ssl" class="arm_general_input arm_iradio" <?php checked( $all_email_settings['arm_smtp_enc'], 'ssl' );?> name="arm_smtp_enc" value="ssl" />
												<label for="arm_smtp_enc_ssl" class="arm_email_settings_help_text" style="margin-right: 0px;"><?php _e('SSL','ARMember');?></label>
											</div>
											<div class="arm_email_settings_select_text_inner">
												<input type="radio" id="arm_smtp_enc_tls" class="arm_general_input arm_iradio" <?php checked( $all_email_settings['arm_smtp_enc'], 'tls' );?> name="arm_smtp_enc" value="tls" />
												<label for="arm_smtp_enc_tls" class="arm_email_settings_help_text" style="margin-right: 0px;"><?php _e('TLS','ARMember');?></label>
											</div>
										</div>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="arm_email_settings_content_label"><b><?php _e('Send Test E-mail</b>', 'ARMember'); ?></b></th>
                                    <td class="arm_email_settings_content_text">
                                        <label id="arm_success_test_mail" style="font-size:14px; display:none; float:left; width:100%; font-family:'open_sanssemibold',Arial,Helvetica,Verdana,sans-serif; color:#4c9738;"><?php _e('Your test mail is successfully sent.', 'ARMember'); ?></label>
                                        <label id="arm_error_test_mail" style="font-size:14px; display:none; float:left; width:100%; font-family:'open_sanssemibold',Arial,Helvetica,Verdana,sans-serif; color:#ff0000;"><?php _e('Your test mail is not sent for some reason, Please check your SMTP setting.', 'ARMember'); ?></label>
                                    </td>
                                </tr> 
                                <tr>
                                    <th class="arm_email_settings_content_label"><?php _e('To', 'ARMember'); ?> *</th>
                                    <td class="arm_email_settings_content_text">
                                        <input type="text" id="arm_test_email_to" name="arm_test_email_to" value="" style="width: 390px;"/>
                                        <span class="error arm_invalid" id="arm_test_email_to_error" style="display: none;"><?php _e('To can not be left blank.', 'ARMember'); ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="arm_email_settings_content_label"><?php _e('Message', 'ARMember'); ?> *</th>
                                    <td class="arm_email_settings_content_text">
                                        <textarea id="arm_test_email_msg" value="" name="arm_test_email_msg" style="width: 390px;"></textarea>
                                        <span class="error arm_invalid" id="arm_test_email_msg_error" style="display: none;"><?php _e('Message can not be left blank.', 'ARMember'); ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="arm_email_settings_content_label"></th>
                                    <td class="arm_email_settings_content_text">
                                        
                                        <button type="button" class="arm_save_btn" id="arm_send_test_mail"><?php _e('Send test mail', 'ARMember');?></button><img src="<?php echo MEMBERSHIPLITE_IMAGES_URL . '/arm_loader.gif'; ?>" id="arm_send_test_mail_loader" width="24" height="24" style="position: relative; top: 8px; display: none;" /><br/><span style="font-style:italic;">(<?php _e('Test e-mail works only after configure SMTP server settings', 'ARMember'); ?>)</span>
                                    </td>
								 </tr>
							</table>
						</div>
					</td>
				</tr>
			</table>
			<div class="arm_solid_divider"></div>
            
            <div class="page_sub_title"><?php _e('Manage Preset Form Fields', 'ARMember'); ?></div>
            <table class="form-table">
                <tbody>
                    <tr>
                        <td>
                        <div class="arm_manage_preset_fields">
                        <div class="arm_manage_preset_fields_btn">
                         <input type="button" value="<?php _e('Edit Preset Form Fields', 'ARMember'); ?>" onclick="arm_open_edit_field_popup();" id="arm_edit_form_fields" class="armemailaddbtn" title="" style="width: 200px;">
                         </div>
                         <div class="arm_manage_preset_fields_text">
                            <span class="arm_info_text"><?php _e('To edit specific form preset fields, click on this button, popup opens, edit fields which you want to update and click on update button.','ARMember'); ?></span>
                            </div>
                         </div>
                         <div class="arm_manage_preset_fields" style="margin-top: 30px;">
                         <div class="arm_manage_preset_fields_btn">
                            <input type="button" value="<?php _e('Clear Preset Form Fields', 'ARMember'); ?>" onclick="arm_open_clear_field_popup();" id="arm_clear_form_fields" class="armemailaddbtn"  style="width: 200px;">
                            </div>
                            <div class="arm_manage_preset_fields_text">
                                <span class="arm_info_text"><?php _e('To remove specific form fields with its value, click on this button, popup opens, select fields which you want to remove from everywhere.','ARMember'); ?></span>
                                </div>
                            </div>
                          
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="arm_solid_divider"></div>
            <div class="page_sub_title"><?php _e('Email notification scheduler setting', 'ARMember'); ?>
            <i class="arm_helptip_icon armfa armfa-question-circle" title="<?php _e("when you change value from below dropdown and save it then it will set new schedular and remove previous one.", 'ARMember'); ?>"></i>
            </div>
            <table class="form-table">
                <tbody>
                    <tr class="form-field">
                        <th><?php _e('Schedule Every', 'ARMember'); ?> </th>
                        <td>
                            <?php $arm_email_schedular_time = isset($general_settings['arm_email_schedular_time']) ? $general_settings['arm_email_schedular_time'] : 12; ?>
                            <input type="hidden" name="arm_general_settings[arm_email_schedular_time]" id="arm_email_schedular_time" value="<?php echo $arm_email_schedular_time ?>" />
                            <dl class="arm_selectbox column_level_dd">
                                <dt style="min-width:40px;max-width:40px;">
                                <span></span>
                                <input type="text" style="display:none;" value="" class="arm_autocomplete"  />
                                <i class="armfa armfa-caret-down armfa-lg"></i>
                                </dt>
                                <dd>
                                    <ul data-id="arm_email_schedular_time" style="display:none;">
                                        <?php
                                        for ($ct = 1; $ct <= 24; $ct++) {
                                            echo "<li data-value='{$ct}' data-label='{$ct}'>{$ct}</li>";
                                        }
                                        ?>
                                    </ul>
                                </dd>
                            </dl>
                            <span><?php _e('Hours','ARMember'); ?></span>
                        </td>
                    </tr>
                    <?php do_action('arm_cron_schedular_from_outside'); ?>
                </tbody>
            </table>
            <div class="arm_solid_divider"></div>
            <?php
            $frontfontOptions = array(
                'level_1_font' => __('Level 1', 'ARMember'),
                'level_2_font' => __('Level 2', 'ARMember'),
                'level_3_font' => __('Level 3', 'ARMember'),
                'level_4_font' => __('Level 4', 'ARMember'),
                'link_font' => __('Links', 'ARMember'),
                'button_font' => __('Buttons', 'ARMember'),
            );
            $frontfontOptions = apply_filters('arm_front_font_settings_type', $frontfontOptions);
            ?>
            <?php if (!empty($frontfontOptions)): ?>
                <div class="page_sub_title"><?php _e('Front End Font Settings', 'ARMember'); ?></div>
                <table class="form-table">
                    <?php
                    $frontOptHtml = '';
                    $frontOptions = isset($general_settings['front_settings']) ? $general_settings['front_settings'] : array();
                    foreach ($frontfontOptions as $key => $title) {
                        $fontVal = ((!empty($frontOptions[$key])) ? $frontOptions[$key] : array());
                        $font_bold = (isset($fontVal['font_bold']) && $fontVal['font_bold'] == '1') ? 1 : 0;
                        $font_italic = (isset($fontVal['font_italic']) && $fontVal['font_italic'] == '1') ? 1 : 0;
                        $font_decoration = (isset($fontVal['font_decoration'])) ? $fontVal['font_decoration'] : '';
                        $frontOptHtml .= '<tr class="form-field">';
                        $frontOptHtml .= '<th class="arm-form-table-label">' . $title;
                        if ($key == 'level_1_font') {
                            $tooltip_title = __("Font settings of Level 1 will be applied to main heading of frontend shortcodes. Like Transaction listing heading and like wise.", 'ARMember');
                        } elseif ($key == 'level_2_font') {
                            $tooltip_title = __("Font settings of Level 2 will be applied to sub heading ( Main Labels ) of frontend shortcodes. For example table heading of trasanction listing.", 'ARMember');
                        } elseif ($key == 'level_3_font') {
                            $tooltip_title = __("Font settings of Level 3 will be applied to sub labels of frontend shortcodes. For example table content of trasanction listing.", 'ARMember');
                        } elseif ($key == 'level_4_font') {
                            $tooltip_title = __("Font settings of Level 4 will be applied to very small labels of frontend shortcodes. For member listing etc.", 'ARMember');
                        } elseif ($key == 'link_font') {
                            $tooltip_title = __("Font settings of Links will be applied to links of frontend shortcodes. For example edit profile, logout link and profile links etc.", 'ARMember');
                        } elseif ($key == 'button_font') {
                            $tooltip_title = __("Font settings of Buttons will be applied to buttons of frontend shortcodes output. For example Renew button, Cancel Button, Make Payment Button etc.", 'ARMember');
                        }
                        $frontOptHtml .= ' <i class="arm_helptip_icon armfa armfa-question-circle" title="' . $tooltip_title . '"></i></th>';
                        $frontOptHtml .= '<td>';
                        $frontOptHtml .= '<input type="hidden" id="arm_front_font_family_' . $key . '" name="arm_general_settings[front_settings][' . $key . '][font_family]" value="' . ((!empty($fontVal['font_family'])) ? $fontVal['font_family'] : 'Helvetica') . '"/>';
                        $frontOptHtml .= '<dl class="arm_selectbox column_level_dd">';
                        $frontOptHtml .= '<dt style="width:160px;"><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"  /><i class="armfa armfa-caret-down armfa-lg"></i></dt>';
                        $frontOptHtml .= '<dd><ul data-id="arm_front_font_family_' . $key . '">';
                        $frontOptHtml .= $arm_member_forms->arm_fonts_list();
                        $frontOptHtml .= '</ul></dd>';
                        $frontOptHtml .= '</dl>';
                        $frontOptHtml .= '<input type="hidden" id="arm_front_font_size_' . $key . '" name="arm_general_settings[front_settings][' . $key . '][font_size]" value="' . (!empty($fontVal['font_size']) ? $fontVal['font_size'] : '14') . '"/>';
                        $frontOptHtml .= '<dl class="arm_selectbox column_level_dd">';
                        $frontOptHtml .= '<dt style="width:70px;"><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"  /><i class="armfa armfa-caret-down armfa-lg"></i></dt>';
                        $frontOptHtml .= '<dd><ul data-id="arm_front_font_size_' . $key . '">';
                        for ($i = 8; $i < 41; $i++) {
                            $frontOptHtml .= '<li data-label="' . $i . ' px" data-value="' . $i . '">' . $i . ' px</li>';
                        }
                        $frontOptHtml .= '</ul></dd>';
                        $frontOptHtml .= '</dl>';
                        $frontOptHtml .= '<div class="arm_front_font_color">';
                        $frontOptHtml .= '<input type="text" id="arm_front_font_color_' . $key . '" name="arm_general_settings[front_settings][' . $key . '][font_color]" class="arm_colorpicker" value="' . (!empty($fontVal['font_color']) ? $fontVal['font_color'] : '#000000') . '">';
                        $frontOptHtml .= '</div>';
                        $frontOptHtml .= '<div class="arm_font_style_options arm_front_font_style_options">';
                        $frontOptHtml .= '<label class="arm_font_style_label ' . (($font_bold == '1') ? 'arm_style_active' : '') . '" data-value="bold" data-field="arm_front_font_bold_' . $key . '"><i class="armfa armfa-bold"></i></label>';
                        $frontOptHtml .= '<input type="hidden" name="arm_general_settings[front_settings][' . $key . '][font_bold]" id="arm_front_font_bold_' . $key . '" class="arm_front_font_bold_' . $key . '" value="' . $font_bold . '" />';
                        $frontOptHtml .= '<label class="arm_font_style_label ' . (($font_italic == '1') ? 'arm_style_active' : '') . '" data-value="italic" data-field="arm_front_font_italic_' . $key . '"><i class="armfa armfa-italic"></i></label>';
                        $frontOptHtml .= '<input type="hidden" name="arm_general_settings[front_settings][' . $key . '][font_italic]" id="arm_front_font_italic_' . $key . '" class="arm_front_font_italic_' . $key . '" value="' . $font_italic . '" />';

									$frontOptHtml .= '<label class="arm_font_style_label arm_decoration_label '.(($font_decoration=='underline')? 'arm_style_active' : '').'" data-value="underline" data-field="arm_front_font_decoration_'.$key.'"><i class="armfa armfa-underline"></i></label>';
									$frontOptHtml .= '<label class="arm_font_style_label arm_decoration_label '.(($font_decoration=='line-through')? 'arm_style_active' : '').'" data-value="line-through" data-field="arm_front_font_decoration_'.$key.'"><i class="armfa armfa-strikethrough"></i></label>';
									$frontOptHtml .= '<input type="hidden" name="arm_general_settings[front_settings]['.$key.'][font_decoration]" id="arm_front_font_decoration_'.$key.'" class="arm_front_font_decoration_'.$key.'" value="'.$font_decoration.'" />';
								$frontOptHtml .= '</div>';
							$frontOptHtml .= '</td>';
						$frontOptHtml .= '</tr>';
					}
					echo $frontOptHtml;
					?>	
				</table>
				
			<?php endif;?>
		
			<?php do_action('arm_after_global_settings_html', $general_settings);?>
			<div class="arm_submit_btn_container">
				<button id="arm_global_settings_btn" class="arm_save_btn" name="arm_global_settings_btn" type="submit"><?php _e('Save', 'ARMember') ?></button>&nbsp;<img src="<?php echo MEMBERSHIPLITE_IMAGES_URL.'/arm_loader.gif' ?>" id="arm_loader_img" style="position:relative;top:8px;display:none;" width="24" height="24" />
				<?php wp_nonce_field( 'arm_wp_nonce' );?>
			</div>
		</form>
	</div>
	<div class="armclear"></div>
	<div class="arm_custom_css_detail_container"></div>
    <div class="arm_edit_form_fields_popup_div popup_wrapper <?php echo (is_rtl()) ? 'arm_page_rtl' : ''; ?>" style="width:1000px; min-height: 200px;">
            <form method="GET" id="arm_edit_preset_fields_form" class="arm_admin_form">
                <div>
                    <div class="popup_header">
                        <span class="popup_close_btn arm_popup_close_btn arm_edit_preset_fields_close_btn"></span>
                        
                        <span class="add_rule_content"><?php _e('Edit Preset Fields', 'ARMember'); ?></span>
                    </div>
                    <div class="popup_content_text arm_edit_form_fields_popup_text" style="text-align:center;">
                            <div style="width: 100%; margin: 45px auto;">   <img src="<?php echo MEMBERSHIPLITE_IMAGES_URL."/arm_loader.gif"; ?>">
                            </div>
                    </div>
                    <div class="popup_content_btn popup_footer">
                        <div class="arm_preset_field_updated_msg">
                                <span class="arm_success_msg"><?php _e('Preset Fields are updated successfully.', 'ARMember'); ?></span>
                                <span class="arm_error_msg"><?php _e('Sorry, something went wrong while updating prest fields.', 'ARMember'); ?></span>
                        </div>
                        <div class="popup_content_btn_wrapper">
                            <img src="<?php echo MEMBERSHIPLITE_IMAGES_URL . '/arm_loader.gif' ?>" id="arm_loader_img_preset_update_field" class="arm_loader_img" style="position: relative;top: 15px;float: <?php echo (is_rtl()) ? 'right' : 'left'; ?>;display: none;" width="20" height="20" />
                            <button class="arm_save_btn arm_edit_preset_fields_button" type="button"><?php _e('Update', 'ARMember') ?></button>
                            <button class="arm_cancel_btn arm_edit_preset_fields_close_btn" type="button"><?php _e('Cancel', 'ARMember'); ?></button>
                        </div>
                    </div>
                    <div class="armclear"></div>
                </div>
            </form>
    </div>
    <div id='arm_clear_form_fields_popup_div' class="popup_wrapper">
        <form method="post" action="#" id="arm_clear_form_fields_frm" class="arm_admin_form">
            <table  cellspacing="0">
                <tr>
                    <td class="arm_clear_field_close_btn arm_popup_close_btn"></td>
                    <td class="popup_header"><?php _e('Clear Form Fields', 'ARMember'); ?></td>
                    <td class="popup_content_text arm_clear_field_wrapper">
                        <?php
                        global $arm_member_forms;
                        $dbProfileFields = $arm_member_forms->arm_get_db_form_fields();
                        
               
                        
                        if (!empty($dbProfileFields['default'])) {

                            foreach ($dbProfileFields['default'] as $fieldMetaKey => $fieldOpt) {
                                if (empty($fieldMetaKey) || $fieldMetaKey == 'user_pass' || in_array($fieldOpt['type'], array('hidden', 'html', 'section', 'rememberme'))) {
                                    continue;
                                }
                                ?>
                                <label class = "account_detail_radio arm_account_detail_options">
                                    <input type = "checkbox" value = "<?php echo $fieldMetaKey; ?>" class = "arm_icheckbox arm_account_detail_fields" name = "clear_fields[<?php echo $fieldMetaKey; ?>]" id = "arm_profile_field_input_<?php echo $fieldMetaKey; ?>"  checked="checked" disabled="disabled" />
                                    <label for="arm_profile_field_input_<?php echo $fieldMetaKey; ?>"><?php echo $fieldOpt['label']; ?></label>
                                    <div class="arm_list_sortable_icon"></div>
                                </label>
                                <?php
                            }
                        }


                        if (!empty($dbProfileFields['other'])) {

                            foreach ($dbProfileFields['other'] as $fieldMetaKey => $fieldOpt) {
                                if (empty($fieldMetaKey) || $fieldMetaKey == 'user_pass' || in_array($fieldOpt['type'], array('hidden', 'html', 'section', 'rememberme'))) {
                                    continue;
                                }
                                $fchecked = '';
                                if ($wpdb->get_var("SELECT count(`arm_form_field_slug`) FROM `" . $ARMember->tbl_arm_form_field . "` WHERE `arm_form_field_slug`='" . $fieldMetaKey . "'") > 0) {
                                    $fchecked = ' checked="checked" disabled="disabled" ';
                                }
                                ?>
                                <label class = "account_detail_radio arm_account_detail_options">
                                    <input type = "checkbox" value = "<?php echo $fieldMetaKey; ?>" class = "arm_icheckbox arm_account_detail_fields" name = "clear_fields[<?php echo $fieldMetaKey; ?>]" id = "arm_profile_field_input_<?php echo $fieldMetaKey; ?>" <?php echo $fchecked;
                                ?>/>
                                    <label for="arm_profile_field_input_<?php echo $fieldMetaKey; ?>"><?php echo $fieldOpt['label']; ?></label>
                                    <?php
                                    if ($fchecked == '' && $wpdb->get_var("SELECT count(`meta_key`) FROM `" . $wpdb->prefix . "usermeta` WHERE `meta_key`='" . $fieldMetaKey . "'") > 0) {
                                        ?><span style="color:red;"><?php _e('(Entry Exists)', 'ARMember'); ?></span>
                                    <?php }
                                    ?>
                                    <div class="arm_list_sortable_icon"></div>
                                </label>
                                <?php
                            }
                        }
                        ?>
                    </td>
                    <td class="popup_content_btn popup_footer">
                        <div class="popup_content_btn_wrapper">
                            <img src="<?php echo MEMBERSHIPLITE_IMAGES_URL . '/arm_loader.gif' ?>" id="arm_loader_img_clear_field" class="arm_loader_img" style="position: relative;top: 15px;float: <?php echo (is_rtl()) ? 'right' : 'left'; ?>;display: none;" width="20" height="20" />
                            <button class="arm_save_btn arm_clear_form_fields_button" type="submit" data-type="add"><?php _e('Ok', 'ARMember') ?></button>
                            <button class="arm_cancel_btn arm_clear_field_close_btn" type="button"><?php _e('Cancel', 'ARMember'); ?></button>
                        </div>
                    </td>
                </tr>
            </table>
        </form>
    </div>
</div>

<script type="text/javascript" charset="utf-8">
// <![CDATA[
var ARM_IMAGE_URL = "<?php echo MEMBERSHIPLITE_IMAGES_URL; ?>";
var ARM_UPDATE_LABEL = "<?php _e('Update', 'ARMember'); ?>";
</script>