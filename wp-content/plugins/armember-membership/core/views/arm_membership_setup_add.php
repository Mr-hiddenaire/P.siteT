<?php
global $wpdb, $ARMember, $arm_slugs, $arm_global_settings,  $arm_subscription_plans, $arm_membership_setup, $arm_member_forms, $arm_payment_gateways;
if (isset($_POST['form_action']) && in_array($_POST['form_action'], array('add', 'update'))) {

 
    do_action('arm_save_membership_setups', $_POST);
}
$manage_gateway_link = admin_url('admin.php?page=' . $arm_slugs->general_settings . '&action=payment_options');
$alertMessages = $ARMember->arm_alert_messages();
$all_global_settings = $arm_global_settings->arm_get_all_global_settings();
$general_settings = $all_global_settings['general_settings'];
$currencies = $arm_payment_gateways->currency['paypal'];
$currency_symbol = $currencies[$general_settings['paymentcurrency']];
$allGateways = $arm_payment_gateways->arm_get_all_payment_gateways_for_setup();

$page_mode = __("Add New Plan + Signup Page", 'ARMember');
$action = 'add';
$setup_id = 0;
$button_labels = array(
    'submit' => __('Submit', 'ARMember'),

     'next' => __('Next', 'ARMember'),
      'previous' => __('Previous', 'ARMember'),
   
);
$setup_modules = array();
$default_setup_style = array(
    'content_width' => '800',
    'plan_skin' => 'skin1',
 
    'hide_current_plans' => 0,
    'plan_selection_area' => 'before',
    'font_family' => 'Helvetica',
    'title_font_size' => 20,
    'title_font_bold' => 0,
    'title_font_italic' => '',
    'title_font_decoration' => '',
    'description_font_size' => 16,
    'description_font_bold' => 0,
    'description_font_italic' => '',
    'description_font_decoration' => '',
    'price_font_size' => 30,
    'price_font_bold' => 0,
    'price_font_italic' => '',
    'price_font_decoration' => '',
    'summary_font_size' => 16,
    'summary_font_bold' => 0,
    'summary_font_italic' => '',
    'summary_font_decoration' => '',
    'plan_title_font_color' => '#616161',
    'plan_desc_font_color' => '#616161',
    'price_font_color' => '#616161',
    'summary_font_color' => '#616161',  
    'bg_active_color' => '#23b7e5',
    'selected_plan_title_font_color' => '#23b7e5',
    'selected_plan_desc_font_color' => '#616161',
    'selected_price_font_color' => '#FFFFFF',
);
if( isset( $_GET['action']) && $_GET['action'] == 'edit_setup' && isset($_GET['id']) && !empty($_GET['id'])) {
    $setup_id = intval($_GET['id']);
    $setup_data = $arm_membership_setup->arm_get_membership_setup($setup_id);
  
    if ($setup_data !== FALSE && !empty($setup_data)) {
        $page_mode = __("Edit Plan + Signup Page", 'ARMember');
        $action = 'update';
        $setup_name = $setup_data['setup_name'];
        $setup_modules = $setup_data['setup_modules'];
		$button_labels = isset($setup_data['setup_labels']['button_labels']) ? $setup_data['setup_labels']['button_labels'] : $button_labels;
	}
}



$user_selected_plan = isset($setup_modules['selected_plan']) ? $setup_modules['selected_plan'] : '';
$user_selected_plan_cycle = isset($setup_modules['selected_plan_cycle']) ? $setup_modules['selected_plan_cycle'] : '';
$selectedForm = (empty($setup_modules['modules']['forms']) || $setup_modules['modules']['forms'] == 0) ? '' : $setup_modules['modules']['forms'];
$selectedPlans = isset($setup_modules['modules']['plans']) ? $setup_modules['modules']['plans'] : array();
$planOrders = (!empty($setup_modules['modules']['plans_order'])) ? $setup_modules['modules']['plans_order'] : array();
$planCycleOrders = (!empty($setup_modules['modules']['plan_cycle_order'])) ? $setup_modules['modules']['plan_cycle_order'] : array();
$gatewayOrders = (!empty($setup_modules['modules']['gateways_order'])) ? $setup_modules['modules']['gateways_order'] : array();

$selectedGateways = isset($setup_modules['modules']['gateways']) ? $setup_modules['modules']['gateways'] : array();
$selectedPaymentModes = isset($setup_modules['modules']['payment_mode']) ? $setup_modules['modules']['payment_mode'] : array();




$setup_modules['style'] = !empty($setup_modules['style']) ? $setup_modules['style'] : $default_setup_style;
$setup_name = !empty($setup_name) ? esc_html(stripslashes($setup_name)) : '';
$all_payment_gateways = $arm_payment_gateways->arm_get_active_payment_gateways();

?>
<?php
 $allPlans = $arm_subscription_plans->arm_get_all_active_subscription_plans();
?>
<div class="wrap arm_page arm_membership_setup_main_wrapper">
	<div class="content_wrapper arm_membership_setup_container" id="content_wrapper">
		<div class="arm_membership_setup_content">
			<form  method="post" id="arm_membership_setup_admin_form" class="arm_membership_setup_admin_form arm_admin_form" >
				<input type="hidden" name="id" value="<?php echo $setup_id;?>">
				<input type="hidden" name="form_action" value="<?php echo $action ?>">
				<div class="page_title"><?php echo $page_mode;?></div>
				<div class="armclear"></div>
				<div class="arm_setup_admin_form_container arm_admin_form_content">
                    <span class="arm_setup_main_error_msg error" style="display: none;"><?php _e('This membership setup can not be saved because in some cases, payment gateway will not be available. So setup cannot be processed.', 'ARMember');?></span>
					<div class="arm_belt_box">
						<div class="arm_belt_block arm_setup_module_box">
							<input name="setup_data[setup_name]" id="setup_name" type="text" style="width: 400px;" title="Setup name" value="<?php echo $setup_name;?>" data-msg-required="<?php _e('Setup name can not be left blank.', 'ARMember');?>" placeholder="<?php _e('Setup name', 'ARMember');?>" required />
							<span class="arm_setup_error_msg"></span>
						</div>
						<div class="arm_belt_block" align="<?php echo (is_rtl()) ? 'left' : 'right';?>">
                            <div class="arm_membership_setup_shortcode_box">
                                <span style="font-size: 18px;"><?php _e('Short Code','ARMember');?></span>
                                <?php if ($action == 'update') : ?>
                                <?php $shortCode = '[arm_setup id="'.$setup_id.'"]';?>
                                <div class="arm_shortcode_text arm_form_shortcode_box">
                                    <span class="armCopyText"><?php echo esc_attr($shortCode);?></span>
                                    <span class="arm_click_to_copy_text" data-code="<?php echo esc_attr($shortCode);?>"><?php _e('Click to copy', 'ARMember');?></span>
                                    <span class="arm_copied_text"><img src="<?php echo MEMBERSHIPLITE_IMAGES_URL;?>/copied_ok.png" alt="ok"/><?php _e('Code Copied', 'ARMember');?></span>
                                </div>
                                <?php else: ?>
                                <span class="arm_shortcode_text">
                                    <span style="display: block;font-size: 12px;line-height: normal;text-align: left;"><?php _e('Shortcode will be display here once you save current setup.', 'ARMember');?></span>
                                </span>
                                <?php endif;?>
                            </div>
						</div>
						<div class="armclear"></div>
					</div>
					<div class="armclear"></div>
                    <span class="arm_info_text" style="margin-bottom: 15px;"><?php _e('This wizard will help you to configure membership registration page. It will generate only single shortcode for processes like plan selection', 'ARMember');?> &rarr; <?php _e('signup', 'ARMember');?> &rarr; <?php _e('payment process.', 'ARMember');?></span>
					<div class="arm_setup_modules_container">
						<div class="arm_right_border"></div>
						<div class="arm_setup_section_title"><span class="arm_title_round">1</span><?php _e('Basic Configuration','ARMember');?></div>
						<div class="arm_setup_section_body">
                            <div class="arm_setup_option_field">
                                <div class="arm_setup_option_label" style="padding-top: 10px;"><?php _e('Select Plans','ARMember');?></div>
                                <div class="arm_setup_option_input arm_setup_plans_container">
                                    <div class="arm_setup_module_box">
                                        <div class="arm_setup_plan_options_list">
                                        <?php echo $arm_membership_setup->arm_setup_plan_list_options($selectedPlans, $allPlans);?>
                                        </div>
                                        <span class="arm_setup_error_msg"></span>
                                    </div>
                                    <div class="armclear"></div>
                                    <a href="<?php echo admin_url('admin.php?page=' . $arm_slugs->manage_plans . '&action=new');?>" target="_blank" class="arm_setup_conf_links arm_ref_info_links"><?php _e('Add New Plan', 'ARMember');?></a>
                                </div>
                            </div>
                            <div class="arm_setup_option_field">
                                <div class="arm_setup_option_label" style="padding-top: 10px;"><?php _e('Select Signup / Registration Form','ARMember');?></div>
                                <div class="arm_setup_option_input arm_setup_forms_container">
                                    <div class="arm_setup_module_box">
                                        <input type="hidden" id="arm_form_select_box" name="setup_data[setup_modules][modules][forms]" value="<?php echo $selectedForm;?>" data-msg-required="<?php _e('Please select signup / registration form.', 'ARMember');?>" />
                                        <dl class="arm_selectbox">
                                            <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                            <dd>
                                                <ul data-id="arm_form_select_box" class="arm_setup_form_options_list">
                                                    <?php echo $arm_membership_setup->arm_setup_form_list_options();?>
                                                </ul>
                                            </dd>
                                        </dl>
                                        <i class="arm_helptip_icon armfa armfa-question-circle" title="<?php _e("If user is not logged in than selected signup form will be displayed at frontend in subscription page.", 'ARMember');?>"></i>
                                        <span class="arm_info_text"><?php _e("Form will be skipped automatically when user is logged in.", 'ARMember');?></span>
                                        <span class="arm_setup_error_msg"></span>
                                    </div>
                                    <div class="armclear"></div>
                                   
                                </div>
                            </div>
                            <div class="arm_setup_option_field">
                                <div class="arm_setup_option_label" style="padding-top: 10px;"><?php _e('Select Payment Gateways','ARMember');?></div>
                                <div class="arm_setup_option_input arm_setup_items_box_gateways">
                                    <?php 
                                   
                                   $plan_options = array();
                                   $plan_detail = array();
                               
                                       
                                      
                                        $plan_object_array = array();
                                        foreach ($allPlans as $pID => $pdata) {
                                            $pddata = isset($allPlans[$pID]) ? $allPlans[$pID] : array();
                                            $plan_object = new ARM_Plan($pID); 
                                             $plan_object_array[$pID] = $plan_object;
                                            if (!empty($pddata)) {
                                                array_push($plan_detail,$pddata);
                                                $s_plan_name = $pddata['arm_subscription_plan_name'];
                                                $plan_type = $pddata['arm_subscription_plan_type'];
                                                $plan_options = maybe_unserialize($pddata['arm_subscription_plan_options']);
                                                $plan_payment_cycles = (isset($plan_options['payment_cycles']) && !empty($plan_options['payment_cycles'])) ? $plan_options['payment_cycles'] : array(); 
                                                if(empty($plan_payment_cycles)){
                                                    $plan_payment_cycles= array(array(
                                                        'cycle_key' => 'arm0',
                                                        'cycle_label' =>$plan_object->plan_text(false,false),
                                                    ));
                                                }
                                                $payment_type = isset($plan_options['payment_type']) ? $plan_options['payment_type'] : '';
                                                
                                                
                                            }
                                        }

                                       
                                        
                                       
                                    ?>
                                    <div class="arm_setup_module_box">
                                        <div class="arm_setup_gateway_options_list">
                                            <?php 
                                           
                                            echo $arm_membership_setup->arm_setup_gateway_list_options($selectedGateways, $all_payment_gateways, $selectedPaymentModes, $selectedPlans, $plan_object_array); ?>
                                        </div>
                                        <span class="arm_setup_error_msg"></span>
                                    </div>
                                    <div class="armclear"></div>
                                    <a href="<?php echo admin_url('admin.php?page=' . $arm_slugs->general_settings . '&action=payment_options');?>" target="_blank" class="arm_setup_conf_links arm_ref_info_links"><?php _e('Configure More Gateways', 'ARMember');?></a>
                                    <span class="arm_setup_gateway_error_msg error" style="display: none;"><?php _e('Atleast one payment gateway configuration is required for paid plan(s) selection.', 'ARMember'); ?></span>
                                    <div class="armclear"></div>
                                  
                                   
                                    <div class="arm_payment_gateway_warnings">
                                       <?php do_action('arm_show_payment_gateway_recurring_notice',$plan_detail); ?>
                                    </div>
                                </div>
                            </div>
                           
						</div>
                        <div class="armclear"></div>
						<div class="arm_setup_section_title"><span class="arm_title_round">2</span><?php _e('Other Options','ARMember');?></div>
                        <div class="arm_setup_section_body">
                            <div class="arm_setup_option_field">
                                <div class="arm_setup_option_label"><?php _e('Submit Button Label', 'ARMember');?></div>
                                <div class="arm_setup_option_input">
                                    <div class="arm_setup_module_box">
                                        <input type="text" name="setup_data[setup_labels][button_labels][submit]" value="<?php echo (isset($button_labels['submit'])) ? esc_html(stripslashes($button_labels['submit'])) : '';?>">
                                        <span class="arm_setup_error_msg"></span>
                                    </div>
                                </div>
                            </div>
                          
                          
                            <div class="arm_setup_option_field">
                                <div class="arm_setup_option_label"><?php _e('Payment Section Title', 'ARMember');?></div>
                                <div class="arm_setup_option_input">
                                    <div class="arm_setup_module_box">
                                        <input type="text" name="setup_data[setup_labels][payment_section_title]" value="<?php echo isset($setup_data['setup_labels']['payment_section_title']) ? $setup_data['setup_labels']['payment_section_title'] : __('Select Your Payment Gateway', 'ARMember');?>">
                                        <span class="arm_setup_error_msg"></span>
                                    </div>
                                </div>
                            </div>
                            <?php
                                $payment_gateways = $arm_payment_gateways->arm_get_all_payment_gateways_for_setup();
                                if( !empty($payment_gateways) && is_array($payment_gateways) ){
                                    foreach( $payment_gateways as $pgkey => $gateway ){
                                        $default_label = $gateway['gateway_name'];
                                        $gateway_field_label = (isset($setup_data['setup_labels']['payment_gateway_labels'][$pgkey]) && $setup_data['setup_labels']['payment_gateway_labels'][$pgkey] != "" ) ? $setup_data['setup_labels']['payment_gateway_labels'][$pgkey] : $default_label;
                            ?>
                            <div class="arm_setup_option_field">
                                <div class="arm_setup_option_label"><?php echo $default_label.' '.__(' Label', 'ARMember'); ?></div>
                                <div class="arm_setup_option_input">
                                    <div class="arm_setup_module_box">
                                        <input type="text" name="setup_data[setup_labels][payment_gateway_labels][<?php echo $pgkey; ?>]" value="<?php echo $gateway_field_label; ?>" />
                                        <span class="arm_setup_error_msg"></span>
                                    </div>
                                </div>
                            </div>
                            <?php
                                    }
                                }
                            ?>
                            <div class="arm_setup_option_field">
                                <div class="arm_setup_option_label"><?php _e('Payment Mode Selection Title', 'ARMember');?></div>
                                <div class="arm_setup_option_input">
                                    <div class="arm_setup_module_box">
                                        <input type="text" name="setup_data[setup_labels][payment_mode_selection]" value="<?php echo isset($setup_data['setup_labels']['payment_mode_selection']) ? $setup_data['setup_labels']['payment_mode_selection'] : __('How you want to pay?', 'ARMember');?>">
                                        <span class="arm_setup_error_msg"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="arm_setup_option_field">
                                <div class="arm_setup_option_label"><?php _e('Automatic Subscription Label', 'ARMember');?></div>
                                <div class="arm_setup_option_input">
                                    <div class="arm_setup_module_box">
                                        <input type="text" name="setup_data[setup_labels][automatic_subscription]" value="<?php echo isset($setup_data['setup_labels']['automatic_subscription']) ? $setup_data['setup_labels']['automatic_subscription'] : __('Auto Debit Payment', 'ARMember');?>">
                                        <span class="arm_setup_error_msg"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="arm_setup_option_field">
                                <div class="arm_setup_option_label"><?php _e('Semi Automatic Subscription Label', 'ARMember');?></div>
                                <div class="arm_setup_option_input">
                                    <div class="arm_setup_module_box">
                                        <input type="text" name="setup_data[setup_labels][semi_automatic_subscription]" value="<?php echo isset($setup_data['setup_labels']['semi_automatic_subscription']) ? $setup_data['setup_labels']['semi_automatic_subscription'] : __('Manual Payment', 'ARMember');?>">
                                        <span class="arm_setup_error_msg"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="arm_setup_option_field">
                                <div class="arm_setup_option_label"><?php _e('Summary Text', 'ARMember');?></div>
                                <div class="arm_setup_option_input">
                                    <div class="arm_setup_module_box">
                                        <textarea name="setup_data[setup_labels][summary_text]" cols="40" rows="5" style="width: 500px;"><?php echo isset($setup_data['setup_labels']['summary_text']) ? stripslashes($setup_data['setup_labels']['summary_text']) : '<div>Payment Summary</div><br/><div>Your currently selected plan : <strong>[PLAN_NAME]</strong>,  Plan Amount : <strong>[PLAN_AMOUNT]</strong> </div><div>, Final Payable Amount: <strong>[PAYABLE_AMOUNT]</strong> </div>';?></textarea>
                                        <div class="arm_setup_summary_tags">
                                            <ul>
                                                <li><code>[PLAN_NAME]</code> - <?php _e("This will be replaced with selected plan's title.", 'ARMember');?></li>
                                                <li><code>[PLAN_AMOUNT]</code> - <?php _e("This will be replaced with selected plan's amount.", 'ARMember');?></li>
                                               
                                                <li><code>[PAYABLE_AMOUNT]</code> - <?php _e("This will be replaced with final payable amount.", 'ARMember');?></li>
                                                <li><code>[TRIAL_AMOUNT]</code> - <?php _e("This will be replaced with plan's trial period amount.", 'ARMember');?></li>
                                            </ul>
                                        </div>
                                        <span class="arm_setup_error_msg"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
						<div class="armclear"></div>
						<div class="arm_setup_section_title"><span class="arm_title_round">3</span><?php _e('Styling & Formatting','ARMember');?></div>
						<div class="arm_setup_section_body">
                            <div class="arm_setup_option_field">
                                
                                <div class="arm_setup_option_input">
                                    <input type='hidden' id="arm_setup_plan_skin" name="setup_data[setup_modules][style][plan_skin]" class="arm_setup_plan_skin" value="skin3" />

                                   
                                </div>
                            </div>
                          

                            <div class="arm_setup_option_field">
                                <div class="arm_setup_option_label"><?php _e('Hide Current Plans', 'ARMember');?></div>
                                <div class="arm_setup_option_input">
                                    <div class="armswitch arm_global_setting_switch">
                                        
                                        <?php $is_hide_current_plans = (isset($setup_modules['style']['hide_current_plans'])) ? $setup_modules['style']['hide_current_plans'] : 0; ?>
                                        <input id="arm_setup_hide_current_plans" class="armswitch_input" <?php checked($is_hide_current_plans, '1');?> value="1" name="setup_data[setup_modules][style][hide_current_plans]" type="checkbox">
                                        <label class="armswitch_label" for="arm_setup_hide_current_plans"></label>
                                    </div>
                                    <label class="arm_global_setting_switch_label" for="arm_setup_two_step"><?php _e('Hide plans which are already owned by user', 'ARMember'); ?></label>
                                </div>
                            </div>

                            
                            <div class="arm_setup_option_field hide_plan_selection">
                                <div class="arm_setup_option_label"><?php _e('Hide Plan Selection Area', 'ARMember');?></div>
                                <div class="arm_setup_option_input">
                                    <div class="armswitch arm_global_setting_switch">
                                        
                                        <?php $is_hide_plans = (isset($setup_modules['style']['hide_plans'])) ? $setup_modules['style']['hide_plans'] : 0; ?>
                                        <input id="arm_setup_hide_plans" class="armswitch_input" <?php checked($is_hide_plans, '1');?> value="1" name="setup_data[setup_modules][style][hide_plans]" type="checkbox">
                                        <label class="armswitch_label" for="arm_setup_hide_plans"></label>
                                    </div>
                                </div>
                            </div>
                            <div class="arm_setup_option_field plan_area_position">
                                 <div class="arm_setup_option_label"><?php _e('Plan Selection Area Position', 'ARMember');?></div>
                                 <div class="arm_setup_option_input">
                                     <input type='hidden' id="arm_setup_plan_area_position" name="setup_data[setup_modules][style][plan_area_position]" class="arm_setup_plan_area_position" value="<?php echo (isset($setup_modules['style']['plan_area_position'])) ? $setup_modules['style']['plan_area_position'] : 'before'; ?>" />

                                     <dl class="arm_selectbox column_level_dd">
                                         <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"  /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                         <dd>
                                             <ul data-id="arm_setup_plan_area_position" class="arm_setup_plan_area_position">
                                                 <li data-label="<?php _e('Before Registration Form', 'ARMember'); ?>" data-value="before"><span class="arm_selectbox_option_list"><?php _e('Before Registration Form', 'ARMember'); ?></span></li>
                                                 <li data-label="<?php _e('After Registration Form', 'ARMember'); ?>" data-value="after"><span class="arm_selectbox_option_list"><?php _e('After Registration Form', 'ARMember'); ?></span></li>
                                             </ul>
                                        
                                         </dd>
                                     </dl>
                                 </div>
                             </div>  <input type='hidden' id="arm_setup_gateway_skin" name="setup_data[setup_modules][style][gateway_skin]" class="arm_setup_gateway_skin" value="radio" />                        
                                                
                                                    
                                                    
                            <div class="arm_setup_option_field">
                                <div class="arm_setup_option_label"><?php _e('Content Width', 'ARMember');?></div>
                                <div class="arm_setup_option_input">
                                    <?php $setup_content_width = ($setup_modules['style']['content_width'] == 0 && $setup_modules['style']['content_width'] != '') ? 800 : $setup_modules['style']['content_width'];
                                    ?>
                                    <input type="text" name="setup_data[setup_modules][style][content_width]" value="<?php echo $setup_content_width; ?>" class="arm_setup_shortcode_form_width">&nbsp;px
                                    <br/><span class="arm_info_text">Leave blank for auto width.</span>
                                </div>
                            </div>
                            <div class="arm_setup_option_field">
                                <div class="arm_setup_option_label"><?php _e('Form Position', 'ARMember');?></div>
                                <div class="arm_setup_option_input">
                                    <?php 
                                    $formPosition = (isset($setup_modules['style']['form_position']) && !empty($setup_modules['style']['form_position'])) ? $setup_modules['style']['form_position'] : 'left';
                                    ?>
                                    <input type="radio" class="arm_iradio arm_setup_form_position_radio" name="setup_data[setup_modules][style][form_position]" value="left" <?php checked($formPosition, 'left', true);?> id="arm_setup_form_position_left">
                                    <label for="arm_setup_form_position_left"><?php _e('Left', 'ARMember');?></label>
                                    <input type="radio" class="arm_iradio arm_setup_form_position_radio" name="setup_data[setup_modules][style][form_position]" value="center" <?php checked($formPosition, 'center', true);?> id="arm_setup_form_position_center">
                                    <label for="arm_setup_form_position_center"><?php _e('Center', 'ARMember');?></label>
                                    <input type="radio" class="arm_iradio arm_setup_form_position_radio" name="setup_data[setup_modules][style][form_position]" value="right" <?php checked($formPosition, 'right', true);?> id="arm_setup_form_position_right">
                                    <label for="arm_setup_form_position_right"><?php _e('Right', 'ARMember');?></label>
                                </div>
                            </div>
                            <div class="arm_setup_option_field">
                                <div class="arm_setup_option_label" style="padding-top: 10px;"><?php _e('Select Plan Layout', 'ARMember');?></div>
                                <div class="arm_setup_option_input">
                                    <?php $planColumnType = (!empty($setup_modules['plans_columns'])) ? $setup_modules['plans_columns'] : '3';?>
                                    <div class="arm_column_layout_types_container">
                                        <label class="<?php echo ($planColumnType == 1) ? 'arm_active_label' : '';?>">
                                            <img class="arm_inactive_img" src="<?php echo MEMBERSHIPLITE_IMAGES_URL;?>/single_column.png" alt=""/>
                                            <img class="arm_active_img" src="<?php echo MEMBERSHIPLITE_IMAGES_URL;?>/single_column_hover.png" alt=""/>
                                            <input type="radio" name="setup_data[setup_modules][plans_columns]" value="1" class="arm_column_layout_type_radio" data-module="plans" <?php checked($planColumnType, 1, true);?>>
                                        </label>
                                        <label class="<?php echo ($planColumnType == 2) ? 'arm_active_label' : '';?>">
                                            <img class="arm_inactive_img" src="<?php echo MEMBERSHIPLITE_IMAGES_URL;?>/two_column.png" alt=""/>
                                            <img class="arm_active_img" src="<?php echo MEMBERSHIPLITE_IMAGES_URL;?>/two_column_hover.png" alt=""/>
                                            <input type="radio" name="setup_data[setup_modules][plans_columns]" value="2" class="arm_column_layout_type_radio" data-module="plans" <?php checked($planColumnType, 2, true);?>>
                                        </label>
                                        <label class="<?php echo ($planColumnType == 3) ? 'arm_active_label' : '';?>">
                                            <img class="arm_inactive_img" src="<?php echo MEMBERSHIPLITE_IMAGES_URL;?>/three_column.png" alt=""/>
                                            <img class="arm_active_img" src="<?php echo MEMBERSHIPLITE_IMAGES_URL;?>/three_column_hover.png" alt=""/>
                                            <input type="radio" name="setup_data[setup_modules][plans_columns]" value="3" class="arm_column_layout_type_radio" data-module="plans" <?php checked($planColumnType, 3, true);?>>
                                        </label>
                                        <label class="<?php echo ($planColumnType == 4) ? 'arm_active_label' : '';?>">
                                            <img class="arm_inactive_img" src="<?php echo MEMBERSHIPLITE_IMAGES_URL;?>/four_column.png" alt=""/>
                                            <img class="arm_active_img" src="<?php echo MEMBERSHIPLITE_IMAGES_URL;?>/four_column_hover.png" alt=""/>
                                            <input type="radio" name="setup_data[setup_modules][plans_columns]" value="4" class="arm_column_layout_type_radio" data-module="plans" <?php checked($planColumnType, 4, true);?>>
                                        </label>
                                        <div class="armclear"></div>
                                    </div>
                                    <ul class="arm_membership_setup_sub_ul arm_setup_plans_ul arm_setup_plan_layout_list arm_column_<?php echo $planColumnType; ?>" style="<?php echo (empty($selectedPlans)) ? 'display:none;' : '' ?> max-width: 785px;">
                                        <?php echo $arm_membership_setup->arm_setup_plan_layout_list_options($planOrders, $selectedPlans, $user_selected_plan); ?>
                                    </ul>
                                </div>
                            </div>
                                                    
                           
                                                    
                            <div class="arm_setup_option_field">
                                <div class="arm_setup_option_label" style="padding-top: 10px;"><?php _e('Select Payment Gateway Layout', 'ARMember');?></div>
                                <div class="arm_setup_option_input">
                                    <?php 
                                    $gatewayColumnType = (!empty($setup_modules['gateways_columns'])) ? $setup_modules['gateways_columns'] : '1';
                                    $orderGateways = $arm_membership_setup->arm_sort_module_by_order($allGateways, $gatewayOrders);
                                    ?>
                                    <div class="arm_column_layout_types_container">
                                        <label class="<?php echo ($gatewayColumnType == 1) ? 'arm_active_label' : '';?>">
                                            <img class="arm_inactive_img" src="<?php echo MEMBERSHIPLITE_IMAGES_URL;?>/single_column.png" alt=""/>
                                            <img class="arm_active_img" src="<?php echo MEMBERSHIPLITE_IMAGES_URL;?>/single_column_hover.png" alt=""/>
                                            <input type="radio" name="setup_data[setup_modules][gateways_columns]" value="1" class="arm_column_layout_type_radio" data-module="gateways" <?php checked($gatewayColumnType, 1, true);?>>
                                        </label>
                                        <label class="<?php echo ($gatewayColumnType == 2) ? 'arm_active_label' : '';?>">
                                            <img class="arm_inactive_img" src="<?php echo MEMBERSHIPLITE_IMAGES_URL;?>/two_column.png" alt=""/>
                                            <img class="arm_active_img" src="<?php echo MEMBERSHIPLITE_IMAGES_URL;?>/two_column_hover.png" alt=""/>
                                            <input type="radio" name="setup_data[setup_modules][gateways_columns]" value="2" class="arm_column_layout_type_radio" data-module="gateways" <?php checked($gatewayColumnType, 2, true);?>>
                                        </label>
                                        <label class="<?php echo ($gatewayColumnType == 3) ? 'arm_active_label' : '';?>">
                                            <img class="arm_inactive_img" src="<?php echo MEMBERSHIPLITE_IMAGES_URL;?>/three_column.png" alt=""/>
                                            <img class="arm_active_img" src="<?php echo MEMBERSHIPLITE_IMAGES_URL;?>/three_column_hover.png" alt=""/>
                                            <input type="radio" name="setup_data[setup_modules][gateways_columns]" value="3" class="arm_column_layout_type_radio" data-module="gateways" <?php checked($gatewayColumnType, 3, true);?>>
                                        </label>
                                        <label class="<?php echo ($gatewayColumnType == 4) ? 'arm_active_label' : '';?>">
                                            <img class="arm_inactive_img" src="<?php echo MEMBERSHIPLITE_IMAGES_URL;?>/four_column.png" alt=""/>
                                            <img class="arm_active_img" src="<?php echo MEMBERSHIPLITE_IMAGES_URL;?>/four_column_hover.png" alt=""/>
                                            <input type="radio" name="setup_data[setup_modules][gateways_columns]" value="4" class="arm_column_layout_type_radio" data-module="gateways" <?php checked($gatewayColumnType, 4, true);?>>
                                        </label>
                                        <div class="armclear"></div>
                                    </div>
                                    <ul class="arm_membership_setup_sub_ul arm_setup_gateways_ul arm_column_<?php echo $gatewayColumnType;?>" style="<?php echo (empty($selectedPlans)) ? 'display:none;' : ''?>">
                                    <?php if (!empty($orderGateways)): ?>
                                        <?php $gi = 1;
                                        foreach ($orderGateways as $key => $pg):?>
                                            <?php 
                                            $gateweyClass = 'arm_membership_setup_gateways_li_' . $key;
                                            $gateweyClass .= ((in_array($key, $selectedGateways) && (isset($pg['status']) && $pg['status'] == '1')) ? '' : ' hidden_section ');
                                            ?>
                                            <li class="arm_membership_setup_sub_li arm_membership_setup_gateways_li <?php echo $gateweyClass;?>">
                                                <div class="arm_membership_setup_sortable_icon"></div>
                                                <span><?php echo $pg['gateway_name'];?></span>
                                                <input type="hidden" name="setup_data[setup_modules][modules][gateways_order][<?php echo $key;?>]" value="<?php echo $gi;?>" class="arm_module_options_order">
                                            </li>
                                        <?php $gi++;
                                        endforeach;?>
                                    <?php endif;?>
                                    </ul>
                                </div>
                            </div>
						</div>
						<div class="armclear"></div>
                        <div class="arm_setup_section_title arm_setup_section_title_last"><span class="arm_title_round">&nbsp;</span></div>
					</div>
					<div class="armclear"></div>
					<!--<div class="arm_divider"></div>-->
					<div class="arm_submit_btn_container">
						<button class="arm_save_btn" name="SetupSubmit" type="submit"><?php _e('Save', 'ARMember') ?></button>
                        <a href="javascript:void(0)" class="arm_setup_preview_btn armemailaddbtn"><?php _e('Preview', 'ARMember');?></a>
						<a class="arm_cancel_btn" href="<?php echo admin_url('admin.php?page='.$arm_slugs->membership_setup);?>"><?php _e('Close', 'ARMember') ?></a>
                        <?php wp_nonce_field( 'arm_wp_nonce' );?>
					</div>
				</div>
			</form>
		</div>
	</div>
	
</div>
<div class="popup_wrapper arm_preview_setup_shortcode_popup_wrapper">
    <div class="popup_wrapper_inner">
        <div class="popup_header">
            <span class="popup_close_btn arm_popup_close_btn arm_preview_setup_shortcode_close_btn"></span>
            <span class="add_rule_content"><?php _e('Preview','ARMember' );?></span>
        </div>
        <div class="popup_content_text arm_setup_shortcode_html_wrapper">
            <div class="arm_setup_shortcode_html"></div>
            <div class="arm_loading_grid arm_setup_preview_loader" style="display: none;"><img src="<?php echo MEMBERSHIPLITE_IMAGES_URL;?>/loader.gif" alt="Loading.."></div>
        </div>
        <div class="armclear"></div>
    </div>
</div>
<?php 
		/* **********./Begin Bulk Delete Plan Popup/.********** */
		$plan_skin_change_content = '<span class="arm_confirm_text">'.__("Please confirm that while changing skin, All colors will be reset to default.",'ARMember' ).'</span>';
		$plan_skin_change_content .= '<input type="hidden" value="false" id="bulk_delete_flag"/>';
		$plan_skin_change_popup_arg = array(
			'id' => 'plan_skin_change_message',
			'class' => 'plan_skin_change_message',
                        'title' => __('Change Plan Skin','ARMember'),
			'content' => $plan_skin_change_content,
			'button_id' => 'plan_skin_change_ok_btn',
			'button_onclick' => "plan_skin_change();",
		);
		echo $arm_global_settings->arm_get_bpopup_html($plan_skin_change_popup_arg);
		/* **********./End Bulk Delete Plan Popup/.********** */
		
$armHomeUrl = ARMLITE_HOME_URL;
$armHomeUrl = $arm_global_settings->add_query_arg('arm_setup_preview', '1', $armHomeUrl);
?>
<script type="text/javascript">
var setupPreviewUrl = '<?php echo $armHomeUrl;?>';
jQuery(window).on("load", function(){
	arm_MembershipSetup_init();
});
function arm_setup_skin_default_color_array(){
    var arm_setup_skin_array;
    arm_setup_skin_array = '<?php echo json_encode($arm_membership_setup->arm_setup_skin_default_color_array()); ?>';
    return arm_setup_skin_array;
}
</script>