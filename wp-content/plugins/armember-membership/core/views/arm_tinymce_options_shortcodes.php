<?php 


global $wpdb, $ARMember, $arm_slugs, $arm_shortcodes, $arm_global_settings, $arm_member_forms, $arm_subscription_plans, $arm_membership_setup, $arm_social_feature, $arm_members_directory;
$arm_forms = $arm_member_forms->arm_get_all_member_forms('arm_form_id, arm_form_label, arm_form_type');
$all_plans = $arm_subscription_plans->arm_get_all_subscription_plans('arm_subscription_plan_id, arm_subscription_plan_name');
$arm_all_free_plans = $arm_subscription_plans->arm_get_all_free_plans();
$all_roles = $arm_global_settings->arm_get_all_roles();
$total_setups = $arm_membership_setup->arm_total_setups();
$wrapperClass = 'arm_shortcode_options_popup_wrapper popup_wrapper arm_normal_wrapper ';
if (is_rtl()) {
	$wrapperClass .= ' arm_rtl_wrapper ';
}
?>
<!--********************/. Form Shortcodes ./********************-->
<div id="arm_form_shortcode_options_popup_wrapper" class="<?php echo $wrapperClass;?>" style="width:960px;">
	<input type="hidden" id="arm_ajaxurl" value="<?php echo admin_url('admin-ajax.php'); ?>" />
	<div class="popup_wrapper_inner">
		<div class="popup_header">
			<span class="popup_close_btn arm_popup_close_btn"></span>
			<span class="popup_header_text"><?php _e('Membership Shortcodes', 'ARMember');?></span>
		</div>
		<div class="popup_content_text arm_shortcode_options_container">
			<div class="arm_tabgroups">
				<div class="arm_tabgroup_belt">
					<ul class="arm_tabgroup_link_container">
						<li class="arm_tabgroup_link arm_active">
							<a href="#arm-forms" data-id="arm-forms"><?php _e('Forms', 'ARMember');?></a>
						</li>
						<?php if($total_setups > 0): ?>
						<li class="arm_tabgroup_link arm_tabgroup_link_setup">
							<a href="#arm-membership-setup" data-id="arm-membership-setup"><?php _e('Membership Setup Wizard', 'ARMember');?></a>
						</li>
						<?php endif;?>
						<li class="arm_tabgroup_link">
							<a href="#arm-action-buttons" data-id="arm-action-buttons"><?php _e('Action Buttons', 'ARMember');?></a>
						</li>
						<li class="arm_tabgroup_link">
							<a href="#arm-other" data-id="arm-other"><?php _e('Others', 'ARMember');?></a>
						</li>
						
						
                                                <?php do_action('arm_shortcode_add_tab'); ?>
					</ul>
					<div class="armclear"></div>
				</div>
				<div class="arm_tabgroup_content_wrapper">
					<div id="arm-forms" class="arm_tabgroup_content arm_show">
						<div class="arm_group_body">
							<table class="arm_shortcode_option_table">
								<tr>
									<th><?php _e('Select Form Type', 'ARMember');?></th>
									<td>
										<input type="hidden" id="arm_shortcode_form_type" name="" value="" />
										<dl class="arm_selectbox column_level_dd">
											<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
											<dd>
												<ul data-id="arm_shortcode_form_type">
													<li data-label="<?php _e('Select Form Type','ARMember');?>" data-value=""><?php _e('Select Form Type', 'ARMember');?></li>
													<li data-label="<?php _e('Registration','ARMember');?>" data-value="registration"><?php _e('Registration', 'ARMember');?></li>
													<li data-label="<?php _e('Login','ARMember');?>" data-value="login"><?php _e('Login', 'ARMember');?></li>
													<li data-label="<?php _e('Forgot Password','ARMember');?>" data-value="forgot_password"><?php _e('Forgot Password', 'ARMember');?></li>
													<li data-label="<?php _e('Change Password','ARMember');?>" data-value="change_password"><?php _e('Change Password', 'ARMember');?></li>
													<li data-label="<?php _e('Edit Profile','ARMember');?>" data-value="edit_profile"><?php _e('Edit Profile', 'ARMember');?></li>
													</ul>
											</dd>
										</dl>
									</td>
								</tr>
							</table>
						</div>

						<form class="arm_shortcode_form_opts arm_shortcode_form_select arm_hidden" onsubmit="return false;">
							<div class="arm_group_body">
								<table class="arm_shortcode_option_table">
									<tr class="arm_shortcode_form_select arm_shortcode_form_main_opt">
										<th><?php _e('Select Form', 'ARMember');?></th>
										<td>
											<input type="hidden" id="arm_shortcode_form_id" name="id" value="" />
											<dl class="arm_selectbox column_level_dd">
												<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
												<dd>
													<ul class="arm_shortcode_form_id_wrapper arm_reg_sc_form_lists" data-id="arm_shortcode_form_id">
														<li data-label="<?php _e('Select Form','ARMember');?>" data-value=""><?php _e('Select Form', 'ARMember');?></li>
														<?php if(!empty($arm_forms)): ?>
															<?php foreach($arm_forms as $_form): ?>
                                                                <?php 
                                                                $formTitle = strip_tags(stripslashes($_form['arm_form_label'])) . ' &nbsp;(ID: ' . $_form['arm_form_id'] . ')';
                                                                ?>
																<li class="arm_shortcode_form_id_li <?php echo $_form['arm_form_type'];?>" data-label="<?php echo $formTitle;?>" data-value="<?php echo $_form['arm_form_id'];?>"><?php echo $formTitle;?></li>
															<?php endforeach;?>
														<?php endif;?>
													</ul>
												</dd>
											</dl>
										</td>
									</tr>									
									
									<tr class="arm_shortcode_form_main_opt arm_shortcode_form_position">
										<th><?php _e('Form Position','ARMember'); ?></th>
										<td>
											<label class="form_popup_type_radio">
												<input type="radio" name="form_position" value="left" class="arm_iradio" />
												<?php _e('Left','ARMember'); ?>
											</label>
											<label class="form_popup_type_radio">
												<input type="radio" name="form_position" value="center" class="arm_iradio" checked="checked" />
												<?php _e('Center','ARMember'); ?>
											</label>
											<label class="form_popup_type_radio">
												<input type="radio" name="form_position" value="right" class="arm_iradio" />
												<?php _e('Right','ARMember'); ?>
											</label>
											<div style="margin-left: 10px;">(<?php _e('With Respect to its container','ARMember') ?>)</div>
										</td>
									</tr>
                                                                        
                                                                        <tr id="arm_assign_default_plan_opt_wrapper" class="arm_shortcode_form_main_opt arm_hidden">
										<th><?php _e('Assign Default Plan','ARMember'); ?></th>
										<td>
                                                                                    <input type="hidden" id="arm_assign_default_plan" name="assign_default_plan" value="0" />
											<dl class="arm_selectbox column_level_dd">
												<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
												<dd>
													<ul class="arm_assign_default_plan_wrapper" data-id="arm_assign_default_plan">
														<li data-label="<?php _e('Select Plan','ARMember');?>" data-value="0"><?php _e('Select Plan', 'ARMember');?></li>
														<?php if(!empty($arm_all_free_plans)): ?>
															<?php foreach($arm_all_free_plans as $plan): ?>
																<li class="arm_assign_default_plan_li <?php echo stripslashes($plan['arm_subscription_plan_name']);?>" data-label="<?php echo stripslashes($plan['arm_subscription_plan_name']);?>" data-value="<?php echo $plan['arm_subscription_plan_id'];?>"><?php echo stripslashes($plan['arm_subscription_plan_name']);?></li>
															<?php endforeach;?>
														<?php endif;?>
													</ul>
												</dd>
											</dl>
										</td>
									</tr>
                                                                        
                                                                        <tr id="arm_logged_in_message_opt_wrapper" class="arm_shortcode_form_options arm_shortcode_form_main_opt arm_shortcode_form_popup_options arm_hidden">
										<th><?php _e('Logged In Message','ARMember'); ?></th>
										<td>
											<input type="text" name="logged_in_message" value="<?php _e('You are already logged in.', 'ARMember') ?>" id="logged_in_message_input"><br/>
										</td>
									</tr>
								</table>
								<div class="armclear"></div>
							</div>

						</form>
						<form class="arm_shortcode_form_opts arm_shortcode_edit_profile_opts arm_hidden" onsubmit="return false;">
							<div class="arm_group_body">
								<table class="arm_shortcode_option_table">
                                                                        <tr class="arm_shortcode_form_select">
										<th><?php _e('Select Form', 'ARMember');?></th>
										<td>
											<input type="hidden" id="arm_shortcode_form_name" class="arm_shortcode_edit_profile_form" name="form_id" value="" />
											<dl class="arm_selectbox column_level_dd">
												<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
												<dd>
													<ul class="arm_shortcode_form_id_wrapper" data-id="arm_shortcode_form_name">
														<li data-label="<?php _e('Select Form','ARMember');?>" data-value=""><?php _e('Select Form', 'ARMember');?></li>
														<?php if(!empty($arm_forms)): ?>
															<?php foreach($arm_forms as $_form): ?>          <?php 
															 if($_form['arm_form_type'] == 'registration')
															 {                                                 $formTitle = strip_tags(stripslashes($_form['arm_form_label'])) . ' &nbsp;(ID: ' . $_form['arm_form_id'] . ')';   ?>
															     <li class="arm_shortcode_form_id_li_edit_profile <?php echo $_form['arm_form_type'];?>" data-label="<?php echo $formTitle;?>" data-value="<?php echo $_form['arm_form_id'];?>"><?php echo $formTitle;?></li>
															     <?php 
															     }  endforeach;?>
														<?php endif;?>
													</ul>
												</dd>
											</dl>
										</td>
									</tr>
								
									<tr>
										<th><?php _e('Form Position','ARMember'); ?></th>
										<td>
											<label class="form_popup_type_radio">
												<input type="radio" name="form_position" value="left" class="arm_iradio arm_shortcode_form_popup_opt" />
												<?php _e('Left','ARMember'); ?>
											</label>
											<label class="form_popup_type_radio">
												<input type="radio" name="form_position" value="center" class="arm_iradio arm_shortcode_form_popup_opt" checked="checked" />
												<?php _e('Center','ARMember'); ?>
											</label>
											<label class="form_popup_type_radio">
												<input type="radio" name="form_position" value="right" class="arm_iradio arm_shortcode_form_popup_opt" />
												<?php _e('Right','ARMember'); ?>
											</label>
											<div style="margin-left: 10px;">(<?php _e('With Respect to its container','ARMember') ?>)</div>
										</td>
									</tr>
									<tr>
										<th><?php _e('Title', 'ARMember');?></th>
										<td><input type="text" name="title" value="<?php _e('Edit Profile', 'ARMember');?>"></td>
									</tr>
									<?php if ($arm_social_feature->isSocialFeature): ?>
									<tr>
										<th><?php _e('Display Avatar', 'ARMember');?></th>
										<td>
											<label class="form_popup_type_radio">
												<input type="radio" name="avatar_field" value="yes" class="arm_iradio arm_shortcode_form_popup_opt" checked="checked" />
												<?php _e('Yes','ARMember'); ?>
											</label>
											<label class="form_popup_type_radio">
												<input type="radio" name="avatar_field" value="no" class="arm_iradio arm_shortcode_form_popup_opt" />
												<?php _e('No','ARMember'); ?>
											</label>
										</td>
									</tr>
									<tr>
										<th><?php _e('Display Profile Cover', 'ARMember');?></th>
										<td>
											<label class="edit_form_popup_type_radio">
												<input type="radio" name="profile_cover_field" value="yes" class="arm_iradio arm_shortcode_form_popup_opt" checked="checked" />
												<?php _e('Yes','ARMember'); ?>
											</label>
											<label class="edit_form_popup_type_radio">
												<input type="radio" name="profile_cover_field" value="no" class="arm_iradio arm_shortcode_form_popup_opt" />
												<?php _e('No','ARMember'); ?>
											</label>
										</td>
									</tr>
									<tr class="arm_edit_profile_cover_options">
										<th><?php _e('Profile Cover Title', 'ARMember');?></th>
										<td><input type="text" name="profile_cover_title" value="<?php _e('Profile Cover', 'ARMember');?>"></td>
									</tr>
									<tr class="arm_edit_profile_cover_options">
										<th><?php _e('Profile Cover Placeholder', 'ARMember');?></th>
										<td><input type="text" name="profile_cover_placeholder" value="<?php _e('Drop file here or click to select', 'ARMember');?>"></td>
									</tr>
									<?php endif; ?>
									<tr>
										<th><?php _e('Message', 'ARMember');?></th>
										<td><input type="text" name="message" value="<?php _e('Your profile has been updated successfully.', 'ARMember');?>"></td>
									</tr>
									<tr>
										<th><?php _e('View Profile','ARMember'); ?></th>
										<td>
											<input type="checkbox" id='arm_profile_field_view_profile' value="true" class="arm_icheckbox" name="view_profile" id="" checked="checked" />
										</td>
									</tr>
									<tr>
										<th><?php _e('View Profile Link Label','ARMember'); ?></th>
										<td>
											<input type="text" name="view_profile_link" value="<?php _e('View Profile','ARMember'); ?>" />
										</td>
									</tr>
								</table>
							</div>

						</form>
					</div>
					<div id="arm-membership-setup" class="arm_tabgroup_content">
                                                <form class="arm_shortcode_membership_setup_opts" onsubmit="return false;">
							<div class="arm_group_body">
								<table class="arm_shortcode_option_table">
                                                                    <tr class="arm_shortcode_setup_main_opt">
										<th><?php _e('Select Setup', 'ARMember');?></th>
										<td class="arm_sc_mem_setup_td">
											<?php

												$setups = $wpdb->get_results("SELECT `arm_setup_id`, `arm_setup_name` FROM `".$ARMember->tbl_arm_membership_setup."` ");
											?>
											<input type="hidden" id="arm_shortcode_membership_setup_id" name="id" value="<?php echo (!empty($setups[0]) ? $setups[0]->arm_setup_id : '');?>" />
											<dl class="arm_selectbox column_level_dd">
												<dt class="arm_sc_mem_setup_dt">
													<span></span>
													<input type="text" style="display:none;" value="" class="arm_autocomplete"/>
													<i class="armfa armfa-caret-down armfa-lg"></i>
												</dt>
												<dd>
													<ul data-id="arm_shortcode_membership_setup_id" class="arm_sc_mem_setup_lists">
                                                    <li data-label="<?php _e('Select Setup', 'ARMember');?>" data-value=""><?php _e('Select Setup', 'ARMember');?></li>
														<?php
															if(!empty($setups)) {
                                                                                                           
																foreach($setups as $ms) {
														?>
														<li data-label="<?php echo stripslashes($ms->arm_setup_name);?>" data-value="<?php echo $ms->arm_setup_id;?>"><?php echo stripslashes($ms->arm_setup_name);?></li>
														<?php
																}
															}
														?>
													</ul>
												</dd>
											</dl>
										</td>
									</tr>
									<tr class="arm_shortcode_setup_main_opt">
										<th><?php _e('Hide Setup Title?', 'ARMember');?></th>
										<td>
											<label>
												<input type="radio" name="hide_title" value="true" class="arm_iradio">
												<span><?php _e('Yes', 'ARMember');?></span>
											</label>
											<label>
												<input type="radio" name="hide_title" value="false" class="arm_iradio" checked="checked">
												<span><?php _e('No', 'ARMember');?></span>
											</label>
										</td>
									</tr>
                                                                        
                                                                        <tr class="arm_shortcode_setup_main_opt">
                                                                            <th style="color: red;"><?php _e('Important Notes', 'ARMember');?></th>
                                                                            <td>
                                                                                <div style="padding-top:5px;"><?php _e('Add hide_plans="1" parameter to hide plan selection area.', 'ARMember'); ?></div>
                                                                                <div style="padding-top:5px;"><?php _e('Add subscription_plan="PLAN_ID" parameter to keep plan having PLAN_ID selected.', 'ARMember'); ?></div>
                                                                            </td>
									</tr>
                                                                        
								</table>
							</div>
						</form>
					</div>
					<div id="arm-action-buttons" class="arm_tabgroup_content">
						<div class="arm_group_body">
							<table class="arm_shortcode_option_table">
								<tr>
									<th><?php _e('Select Action Type', 'ARMember');?></th>
									<td>
										<input type="hidden" id="arm_shortcode_action_button_type" value="" />
										<dl class="arm_selectbox column_level_dd">
											<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
											<dd>
												<ul data-id="arm_shortcode_action_button_type">
													<li data-label="<?php _e('Select Action Type','ARMember');?>" data-value=""><?php _e('Select Action Type', 'ARMember');?></li>
													<?php                                                  $social_options = $arm_social_feature->arm_get_active_social_options(); ?>                                                                                                      
													<li data-label="<?php _e('Logout','ARMember');?>" data-value="arm_logout"><?php _e('Logout', 'ARMember');?></li>

												</ul>
											</dd>
										</dl>
									</td>
								</tr>
							</table>
						</div>
                     
						<form class="arm_shortcode_action_button_opts arm_shortcode_action_button_opts_arm_logout arm_hidden" onsubmit="return false;">
							<div class="arm_group_body">
								<table class="arm_shortcode_option_table">
									<tr>
										<th><?php _e('Link Type', 'ARMember');?></th>
										<td>
											<input type="hidden" id="arm_shortcode_logout_link_type" name="type" value="link" />
											<dl class="arm_selectbox column_level_dd">
												<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
												<dd>
													<ul data-id="arm_shortcode_logout_link_type">
														<li data-label="<?php _e('Link','ARMember');?>" data-value="link"><?php _e('Link', 'ARMember');?></li>
														<li data-label="<?php _e('Button','ARMember');?>" data-value="button"><?php _e('Button', 'ARMember');?></li>
													</ul>
												</dd>
											</dl>
										</td>
									</tr>
									<tr>
										<th>
											<span class="arm_shortcode_logout_link_opts"><?php _e('Link Text', 'ARMember'); ?></span>
											<span class="arm_shortcode_logout_button_opts arm_hidden"><?php _e('Button Text', 'ARMember'); ?></span>
										</th>
										<td><input type="text" name="label" value="<?php _e('Logout', 'ARMember');?>"></td>
									</tr>
									<tr>
										<th><?php _e('Display User Info?', 'ARMember');?></th>
										<td>
											<label>
												<input type="radio" name="user_info" value="true" class="arm_iradio" checked="checked">
												<span><?php _e('Yes', 'ARMember');?></span>
											</label>
											<label>
												<input type="radio" name="user_info" value="false" class="arm_iradio">
												<span><?php _e('No', 'ARMember');?></span>
											</label>
										</td>
									</tr>
									<tr>
										<th><?php _e('Redirect After Logout', 'ARMember');?></th>
										<td>
											<input type="text" name="redirect_to" value="<?php echo ARMLITE_HOME_URL;?>">
										</td>
									</tr>
									<tr>
										<th>
											<span class="arm_shortcode_logout_link_opts"><?php _e('Link CSS', 'ARMember'); ?></span>
											<span class="arm_shortcode_logout_button_opts arm_hidden"><?php _e('Button CSS', 'ARMember'); ?></span>
										</th>
										<td>
											<textarea class="arm_popup_textarea" name="link_css" rows="3"></textarea>
                                            <br/>
                                            <em>e.g. color: #000000;</em>
										</td>
									</tr>
									<tr>
										<th>
											<span class="arm_shortcode_logout_link_opts"><?php _e('Link Hover CSS', 'ARMember'); ?></span>
											<span class="arm_shortcode_logout_button_opts arm_hidden"><?php _e('Button Hover CSS', 'ARMember'); ?></span>
										</th>
										<td>
											<textarea class="arm_popup_textarea" name="link_hover_css" rows="3"></textarea>
                                            <br/>
                                            <em>e.g. color: #ffffff;</em>
										</td>
									</tr>
								</table>
							</div>
						</form>

						<form class="arm_shortcode_action_button_opts arm_shortcode_action_button_opts_arm_cancel_membership arm_hidden" onsubmit="return false;">
							<div class="arm_group_body">
								<table class="arm_shortcode_option_table">
									<tr>
										<th><?php _e('Link Type', 'ARMember'); ?></th>
										<td>
											<input type="hidden" id="arm_shortcode_cancel_membership_link_type" name="type" value="link" />
											<dl class="arm_selectbox column_level_dd">
												<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
												<dd>
													<ul data-id="arm_shortcode_cancel_membership_link_type">
														<li data-label="<?php _e('Link','ARMember');?>" data-value="link"><?php _e('Link', 'ARMember');?></li>
														<li data-label="<?php _e('Button','ARMember');?>" data-value="button"><?php _e('Button', 'ARMember');?></li>
													</ul>
												</dd>
											</dl>
										</td>
									</tr>
									<tr>
										<th>
											<span class="arm_shortcode_cancel_membership_link_opts"><?php _e('Link Text', 'ARMember'); ?></span>
											<span class="arm_shortcode_cancel_membership_button_opts arm_hidden"><?php _e('Button Text', 'ARMember'); ?></span>
										</th>
										<td><input type="text" name="label" value="<?php _e('Cancel Subscription', 'ARMember'); ?>"></td>
									</tr>
									<tr>
										<th>
											<span class="arm_shortcode_cancel_membership_link_opts"><?php _e('Link CSS', 'ARMember'); ?></span>
											<span class="arm_shortcode_cancel_membership_button_opts arm_hidden"><?php _e('Button CSS', 'ARMember'); ?></span>
										</th>
										<td>
											<textarea class="arm_popup_textarea" name="link_css" rows="3"></textarea>
                                            <br/>
                                            <em>e.g. color: #000000;</em>
										</td>
									</tr>
									<tr>
										<th>
											<span class="arm_shortcode_cancel_membership_link_opts"><?php _e('Link Hover CSS', 'ARMember'); ?></span>
											<span class="arm_shortcode_cancel_membership_button_opts arm_hidden"><?php _e('Button Hover CSS', 'ARMember'); ?></span>
										</th>
										<td>
											<textarea class="arm_popup_textarea" name="link_hover_css" rows="3"></textarea>
                                                <br/>
                                                <em>e.g. color: #ffffff;</em>
                                        </td>
                                                                                
									</tr>
								</table>
							</div>

						</form>
					</div>
					<div id="arm-other" class="arm_tabgroup_content">
						<div class="arm_group_body">
							<table class="arm_shortcode_option_table">
								<tr>
									<th><?php _e('Select Option', 'ARMember');?></th>
									<td>
										<input type="hidden" id="arm_shortcode_other_type" value="" />
										<dl class="arm_selectbox column_level_dd">
											<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
											<dd>
												<ul data-id="arm_shortcode_other_type">
													<li data-label="<?php _e('Select Option','ARMember');?>" data-value=""><?php _e('Select Option', 'ARMember');?></li>
													<li data-label="<?php _e('My Profile','ARMember');?>" data-value="arm_account_detail"><?php _e('My Profile', 'ARMember');?></li>
													<li data-label="<?php _e('Payment Transactions','ARMember');?>" data-value="arm_member_transaction"><?php _e('Payment Transactions', 'ARMember');?></li>
													<li data-label="<?php _e('Current Membership','ARMember'); ?>" data-value="arm_current_membership"><?php _e('Current Membership','ARMember' ); ?></li>
                                                   
                                                     <li data-label="<?php _e('Close Account','ARMember'); ?>"
                                                                  	data-value="arm_close_account"><?php _e('Close Account', 'ARMember');?></li>
                                                    <li data-label="<?php _e('Current User Information','ARMember');?>" data-value="arm_greeting_message"><?php _e('Current User Information', 'ARMember');?></li>
                                                    <li data-label="<?php _e('Check If User In Trial Period','ARMember');?>" data-value="arm_check_if_user_in_trial"><?php _e('Check If User In Trial Period', 'ARMember');?></li>
                                                   
                                                <li data-label="<?php _e('User Plan Information','ARMember');?>" data-value="arm_user_planinfo"><?php _e('User Plan Information', 'ARMember');?></li>
                                                    <?php do_action('add_others_section_option_tinymce'); ?>
												</ul>
											</dd>
										</dl>
									</td>
								</tr>
							</table>
						</div>

						
						
						<form class="arm_shortcode_other_opts arm_shortcode_other_opts_arm_member_transaction arm_hidden" onsubmit="return false;">
							<div class="arm_group_body">
								<table class="arm_shortcode_option_table">
									<tr>
										<th><?php _e('Transaction History','ARMember'); ?></th>
										<td>
											<ul class="arm_member_transaction_fields">
												<li class="arm_member_transaction_field_list">
													<label class="arm_member_transaction_field_item">
														<input type="checkbox" class="arm_icheckbox arm_member_transaction_fields" name="arm_transaction_fields[]" value="transaction_id" checked="checked" />
													</label>
													<input type="text" class="arm_member_transaction_fields" name="arm_transaction_field_label_transaction_id" value="<?php _e('Transaction ID','ARMember'); ?>" />
												</li>
												<li class="arm_member_transaction_field_list">
													<label class="arm_member_transaction_field_item">
														<input type="checkbox" class="arm_icheckbox arm_member_transaction_fields" name="arm_transaction_fields[]" value="plan" checked="checked" />
													</label>
													<input type="text" class="arm_member_transaction_fields" name="arm_transaction_field_label_plan" value="<?php _e('Plan','ARMember'); ?>" />
												</li>
												<li class="arm_member_transaction_field_list">
													<label class="arm_member_transaction_field_item">
														<input type="checkbox" class="arm_icheckbox arm_member_transaction_fields" name="arm_transaction_fields[]" value="payment_gateway" checked="checked" />
													</label>
													<input type="text" class="arm_member_transaction_fields" name="arm_transaction_field_label_payment_gateway" value="<?php _e('Payment Gateway','ARMember'); ?>" />
												</li>
												<li class="arm_member_transaction_field_list">
													<label class="arm_member_transaction_field_item">
														<input type="checkbox" class="arm_icheckbox arm_member_transaction_fields" name="arm_transaction_fields[]" value="payment_type" checked="checked" />
													</label>
													<input type="text" class="arm_member_transaction_fields" name="arm_transaction_field_label_payment_type" value="<?php _e('Payment Type','ARMember'); ?>" />
												</li>
												<li class="arm_member_transaction_field_list">
													<label class="arm_member_transaction_field_item">
														<input type="checkbox" class="arm_icheckbox arm_member_transaction_fields" name="arm_transaction_fields[]" value="transaction_status" checked="checked" />
													</label>
													<input type="text" class="arm_member_transaction_fields" name="arm_transaction_field_label_transaction_status" value="<?php _e('Transaction Status','ARMember'); ?>" />
												</li>
												<li class="arm_member_transaction_field_list">
													<label class="arm_member_transaction_field_item">
														<input type="checkbox" class="arm_icheckbox arm_member_transaction_fields" name="arm_transaction_fields[]" value="amount" checked="checked" />
													</label>
													<input type="text" class="arm_member_transaction_fields" name="arm_transaction_field_label_amount" value="<?php _e('Amount','ARMember'); ?>" />
												</li>
											
												
												<li class="arm_member_transaction_field_list">
													<label class="arm_member_transaction_field_item">
														<input type="checkbox" class="arm_icheckbox arm_member_transaction_fields" name="arm_transaction_fields[]" value="payment_date" checked="checked" />
													</label>
													<input type="text" class="arm_member_transaction_fields" name="arm_transaction_field_label_payment_date" value="<?php _e('Payment Date','ARMember'); ?>" />
												</li>
											</ul>
										</td>
									</tr>


									
                                  
                                    


									<tr>
										<th><?php _e('Title', 'ARMember');?></th>
										<td>
											<input type="text" class='arm_member_transaction_opts' name="title" value="<?php _e('Transactions', 'ARMember');?>">
										</td>
									</tr>
                                                                        <tr>
										<th><?php _e('Records Per Page', 'ARMember');?></th>
										<td>
											<input type="text" class="arm_member_transaction_opts" name="per_page" value="5">
										</td>
									</tr>
									<tr>
										<th><?php _e('No Records Message', 'ARMember');?></th>
										<td>
											<input type="text" class="arm_member_transaction_opts" name="message_no_record" value="<?php _e('There is no any Transactions found', 'ARMember');?>">
										</td>
									</tr>
								</table>
							</div>

						</form>
						<form class="arm_shortcode_other_opts arm_shortcode_other_opts_arm_account_detail arm_hidden" onsubmit="return false;">
							<div class="arm_group_body">
								<table class="arm_shortcode_option_table">
									
									<tr>
										<th><?php _e("Profile Fields",'ARMember'); ?></th>
										<td class="arm_view_profile_wrapper">
										<?php
                                            $dbProfileFields = $arm_members_directory->arm_template_profile_fields();
					    if (!empty($dbProfileFields)):
						?>
    					   <ul class="arm_member_transaction_fields">
    					   
						<?php
						$i = 1;
						foreach ($dbProfileFields as $fieldMetaKey => $fieldOpt):
						    ?>
                                                <?php
                                                if (empty($fieldMetaKey) || $fieldMetaKey == 'user_pass' || in_array($fieldOpt['type'], array('hidden', 'html', 'section', 'rememberme'))) {
                                                    continue;
                                                }
                                                $fchecked = '';
                                                if (in_array($fieldMetaKey, array('user_email', 'user_login', 'first_name', 'last_name'))) {
                                                    $fchecked = 'checked="checked"';
                                                }
                                                ?>



                                                
												<li class="arm_member_transaction_field_list">
													<label class="arm_member_transaction_field_item">
														<input type="checkbox" class="arm_icheckbox arm_member_account_detail_fields" name="arm_account_detail_fields[]" value="<?php echo $fieldMetaKey;?>" <?php echo $fchecked;?> />
													</label>
													<input type="text" class="arm_member_transaction_fields" name="arm_account_detail_field_label_<?php echo $fieldMetaKey;?>" value="<?php echo $fieldOpt['label'];?>" />
												</li>
                                                 
                                                
						    <?php
						    $i++;
						endforeach;
						?>
                                            </ul>
                                            <?php endif; ?>
										</td>
									</tr>
                                  
								</table>
							</div>

						</form>
                                            <form class="arm_shortcode_other_opts arm_shortcode_other_opts_arm_current_membership arm_hidden" onsubmit="return false;">
							
                                                    
                                                    <div class="arm_group_body">
								<table class="arm_shortcode_option_table">
                                                                    <tr>
										<th><?php _e('Title', 'ARMember');?></th>
										<td>
											<input type="text" class='arm_member_current_membership_opts' name="title" value="<?php _e('Current Membership', 'ARMember');?>">
										</td>
									</tr>
                                                                        
                                                                        
                                                                        
                                                                        <tr>
										<th><?php _e('Select Setup', 'ARMember');?></th>
										<td>
											<?php //$setups = $wpdb->get_results("SELECT `arm_setup_id`, `arm_setup_name` FROM `".$ARMember->tbl_arm_membership_setup."` ");?>
											<input type="hidden" id="arm_shortcode_current_membership_setup_id" name="setup_id" value="" />
											<dl class="arm_selectbox column_level_dd">
												<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
												<dd>
													<ul data-id="arm_shortcode_current_membership_setup_id">
               									    <li data-label="<?php _e('Select Setup', 'ARMember');?>" data-value=""><?php _e('Select Setup', 'ARMember');?></li>
													<?php if(!empty($setups)):?>
														<?php foreach($setups as $ms):?>
														<li data-label="<?php echo stripslashes($ms->arm_setup_name);?>" data-value="<?php echo $ms->arm_setup_id;?>"><?php echo stripslashes($ms->arm_setup_name);?></li>
														<?php endforeach;?>
													<?php endif;?>
													</ul>
												</dd>
											</dl>
										</td>
									</tr>
                                                                        
                                                                        
                                                                        
									<tr>
										<th><?php _e('Current Membership','ARMember'); ?></th>
										<td>
											<ul class="arm_member_current_membership_fields">

											<li class="arm_member_current_membership_field_list">
													<label class="arm_member_current_membership_field_item">
														<input type="checkbox" class="arm_icheckbox arm_member_current_membership_fields" name="arm_current_membership_fields[]" value="current_membership_no" checked="checked" />
													</label>
													<input type="text" class="arm_member_current_membership_fields" name="arm_current_membership_field_label_current_membership_no" value="<?php _e('No.','ARMember'); ?>" />
												</li>
												<li class="arm_member_current_membership_field_list">
													<label class="arm_member_current_membership_field_item">
														<input type="checkbox" class="arm_icheckbox arm_member_current_membership_fields" name="arm_current_membership_fields[]" value="current_membership_is" checked="checked" />
													</label>
													<input type="text" class="arm_member_current_membership_fields" name="arm_current_membership_field_label_current_membership_is" value="<?php _e('Membership Plan','ARMember'); ?>" />
												</li>
												 <li class="arm_member_current_membership_field_list">
													<label class="arm_member_current_membership_field_item">
														<input type="checkbox" class="arm_icheckbox arm_member_current_membership_fields" name="arm_current_membership_fields[]" value="current_membership_recurring_profile" checked="checked" />
													</label>
													<input type="text" class="arm_member_current_membership_fields" name="arm_current_membership_field_label_current_membership_recurring_profile" value="<?php _e('Plan Type','ARMember'); ?>" />
												</li>
                                                 
                                   			       <li class="arm_member_current_membership_field_list">
													<label class="arm_member_current_membership_field_item">
														<input type="checkbox" class="arm_icheckbox arm_member_current_membership_fields" name="arm_current_membership_fields[]" value="current_membership_started_on" checked="checked" />
													</label>
													<input type="text" class="arm_member_current_membership_fields" name="arm_current_membership_field_label_current_membership_started_on" value="<?php _e('Starts On','ARMember'); ?>" />
												</li>
                                                    <li class="arm_member_current_membership_field_list">
													<label class="arm_member_current_membership_field_item">
														<input type="checkbox" class="arm_icheckbox arm_member_current_membership_fields" name="arm_current_membership_fields[]" value="current_membership_expired_on" checked="checked" />
													</label>
													<input type="text" class="arm_member_current_membership_fields" name="arm_current_membership_field_label_current_membership_expired_on" value="<?php _e('Expires On','ARMember'); ?>" />
												</li>
                                                    <li class="arm_member_current_membership_field_list">
													<label class="arm_member_current_membership_field_item">
														<input type="checkbox" class="arm_icheckbox arm_member_current_membership_fields" name="arm_current_membership_fields[]" value="current_membership_next_billing_date" checked="checked" />
													</label>
													<input type="text" class="arm_member_current_membership_fields" name="arm_current_membership_field_label_current_membership_next_billing_date" value="<?php _e('Cycle Date','ARMember'); ?>" />
												</li>
												<li class="arm_member_current_membership_field_list">
													<label class="arm_member_current_membership_field_item">
														<input type="checkbox" class="arm_icheckbox arm_member_current_membership_fields" name="arm_current_membership_fields[]" value="action_button" checked="checked" />
													</label>
													<input type="text" class="arm_member_current_membership_fields" name="arm_current_membership_field_label_action_button" value="<?php _e('Action','ARMember'); ?>" />
												</li>
                                                                                               
												
											</ul>
										</td>
									</tr>
									
                                                                        
                                                                        <tr>
                                        <th><?php _e('Display Renew Subscription Button?','ARMember'); ?></th>
                                        <td>
                                            <label class="renew_subscription_radio">
                                                <input type="radio" name="display_renew_button" value="false" class="arm_iradio arm_shortcode_subscription_opt" checked="checked" />
                                                <?php _e('No', 'ARMember'); ?>
                                            </label>
                                            <label class="renew_subscription_radio">
                                                <input type="radio" name="display_renew_button" value="true" class="arm_iradio arm_shortcode_subscription_opt"  />
                                                <?php _e('Yes','ARMember'); ?>
                                            </label>
                                        </td>
                                    </tr>
                                    <tr class="renew_subscription_btn_options">
                                        <th><?php _e('Renew Text','ARMember'); ?></th>
                                        <td><input type="text" name="renew_text" value="<?php _e('Renew','ARMember'); ?>" /></td>
                                    </tr>
                                    <tr class="renew_subscription_btn_options">
                                        <th><?php _e('Make Payment Text','ARMember'); ?></th>
                                        <td><input type="text" name="make_payment_text" value="<?php _e('Make Payment','ARMember'); ?>" /></td>
                                    </tr>
                                  
                                    <tr class="renew_subscription_btn_options">
                                        <th><?php _e('Button CSS','ARMember'); ?></th>
                                        <td>
                                            <textarea class="arm_popup_textarea" name="renew_css" rows="3"></textarea>
                                            <br/>
                                            <em>e.g. color: #ffffff;</em>
                                        </td>
                                    </tr>
                                    <tr class="renew_subscription_btn_options">
                                        <th><?php _e('Button Hover CSS','ARMember'); ?></th>
                                        <td>
                                            <textarea class="arm_popup_textarea" name="renew_hover_css" rows="3"></textarea>
                                            <br/>
                                            <em>e.g. color: #ffffff;</em>
                                        </td>
                                    </tr>
                                    
                                    
                                    <tr>
                                        <th><?php _e('Display Cancel Subscription Button?','ARMember'); ?></th>
                                        <td>
                                            <label class="cancel_subscription_radio">
                                                <input type="radio" name="display_cancel_button" value="false" class="arm_iradio arm_shortcode_subscription_opt" checked="checked"/>
                                                <?php _e('No', 'ARMember'); ?>
                                            </label>
                                            <label class="cancel_subscription_radio">
                                                <input type="radio" name="display_cancel_button" value="true" class="arm_iradio arm_shortcode_subscription_opt" />
                                                <?php _e('Yes','ARMember'); ?>
                                            </label>
                                        </td>
                                    </tr>
                                    <tr class="cancel_subscription_btn_options">
                                        <th><?php _e('Button Text','ARMember'); ?></th>
                                        <td><input type="text" name="cancel_text" value="<?php _e('Cancel','ARMember'); ?>" /></td>
                                    </tr>
                                   
                                    <tr class="cancel_subscription_btn_options">
                                        <th><?php _e('Button CSS','ARMember'); ?></th>
                                        <td>
                                            <textarea class="arm_popup_textarea" name="cancel_css" rows="3"></textarea>
                                            <br/>
                                            <em>e.g. color: #ffffff;</em>
                                        </td>
                                    </tr>
                                    <tr class="cancel_subscription_btn_options">
                                        <th><?php _e('Button Hover CSS','ARMember'); ?></th>
                                        <td>
                                            <textarea class="arm_popup_textarea" name="cancel_hover_css" rows="3"></textarea>
                                            <br/>
                                            <em>e.g. color: #ffffff;</em>
                                        </td>
                                    </tr>
                                    <tr class="cancel_subscription_btn_options">
                                        <th><?php _e('Subscription Cancelled Message','ARMember'); ?></th>
                                        <td><input type="text" name="cancel_message" value="<?php _e('Your subscription has been cancelled.','ARMember'); ?>" /></td>
                                    </tr>
                                    <tr>
                                        <th><?php _e('Display Update Card Subscription Button?','ARMember'); ?></th>
                                        <td>
                                            <label class="update_card_subscription_radio">
                                                <input type="radio" name="display_update_card_button" value="false" class="arm_iradio arm_shortcode_subscription_opt" checked="checked" />
                                                <?php _e('No', 'ARMember'); ?>
                                            </label>
                                            <label class="update_card_subscription_radio">
                                                <input type="radio" name="display_update_card_button" value="true" class="arm_iradio arm_shortcode_subscription_opt"  />
                                                <?php _e('Yes','ARMember'); ?>
                                            </label>
                                        </td>
                                    </tr>
                                    <tr class="update_card_subscription_btn_options">
                                        <th><?php _e('Update Card Text','ARMember'); ?></th>
                                        <td><input type="text" name="update_card_text" value="<?php _e('Update Card','ARMember'); ?>" /></td>
                                    </tr>
                                    
                                    <tr class="update_card_subscription_btn_options">
                                        <th><?php _e('Button CSS','ARMember'); ?></th>
                                        <td>
                                            <textarea class="arm_popup_textarea" name="update_card_css" rows="3"></textarea>
                                            <br/>
                                            <em>e.g. color: #ffffff;</em>
                                        </td>
                                    </tr>
                                    <tr class="update_card_subscription_btn_options">
                                        <th><?php _e('Button Hover CSS','ARMember'); ?></th>
                                        <td>
                                            <textarea class="arm_popup_textarea" name="update_card_hover_css" rows="3"></textarea>
                                            <br/>
                                            <em>e.g. color: #ffffff;</em>
                                        </td>
                                    </tr>
                                    <tr>
										<th><?php _e('Trial Active Label', 'ARMember');?></th>
										<td>
											<input type="text" class="arm_member_current_membership_opts" name="trial_active" value="<?php _e('trial active', 'ARMember');?>">
										</td>
									</tr>
     
									<tr>
										<th><?php _e('No Records Message', 'ARMember');?></th>
										<td>
											<input type="text" class="arm_member_current_membership_opts" name="message_no_record" value="<?php _e('There is no membership found.', 'ARMember');?>">
										</td>
									</tr>
								</table>
							</div>
                                                    
                                       

						</form>
                                      
                                            
                                            
						
						<form class="arm_shortcode_other_opts arm_shortcode_other_opts_arm_close_account arm_hidden" onsubmit="return false;">
							<div class="arm_group_body">
								<table class="arm_shortcode_option_table">
									<tr>
										<th><?php _e('Select Set of Login Form', 'ARMember'); ?></th>
										<td>
											<input type="hidden" id="arm_shortcode_close_account" name="set_id" value="" />
											<dl class="arm_selectbox column_level_dd">
												<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
												<dd>
                                                    <?php $setnames= $wpdb->get_results("SELECT * FROM `" . $ARMember->tbl_arm_forms . "` WHERE `arm_form_type` = 'login' GROUP BY arm_set_id ORDER BY arm_form_id ASC");?>
                                                    <ul data-id="arm_shortcode_close_account" class="arm_shortcode_form_id_wrapper">
                                                        <li data-label="<?php _e('Select Set','ARMember');?>" data-value=""><?php _e('Select Set', 'ARMember'); ?></li>
                                                        <?php if(!empty($setnames)):?>
                                                            <?php foreach($setnames as $sn): ?>
                                                                <li data-label="<?php echo stripslashes($sn->arm_set_name);?>" data-value="<?php echo $sn->arm_form_id;?>"><?php echo stripslashes($sn->arm_set_name);?></li>
                                                            <?php endforeach;?>
                                                        <?php endif;?>
													</ul>
												</dd>
											</dl>
										</td>
									</tr>
                                                                       
								</table>
							</div>

						</form>
						<form class="arm_shortcode_other_opts arm_shortcode_other_opts_arm_greeting_message arm_hidden" onsubmit="return false;">
							<div class="arm_group_body">
								<table class="arm_shortcode_option_table">
                                                                        <tr>
									<th><?php _e('Display Information Based On', 'ARMember');?></th>
									<td>
										<input type="hidden" id="arm_shortcode_username_type" name="type" value="" class="type" />
										<dl class="arm_selectbox column_level_dd">
											<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
											<dd>
												<ul data-id="arm_shortcode_username_type">
													<li data-label="<?php _e('Select Type','ARMember');?>" data-value=""><?php _e('Select Username Type', 'ARMember');?></li>
													<li data-label="<?php _e('User ID','ARMember');?>" data-value="arm_userid"><?php _e('User ID', 'ARMember');?></li>
													<li data-label="<?php _e('Username','ARMember');?>" data-value="arm_username"><?php _e('Username', 'ARMember');?></li>
													<li data-label="<?php _e('Display Name','ARMember');?>" data-value="arm_displayname"><?php _e('Display Name', 'ARMember');?></li>
													<li data-label="<?php _e('Firstname Lastname','ARMember');?>" data-value="arm_firstname_lastname"><?php _e('Firstname Lastname', 'ARMember');?></li>
											<li data-label="<?php _e('User Plan','ARMember');?>" data-value="arm_user_plan"><?php _e('User Plan', 'ARMember');?></li>
													<li data-label="<?php _e('Avatar','ARMember');?>" data-value="arm_avatar"><?php _e('Avatar', 'ARMember');?></li>											
													<li data-label="<?php _e('Custom Meta','ARMember');?>" data-value="arm_usermeta"><?php _e('Custom Meta', 'ARMember');?></li>
												</ul>
											</dd>
										</dl>
									</td>
								</tr>
                                                                <tr class="arm_shortcode_other_opts_arm_greeting_message_arm_usermeta arm_hidden">
                                                                    <th><?php _e('Enter Usermeta Name', 'ARMember');?></th>
                                                                    <td>
                                                                        <input type="text" name="arm_custom_user_meta" id="arm_custom_user_meta" value="" />
                                                                    </td>
                                                                </tr>
								</table>
							</div>

						</form>
                                                <form class="arm_shortcode_other_opts arm_shortcode_other_opts_arm_check_if_user_in_trial arm_hidden" onsubmit="return false;">
							<div class="arm_group_body">
								<table class="arm_shortcode_option_table">
                                                                        <tr>
									<th><?php _e('Display Content Based On', 'ARMember');?></th>
									<td>
										<input type="hidden" id="arm_shortcode_if_user_in_trial_or_not" name="type" value="" class="type" />
										<dl class="arm_selectbox column_level_dd">
											<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
											<dd>
												<ul data-id="arm_shortcode_if_user_in_trial_or_not">
													<li data-label="<?php _e('Select Type','ARMember');?>" data-value=""><?php _e('Select Type', 'ARMember');?></li>
													<li data-label="<?php _e('If User In Trial','ARMember');?>" data-value="arm_if_user_in_trial"><?php _e('If User <b>In</b> Trial Period', 'ARMember');?></li>
                                                                                                        <li data-label="<?php _e('If User Not In Trial','ARMember');?>" data-value="arm_not_if_user_in_trial"><?php _e('If User <b>Not In</b> Trial Period', 'ARMember');?></li>
													
												</ul>
											</dd>
										</dl>
									</td>
								</tr>
								</table>
							</div>

						</form>
                                                <form class="arm_shortcode_other_opts arm_shortcode_other_opts_user_badge arm_hidden" onsubmit="return false;">
							<div class="arm_group_body">
								<table class="arm_shortcode_option_table">
                                                                        <tr>
									<th><?php _e('User Id', 'ARMember');?></th>
									<td>
										<input type="text" id="user_id" name="user_id" value="" class="type" />
									</td>
								</tr>
								</table>
							</div>

						</form>
                                            <form class="arm_shortcode_other_opts arm_shortcode_other_opts_arm_user_planinfo arm_hidden" onsubmit="return false;">
							<div class="arm_group_body">
								<table class="arm_shortcode_option_table">
                                                                        <tr>
                                                                            <th><?php _e('Select Membership Plan', 'ARMember');?></th>
										<td class="arm_sc_upi_mp_td">
                                                                                <input type='hidden' class="arm_user_plan_change_input" name="plan_id" id="arm_user_plan_0" value=""/>
                                                                                <dl class="arm_selectbox column_level_dd arm_member_form_dropdown">
												<dt class="arm_sc_upi_mp_dt">
													<span></span>
													<input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i>
												</dt>
												<dd>
													<ul data-id="arm_user_plan_0" class="arm_upi_plan_list">
                                                                                    

		<li data-label="<?php _e('Select Plan', 'ARMember'); ?>" data-value=""><?php _e('Select Plan', 'ARMember'); ?></li>
														<?php
															foreach ($all_plans as $p) {

                                                                                     echo '<li data-label="' . stripslashes(esc_attr($p['arm_subscription_plan_name'])) . '" data-value="' . $p['arm_subscription_plan_id']. '">' . stripslashes(esc_attr($p['arm_subscription_plan_name'])) . '</li>';
                                                                                        }
                                                                                        ?>
													</ul>
												</dd>
                                                                                </dl>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th><?php _e('Select Plan Information', 'ARMember');?></th>
                                                                            <td>
                                                                                <input type='hidden' class="arm_user_plan_change_input" name="plan_info" id="arm_user_plan_info" value="start_date"/>
                                                                                <dl class="arm_selectbox column_level_dd arm_member_form_dropdown">
                                                                                    <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                                                    <dd><ul data-id="arm_user_plan_info">
                                                                                            <li data-label="<?php _e('Start Date', 'ARMember'); ?>" data-value="arm_start_plan"><?php _e('Start Date', 'ARMember'); ?></li>
                                                                                            <li data-label="<?php _e('End Date', 'ARMember'); ?>" data-value="arm_expire_plan"><?php _e('End Date', 'ARMember'); ?></li>
                                                                                            <li data-label="<?php _e('Trial Start Date', 'ARMember'); ?>" data-value="arm_trial_start"><?php _e('Trial Start Date', 'ARMember'); ?></li>
                                                                                            <li data-label="<?php _e('Trial End Date', 'ARMember'); ?>" data-value="arm_trial_end"><?php _e('Trial End Date', 'ARMember'); ?></li>
                                                                                            <li data-label="<?php _e('Grace End Date', 'ARMember'); ?>" data-value="arm_grace_period_end"><?php _e('Grace End Date', 'ARMember'); ?></li>
                                                                                            <li data-label="<?php _e('Paid By', 'ARMember'); ?>" data-value="arm_user_gateway"><?php _e('Paid By', 'ARMember'); ?></li>
                                                                                            <li data-label="<?php _e('Completed Recurrence', 'ARMember'); ?>" data-value="arm_completed_recurring"><?php _e('Completed Recurrence', 'ARMember'); ?></li>
                                                                                            <li data-label="<?php _e('Next Due Date', 'ARMember'); ?>" data-value="arm_next_due_payment"><?php _e('Next Due Date', 'ARMember'); ?></li>
                                                                                            <li data-label="<?php _e('Payment Mode', 'ARMember'); ?>" data-value="arm_payment_mode"><?php _e('Payment Mode', 'ARMember'); ?></li>
                                                                                            <li data-label="<?php _e('Payment Cycle', 'ARMember'); ?>" data-value="arm_payment_cycle"><?php _e('Payment Cycle', 'ARMember'); ?></li>
                                                                                        </ul></dd>
                                                                                </dl>
                                                                            </td>
                                                                        </tr>
								</table>
							</div>

						</form>
                                              
                                           
                        <form class="arm_shortcode_other_opts arm_shortcode_other_opts_arm_last_login_history arm_hidden" onsubmit="return false;">
                        	<div class='arm_group_body'>
                        	</div>

						</form>
                                                <?php do_action('add_others_section_select_option_tinymce'); ?>
					</div>
				
					
                                        <?php do_action('arm_shortcode_add_tab_content'); ?>
				</div>
                            
                            <!-- add form shortcode buttons -->
                            <div id="arm-forms_buttons" class="arm_tabgroup_content_buttons arm_show">
                                    <div class="arm_shortcode_form_opts arm_shortcode_form_opts_no_type" style="">
                                            <div class="arm_group_footer">
                                                    <div class="popup_content_btn_wrapper">
                                                            <button type="button" class="arm_insrt_btn" disabled="disabled"><?php _e('Add Shortcode', 'ARMember');?></button>
                                                            <a class="arm_cancel_btn popup_close_btn" href="javascript:void(0)"><?php _e('Cancel', 'ARMember') ?></a>
                                                    </div>
                                            </div>
                                    </div>
                                    

                                    <div class="arm_group_footer arm_shortcode_form_opts arm_shortcode_form_select arm_hidden" style="position:relative;">
                                            <div class="popup_content_btn_wrapper">
                                                <button type="button" class="arm_shortcode_form_insert_btn arm_insrt_btn arm_shortcode_form_add_btn" id="arm_shortcode_form_select" data-code="arm_form"><?php _e('Add Shortcode', 'ARMember'); ?></button>
                                                    <a class="arm_cancel_btn popup_close_btn" href="javascript:void(0)"><?php _e('Cancel', 'ARMember') ?></a>
                                            </div>
                                    </div>
                                    <div class="arm_group_footer arm_shortcode_form_opts arm_shortcode_edit_profile_opts arm_hidden">
                                            <div class="popup_content_btn_wrapper">
                                                    <button type="button" class="arm_shortcode_insert_btn arm_insrt_btn" id="arm_shortcode_edit_profile_opts" data-code="arm_edit_profile"><?php _e('Add Shortcode', 'ARMember');?></button>
                                                    <a class="arm_cancel_btn popup_close_btn" href="javascript:void(0)"><?php _e('Cancel', 'ARMember') ?></a>
                                            </div>
                                    </div>                                
                            </div>
                            <!-- add setup shortcode buttons -->
                            <div id="arm-membership-setup_buttons" class="arm_tabgroup_content_buttons">      
                                    <div class="arm_group_footer" style="">
                                            <div class="popup_content_btn_wrapper">
                                                    <button type="button" class="arm_shortcode_insert_setup_btn arm_insrt_btn" id="arm_shortcode_membership_setup_opts" data-code="arm_setup"><?php _e('Add Shortcode', 'ARMember');?></button>
                                                    <a class="arm_cancel_btn popup_close_btn" href="javascript:void(0)"><?php _e('Cancel', 'ARMember') ?></a>
                                            </div>
                                    </div>                                
                            </div>
                            <!-- add action shortcode buttons -->
                            <div id="arm-action-buttons_buttons" class="arm_tabgroup_content_buttons">
                                    <div class="arm_shortcode_action_button_opts arm_shortcode_action_button_opts_no_type" style="">
                                            <div class="arm_group_footer">
                                                    <div class="popup_content_btn_wrapper">
                                                            <button type="button" class="arm_insrt_btn" disabled="disabled"><?php _e('Add Shortcode', 'ARMember');?></button>
                                                            <a class="arm_cancel_btn popup_close_btn" href="javascript:void(0)"><?php _e('Cancel', 'ARMember');?></a>
                                                    </div>
                                            </div>
                                    </div>                                

                                                  
                                    <div class="arm_group_footer arm_shortcode_action_button_opts arm_shortcode_action_button_opts_arm_logout arm_hidden">
                                            <div class="popup_content_btn_wrapper">
                                                    <button type="button" class="arm_shortcode_insert_btn arm_insrt_btn" id="arm_shortcode_action_button_opts_arm_logout" data-code="arm_logout"><?php _e('Add Shortcode', 'ARMember');?></button>
                                                    <a class="arm_cancel_btn popup_close_btn" href="javascript:void(0)"><?php _e('Cancel', 'ARMember') ?></a>
                                            </div>
                                    </div>
                                    <div class="arm_group_footer arm_shortcode_action_button_opts arm_shortcode_action_button_opts_arm_cancel_membership arm_hidden">
                                            <div class="popup_content_btn_wrapper">
                                                    <button type="button" class="arm_shortcode_insert_btn arm_insrt_btn" id="arm_shortcode_action_button_opts_arm_cancel_membership" data-code="arm_cancel_membership"><?php _e('Add Shortcode', 'ARMember');?></button>
                                                    <a class="arm_cancel_btn popup_close_btn" href="javascript:void(0)"><?php _e('Cancel', 'ARMember') ?></a>
                                            </div>
                                    </div>                                
                            </div>
                            <!-- add other shortcode buttons -->
                            <div id="arm-other_buttons" class="arm_tabgroup_content_buttons">
                                    <div class="arm_shortcode_other_opts arm_shortcode_other_opts_no_type" style="">
                                            <div class="arm_group_footer">
                                                    <div class="popup_content_btn_wrapper">
                                                            <button type="button" class="arm_insrt_btn" disabled="disabled"><?php _e('Add Shortcode', 'ARMember');?></button>
                                                            <a class="arm_cancel_btn popup_close_btn" href="javascript:void(0)"><?php _e('Cancel', 'ARMember') ?></a>
                                                    </div>
                                            </div>
                                    </div>     
                                    <div class="arm_group_footer arm_shortcode_other_opts arm_shortcode_other_opts_arm_member_transaction arm_hidden">
                                            <div class="popup_content_btn_wrapper">
                                                    <button type="button" class="arm_shortcode_insert_btn arm_insrt_btn" id="arm_shortcode_other_opts_arm_member_transaction" data-code="arm_member_transaction"><?php _e('Add Shortcode', 'ARMember');?></button>
                                                    <a class="arm_cancel_btn popup_close_btn" href="javascript:void(0)"><?php _e('Cancel', 'ARMember') ?></a>
                                            </div>
                                    </div>  
                                <div class="arm_group_footer arm_shortcode_other_opts arm_shortcode_other_opts_arm_user_planinfo arm_hidden">
                                            <div class="popup_content_btn_wrapper">
                                                    <button type="button" class="arm_shortcode_insert_btn arm_user_planinfo_shortcode arm_insrt_btn" id="arm_shortcode_other_opts_arm_user_planinfo" data-code="arm_user_planinfo" disabled="disabled"><?php _e('Add Shortcode', 'ARMember');?></button>
                                                    <a class="arm_cancel_btn popup_close_btn" href="javascript:void(0)"><?php _e('Cancel', 'ARMember') ?></a>
                                            </div>
                                    </div> 
                                    <div class="arm_group_footer arm_shortcode_other_opts arm_shortcode_other_opts_arm_account_detail arm_hidden" style="">
                                            <div class="popup_content_btn_wrapper">
                                                    <button type="button" class="arm_shortcode_insert_btn arm_insrt_btn" id="arm_shortcode_other_opts_arm_account_detail" data-code="arm_account_detail"><?php _e('Add Shortcode', 'ARMember');?></button>
                                                    <a class="arm_cancel_btn popup_close_btn" href="javascript:void(0)"><?php _e('Cancel', 'ARMember') ?></a>
                                            </div>
                                    </div>      
                                    <div class="arm_group_footer arm_shortcode_other_opts arm_shortcode_other_opts_arm_current_membership arm_hidden" style="">
                                            <div class="popup_content_btn_wrapper">
                                                    <button type="button" class="arm_shortcode_insert_btn arm_current_membership_shortcode arm_insrt_btn" id="arm_shortcode_other_opts_arm_current_membership" data-code="arm_membership" disabled="disabled"><?php _e('Add Shortcode','ARMember'); ?></button>
                                                    <a class="arm_cancel_btn popup_close_btn" href="javascript:void(0)"><?php _e('Cancel','ARMember'); ?></a>
                                            </div>
                                    </div>                                
                                                              
                                    <div class="arm_group_footer arm_shortcode_other_opts arm_shortcode_other_opts_arm_close_account arm_hidden" style="">
                                            <div class="popup_content_btn_wrapper">
                                                    <button type="button" class="arm_shortcode_insert_btn arm_insrt_btn arm_close_account_btn" id="arm_shortcode_other_opts_arm_close_account" data-code="arm_close_account"><?php _e('Add Shortcode', 'ARMember');?></button>
                                                    <a class="arm_cancel_btn popup_close_btn" href="javascript:void(0)"><?php _e('Cancel', 'ARMember') ?></a>
                                            </div>
                                    </div>          
                                    <div class="arm_group_footer arm_shortcode_other_opts arm_shortcode_other_opts_arm_greeting_message arm_hidden" style="">
                                            <div class="popup_content_btn_wrapper">
                                                    <button type="button" class="arm_shortcode_insert_btn arm_insrt_btn" id="arm_shortcode_other_opts_arm_greeting_message" data-code="arm_greeting_message"><?php _e('Add Shortcode', 'ARMember');?></button>
                                                    <a class="arm_cancel_btn popup_close_btn" href="javascript:void(0)"><?php _e('Cancel', 'ARMember') ?></a>
                                            </div>
                                    </div>   
                                    <div class="arm_group_footer arm_shortcode_other_opts arm_shortcode_other_opts_arm_check_if_user_in_trial arm_hidden" style="">
                                            <div class="popup_content_btn_wrapper">
                                                    <button type="button" class="arm_shortcode_insert_btn arm_insrt_btn" id="arm_shortcode_other_opts_arm_check_if_user_in_trial" data-code="arm_if_user_in_trial_or_not"><?php _e('Add Shortcode', 'ARMember');?></button>
                                                    <a class="arm_cancel_btn popup_close_btn" href="javascript:void(0)"><?php _e('Cancel', 'ARMember') ?></a>
                                            </div>
                                    </div>     
                                     
                                    
                                   
                                    <div class="arm_group_footer arm_shortcode_other_opts arm_shortcode_other_opts_arm_login_history arm_hidden" style="">
                                            <div class="popup_content_btn_wrapper">
                                                    <button type="button" class="arm_shortcode_insert_btn arm_insrt_btn" id="arm_shortcode_other_opts_arm_login_history" data-code="arm_login_history"><?php _e('Add Shortcode', 'ARMember');?></button>
                                                    <a class="arm_cancel_btn popup_close_btn" href="javascript:void(0)"><?php _e('Cancel', 'ARMember') ?></a>
                                            </div>
                                    </div>                                

                                    <div class="arm_group_footer arm_shortcode_other_opts arm_shortcode_other_opts_arm_last_login_history arm_hidden" style="">
                                            <div class="popup_content_btn_wrapper">
                                                    <button type="button" class="arm_shortcode_insert_btn arm_insrt_btn" id="arm_shortcode_other_opts_arm_last_login_history" data-code="arm_last_login_history"><?php _e('Add Shortcode', 'ARMember'); ?></button>
                                                    <a class="arm_cancel_btn popup_close_btn" href="javascript:void(0)"><?php _e('Cancel', 'ARMember') ?></a>
                                            </div>
                                    </div>                                
                            </div>
                          
                           
                            <?php do_action('arm_shortcode_add_tab_buttons'); ?>
			</div>
		</div>
		<div class="armclear"></div>
	</div>
</div>
<!--********************/. Restrict Content Shortcodes ./********************-->
<div id="arm_restriction_shortcode_options_popup_wrapper" class="<?php echo $wrapperClass;?>">
	<div class="popup_wrapper_inner">
		<div class="popup_header">
			<span class="popup_close_btn arm_popup_close_btn"></span>
			<span class="popup_header_text"><?php _e('Content Restriction Shortcode', 'ARMember');?></span>
		</div>
		<div class="popup_content_text arm_shortcode_options_container">
                            <form onsubmit="return false;" class="arm_shortcode_rc_form">
				<div class="arm_group_body" style="padding-top: 25px;">
					<table class="arm_shortcode_option_table">
						<tr>
							<th><?php _e('Restriction Type', 'ARMember'); ?></th>
							<td>
								<input type="hidden" id="arm_restriction_type" name="type" value="hide" />
								<dl class="arm_selectbox column_level_dd">
									<dt style="width: 330px;"><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
									<dd>
										<ul data-id="arm_restriction_type">
											<li data-label="<?php _e('Hide content only for','ARMember');?>" data-value="hide"><?php _e('Hide content only for', 'ARMember');?></li>
											<li data-label="<?php _e('Show content only for','ARMember');?>" data-value="show"><?php _e('Show content only for', 'ARMember');?></li>
										</ul>
									</dd>
								</dl>
							</td>
						</tr>
						<tr>
							<th><?php _e('Target Users', 'ARMember'); ?></th>
							<td>
								<select name="plan" class="arm_chosen_selectbox" multiple data-placeholder="<?php _e('Everyone', 'ARMember');?>" tabindex="-1" style="width: 350px;">
									<option value="registered"><?php _e('Loggedin Users', 'ARMember');?></option>
									<option value="unregistered"><?php _e('Non Loggedin Users', 'ARMember');?></option>
									<?php 
									if(!empty($all_plans))
									{
										foreach($all_plans as $plan) {
											?><option value="<?php echo $plan['arm_subscription_plan_id'];?>"><?php echo stripslashes($plan['arm_subscription_plan_name']);?></option><?php
										}
									}
									?>
									<option value="any_plan"><?php _e('Any Plans', 'ARMember');?></option>
								</select>
							</td>
						</tr>
<!--						<tr>
							<th><?php _e('Enter content here which will be restricted', 'ARMember'); ?></th>
							<td>
								<?php 
								$armshortcodecontent_editor = array(
									'textarea_name' => 'armshortcodecontent',
									'media_buttons' => false,
									'textarea_rows' => 5,
									'default_editor' => 'html',
									'editor_css' => '<style type="text/css"> body#tinymce{margin:0px !important;} </style>',
									'tinymce' => true,
								);
								wp_editor('', 'armshortcodecontent', $armshortcodecontent_editor);
								?>
							</td>
						</tr>
						<tr>
							<th><?php _e('What to display when content is restricted', 'ARMember'); ?></th>
							<td>
								<?php 
								$armelse_message_editor = array(
									'textarea_name' => 'armelse_message',
									'media_buttons' => false,
									'textarea_rows' => 5,
									'default_editor' => 'html',
									'editor_css' => '<style type="text/css"> body#tinymce{margin:0px !important;} </style>',
									'tinymce' => true,
								);
								wp_editor('', 'armelse_message', $armelse_message_editor);
								?>
							</td>
						</tr>-->
					</table>
				</div>
				<div class="arm_group_footer">
					<div class="popup_content_btn_wrapper">
						<button type="button" class="arm_shortcode_insert_rc_btn arm_insrt_btn" data-code="arm_restrict_content"><?php _e('Add Shortcode', 'ARMember');?></button>
						<a class="arm_cancel_btn popup_close_btn" href="javascript:void(0)"><?php _e('Cancel', 'ARMember') ?></a>
					</div>
				</div>
			</form>
			<div class="armclear"></div>
		</div>
		<div class="armclear"></div>
	</div>
</div>
<script type="text/javascript">
jQuery(function($){
	if (typeof armICheckInit == "function") {
		armICheckInit();
	}
	/*For Chosen Select Boxes*/
    if (jQuery.isFunction(jQuery().chosen)) {
		jQuery(".arm_chosen_selectbox").chosen({
			no_results_text: "<?php _e('Oops, nothing found', 'ARMember');?>"
		});
    }
	if (jQuery.isFunction(jQuery().colpick))
    {
		jQuery('.arm_colorpicker').each(function (e) {
			var $arm_colorpicker = jQuery(this);
			var default_color = $arm_colorpicker.val();
			if (default_color == '') {
				default_color = '#000';
			}
			$arm_colorpicker.wrap('<label class="arm_colorpicker_label" style="background-color:' + default_color + '"></label>');
			$arm_colorpicker.colpick({
				layout: 'hex',
				submit: 0,
				colorScheme: 'dark',
				color: default_color,
				onChange: function (hsb, hex, rgb, el, bySetColor) {
					jQuery(el).parent('.arm_colorpicker_label').css('background-color', '#' + hex);
					/*Fill the text box just if the color was set using the picker, and not the colpickSetColor function.*/
					if (!bySetColor) {
						jQuery(el).val('#' + hex);
					}
				}
			});
		});
    }
});
</script>