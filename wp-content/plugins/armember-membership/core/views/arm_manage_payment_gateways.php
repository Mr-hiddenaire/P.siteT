<?php
global $wpdb, $ARMember, $arm_slugs, $arm_members_class, $arm_member_forms, $arm_global_settings, $arm_email_settings,  $arm_payment_gateways;
$arm_all_global_settings = $arm_global_settings->arm_get_all_global_settings();
$arm_general_settings = $arm_all_global_settings['general_settings'];
$global_currency = $arm_payment_gateways->arm_get_global_currency();
$all_currency = $arm_payment_gateways->arm_get_all_currencies();
$global_currency_symbol = $all_currency[strtoupper($global_currency)];
$payment_gateways = $arm_payment_gateways->arm_get_all_payment_gateways_for_setup();
$arm_paypal_currency = $arm_payment_gateways->currency['paypal'];

?>
<div class="arm_global_settings_main_wrapper">
	<div class="page_sub_content" id="content_wrapper">
		<form method="post" action="#" id="arm_payment_geteway_form" class="arm_payment_geteway_form arm_admin_form">
		<?php $i=0;foreach ($payment_gateways as $gateway_name => $gateway_options): ?>
			<?php 
			$gateway_options['status'] = isset($gateway_options['status']) ? $gateway_options['status'] : 0;
			$arm_status_switchChecked = ($gateway_options['status'] == '1') ? 'checked="checked"' : '';
			$disabled_field_attr = ($gateway_options['status']=='1') ? '' : 'disabled="disabled"';
			$readonly_field_attr = ($gateway_options['status']=='1') ? '' : 'readonly="readonly"';
			?>
			<?php if ($i != 0): ?><div class="arm_solid_divider"></div><?php endif;?>
			<?php $i++;?>
			<div class="page_sub_title">
				<?php echo $gateway_options['gateway_name'];?> 
				<?php 
				$titleTooltip = '';
				$apiCallbackUrlInfo = '';
				switch ($gateway_name) {
					case 'paypal':
						$titleTooltip = __('Click below links for more details about how to get API Credentials:', 'ARMember').'<br><a href="https://developer.paypal.com/docs/classic/lifecycle/ug_sandbox/" target="_blank">'.__('Sandbox API Detail', 'ARMember').'</a>, <a href="https://developer.paypal.com/docs/classic/api/apiCredentials/" target="_blank">'.__('Live API Detail', 'ARMember').'</a>';
                                               
                                                break;
					
					default:
						break;
				}
				$titleTooltip = apply_filters('arm_change_payment_gateway_tooltip', $titleTooltip, $gateway_name, $gateway_options);
				$apiCallbackUrlInfo = apply_filters('arm_gateway_callback_info', $apiCallbackUrlInfo, $gateway_name, $gateway_options);
				if (!empty($titleTooltip)) {
					?><i class="arm_helptip_icon armfa armfa-question-circle" title="<?php echo htmlentities($titleTooltip);?>"></i><?php
				}
				?>
			</div>			
			<div class="armclear"></div>
			<table class="form-table arm_active_payment_gateways">
				<tr class="form-field">
					<th class="arm-form-table-label"><label><?php _e('Active', 'ARMember');?></label></th>
					<td class="arm-form-table-content">
						<div class="armswitch arm_payment_setting_switch">
							<input type="checkbox" id="arm_<?php echo strtolower($gateway_name);?>_status" <?php echo $arm_status_switchChecked;?> value="1" class="armswitch_input armswitch_payment_input" name="payment_gateway_settings[<?php echo strtolower($gateway_name);?>][status]" data-payment="<?php echo strtolower($gateway_name);?>"/>
							<label for="arm_<?php echo strtolower($gateway_name);?>_status" class="armswitch_label"></label>
						</div>
					</td>
				</tr>
				<?php
				switch (strtolower($gateway_name))
				{
					case 'paypal':
						$gateway_options['paypal_payment_mode'] = (!empty($gateway_options['paypal_payment_mode'])) ? $gateway_options['paypal_payment_mode'] : 'sandbox';
						$globalSettings = $arm_global_settings->global_settings;
						$ty_pageid = isset($globalSettings['thank_you_page_id']) ? $globalSettings['thank_you_page_id'] : 0;
						$cp_page_id = isset($globalSettings['cancel_payment_page_id']) ? $globalSettings['cancel_payment_page_id'] : 0;
						$default_return_url = $arm_global_settings->arm_get_permalink('', $ty_pageid);
						$default_cancel_url = $arm_global_settings->arm_get_permalink('', $cp_page_id);
						$return_url = (!empty($gateway_options['return_url'])) ? $gateway_options['return_url'] : $default_return_url;
						$cancel_url = (!empty($gateway_options['cancel_url'])) ? $gateway_options['cancel_url'] : $default_cancel_url;
						?>
						<tr class="form-field">
							<th class="arm-form-table-label"><label><?php _e('Merchant Email', 'ARMember');?> *</label></th>
							<td class="arm-form-table-content">
								<input class="arm_active_payment_<?php echo strtolower($gateway_name);?>" id="arm_payment_gateway_merch_email" type="text" name="payment_gateway_settings[paypal][paypal_merchant_email]" value="<?php echo (!empty($gateway_options['paypal_merchant_email']) ? $gateway_options['paypal_merchant_email'] : "" );?>" data-msg-required="<?php _e('Merchant Email can not be left blank.', 'ARMember');?>" <?php echo $readonly_field_attr;?>>
							</td>
						</tr>
						<tr class="form-field">
							<th class="arm-form-table-label"><label><?php _e('Payment Mode', 'ARMember');?></label></th>
							<td class="arm-form-table-content">
								<div class="arm_paypal_mode_container" id="arm_paypal_mode_container">
									<input id="arm_payment_gateway_mode_sand" class="arm_general_input arm_paypal_mode_radio arm_iradio arm_active_payment_<?php echo strtolower($gateway_name);?>" type="radio" value="sandbox" name="payment_gateway_settings[paypal][paypal_payment_mode]" <?php checked($gateway_options['paypal_payment_mode'], 'sandbox');?> <?php echo $disabled_field_attr;?>>
									<label for="arm_payment_gateway_mode_sand"><?php _e('Sandbox', 'ARMember');?></label>
									<input id="arm_payment_gateway_mode_pro" class="arm_general_input arm_paypal_mode_radio arm_iradio arm_active_payment_<?php echo strtolower($gateway_name);?>" type="radio" value="live" name="payment_gateway_settings[paypal][paypal_payment_mode]" <?php checked($gateway_options['paypal_payment_mode'], 'live');?> <?php echo $disabled_field_attr;?>>
									<label for="arm_payment_gateway_mode_pro"><?php _e('Live', 'ARMember');?></label>
								</div>
							</td>
						</tr>
						<!--**********./Begin Paypal Sandbox Details/.**********-->
						<tr class="form-field arm_paypal_sandbox_fields <?php echo ($gateway_options['paypal_payment_mode']=='sandbox') ? '' : 'hidden_section';?>">
							<th class="arm-form-table-label"><label><?php _e('Sandbox API Username', 'ARMember');?> *</label></th>
							<td class="arm-form-table-content">
								<input class="arm_active_payment_<?php echo strtolower($gateway_name);?>" type="text" name="payment_gateway_settings[paypal][sandbox_api_username]" value="<?php echo (!empty($gateway_options['sandbox_api_username']) ? $gateway_options['sandbox_api_username'] : "" );?>" data-msg-required="<?php _e('API Username can not be left blank.', 'ARMember');?>" <?php echo $readonly_field_attr;?>>
							</td>
						</tr>
						<tr class="form-field arm_paypal_sandbox_fields <?php echo ($gateway_options['paypal_payment_mode']=='sandbox') ? '' : 'hidden_section';?>">
							<th class="arm-form-table-label"><label><?php _e('Sandbox API Password', 'ARMember');?> *</label></th>
							<td class="arm-form-table-content">
								<input class="arm_active_payment_<?php echo strtolower($gateway_name);?>" type="text" name="payment_gateway_settings[paypal][sandbox_api_password]" value="<?php echo (!empty($gateway_options['sandbox_api_password']) ? $gateway_options['sandbox_api_password'] : "" );?>" data-msg-required="<?php _e('API Password can not be left blank.', 'ARMember');?>" <?php echo $readonly_field_attr;?>>
							</td>
						</tr>
						<tr class="form-field arm_paypal_sandbox_fields <?php echo ($gateway_options['paypal_payment_mode']=='sandbox') ? '' : 'hidden_section';?>">
							<th class="arm-form-table-label"><label><?php _e('Sandbox API Signature', 'ARMember');?> *</label></th>
							<td class="arm-form-table-content">
								<input class="arm_active_payment_<?php echo strtolower($gateway_name);?>" type="text" name="payment_gateway_settings[paypal][sandbox_api_signature]" value="<?php echo (!empty($gateway_options['sandbox_api_signature']) ? $gateway_options['sandbox_api_signature'] : "" );?>" data-msg-required="<?php _e('API Signature can not be left blank.', 'ARMember');?>" <?php echo $readonly_field_attr;?>>
							</td>
						</tr>
						<!--**********./End Paypal Sandbox Details/.**********-->
						<!--**********./Begin Paypal Live Details/.**********-->
						<tr class="form-field arm_paypal_live_fields <?php echo ($gateway_options['paypal_payment_mode']=='live') ? '' : 'hidden_section';?>">
							<th class="arm-form-table-label"><label><?php _e('Live API Username', 'ARMember');?> *</label></th>
							<td class="arm-form-table-content">
								<input class="arm_active_payment_<?php echo strtolower($gateway_name);?>" type="text" name="payment_gateway_settings[paypal][live_api_username]" value="<?php echo (!empty($gateway_options['live_api_username']) ? $gateway_options['live_api_username'] : "" );?>" data-msg-required="<?php _e('API Username can not be left blank.', 'ARMember');?>" <?php echo $readonly_field_attr;?>>
							</td>
						</tr>
						<tr class="form-field arm_paypal_live_fields <?php echo ($gateway_options['paypal_payment_mode']=='live') ? '' : 'hidden_section';?>">
							<th class="arm-form-table-label"><label><?php _e('Live API Password', 'ARMember');?> *</label></th>
							<td class="arm-form-table-content">
								<input class="arm_active_payment_<?php echo strtolower($gateway_name);?>" type="text" name="payment_gateway_settings[paypal][live_api_password]" value="<?php echo (!empty($gateway_options['live_api_password']) ? $gateway_options['live_api_password'] : "" );?>" data-msg-required="<?php _e('API Password can not be left blank.', 'ARMember');?>" <?php echo $readonly_field_attr;?>>
							</td>
						</tr>
						<tr class="form-field arm_paypal_live_fields <?php echo ($gateway_options['paypal_payment_mode']=='live') ? '' : 'hidden_section';?>">
							<th class="arm-form-table-label"><label><?php _e('Live API Signature', 'ARMember');?> *</label></th>
							<td class="arm-form-table-content">
								<input class="arm_active_payment_<?php echo strtolower($gateway_name);?>" type="text" name="payment_gateway_settings[paypal][live_api_signature]" value="<?php echo (!empty($gateway_options['live_api_signature']) ? $gateway_options['live_api_signature'] : "" );?>" data-msg-required="<?php _e('API Signature can not be left blank.', 'ARMember');?>" <?php echo $readonly_field_attr;?>>
							</td>
						</tr>
						<!--**********./End Paypal Live Details/.**********-->
						<tr class="form-field">
							<th class="arm-form-table-label"><label><?php _e('Unsuccessful / Cancel Url', 'ARMember');?></label></th>
							<td class="arm-form-table-content">
								<input class="arm_active_payment_<?php echo strtolower($gateway_name);?>" type="text" name="payment_gateway_settings[paypal][cancel_url]" value="<?php echo $cancel_url;?>" <?php echo $readonly_field_attr;?>>
							</td>
						</tr>
                                                <tr class="form-field">
							<th class="arm-form-table-label"><label><?php _e('Language', 'ARMember');?></label></th>
							<td class="arm-form-table-content">
                                                            <?php $arm_paypal_language = $arm_payment_gateways->arm_paypal_language(); ?>
                                                            <input type='hidden' id='arm_paypal_language' name="payment_gateway_settings[paypal][language]" value="<?php echo (!empty($gateway_options['language'])) ? $gateway_options['language'] : 'en_US'; ?>" />
                                                            <dl class="arm_selectbox arm_active_payment_<?php echo strtolower($gateway_name);?>" <?php echo $disabled_field_attr; ?>>
                                                                    <dt <?php echo ($gateway_options['status']=='1') ? '' : 'style="border:1px solid #DBE1E8"'; ?>><span></span><input type="text" style="display:none;" value="<?php _e('English/United States ( en_US )', 'ARMember'); ?>" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                                    <dd>
                                                                            <ul data-id="arm_paypal_language">
                                                                                    <?php foreach ($arm_paypal_language as $key => $value): ?>
                                                                                    <li data-label="<?php echo $value . " ( $key ) ";?>" data-value="<?php echo esc_attr($key);?>"><?php echo $value . " ( $key ) ";?></li>
                                                                                    <?php endforeach;?>
                                                                            </ul>
                                                                    </dd>
                                                            </dl>
							</td>
						</tr>
						<?php 
						break;
					
					case 'bank_transfer':
						?>
						<tr class="form-field">
							<th class="arm-form-table-label"><label for="arm_bank_transfer_note"><?php _e('Note/Description', 'ARMember');?></label></th>
							<td class="arm-form-table-content">
								<?php 
								wp_editor(
									stripslashes((isset($gateway_options['note'])) ? $gateway_options['note'] : ''),
									'arm_bank_transfer_note',
									array('textarea_name' => 'payment_gateway_settings[bank_transfer][note]', 'textarea_rows' => 6)
								);
								?>
							</td>
						</tr>
						<tr class="form-field">
							<th class="arm-form-table-label"><label><?php _e('Fields to be included in payment form', 'ARMember');?></label></th>
							<td class="arm-form-table-content armBankTransferFields">
								<label>
                                        <?php $gateway_options['fields']['transaction_id'] = isset($gateway_options['fields']['transaction_id']) ? $gateway_options['fields']['transaction_id'] : ''; ?>
                                        <input class="arm_general_input arm_icheckbox arm_active_payment_<?php echo strtolower($gateway_name); ?>" type="checkbox" id="bank_transfer_transaction_id" name="payment_gateway_settings[bank_transfer][fields][transaction_id]" value="1" <?php checked($gateway_options['fields']['transaction_id'],1); ?> <?php echo $disabled_field_attr; ?> >
									<span><?php _e('Transaction ID', 'ARMember');?></span>
								</label>
                                <label>
									<?php $gateway_options['fields']['bank_name'] = (isset($gateway_options['fields']['bank_name'])) ? $gateway_options['fields']['bank_name'] : ''; ?>
									<input class="arm_general_input arm_icheckbox arm_active_payment_<?php echo strtolower($gateway_name);?>" type="checkbox" id="bank_transfer_bank_name" name="payment_gateway_settings[bank_transfer][fields][bank_name]" value="1" <?php checked($gateway_options['fields']['bank_name'], 1);?> <?php echo $disabled_field_attr;?>>
									<span><?php _e('Bank Name', 'ARMember');?></span>
								</label>
                                <label>
									<?php $gateway_options['fields']['account_name'] = (isset($gateway_options['fields']['account_name'])) ? $gateway_options['fields']['account_name'] : ''; ?>
									<input class="arm_general_input arm_icheckbox arm_active_payment_<?php echo strtolower($gateway_name);?>" type="checkbox" id="bank_transfer_account_name" name="payment_gateway_settings[bank_transfer][fields][account_name]" value="1" <?php checked($gateway_options['fields']['account_name'], 1);?> <?php echo $disabled_field_attr;?>>
									<span><?php _e('Account Holder Name', 'ARMember');?></span>
								</label>
                                <label>
									<?php $gateway_options['fields']['additional_info'] = (isset($gateway_options['fields']['additional_info'])) ? $gateway_options['fields']['additional_info'] : ''; ?>
									<input class="arm_general_input arm_icheckbox arm_active_payment_<?php echo strtolower($gateway_name);?>" type="checkbox" id="bank_transfer_additional_info" name="payment_gateway_settings[bank_transfer][fields][additional_info]" value="1" <?php checked($gateway_options['fields']['additional_info'], 1);?> <?php echo $disabled_field_attr;?>>
									<span><?php _e('Additional Info/Note', 'ARMember');?></span>
								</label>
							</td>
						</tr>
                        <tr class="form-field">
                            <th class="arm-form-table-label"><label><?php _e('Transaction ID Label', 'ARMember');?></label></th>
                            <td class="arm-form-table-content"><input class="arm_active_payment_<?php echo strtolower($gateway_name);?>" id="arm_bank_transfer_transaction_id_label" type="text" name="payment_gateway_settings[bank_transfer][transaction_id_label]" value="<?php echo (!empty($gateway_options['transaction_id_label']) ? esc_html(stripslashes($gateway_options['transaction_id_label'])) : __('Transaction ID', 'ARMember'));?>" data-msg-required="<?php _e('Transaction ID Label can not be left blank.', 'ARMember');?>" <?php echo $readonly_field_attr;?>></td>
                        </tr>
                        <tr class="form-field">
                            <th class="arm-form-table-label"><label><?php _e('Bank Name Label', 'ARMember');?></label></th>
                            <td class="arm-form-table-content"><input class="arm_active_payment_<?php echo strtolower($gateway_name);?>" id="arm_bank_transfer_bank_name_label" type="text" name="payment_gateway_settings[bank_transfer][bank_name_label]" value="<?php echo (!empty($gateway_options['bank_name_label']) ? esc_html(stripslashes($gateway_options['bank_name_label'])) : __('Bank Name', 'ARMember'));?>" data-msg-required="<?php _e('Bank Name Label can not be left blank.', 'ARMember');?>" <?php echo $readonly_field_attr;?>></td>
                        </tr>
                        <tr class="form-field">
                            <th class="arm-form-table-label"><label><?php _e('Account Holder Name Label', 'ARMember');?></label></th>
                            <td class="arm-form-table-content"><input class="arm_active_payment_<?php echo strtolower($gateway_name);?>" id="arm_bank_transfer_account_name_label" type="text" name="payment_gateway_settings[bank_transfer][account_name_label]" value="<?php echo (!empty($gateway_options['account_name_label']) ? esc_html(stripslashes($gateway_options['account_name_label'])) : __('Account Holder Name', 'ARMember'));?>" data-msg-required="<?php _e('Account Holder Name Label can not be left blank.', 'ARMember');?>" <?php echo $readonly_field_attr;?>></td>
                        </tr>
                        <tr class="form-field">
                            <th class="arm-form-table-label"><label><?php _e('Additional Info/Note Label', 'ARMember');?></label></th>
                            <td class="arm-form-table-content"><input class="arm_active_payment_<?php echo strtolower($gateway_name);?>" id="arm_bank_transfer_additional_info_label" type="text" name="payment_gateway_settings[bank_transfer][additional_info_label]" value="<?php echo (!empty($gateway_options['additional_info_label']) ? esc_html(stripslashes($gateway_options['additional_info_label'])) : __('Additional Info/Note', 'ARMember'));?>" data-msg-required="<?php _e('Additional Info/Note Label can not be left blank.', 'ARMember');?>" <?php echo $readonly_field_attr;?>></td>
                        </tr>
						<?php
						break;
					default:
						break;
				}
				do_action('arm_after_payment_gateway_listing_section', $gateway_name, $gateway_options);
				$pgHasCCFields = apply_filters('arm_payment_gateway_has_ccfields', false, $gateway_name, $gateway_options);
				if ($pgHasCCFields)
				{
					?>
					<tr class="form-field">
						<th class="arm-form-table-label"><label><?php _e('Credit Card Label', 'ARMember');?></label></th>
						<td class="arm-form-table-content">
							<input class="arm_active_payment_<?php echo strtolower($gateway_name);?>" id="arm_payment_gateway_<?php echo $gateway_name;?>_cc_label" type="text" name="payment_gateway_settings[<?php echo $gateway_name;?>][cc_label]" value="<?php echo (!empty($gateway_options['cc_label']) ? esc_html(stripslashes($gateway_options['cc_label'])) : __('Credit Card Number', 'ARMember'));?>" <?php echo $readonly_field_attr;?>>
							<i class="arm_helptip_icon armfa armfa-question-circle" title="<?php _e("This label will be displayed at fronted membership setup wizard page while payment.", 'ARMember');?>"></i>
						</td>
					</tr>
					<tr class="form-field">
						<th class="arm-form-table-label"><label><?php _e('Credit Card Description', 'ARMember');?></label></th>
						<td class="arm-form-table-content">
							<input class="arm_active_payment_<?php echo strtolower($gateway_name);?>" id="arm_payment_gateway_<?php echo $gateway_name;?>_cc_desc" type="text" name="payment_gateway_settings[<?php echo $gateway_name;?>][cc_desc]" value="<?php echo (!empty($gateway_options['cc_desc']) ? esc_html(stripslashes($gateway_options['cc_desc'])) : "" );?>" <?php echo $readonly_field_attr;?>>
						</td>
					</tr>
					<tr class="form-field">
						<th class="arm-form-table-label"><label><?php _e('Expire Month Label', 'ARMember');?></label></th>
						<td class="arm-form-table-content">
							<input class="arm_active_payment_<?php echo strtolower($gateway_name);?>" id="arm_payment_gateway_<?php echo $gateway_name;?>_em_label" type="text" name="payment_gateway_settings[<?php echo $gateway_name;?>][em_label]" value="<?php echo (!empty($gateway_options['em_label']) ? esc_html(stripslashes($gateway_options['em_label'])) : __('Expiration Month', 'ARMember'));?>" <?php echo $readonly_field_attr;?>>
							<i class="arm_helptip_icon armfa armfa-question-circle" title="<?php _e("This label will be displayed at fronted membership setup wizard page while payment.", 'ARMember');?>"></i>
						</td>
					</tr>
					<tr class="form-field">
						<th class="arm-form-table-label"><label><?php _e('Expire Month Description', 'ARMember');?></label></th>
						<td class="arm-form-table-content">
							<input class="arm_active_payment_<?php echo strtolower($gateway_name);?>" id="arm_payment_gateway_<?php echo $gateway_name;?>_em_desc" type="text" name="payment_gateway_settings[<?php echo $gateway_name;?>][em_desc]" value="<?php echo (!empty($gateway_options['em_desc']) ? esc_html(stripslashes($gateway_options['em_desc'])) : "" );?>" <?php echo $readonly_field_attr;?>>
						</td>
					</tr>
					<tr class="form-field">
						<th class="arm-form-table-label"><label><?php _e('Expire Year Label', 'ARMember');?></label></th>
						<td class="arm-form-table-content">
							<input class="arm_active_payment_<?php echo strtolower($gateway_name);?>" id="arm_payment_gateway_<?php echo $gateway_name;?>_ey_label" type="text" name="payment_gateway_settings[<?php echo $gateway_name;?>][ey_label]" value="<?php echo (!empty($gateway_options['ey_label']) ? esc_html(stripslashes($gateway_options['ey_label'])) : __('Expiration Year', 'ARMember'));?>" <?php echo $readonly_field_attr;?>>
							<i class="arm_helptip_icon armfa armfa-question-circle" title="<?php _e("This label will be displayed at fronted membership setup wizard page while payment.", 'ARMember');?>"></i>
						</td>
					</tr>
					<tr class="form-field">
						<th class="arm-form-table-label"><label><?php _e('Expire Year Description', 'ARMember');?></label></th>
						<td class="arm-form-table-content">
							<input class="arm_active_payment_<?php echo strtolower($gateway_name);?>" id="arm_payment_gateway_<?php echo $gateway_name;?>_ey_desc" type="text" name="payment_gateway_settings[<?php echo $gateway_name;?>][ey_desc]" value="<?php echo (!empty($gateway_options['ey_desc']) ? esc_html(stripslashes($gateway_options['ey_desc'])) : "" );?>" <?php echo $readonly_field_attr;?>>
						</td>
					</tr>
					<tr class="form-field">
						<th class="arm-form-table-label"><label><?php _e('CVV Label', 'ARMember');?></label></th>
						<td class="arm-form-table-content">
							<input class="arm_active_payment_<?php echo strtolower($gateway_name);?>" id="arm_payment_gateway_<?php echo $gateway_name;?>_cvv_label" type="text" name="payment_gateway_settings[<?php echo $gateway_name;?>][cvv_label]" value="<?php echo (!empty($gateway_options['cvv_label']) ? esc_html(stripslashes($gateway_options['cvv_label'])) : __('CVV Code', 'ARMember'));?>" <?php echo $readonly_field_attr;?>>
							<i class="arm_helptip_icon armfa armfa-question-circle" title="<?php _e("This label will be displayed at fronted membership setup wizard page while payment.", 'ARMember');?>"></i>
						</td>
					</tr>
					<tr class="form-field">
						<th class="arm-form-table-label"><label><?php _e('CVV Description', 'ARMember');?></label></th>
						<td class="arm-form-table-content">
							<input class="arm_active_payment_<?php echo strtolower($gateway_name);?>" id="arm_payment_gateway_<?php echo $gateway_name;?>_cvv_desc" type="text" name="payment_gateway_settings[<?php echo $gateway_name;?>][cvv_desc]" value="<?php echo (!empty($gateway_options['cvv_desc']) ? esc_html(stripslashes($gateway_options['cvv_desc'])) : "" );?>" <?php echo $readonly_field_attr;?>>
						</td>
					</tr>
					<?php 
				}
				do_action('arm_payment_gateway_add_ccfields', $gateway_name, $gateway_options, $readonly_field_attr);
                                ?>
				<tr class="form-field">
					<th class="arm-form-table-label"><label><?php _e('Currency', 'ARMember');?></label></th>
					<td class="arm-form-table-content">
						<label class="arm_payment_gateway_currency_label"><?php echo $global_currency;?><?php echo ' ( '.$global_currency_symbol.' ) ';?></label>
						<a class="arm_payment_gateway_currency_link arm_ref_info_links" href="<?php echo admin_url('admin.php?page=' . $arm_slugs->general_settings.'#changeCurrency'); ?>"><?php _e('Change currency', 'ARMember'); ?></a>
					</td>
				</tr>
				<?php if (!empty($apiCallbackUrlInfo)): ?>
				<tr>
					<td colspan="2">
						<span class="arm_info_text"><?php echo $apiCallbackUrlInfo;?></span>
					</td>
				</tr>
				<?php endif;?>
			</table>
		<?php endforeach;?>
			<div class="arm_submit_btn_container">
				<button class="arm_save_btn arm_pay_gate_settings_btn" type="submit" name="arm_pay_gate_settings_btn"><?php _e('Save', 'ARMember') ?></button>&nbsp;<img src="<?php echo MEMBERSHIPLITE_IMAGES_URL.'/arm_loader.gif' ?>" id="arm_loader_img" style="position:relative;top:8px;display:none;" width="24" height="24" />
				<?php wp_nonce_field( 'arm_wp_nonce' );?>
			</div>
		</form>
	</div>
</div>