<?php

if( !class_exists('ARM_Stripe_SCA') ){

	class ARM_Stripe_SCA{

        function __construct(){

            add_action( 'wp_ajax_arm_stripe_made_charge', array( $this, 'arm_stripe_made_charge_function' ) );
            add_action( 'wp_ajax_nopriv_arm_stripe_made_charge', array( $this, 'arm_stripe_made_charge_function' ) );
            
            add_action( 'wp_ajax_arm_stripe_made_charge_onetime', array( $this, 'arm_stripe_made_charge_onetime_function'));
            add_action( 'wp_ajax_nopriv_arm_stripe_made_charge_onetime', array( $this, 'arm_stripe_made_charge_onetime_function'));
            
            add_action( 'wp_ajax_arm_store_subscription_payment', array( $this, 'arm_store_stripe_subscription_payment') );
            add_action( 'wp_ajax_nopriv_arm_store_subscription_payment', array( $this, 'arm_store_stripe_subscription_payment') );
            
            add_action( 'wp_ajax_arm_stripe_made_charge_subscription_paid_trial', array( $this, 'arm_store_paid_trial_subscription_payment') );
            add_action( 'wp_ajax_nopriv_arm_stripe_made_charge_subscription_paid_trial', array( $this, 'arm_store_paid_trial_subscription_payment') );

            add_filter( 'arm_hide_cc_fields', array( $this, 'arm_hide_stripe_cc_fields' ), 10, 3 );

            add_filter( 'arm_payment_gateway_has_ccfields', array( $this, 'arm_display_cc_fields_for_setup'), 10, 3);

            add_filter( 'arm_allow_gateways_update_card_detail', array( $this, 'arm_display_cc_fields_in_update_card'), 10 , 2 );

            //add_action( 'wp_head', array( $this, 'arm_enqueue_stripe_js'),100);

            add_action( 'wp_ajax_arm_update_stripe_card', array( $this, 'arm_update_stripe_card_function') );
            add_action( 'wp_ajax_arm_stripe_made_update_card', array( $this, 'arm_stripe_made_update_card_function') );

            add_filter( 'arm_display_update_card_button_from_outside', array( $this, 'arm_display_update_card_button'), 10, 3 );
            add_filter( 'arm_render_update_card_button_from_outside', array( $this, 'arm_render_update_card_button'), 10, 6 );

            //add_action( 'wp', array( $this, 'arm_StripeEventListener'), 4);

            add_filter( 'arm_add_arm_entries_value', array( $this, 'arm_modify_entry_values'), 10 );

            add_action('arm_after_cancel_subscription', array(&$this, 'arm_cancel_subscription_instant'), 100, 4);
        }


        function arm_cancel_subscription_instant($user_id, $plan, $cancel_plan_action, $planData)
        {
            global $wpdb, $ARMember, $arm_global_settings, $arm_subscription_plans, $arm_member_forms, $arm_payment_gateways, $arm_manage_communication;

            $all_payment_gateways = $arm_payment_gateways->arm_get_active_payment_gateways();
            $plan_id = isset($plan->ID) ? $plan->ID : 0;

            if(empty($planData) && !empty($plan_id))
            {
                $planData = get_user_meta($user_id, 'arm_user_plan_' . $plan_id, true);
            }

		$user_stripe_data = !empty($planData['arm_stripe']) ? $planData['arm_stripe'] : '' ;

		$payment_mode = !empty($planData['arm_payment_mode']) ? $planData['arm_payment_mode'] : '';

		$subscription_id = isset($user_stripe_data['transaction_id']) ? $user_stripe_data['transaction_id'] : '';

		$plan_cycle = isset($planData['arm_payment_cycle']) ? $planData['arm_payment_cycle'] : '';
		$paly_cycle_data = $plan->prepare_recurring_data($plan_cycle);

		$user_payment_gateway = !empty($planData['arm_user_gateway']) ? $planData['arm_user_gateway'] : '';

		if(!empty($subscription_id) && strtolower($user_payment_gateway) == 'stripe' && $payment_mode == "auto_debit_subscription" && $cancel_plan_action == "on_expire" && $paly_cycle_data['rec_time'] == 'infinite')
		{
		$this->arm_stripe_cancel_subscription_immediately($all_payment_gateways, $subscription_id);
		}
	    
        }


        function arm_stripe_cancel_subscription_immediately($all_payment_gateways, $subscription_id)
        {
            global $wpdb, $ARMember, $arm_global_settings, $arm_subscription_plans, $arm_member_forms, $arm_payment_gateways, $arm_manage_communication, $arm_subscription_cancel_msg;

            $delete_subscr = "";
            try
            {
                $stripe_options = $all_payment_gateways['stripe'];
                $arm_stripe_enable_debug_mode = isset($stripe_options['enable_debug_mode']) ? $stripe_options['enable_debug_mode'] : 0;

                if ($stripe_options['stripe_payment_mode'] == 'live') {
                    $sec_key = $stripe_options['stripe_secret_key'];
                } else {
                    $sec_key = $stripe_options['stripe_test_secret_key'];
                }

                $headers = array(
                    'Authorization' => 'Bearer '.$sec_key
                );

                $subscription_url = 'https://api.stripe.com/v1/subscriptions/'. $subscription_id;

                $delete_subscr = wp_remote_request(
                    $subscription_url,
                    array(
                        'timeout' => 5000,
                        'method' => 'DELETE',
                        'headers' => $headers
                    )
                );
            }
            catch(Exception $e)
            {
                $ARMember->arm_write_response('Exception in Cancel Stripe SCA Subscription => '.json_encode($e->getMessage()));
            }
            return $delete_subscr;
        }

        function arm_cancel_stripe_sca_subscription( $plan_id, $user_id ){
            global $wpdb, $ARMember, $arm_global_settings, $arm_subscription_plans, $arm_member_forms, $arm_payment_gateways, $arm_manage_communication, $arm_subscription_cancel_msg;

            if( $plan_id != '' && $user_id != '' ){
                $all_payment_gateways = $arm_payment_gateways->arm_get_active_payment_gateways();
                $apikey = "";
                if (isset($all_payment_gateways['stripe']) && !empty($all_payment_gateways['stripe'])) {

                    $defaultPlanData = $arm_subscription_plans->arm_default_plan_array();
                    $userPlanDatameta = get_user_meta($user_id, 'arm_user_plan_' . $plan_id, true);
                    $userPlanDatameta = !empty($userPlanDatameta) ? $userPlanDatameta : array();
                    $planData = shortcode_atts($defaultPlanData, $userPlanDatameta);

                    $user_payment_gateway = $planData['arm_user_gateway'];

                    if (strtolower($user_payment_gateway) == 'stripe') {
                        
                        $user_stripe_data = $planData['arm_stripe'];
                        $payment_mode = $planData['arm_payment_mode'];

                        $planDetail = $planData['arm_current_plan_detail'];

                        if (!empty($planDetail)) {
                            $plan = new ARM_Plan(0);
                            $plan->init((object) $planDetail);
                        } else {
                            $plan = new ARM_Plan($plan_id);
                        }


                        $arm_payment_cycle = $planData['arm_payment_cycle'];
                        $recurring_data = $plan->prepare_recurring_data($arm_payment_cycle);
                        $amount = $recurring_data['amount'];

                        $customer_id = isset($user_stripe_data['customer_id']) ? $user_stripe_data['customer_id'] : '';
                        $subscription_id = isset($user_stripe_data['transaction_id']) ? $user_stripe_data['transaction_id'] : '';

                        $user_detail = get_userdata($user_id);
                        $payer_email = $user_detail->user_email;

                        if( 'auto_debit_subscription' == $payment_mode ){
                            $delete_subscr = $this->arm_stripe_cancel_subscription_immediately($all_payment_gateways, $subscription_id);

                            $StripeSCAResponseData = json_decode($delete_subscr['body'], true);
                
                            if(!empty($StripeSCAResponseData['error']))
                            {
                                $autho_options = $all_payment_gateways['stripe'];
                                $arm_enable_debug_mode = isset($autho_options['enable_debug_mode']) ? $autho_options['enable_debug_mode'] : 0;
                                if($arm_enable_debug_mode)
                                {
                                    if(!empty($StripeSCAResponseData['error']['message']))
                                    {
                                        $arm_subscription_cancel_msg = __("Error in cancel subscription from Stripe.", "ARMember")." ".$StripeSCAResponseData['error']['message'];
                                    }
                                    else
                                    {
                                        $common_messages = isset($arm_global_settings->common_message) ? $arm_global_settings->common_message : array();
                                        $arm_subscription_cancel_msg = isset($common_messages['arm_payment_gateway_subscription_failed_error_msg']) ? $common_messages['arm_payment_gateway_subscription_failed_error_msg'] : __("Membership plan couldn't cancel. Please contact the site administrator.", 'ARMember');
                                    }
                                }
                                else
                                {
                                    $common_messages = isset($arm_global_settings->common_message) ? $arm_global_settings->common_message : array();
                                    $arm_subscription_cancel_msg = isset($common_messages['arm_payment_gateway_subscription_failed_error_msg']) ? $common_messages['arm_payment_gateway_subscription_failed_error_msg'] : __("Membership plan couldn't cancel from Payment Gateway. Please contact the site administrator.", 'ARMember');
                                }
                                return;
                            }
                                

                            if( (isset( $StripeAcion->id ) && 'canceled' == $StripeAcion->status) || empty($subscription_id) ){
                                $arm_manage_communication->arm_user_plan_status_action_mail(array('plan_id' => $plan_id, 'user_id' => $user_id, 'action' => 'on_cancel_subscription'));
                                $payment_data = array(
                                    'arm_user_id' => $user_id,
                                    'arm_first_name'=> $user_detail->first_name,
                                    'arm_last_name'=> $user_detail->last_name,
                                    'arm_plan_id' => $plan_id,
                                    'arm_payment_gateway' => 'stripe',
                                    'arm_payment_type' => 'subscription',
                                    'arm_token' => $StripeAcion->customer,
                                    'arm_payer_email' => $payer_email,
                                    'arm_receiver_email' => '',
                                    'arm_transaction_id' => $StripeAcion->id,
                                    'arm_transaction_payment_type' => $StripeAcion->object,
                                    'arm_payment_mode' => $payment_mode,
                                    'arm_transaction_status' => $StripeAcion->status,
                                    'arm_payment_date' => current_time('mysql'),
                                    'arm_amount' => $amount,
                                    'arm_coupon_code' => '',
                                    'arm_response_text' => utf8_encode(maybe_serialize($StripeAcion)),
                                    'arm_is_trial' => '0',
                                    'arm_created_date' => current_time('mysql')
                                );
                                $payment_log_id = $arm_payment_gateways->arm_save_payment_log($payment_data);
                                return;
                            }

                        } else {
                            $arm_manage_communication->arm_user_plan_status_action_mail(array('plan_id' => $plan_id, 'user_id' => $user_id, 'action' => 'on_cancel_subscription'));
                            $payment_data = array(
                                'arm_user_id' => $user_id,
                                'arm_first_name'=> $user_detail->first_name,
                                'arm_last_name'=> $user_detail->last_name,
                                'arm_plan_id' => $plan_id,
                                'arm_payment_gateway' => 'stripe',
                                'arm_payment_type' => 'subscription',
                                'arm_token' => $subscr_id,
                                'arm_payer_email' => $payer_email,
                                'arm_receiver_email' => '',
                                'arm_transaction_id' => $subscr_id,
                                'arm_transaction_payment_type' => 'subscription',
                                'arm_payment_mode' => $payment_mode,
                                'arm_transaction_status' => 'canceled',
                                'arm_payment_date' => current_time('mysql'),
                                'arm_amount' => $amount,
                                'arm_coupon_code' => '',
                                'arm_response_text' => '',
                                'arm_is_trial' => '0',
                                'arm_created_date' => current_time('mysql')
                            );
                            $payment_log_id = $arm_payment_gateways->arm_save_payment_log($payment_data);

                            return;
                        }

                    }
                }

            }
        }

        function arm_modify_entry_values( $entry_post_data ){

            if( 'stripe' == $entry_post_data['payment_gateway'] ){
                global $arm_payment_gateways;

                $active_payment_gateways = $arm_payment_gateways->arm_get_active_payment_gateways();

                $stripe_pg_options = $active_payment_gateways['stripe'];

                if( isset( $stripe_pg_options['stripe_payment_method'] ) ){
                    $entry_post_data['stripe_payment_method'] = $stripe_pg_options['stripe_payment_method'];
                }
            }

            return $entry_post_data;
        }

        function arm_StripeEventListener(){
            global $wpdb, $ARMember, $arm_global_settings, $arm_member_forms, $arm_payment_gateways, $arm_subscription_plans, $arm_manage_communication, $arm_members_class, $arm_manage_coupons;

            
            if ( isset( $_REQUEST['arm-listener'] ) && in_array( $_REQUEST['arm-listener'], array( 'arm_stripe_api', 'arm_stripe_notify', 'stripe' ) ) ) {
                $all_payment_gateways = $arm_payment_gateways->arm_get_active_payment_gateways();
                if( isset( $all_payment_gateways['stripe'] ) && !empty( $all_payment_gateways['stripe'] ) ){

                    $stripe_options = $all_payment_gateways['stripe'];
                    
                    if ($stripe_options['stripe_payment_mode'] == 'live') {
                        $secret_key = $stripe_options['stripe_secret_key'];
                    } else {
                        $secret_key = $stripe_options['stripe_test_secret_key'];
                    }

                    $file_content = file_get_contents('php://input');


                    $response = json_decode( trim( $file_content ) );

                    if( isset( $response->type) && 'invoice.payment_succeeded' == $response->type ){

                        $customer_id = $response->data->object->customer;

                        $invoice = $response->data->object;

                        $in_obj = $invoice->lines->data[0];
                        $subscription_id = $in_obj->subscription;

                        if( null == $subscription_id || '' == $subscription_id ){
                            $subscription_id = $in_obj->id;
                        }

                        $getData = $wpdb->get_row( "SELECT arm_token,arm_log_id,arm_user_id,arm_extra_vars FROM `" . $ARMember->tbl_arm_payment_log. "` WHERE arm_token LIKE '%".$customer_id."%' AND arm_display_log = 0 AND arm_payment_gateway = 'stripe' AND arm_transaction_id IS NULL"  );

                        if( !empty( $getData ) && ( empty($getData->arm_user_id) || '0' == $getData->arm_user_id ) ){
                            
                            $arm_token = $getData->arm_token;
                            
                            $token_data = explode( '|', $arm_token );
                            
                            $arm_token_id = $getData->arm_log_id;

                            $charge_details = json_decode( $getData->arm_extra_vars, true );

                            $wpdb->update(
                                $ARMember->tbl_arm_payment_log,
                                array(
                                    'arm_token' => ''
                                ),
                                array(
                                    'arm_log_id' => $arm_token_id
                                )
                            );

                            $subscription_id = $token_data[1];

                            $api_url = 'https://api.stripe.com/v1/subscriptions/' . $subscription_id;

                            $headers = array(
                                'Authorization' => 'Bearer ' . $secret_key,
                            );

                            $wp_post_data = wp_remote_post(
                                $api_url, array(
                                    'timeout' => 5000,
                                    'headers' => $headers
                                )
                            );

                            if( is_wp_error($wp_post_data) ){
                                $ARMember->arm_write_response( " == NEW USER SUBSCRIPTION DATA ERROR == " );
                                $ARMember->arm_write_response( json_encode( $wp_post_data ) );
                            } else {
                                $subscription_data = json_decode( $wp_post_data['body'] );
                                
                                if( isset( $subscription_data->status ) && ( 'active' == $subscription_data->status || 'paid' == $subscription_data->status || 'trialing' == $subscription_data->status )  ) {

                                    $subscription_id = $subscription_data->id;

                                    $invoice_id = $subscription_data->latest_invoice;

                                    $headers = array(
                                        'Authorization' => 'Bearer ' . $secret_key,
                                    );

                                    $wp_post_data = wp_remote_post(
                                        'https://api.stripe.com/v1/invoices/' . $invoice_id,
                                        array(
                                            'headers' => $headers,
                                            'timeout' => 5000
                                        )
                                    );

                                    if( is_wp_error( $wp_post_data ) ){
                                        $ARMember->arm_write_response( " == NEW USER INVOICE DATA ERROR == " );
                                        $ARMember->arm_write_response( json_encode( $wp_post_data ) );
                                    } else {
                                        $invoice_data = json_decode( $wp_post_data['body'] );
                                        
                                        if( isset( $invoice_data->charge ) ){
                                            $charge_id = $invoice_data->charge;

                                            $charge_obj = wp_remote_post(
                                                'https://api.stripe.com/v1/charges/' . $charge_id,
                                                array(
                                                    'headers' => $headers,
                                                    'timeout' => 5000
                                                )
                                            );

                                            if( is_wp_error( $charge_obj ) ){
                                                $ARMember->arm_write_response( " == NEW USER CHARGE DATA ERROR == " );
                                                $ARMember->arm_write_response( json_encode( $charge_obj ) );
                                            } else {
                                                $charge_data = json_decode( $charge_obj['body'] );

                                                if( isset( $charge_data->id ) ){

                                                    $pi_id = $charge_data->payment_intent;

                                                    $stripelog = new stdClass();
                                                    foreach( $charge_data as $k => $v ){
                                                        $stripelog->$k = $v;
                                                    }

                                                    $card_number = $charge_data->payment_method_details->card->last4;
                                                    

                                                    $customs = explode('|', $subscription_data->metadata->custom);
                                                    $entry_id = $customs[0];
                                                    $entry_email = $customs[1];
                                                    $arm_payment_type = $customs[2];

                                                    $txn_id = $subscription_id;

                                                    $user_id = 0;

                                                    $entry_data = $wpdb->get_row("SELECT `arm_entry_id`, `arm_entry_email`, `arm_entry_value`, `arm_form_id`, `arm_user_id`, `arm_plan_id` FROM `" . $ARMember->tbl_arm_entries . "` WHERE `arm_entry_id`='" . $entry_id . "' AND `arm_entry_email`='" . $entry_email . "'", ARRAY_A);

                                                    if( !empty( $entry_data ) ){
                                                        
                                                        $is_log = false;

                                                        if( !empty( $charge_details ) ){

                                                            if (isset($charge_details['status']) && $charge_details['status'] == TRUE) {
                                                                $payment_done = $charge_details;
                                                                return $payment_done;
                                                            }
                                                            if (isset($charge_details['extraVars'])) {
                                                                $extraParam = $charge_details['extraVars'];
                                                                unset($charge_details['extraVars']);
                                                            }
                                                            $coupon_details = array();
                                                            if (isset($charge_details['coupon_details'])) {
                                                                $coupon_details = $charge_details['coupon_details'];
                                                            }
                                                            $charge_details['plan_action'] = $plan_action;
                                                            $charge_details['expire_date'] = $plan_expiry_date;

                                                            $charge_details['tax_percentage'] = $tax_percentage; 
                                                            $extraParam['tax_percentage'] = $tax_percentage;
                                                            $extraParam['tax_amount'] =  isset($charge_details['tax_amount'])? $charge_details['tax_amount']  : 0; 
                                                            unset($charge_details['tax_amount']);
                                                        }

                                                        $extraParam['plan_amount'] = $charge_data->amount;
                                                        $extraParam['paid_amount'] = $charge_data->amount;
                                                        $extraParam['card_number'] = 'xxxx-xxxx-xxxx-'.$card_number;
                                                        $entry_values = maybe_unserialize($entry_data['arm_entry_value']);
                                                        $payment_mode = $entry_values['arm_selected_payment_mode'];
                                                        $payment_cycle = $entry_values['arm_selected_payment_cycle'];
                                                        $arm_user_old_plan = (isset($entry_values['arm_user_old_plan']) && !empty($entry_values['arm_user_old_plan'])) ? explode(",", $entry_values['arm_user_old_plan']) : array();
                                                        $setup_id = $entry_values['setup_id'];
                                                        $tax_percentage = $entry_values['tax_percentage'];

                                                        $entry_plan = $entry_data['arm_plan_id'];
                                                        $stripelog->arm_coupon_code = $entry_values['arm_coupon_code'];
                                                        $stripelog->arm_payment_type = $arm_payment_type;
                                                        $extraParam['arm_is_trial'] = '0';
                                                        $extraParam['tax_percentage'] = (isset($tax_percentage) && $tax_percentage > 0) ? $tax_percentage : 0; 
                                                        
                                                        $user_info = get_user_by('email', $entry_email);                                                        

                                                        $do_not_update_user = true;
                                                        if ($user_info) {
                                                            $user_id = $user_info->ID;

                                                            $trxn_success_log_id = $wpdb->get_var("SELECT `arm_log_id` FROM `" . $ARMember->tbl_arm_payment_log . "` WHERE `arm_user_id`='" . $user_id . "' AND `arm_transaction_id`='" . $txn_id . "' AND `arm_transaction_status` = 'success' AND `arm_payment_gateway` = 'stripe'");
                                                            if($trxn_success_log_id!='')
                                                            {
                                                                $do_not_update_user = false;
                                                            }

                                                            if($do_not_update_user)
                                                            {
                                                                $log_id = $wpdb->get_var("SELECT `arm_log_id` FROM `" . $ARMember->tbl_arm_payment_log . "` WHERE `arm_user_id`='" . $user_id . "' AND `arm_transaction_id`='" . $txn_id . "' AND `arm_transaction_status` = 'pending' AND `arm_payment_gateway` = 'stripe'");

                                                                if ($log_id != '') {
                                                                    $payment_history_data = array();
                                                                    $payment_history_data['arm_transaction_status'] = 'success';
                                                                    $field_update = $wpdb->update($ARMember->tbl_arm_payment_log, $payment_history_data, array('arm_log_id' => $log_id));
                                                                    $do_not_update_user = false;
                                                                }
                                                            }
                                                        }

                                                        if ($do_not_update_user){
                                                            
                                                            $form_id = $entry_data['arm_form_id'];
                                                            $armform = new ARM_Form('id', $form_id);
                                                            $user_info = get_user_by('email', $entry_email);
                                                            $new_plan = new ARM_Plan($entry_plan);
                                                            $plan_action = "new_subscription";
                                                            if ($new_plan->is_recurring()) {
                                                                $plan_action = "renew_subscription";
                                                                if (in_array($entry_plan, $arm_user_old_plan)) {
                                                                    $is_recurring_payment = $arm_subscription_plans->arm_is_recurring_payment_of_user($user_id, $entry_plan, $payment_mode);
                                                                    if ($is_recurring_payment) {
                                                                        $plan_action = 'recurring_payment';
                                                                        $planData = get_user_meta($user_id, 'arm_user_plan_' . $entry_plan, true);
                                                                        $oldPlanDetail = $planData['arm_current_plan_detail'];
                                                                        if (!empty($oldPlanDetail)) {
                                                                            $plan = new ARM_Plan(0);
                                                                            $plan->init((object) $oldPlanDetail);
                                                                            $plan_data = $plan->prepare_recurring_data($payment_cycle);
                                                                            $extraParam['plan_amount'] = $plan_data['amount'];
                                                                        }
                                                                    } else {
                                                                        $plan_data = $new_plan->prepare_recurring_data($payment_cycle);
                                                                        $extraParam['plan_amount'] = $plan_data['amount'];
                                                                    }
                                                                } else {
                                                                    $plan_data = $new_plan->prepare_recurring_data($payment_cycle);
                                                                    $extraParam['plan_amount'] = $plan_data['amount'];
                                                                }
                                                            } else {
                                                               
                                                                $extraParam['plan_amount'] = $new_plan->amount;
                                                            }

                                                            $couponCode = isset($entry_values['arm_coupon_code']) ? $entry_values['arm_coupon_code'] : '';
                                                            $arm_coupon_discount = 0;
                                                            if (!empty($couponCode)) {
                                                                $couponApply = $arm_manage_coupons->arm_apply_coupon_code($couponCode, $new_plan, $setup_id, $payment_cycle, $arm_user_old_plan);
                                                                $coupon_amount = isset($couponApply['coupon_amt']) ? $couponApply['coupon_amt'] : 0;
                                                                $arm_coupon_on_each_subscriptions = isset($couponApply['arm_coupon_on_each_subscriptions']) ? $couponApply['arm_coupon_on_each_subscriptions'] : 0;


                                                                if ($coupon_amount != 0) {
                                                                    $extraParam['coupon'] = array(
                                                                        'coupon_code' => $couponCode,
                                                                        'amount' => $coupon_amount,
                                                                        'arm_coupon_on_each_subscriptions' => $arm_coupon_on_each_subscriptions,
                                                                    );

                                                                    $arm_coupon_discount = $couponApply['discount'];
                                                                    $global_currency = $arm_payment_gateways->arm_get_global_currency();
                                                                    $arm_coupon_discount_type = ($couponApply['discount_type'] != 'percentage') ? $global_currency : "%";
                                                                    $stripelog->coupon_code = $couponCode;
                                                                    $stripelog->arm_coupon_discount = $arm_coupon_discount;
                                                                    $stripelog->arm_coupon_discount_type = $arm_coupon_discount_type;
                                                                    $stripelog->arm_coupon_on_each_subscriptions = $arm_coupon_on_each_subscriptions;
                                                                }
                                                            }

                                                            $defaultPlanData = $arm_subscription_plans->arm_default_plan_array();
                                                            $userPlanDatameta = get_user_meta($user_id, 'arm_user_plan_' . $entry_plan, true);
                                                            $userPlanDatameta = !empty($userPlanDatameta) ? $userPlanDatameta : array();
                                                            $userPlanData = shortcode_atts($defaultPlanData, $userPlanDatameta);
                                                            
                                                            $create_new_user = false;

                                                            if (!$user_info && in_array($armform->type, array('registration'))) {
                                                                
                                                                $payment_done = array();
                                                                if ($payment_log_id) {
                                                                    $payment_done = array('status' => TRUE, 'log_id' => $payment_log_id, 'entry_id' => $entry_id);
                                                                }
                                                                $entry_values['payment_done'] = '1';
                                                                $entry_values['arm_entry_id'] = $entry_id;
                                                                $entry_values['arm_update_user_from_profile'] = 0;

                                                                $create_new_user = true;
                                                            } else {

                                                                $user_id = $user_info->ID;
                                                                if (!empty($user_id)) {
                                                                    global $is_multiple_membership_feature;
                                                                    
                                                                    $arm_is_paid_post = false;
                                                                    if( !empty( $entry_values['arm_is_post_entry'] ) && !empty( $entry_values['arm_paid_post_id'] ) ){
                                                                        $arm_is_paid_post = true;
                                                                    }

                                                                    if (!$is_multiple_membership_feature->isMultipleMembershipFeature && !$arm_is_paid_post ) {
                                                                        
                                                                        $old_plan_ids = get_user_meta($user_id, 'arm_user_plan_ids', true);
                                                                        $old_plan_id = isset($old_plan_ids[0]) ? $old_plan_ids[0] : 0;
                                                                        $oldPlanDetail = array();
                                                                        $old_subscription_id = '';
                                                                        if (!empty($old_plan_id)) {
                                                                            $oldPlanData = get_user_meta($user_id, 'arm_user_plan_' . $old_plan_id, true);
                                                                            $oldPlanData = !empty($oldPlanData) ? $oldPlanData : array();
                                                                            $oldPlanData = shortcode_atts($defaultPlanData, $oldPlanData);
                                                                            $oldPlanDetail = $oldPlanData['arm_current_plan_detail'];
                                                                            $subscr_effective = $oldPlanData['arm_expire_plan'];
                                                                            $old_subscription_id = $oldPlanData['arm_subscr_id'];
                                                                        }
                                                                        
                                                                        $arm_user_old_plan_details = (isset($userPlanData['arm_current_plan_detail']) && !empty($userPlanData['arm_current_plan_detail'])) ? $userPlanData['arm_current_plan_detail'] : array();
                                                                        $arm_user_old_plan_details['arm_user_old_payment_mode'] = $userPlanData['arm_payment_mode'];

                                                                        if (!empty($old_subscription_id) && $entry_values['arm_selected_payment_mode'] == 'auto_debit_subscription' && $arm_token == $old_subscription_id) {

                                                                            
                                                                            $arm_next_due_payment_date = $userPlanData['arm_next_due_payment'];
                                                                            if (!empty($arm_next_due_payment_date)) {
                                                                                if (strtotime(current_time('mysql')) >= $arm_next_due_payment_date) {
                                                                                    $arm_user_completed_recurrence = $userPlanData['arm_completed_recurring'];
                                                                                    $arm_user_completed_recurrence++;
                                                                                    $userPlanData['arm_completed_recurring'] = $arm_user_completed_recurrence;
                                                                                    update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);
                                                                                    $arm_next_payment_date = $arm_members_class->arm_get_next_due_date($user_id, $entry_plan, false, $payment_cycle);
                                                                                    if ($arm_next_payment_date != '') {
                                                                                        $userPlanData['arm_next_due_payment'] = $arm_next_payment_date;
                                                                                        update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);
                                                                                    }

                                                                                   
                                                                                }
                                                                                else{

                                                                                        $now = current_time('mysql');
                                                                                        $arm_last_payment_status = $wpdb->get_var($wpdb->prepare("SELECT `arm_transaction_status` FROM `" . $ARMember->tbl_arm_payment_log . "` WHERE `arm_user_id`=%d AND `arm_plan_id`=%d AND `arm_created_date`<=%s ORDER BY `arm_log_id` DESC LIMIT 0,1", $user_id, $entry_plan, $now));

                                                                                           if(in_array($arm_last_payment_status, array('success','pending'))){
                                                                                            $arm_user_completed_recurrence = $userPlanData['arm_completed_recurring'];
                                                                                                $arm_user_completed_recurrence++;
                                                                                                $userPlanData['arm_completed_recurring'] = $arm_user_completed_recurrence;
                                                                                                update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);
                                                                                                $arm_next_payment_date = $arm_members_class->arm_get_next_due_date($user_id, $entry_plan, false, $payment_cycle);
                                                                                                if ($arm_next_payment_date != '') {
                                                                                                    $userPlanData['arm_next_due_payment'] = $arm_next_payment_date;
                                                                                                    update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);
                                                                                                }
                                                                                            
                                                                                        }
                                                                                    }
                                                                            }

                                                                            $suspended_plan_ids = get_user_meta($user_id, 'arm_user_suspended_plan_ids', true);
                                                                            $suspended_plan_id = (isset($suspended_plan_ids) && !empty($suspended_plan_ids)) ? $suspended_plan_ids : array();

                                                                            if (in_array($entry_plan, $suspended_plan_id)) {
                                                                                unset($suspended_plan_id[array_search($entry_plan, $suspended_plan_id)]);
                                                                                update_user_meta($user_id, 'arm_user_suspended_plan_ids', array_values($suspended_plan_id));
                                                                            }
                                                                        } else {

                                                                            $now = current_time('mysql');
                                                                            $arm_last_payment_status = $wpdb->get_var($wpdb->prepare("SELECT `arm_transaction_status` FROM `" . $ARMember->tbl_arm_payment_log . "` WHERE `arm_user_id`=%d AND `arm_plan_id`=%d AND `arm_created_date`<=%s ORDER BY `arm_log_id` DESC LIMIT 0,1", $user_id, $entry_plan, $now));
                                                                            

                                                                            $userPlanData['arm_current_plan_detail'] = $arm_user_old_plan_details;

                                                                            $userPlanData['arm_payment_mode'] = $entry_values['arm_selected_payment_mode'];
                                                                            $userPlanData['arm_payment_cycle'] = $entry_values['arm_selected_payment_cycle'];



                                                                            if (!empty($oldPlanDetail)) {
                                                                                $old_plan = new ARM_Plan(0);
                                                                                $old_plan->init((object) $oldPlanDetail);
                                                                            } else {
                                                                                $old_plan = new ARM_Plan($old_plan_id);
                                                                            }
                                                                            $is_update_plan = true;
                                                                            

                                                                            $recurring_data = $new_plan->prepare_recurring_data($payment_cycle);
                                                                            if (!empty($recurring_data['trial']) && empty($arm_user_old_plan)) {
                                                                                $extraParam['trial'] = array(
                                                                                    'amount' => $recurring_data['trial']['amount'],
                                                                                    'period' => $recurring_data['trial']['period'],
                                                                                    'interval' => $recurring_data['trial']['interval'],
                                                                                   
                                                                                );
                                                                                $extraParam['tax_amount'] = isset( $charge_details['tax_amount'] ) ? $charge_details['tax_amount'] : 0;
                                                                            }
                                                                            if( $arm_coupon_discount > 0){
                                                                                $extraParam['tax_amount'] = isset( $charge_details['tax_amount'] ) ? $charge_details['tax_amount'] : 0;
                                                                            }
                                                                            if ($old_plan->exists()) {
                                                                                if ($old_plan->is_lifetime() || $old_plan->is_free() || ($old_plan->is_recurring() && $new_plan->is_recurring())) {
                                                                                    $is_update_plan = true;
                                                                                } else {
                                                                                    $change_act = 'immediate';
                                                                                    if ($old_plan->enable_upgrade_downgrade_action == 1) {
                                                                                        if (!empty($old_plan->downgrade_plans) && in_array($new_plan->ID, $old_plan->downgrade_plans)) {
                                                                                            $change_act = $old_plan->downgrade_action;
                                                                                        }
                                                                                        if (!empty($old_plan->upgrade_plans) && in_array($new_plan->ID, $old_plan->upgrade_plans)) {
                                                                                            $change_act = $old_plan->upgrade_action;
                                                                                        }
                                                                                    }
                                                                                    if ($change_act == 'on_expire' && !empty($subscr_effective)) {
                                                                                        $is_update_plan = false;
                                                                                        $oldPlanData['arm_subscr_effective'] = $subscr_effective;
                                                                                        $oldPlanData['arm_change_plan_to'] = $entry_plan;
                                                                                        update_user_meta($user_id, 'arm_user_plan_' . $old_plan_id, $oldPlanData);
                                                                                    }
                                                                                }
                                                                            }

                                                                            update_user_meta($user_id, 'arm_entry_id', $entry_id);
                                                                            $userPlanData['arm_user_gateway'] = 'stripe';

                                                                            if (!empty($arm_token)) {
                                                                                $userPlanData['arm_subscr_id'] = $arm_token;
                                                                            }
                                                                            update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);
                                                                            if ($is_update_plan) {
                                                                               
                                                                                $arm_subscription_plans->arm_update_user_subscription($user_id, $entry_plan, '', true, $arm_last_payment_status);
                                                                            } else {
                                                                                
                                                                                $arm_subscription_plans->arm_add_membership_history($user_id, $entry_plan, 'change_subscription');
                                                                            }
                                                                        }
                                                                    } else {
                                                                        
                                                                        $old_plan_ids = get_user_meta($user_id, 'arm_user_plan_ids', true);

                                                                        $oldPlanDetail = array();
                                                                        $old_subscription_id = '';
                                                                        
                                                                        if (in_array($entry_plan, $old_plan_ids)) {

                                                                           
                                                                            $oldPlanData = get_user_meta($user_id, 'arm_user_plan_' . $entry_plan, true);
                                                                            $oldPlanDetail = $oldPlanData['arm_current_plan_detail'];
                                                                            $subscr_effective = $oldPlanData['arm_expire_plan'];
                                                                            $old_subscription_id = $oldPlanData['arm_subscr_id'];
                                                                            
                                                                            $arm_user_old_plan_details = (isset($userPlanData['arm_current_plan_detail']) && !empty($userPlanData['arm_current_plan_detail'])) ? $userPlanData['arm_current_plan_detail'] : array();
                                                                            $arm_user_old_plan_details['arm_user_old_payment_mode'] = $userPlanData['arm_payment_mode'];
                                                                            if (!empty($old_subscription_id) && $entry_values['arm_selected_payment_mode'] == 'auto_debit_subscription' && $arm_token == $old_subscription_id) {
                                                                               
                                                                                $userPlanData['arm_payment_mode'] = $entry_values['arm_selected_payment_mode'];
                                                                                $userPlanData['arm_payment_cycle'] = $entry_values['arm_selected_payment_cycle'];

                                                                                $is_update_plan = true;
                                                                                

                                                                                $recurring_data = $new_plan->prepare_recurring_data($payment_cycle);
                                                                                if (!empty($recurring_data['trial']) && empty($arm_user_old_plan)) {
                                                                                    $extraParam['trial'] = array(
                                                                                        'amount' => $recurring_data['trial']['amount'],
                                                                                        'period' => $recurring_data['trial']['period'],
                                                                                        'interval' => $recurring_data['trial']['interval'],
                                                                                    );
                                                                                    $extraParam['tax_amount'] = isset( $charge_details['tax_amount'] ) ? $charge_details['tax_amount'] : 0;
                                                                                }

                                                                                if( $arm_coupon_discount > 0){
                                                                                    $extraParam['tax_amount'] = isset( $charge_details['tax_amount'] ) ? $charge_details['tax_amount'] : 0;
                                                                                }

                                                                                update_user_meta($user_id, 'arm_entry_id', $entry_id);
                                                                                $userPlanData['arm_user_gateway'] = 'stripe';

                                                                                if (!empty($arm_token)) {
                                                                                    $userPlanData['arm_subscr_id'] = $arm_token;
                                                                                }
                                                                                update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);
                                                                                if ($is_update_plan) {
                                                                                    $arm_subscription_plans->arm_update_user_subscription($user_id, $entry_plan);
                                                                                } else {
                                                                                    $arm_subscription_plans->arm_add_membership_history($user_id, $entry_plan, 'new_subscription');
                                                                                }
                                                                            } else {
                                                                                $now = current_time('mysql');
                                                                                $arm_last_payment_status = $wpdb->get_var($wpdb->prepare("SELECT `arm_transaction_status` FROM `" . $ARMember->tbl_arm_payment_log . "` WHERE `arm_user_id`=%d AND `arm_plan_id`=%d AND `arm_created_date`<=%s ORDER BY `arm_log_id` DESC LIMIT 0,1", $user_id, $entry_plan, $now));
                                                                                

                                                                                $userPlanData['arm_current_plan_detail'] = $arm_user_old_plan_details;

                                                                                $userPlanData['arm_payment_mode'] = $entry_values['arm_selected_payment_mode'];
                                                                                $userPlanData['arm_payment_cycle'] = $entry_values['arm_selected_payment_cycle'];



                                                                                if (!empty($oldPlanDetail)) {
                                                                                    $old_plan = new ARM_Plan(0);
                                                                                    $old_plan->init((object) $oldPlanDetail);
                                                                                } else {
                                                                                    $old_plan = new ARM_Plan($old_plan_id);
                                                                                }
                                                                                $is_update_plan = true;
                                                                                

                                                                                $recurring_data = $new_plan->prepare_recurring_data($payment_cycle);
                                                                                if (!empty($recurring_data['trial']) && empty($arm_user_old_plan)) {
                                                                                    $extraParam['trial'] = array(
                                                                                        'amount' => $recurring_data['trial']['amount'],
                                                                                        'period' => $recurring_data['trial']['period'],
                                                                                        'interval' => $recurring_data['trial']['interval'],
                                                                                       
                                                                                    );
                                                                                    $extraParam['tax_amount'] = isset( $charge_details['tax_amount'] ) ? $charge_details['tax_amount'] : 0;
                                                                                }
                                                                                if( $arm_coupon_discount > 0){
                                                                                    $extraParam['tax_amount'] = isset( $charge_details['tax_amount'] ) ? $charge_details['tax_amount'] : 0;
                                                                                }
                                                                                if ($old_plan->exists()) {
                                                                                    if ($old_plan->is_lifetime() || $old_plan->is_free() || ($old_plan->is_recurring() && $new_plan->is_recurring())) {
                                                                                        $is_update_plan = true;
                                                                                    } else {
                                                                                        $change_act = 'immediate';
                                                                                        if ($old_plan->enable_upgrade_downgrade_action == 1) {
                                                                                            if (!empty($old_plan->downgrade_plans) && in_array($new_plan->ID, $old_plan->downgrade_plans)) {
                                                                                                $change_act = $old_plan->downgrade_action;
                                                                                            }
                                                                                            if (!empty($old_plan->upgrade_plans) && in_array($new_plan->ID, $old_plan->upgrade_plans)) {
                                                                                                $change_act = $old_plan->upgrade_action;
                                                                                            }
                                                                                        }
                                                                                        if ($change_act == 'on_expire' && !empty($subscr_effective)) {
                                                                                            $is_update_plan = false;
                                                                                            $oldPlanData['arm_subscr_effective'] = $subscr_effective;
                                                                                            $oldPlanData['arm_change_plan_to'] = $entry_plan;
                                                                                            update_user_meta($user_id, 'arm_user_plan_' . $old_plan_id, $oldPlanData);
                                                                                        }
                                                                                    }
                                                                                }

                                                                                update_user_meta($user_id, 'arm_entry_id', $entry_id);
                                                                                $userPlanData['arm_user_gateway'] = 'stripe';

                                                                                if (!empty($arm_token)) {
                                                                                    $userPlanData['arm_subscr_id'] = $arm_token;
                                                                                }
                                                                                update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);
                                                                                if ($is_update_plan) {
                                                                                   
                                                                                    $arm_subscription_plans->arm_update_user_subscription($user_id, $entry_plan, '', true, $arm_last_payment_status);
                                                                                } else {
                                                                                    
                                                                                    $arm_subscription_plans->arm_add_membership_history($user_id, $entry_plan, 'change_subscription');
                                                                                }
                                                                                $suspended_plan_ids = get_user_meta($user_id, 'arm_user_suspended_plan_ids', true);
                                                                                $suspended_plan_id = (isset($suspended_plan_ids) && !empty($suspended_plan_ids)) ? $suspended_plan_ids : array();

                                                                                if (in_array($entry_plan, $suspended_plan_id)) {
                                                                                    unset($suspended_plan_id[array_search($entry_plan, $suspended_plan_id)]);
                                                                                    update_user_meta($user_id, 'arm_user_suspended_plan_ids', array_values($suspended_plan_id));
                                                                                }
                                                                            }
                                                                        } else {

                                                                            
                                                                            $userPlanData['arm_payment_mode'] = $entry_values['arm_selected_payment_mode'];
                                                                            $userPlanData['arm_payment_cycle'] = $entry_values['arm_selected_payment_cycle'];
                                                                            $is_update_plan = true;
                                                                            
                                                                            $recurring_data = $new_plan->prepare_recurring_data($payment_cycle);
                                                                            if (!empty($recurring_data['trial']) && empty($arm_user_old_plan)) {
                                                                                $extraParam['trial'] = array(
                                                                                    'amount' => $recurring_data['trial']['amount'],
                                                                                    'period' => $recurring_data['trial']['period'],
                                                                                    'interval' => $recurring_data['trial']['interval'],
                                                                                   
                                                                                );
                                                                                $extraParam['tax_amount'] = isset( $charge_details['tax_amount'] ) ? $charge_details['tax_amount'] : 0;
                                                                            }
                                                                            if( $arm_coupon_discount > 0){
                                                                                    $extraParam['tax_amount'] = isset( $charge_details['tax_amount'] ) ? $charge_details['tax_amount'] : 0;
                                                                                }
                                                                            update_user_meta($user_id, 'arm_entry_id', $entry_id);
                                                                            $userPlanData['arm_user_gateway'] = 'stripe';

                                                                            if (!empty($arm_token)) {
                                                                                $userPlanData['arm_subscr_id'] = $arm_token;
                                                                            }
                                                                            update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);
                                                                            if ($is_update_plan) {
                                                                               
                                                                                $arm_subscription_plans->arm_update_user_subscription($user_id, $entry_plan);
                                                                            } else {
                                                                                
                                                                                $arm_subscription_plans->arm_add_membership_history($user_id, $entry_plan, 'new_subscription');
                                                                            }
                                                                        }
                                                                    }
                                                                    $is_log = true;
                                                                    
                                                                }
                                                            }

                                                            $stripe_response = $stripelog;

                                                            $plan_id = $entry_plan;
                                                            $payer_email = $entry_email;
                                                            $extraVars = $extraParam;

                                                            $custom_var = $subscription_data->metadata->custom;
                                                            $customs = explode('|', $custom_var);
                                                            $entry_id = $customs[0];
                                                            $entry_email = $customs[1];
                                                            $form_id = $customs[2];
                                                            $arm_payment_type = $customs[3];
                                                            $tax_percentage = isset($subscription_data->metadata->tax_percentage) ? $subscription_data->metadata->tax_percentage : 0;
                                                            $zero_demial_currencies = $arm_payment_gateways->arm_stripe_zero_decimal_currency_array();

                                                            if (!empty($subscription_data->plan) && $subscription_data->object == 'subscription') {
                                                                
                                                                $amount = $subscription_data->plan->amount;
                                                                $currency = strtoupper($subscription_data->plan->currency);
                                                                if (!in_array($currency, $zero_demial_currencies)) {
                                                                     $amount = $subscription_data->plan->amount / 100; 
                                                                }

                                                                $arm_payment_date = date('Y-m-d H:i:s', $subscription_data->current_period_start);
                                                                $arm_token = $subscription_data->customer;
                                                                $arm_payment_type = 'subscription';

                                                                if( $subscription_data->discount != null  && $subscription_data->discount != 'null') {
                                                                    if( isset($subscription_data->discount->coupon)) {
                                                                        if($subscription_data->discount->coupon->amount_off != null && $subscription_data->discount->coupon->amount_off != 'null') {

                                                                            $amount_off = $subscription_data->discount->coupon->amount_off;
                                                                          
                                                                            if($amount_off > 0) {

                                                                                if (!in_array($currency, $zero_demial_currencies)) {
                                                                                    $amount_off = $amount_off/100;
                                                                                }

                                                                                $amount = $amount - $amount_off;
                                                                            }
                                                                        }
                                                                        else if($subscription_data->discount->coupon->percent_off != null && $subscription_data->discount->coupon->percent_off != 'null') {
                                                                            $percent_off = $subscription_data->discount->coupon->percent_off;
                                                                                
                                                                            if($percent_off > 0) {

                                                                                $coupon_amount = ($amount*$percent_off)/100;
                                                                                $coupon_amount = number_format((float)$coupon_amount, 2, '.', '');
                                                                                $amount = $amount - $coupon_amount;
                                                                            }
                                                                        }
                                                                    }
                                                                }

                                                                if($tax_percentage > 0) {
                                                                    $tax_amount = ($amount*$tax_percentage)/100;
                                                                    $tax_amount = number_format((float)$tax_amount, 2, '.', '');
                                                                    $amount = $tax_amount + $amount;
                                                                }
                                                            } else {
                                                                
                                                                $currency = strtoupper($stripe_response->currency);
                                                                $amount = $stripe_response->amount_paid;
                                                                if (!in_array($currency, $zero_demial_currencies)) {
                                                                    $amount = $stripe_response->amount_paid / 100;
                                                                }

                                                                if( !empty($stripe_response->created) ) {
                                                                    $arm_payment_date = date('Y-m-d H:i:s', $stripe_response->created);
                                                                }
                                                                else {
                                                                    $arm_payment_date = date('Y-m-d H:i:s');
                                                                }

                                                                $arm_token = $charge_data->source->id;
                                                                $arm_payment_type = 'subscription';
                                                            }

                                                            $coupon_code = '';
                                                            $coupon_discount = 0;
                                                            $coupon_discount_type = '';
                                                            $arm_coupon_on_each_subscriptions = '0';
                                                            if (isset($coupon_details) && !empty($coupon_details)) {
                                                                $coupon_code = $coupon_details['coupon_code'];
                                                                $coupon_discount = $coupon_details['arm_coupon_discount'];
                                                                $coupon_discount_type = $coupon_details['arm_coupon_discount_type'];
                                                                $arm_coupon_on_each_subscriptions = isset($coupon_details['arm_coupon_on_each_subscriptions']) ? $coupon_details['arm_coupon_on_each_subscriptions'] : '0';
                                                            }

                                                            if($amount < 0) {
                                                                $amount = 0;
                                                            }

                                                            $arm_response_text = array(
                                                                'invoice_data' => (array)$invoice_data,
                                                                'charge_data' => (array)$charge_data,
                                                                'subscription_data' => (array)$subscription_data
                                                            );

                                                            $payment_data = array(
                                                                'arm_user_id' => $user_id,
                                                                'arm_first_name'=> $user_info->first_name,
                                                                'arm_last_name'=> $user_info->last_name,
                                                                'arm_plan_id' => $plan_id,
                                                                'arm_payment_gateway' => 'stripe',
                                                                'arm_payment_type' => $arm_payment_type,
                                                                'arm_token' => $arm_token,
                                                                'arm_payer_email' => $payer_email,
                                                                'arm_receiver_email' => '',
                                                                'arm_transaction_id' => $subscription_id,
                                                                'arm_transaction_payment_type' => $subscription_data->object,
                                                                'arm_transaction_status' => $stripe_response->status,
                                                                'arm_payment_mode' => $payment_mode,
                                                                'arm_payment_date' => $arm_payment_date,
                                                                'arm_amount' => $amount,
                                                                'arm_currency' => $currency,
                                                                'arm_coupon_code' => $coupon_code,
                                                                'arm_coupon_discount' => $coupon_discount,
                                                                'arm_coupon_discount_type' => $coupon_discount_type,
                                                                'arm_response_text' => utf8_encode(maybe_serialize($arm_response_text)),
                                                                'arm_extra_vars' => maybe_serialize($extraVars),
                                                                'arm_is_trial' => isset($extraVars['arm_is_trial']) ? $extraVars['arm_is_trial'] : '0',
                                                                'arm_created_date' => current_time('mysql'),
                                                                'arm_coupon_on_each_subscriptions' => $arm_coupon_on_each_subscriptions,
                                                                'arm_display_log' => 1
                                                            );

                                                            
                                                            $wpdb->update(
                                                                $ARMember->tbl_arm_payment_log,
                                                                $payment_data,
                                                                array(
                                                                    'arm_log_id' => $arm_token_id
                                                                )
                                                            );

                                                            if ($arm_token_id) {
                                                                $payment_done = array('status' => TRUE, 'log_id' => $arm_token_id, 'entry_id' => $entry_id);
                                                            }

                                                            if( $create_new_user ){
                                                                $user_id = $arm_member_forms->arm_register_new_member($entry_values, $armform);
                                                            }

                                                            if (is_numeric($user_id) && !is_array($user_id)) {
                                                                
                                                                if ($arm_payment_type == 'subscription') {
                                                                    $userPlanDatameta = get_user_meta($user_id, 'arm_user_plan_' . $entry_plan, true);
                                                                    $userPlanDatameta = !empty($userPlanDatameta) ? $userPlanDatameta : array();
                                                                    $userPlanData = shortcode_atts($defaultPlanData, $userPlanDatameta);

                                                                    $userPlanData['arm_subscr_id'] = $arm_token;
                                                                    $userPlanData['arm_stripe'] = array(
                                                                        'customer_id' => $customer_id,
                                                                        'transaction_id' => $subscription_id
                                                                    );
                                                                    update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);

                                                                    $pgateway = 'stripe';
                                                                    $arm_manage_coupons->arm_coupon_apply_to_subscription($user_id, $payment_log_id, $pgateway, $userPlanData);
                                                                }
                                                                update_user_meta($user_id, 'arm_entry_id', $entry_id);
                                                            }

                                                            if($plan_action=="recurring_payment")
                                                            {
                                                                do_action('arm_after_recurring_payment_success_outside', $user_id, $entry_plan, 'stripe', $entry_values['arm_selected_payment_mode']);
                                                            }

                                                        }
                                                    }
                                                } else {
                                                    
                                                }
                                            }

                                        } else if( 0 == $invoice_data->amount_due && 0 == $invoice_data->amount_paid ) {

                                            $customs = explode('|', $subscription_data->metadata->custom);
                                            $entry_id = $customs[0];
                                            $entry_email = $customs[1];
                                            $arm_payment_type = $customs[2];

                                            //$charge_details = $invoice_data->lines->data[0];

                                            $txn_id = $subscription_id;

                                            $user_id = 0;

                                            $entry_data = $wpdb->get_row("SELECT `arm_entry_id`, `arm_entry_email`, `arm_entry_value`, `arm_form_id`, `arm_user_id`, `arm_plan_id` FROM `" . $ARMember->tbl_arm_entries . "` WHERE `arm_entry_id`='" . $entry_id . "' AND `arm_entry_email`='" . $entry_email . "'", ARRAY_A);

                                            $stripelog = new stdClass();

                                            if( !empty( $entry_data ) ){
                                                        
                                                $is_log = false;

                                                if( !empty( $charge_details ) ){

                                                    if (isset($charge_details['status']) && $charge_details['status'] == TRUE) {
                                                        $payment_done = $charge_details;
                                                        return $payment_done;
                                                    }
                                                    if (isset($charge_details['extraVars'])) {
                                                        $extraParam = $charge_details['extraVars'];
                                                        unset($charge_details['extraVars']);
                                                    }
                                                    $coupon_details = array();
                                                    if (isset($charge_details['coupon_details'])) {
                                                        $coupon_details = $charge_details['coupon_details'];
                                                    }
                                                    $charge_details['plan_action'] = $plan_action;
                                                    $charge_details['expire_date'] = $plan_expiry_date;

                                                    $charge_details['tax_percentage'] = $tax_percentage; 
                                                    $extraParam['tax_percentage'] = $tax_percentage;
                                                    $extraParam['tax_amount'] =  isset($charge_details['tax_amount'])? $charge_details['tax_amount']  : 0; 
                                                    unset($charge_details['tax_amount']);
                                                }

                                                $extraParam['plan_amount'] = $plan_data['amount'];
                                                $extraParam['paid_amount'] = $invoice_data->amount_paid;
                                                $extraParam['card_number'] = 'xxxx-xxxx-xxxx-'.$card_number;
                                                $entry_values = maybe_unserialize($entry_data['arm_entry_value']);
                                                $payment_mode = $entry_values['arm_selected_payment_mode'];
                                                $payment_cycle = $entry_values['arm_selected_payment_cycle'];
                                                $arm_user_old_plan = (isset($entry_values['arm_user_old_plan']) && !empty($entry_values['arm_user_old_plan'])) ? explode(",", $entry_values['arm_user_old_plan']) : array();
                                                $setup_id = $entry_values['setup_id'];
                                                $tax_percentage = $entry_values['tax_percentage'];

                                                $entry_plan = $entry_data['arm_plan_id'];
                                                $stripelog->arm_coupon_code = $entry_values['arm_coupon_code'];
                                                $stripelog->arm_payment_type = $arm_payment_type;
                                                $extraParam['arm_is_trial'] = '0';
                                                $extraParam['tax_percentage'] = (isset($tax_percentage) && $tax_percentage > 0) ? $tax_percentage : 0; 
                                                
                                                $user_info = get_user_by('email', $entry_email);                                                        

                                                $do_not_update_user = true;
                                                if ($user_info) {
                                                    $user_id = $user_info->ID;

                                                    $trxn_success_log_id = $wpdb->get_var("SELECT `arm_log_id` FROM `" . $ARMember->tbl_arm_payment_log . "` WHERE `arm_user_id`='" . $user_id . "' AND `arm_transaction_id`='" . $txn_id . "' AND `arm_transaction_status` = 'success' AND `arm_payment_gateway` = 'stripe'");
                                                    if($trxn_success_log_id!='')
                                                    {
                                                        $do_not_update_user = false;
                                                    }

                                                    if($do_not_update_user)
                                                    {
                                                        $log_id = $wpdb->get_var("SELECT `arm_log_id` FROM `" . $ARMember->tbl_arm_payment_log . "` WHERE `arm_user_id`='" . $user_id . "' AND `arm_transaction_id`='" . $txn_id . "' AND `arm_transaction_status` = 'pending' AND `arm_payment_gateway` = 'stripe'");

                                                        if ($log_id != '') {
                                                            $payment_history_data = array();
                                                            $payment_history_data['arm_transaction_status'] = 'success';
                                                            $field_update = $wpdb->update($ARMember->tbl_arm_payment_log, $payment_history_data, array('arm_log_id' => $log_id));
                                                            $do_not_update_user = false;
                                                        }
                                                    }
                                                }

                                                if ($do_not_update_user){
                                                    
                                                    $form_id = $entry_data['arm_form_id'];
                                                    $armform = new ARM_Form('id', $form_id);
                                                    $user_info = get_user_by('email', $entry_email);
                                                    $new_plan = new ARM_Plan($entry_plan);
                                                    $plan_action = "new_subscription";
                                                    if ($new_plan->is_recurring()) {
                                                        $plan_action = "renew_subscription";
                                                        if (in_array($entry_plan, $arm_user_old_plan)) {
                                                            $is_recurring_payment = $arm_subscription_plans->arm_is_recurring_payment_of_user($user_id, $entry_plan, $payment_mode);
                                                            if ($is_recurring_payment) {
                                                                $plan_action = 'recurring_payment';
                                                                $planData = get_user_meta($user_id, 'arm_user_plan_' . $entry_plan, true);
                                                                $oldPlanDetail = $planData['arm_current_plan_detail'];
                                                                if (!empty($oldPlanDetail)) {
                                                                    $plan = new ARM_Plan(0);
                                                                    $plan->init((object) $oldPlanDetail);
                                                                    $plan_data = $plan->prepare_recurring_data($payment_cycle);
                                                                    $extraParam['plan_amount'] = $plan_data['amount'];
                                                                }
                                                            } else {
                                                                $plan_data = $new_plan->prepare_recurring_data($payment_cycle);
                                                                $extraParam['plan_amount'] = $plan_data['amount'];
                                                            }
                                                        } else {
                                                            $plan_data = $new_plan->prepare_recurring_data($payment_cycle);
                                                            $extraParam['plan_amount'] = $plan_data['amount'];
                                                        }
                                                    } else {
                                                       
                                                        $extraParam['plan_amount'] = $new_plan->amount;
                                                    }

                                                    $couponCode = isset($entry_values['arm_coupon_code']) ? $entry_values['arm_coupon_code'] : '';
                                                    $arm_coupon_discount = 0;
                                                    if (!empty($couponCode)) {
                                                        $couponApply = $arm_manage_coupons->arm_apply_coupon_code($couponCode, $new_plan, $setup_id, $payment_cycle, $arm_user_old_plan);
                                                        $coupon_amount = isset($couponApply['coupon_amt']) ? $couponApply['coupon_amt'] : 0;
                                                        $arm_coupon_on_each_subscriptions = isset($couponApply['arm_coupon_on_each_subscriptions']) ? $couponApply['arm_coupon_on_each_subscriptions'] : 0;


                                                        if ($coupon_amount != 0) {
                                                            $extraParam['coupon'] = array(
                                                                'coupon_code' => $couponCode,
                                                                'amount' => $coupon_amount,
                                                                'arm_coupon_on_each_subscriptions' => $arm_coupon_on_each_subscriptions,
                                                            );

                                                            $arm_coupon_discount = $couponApply['discount'];
                                                            $global_currency = $arm_payment_gateways->arm_get_global_currency();
                                                            $arm_coupon_discount_type = ($couponApply['discount_type'] != 'percentage') ? $global_currency : "%";
                                                            $stripelog->coupon_code = $couponCode;
                                                            $stripelog->arm_coupon_discount = $arm_coupon_discount;
                                                            $stripelog->arm_coupon_discount_type = $arm_coupon_discount_type;
                                                            $stripelog->arm_coupon_on_each_subscriptions = $arm_coupon_on_each_subscriptions;
                                                        }
                                                    }

                                                    $defaultPlanData = $arm_subscription_plans->arm_default_plan_array();
                                                    $userPlanDatameta = get_user_meta($user_id, 'arm_user_plan_' . $entry_plan, true);
                                                    $userPlanDatameta = !empty($userPlanDatameta) ? $userPlanDatameta : array();
                                                    $userPlanData = shortcode_atts($defaultPlanData, $userPlanDatameta);
                                                    
                                                    $create_new_user = false;

                                                    if (!$user_info && in_array($armform->type, array('registration'))) {
                                                        
                                                        $payment_done = array();
                                                        if ($payment_log_id) {
                                                            $payment_done = array('status' => TRUE, 'log_id' => $payment_log_id, 'entry_id' => $entry_id);
                                                        }
                                                        $entry_values['payment_done'] = '1';
                                                        $entry_values['arm_entry_id'] = $entry_id;
                                                        $entry_values['arm_update_user_from_profile'] = 0;

                                                        $create_new_user = true;
                                                        
                                                    } else {

                                                        $user_id = $user_info->ID;
                                                        if (!empty($user_id)) {
                                                            global $is_multiple_membership_feature;
                                                            $arm_is_paid_post = false;
                                                            if( !empty( $entry_values['arm_is_post_entry'] ) && !empty( $entry_values['arm_paid_post_id'] ) ){
                                                                $arm_is_paid_post = true;
                                                            }
                                                            if ( !$is_multiple_membership_feature->isMultipleMembershipFeature && !$arm_is_paid_post ) {
                                                                
                                                                $old_plan_ids = get_user_meta($user_id, 'arm_user_plan_ids', true);
                                                                $old_plan_id = isset($old_plan_ids[0]) ? $old_plan_ids[0] : 0;
                                                                $oldPlanDetail = array();
                                                                $old_subscription_id = '';
                                                                if (!empty($old_plan_id)) {
                                                                    $oldPlanData = get_user_meta($user_id, 'arm_user_plan_' . $old_plan_id, true);
                                                                    $oldPlanData = !empty($oldPlanData) ? $oldPlanData : array();
                                                                    $oldPlanData = shortcode_atts($defaultPlanData, $oldPlanData);
                                                                    $oldPlanDetail = $oldPlanData['arm_current_plan_detail'];
                                                                    $subscr_effective = $oldPlanData['arm_expire_plan'];
                                                                    $old_subscription_id = $oldPlanData['arm_subscr_id'];
                                                                }
                                                                
                                                                $arm_user_old_plan_details = (isset($userPlanData['arm_current_plan_detail']) && !empty($userPlanData['arm_current_plan_detail'])) ? $userPlanData['arm_current_plan_detail'] : array();
                                                                $arm_user_old_plan_details['arm_user_old_payment_mode'] = $userPlanData['arm_payment_mode'];

                                                                if (!empty($old_subscription_id) && $entry_values['arm_selected_payment_mode'] == 'auto_debit_subscription' && $arm_token == $old_subscription_id) {

                                                                    
                                                                    $arm_next_due_payment_date = $userPlanData['arm_next_due_payment'];
                                                                    if (!empty($arm_next_due_payment_date)) {
                                                                        if (strtotime(current_time('mysql')) >= $arm_next_due_payment_date) {
                                                                            $arm_user_completed_recurrence = $userPlanData['arm_completed_recurring'];
                                                                            $arm_user_completed_recurrence++;
                                                                            $userPlanData['arm_completed_recurring'] = $arm_user_completed_recurrence;
                                                                            update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);
                                                                            $arm_next_payment_date = $arm_members_class->arm_get_next_due_date($user_id, $entry_plan, false, $payment_cycle);
                                                                            if ($arm_next_payment_date != '') {
                                                                                $userPlanData['arm_next_due_payment'] = $arm_next_payment_date;
                                                                                update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);
                                                                            }

                                                                           
                                                                        }
                                                                        else{

                                                                                $now = current_time('mysql');
                                                                                $arm_last_payment_status = $wpdb->get_var($wpdb->prepare("SELECT `arm_transaction_status` FROM `" . $ARMember->tbl_arm_payment_log . "` WHERE `arm_user_id`=%d AND `arm_plan_id`=%d AND `arm_created_date`<=%s ORDER BY `arm_log_id` DESC LIMIT 0,1", $user_id, $entry_plan, $now));

                                                                                   if(in_array($arm_last_payment_status, array('success','pending'))){
                                                                                    $arm_user_completed_recurrence = $userPlanData['arm_completed_recurring'];
                                                                                        $arm_user_completed_recurrence++;
                                                                                        $userPlanData['arm_completed_recurring'] = $arm_user_completed_recurrence;
                                                                                        update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);
                                                                                        $arm_next_payment_date = $arm_members_class->arm_get_next_due_date($user_id, $entry_plan, false, $payment_cycle);
                                                                                        if ($arm_next_payment_date != '') {
                                                                                            $userPlanData['arm_next_due_payment'] = $arm_next_payment_date;
                                                                                            update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);
                                                                                        }
                                                                                    
                                                                                }
                                                                            }
                                                                    }

                                                                    $suspended_plan_ids = get_user_meta($user_id, 'arm_user_suspended_plan_ids', true);
                                                                    $suspended_plan_id = (isset($suspended_plan_ids) && !empty($suspended_plan_ids)) ? $suspended_plan_ids : array();

                                                                    if (in_array($entry_plan, $suspended_plan_id)) {
                                                                        unset($suspended_plan_id[array_search($entry_plan, $suspended_plan_id)]);
                                                                        update_user_meta($user_id, 'arm_user_suspended_plan_ids', array_values($suspended_plan_id));
                                                                    }
                                                                } else {

                                                                    $now = current_time('mysql');
                                                                    $arm_last_payment_status = $wpdb->get_var($wpdb->prepare("SELECT `arm_transaction_status` FROM `" . $ARMember->tbl_arm_payment_log . "` WHERE `arm_user_id`=%d AND `arm_plan_id`=%d AND `arm_created_date`<=%s ORDER BY `arm_log_id` DESC LIMIT 0,1", $user_id, $entry_plan, $now));
                                                                    

                                                                    $userPlanData['arm_current_plan_detail'] = $arm_user_old_plan_details;

                                                                    $userPlanData['arm_payment_mode'] = $entry_values['arm_selected_payment_mode'];
                                                                    $userPlanData['arm_payment_cycle'] = $entry_values['arm_selected_payment_cycle'];



                                                                    if (!empty($oldPlanDetail)) {
                                                                        $old_plan = new ARM_Plan(0);
                                                                        $old_plan->init((object) $oldPlanDetail);
                                                                    } else {
                                                                        $old_plan = new ARM_Plan($old_plan_id);
                                                                    }
                                                                    $is_update_plan = true;
                                                                    

                                                                    $recurring_data = $new_plan->prepare_recurring_data($payment_cycle);
                                                                    if (!empty($recurring_data['trial']) && empty($arm_user_old_plan)) {
                                                                        $extraParam['trial'] = array(
                                                                            'amount' => $recurring_data['trial']['amount'],
                                                                            'period' => $recurring_data['trial']['period'],
                                                                            'interval' => $recurring_data['trial']['interval'],
                                                                           
                                                                        );
                                                                        $extraParam['tax_amount'] = isset( $charge_details['tax_amount'] ) ? $charge_details['tax_amount'] : 0;
                                                                    }
                                                                    if( $arm_coupon_discount > 0){
                                                                        $extraParam['tax_amount'] = isset( $charge_details['tax_amount'] ) ? $charge_details['tax_amount'] : 0;
                                                                    }
                                                                    if ($old_plan->exists()) {
                                                                        if ($old_plan->is_lifetime() || $old_plan->is_free() || ($old_plan->is_recurring() && $new_plan->is_recurring())) {
                                                                            $is_update_plan = true;
                                                                        } else {
                                                                            $change_act = 'immediate';
                                                                            if ($old_plan->enable_upgrade_downgrade_action == 1) {
                                                                                if (!empty($old_plan->downgrade_plans) && in_array($new_plan->ID, $old_plan->downgrade_plans)) {
                                                                                    $change_act = $old_plan->downgrade_action;
                                                                                }
                                                                                if (!empty($old_plan->upgrade_plans) && in_array($new_plan->ID, $old_plan->upgrade_plans)) {
                                                                                    $change_act = $old_plan->upgrade_action;
                                                                                }
                                                                            }
                                                                            if ($change_act == 'on_expire' && !empty($subscr_effective)) {
                                                                                $is_update_plan = false;
                                                                                $oldPlanData['arm_subscr_effective'] = $subscr_effective;
                                                                                $oldPlanData['arm_change_plan_to'] = $entry_plan;
                                                                                update_user_meta($user_id, 'arm_user_plan_' . $old_plan_id, $oldPlanData);
                                                                            }
                                                                        }
                                                                    }

                                                                    update_user_meta($user_id, 'arm_entry_id', $entry_id);
                                                                    $userPlanData['arm_user_gateway'] = 'stripe';

                                                                    if (!empty($arm_token)) {
                                                                        $userPlanData['arm_subscr_id'] = $arm_token;
                                                                    }
                                                                    update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);
                                                                    if ($is_update_plan) {
                                                                       
                                                                        $arm_subscription_plans->arm_update_user_subscription($user_id, $entry_plan, '', true, $arm_last_payment_status);
                                                                    } else {
                                                                        
                                                                        $arm_subscription_plans->arm_add_membership_history($user_id, $entry_plan, 'change_subscription');
                                                                    }
                                                                }
                                                            } else {
                                                                
                                                                $old_plan_ids = get_user_meta($user_id, 'arm_user_plan_ids', true);

                                                                $oldPlanDetail = array();
                                                                $old_subscription_id = '';
                                                                
                                                                if (in_array($entry_plan, $old_plan_ids)) {

                                                                   
                                                                    $oldPlanData = get_user_meta($user_id, 'arm_user_plan_' . $entry_plan, true);
                                                                    $oldPlanDetail = $oldPlanData['arm_current_plan_detail'];
                                                                    $subscr_effective = $oldPlanData['arm_expire_plan'];
                                                                    $old_subscription_id = $oldPlanData['arm_subscr_id'];
                                                                    
                                                                    $arm_user_old_plan_details = (isset($userPlanData['arm_current_plan_detail']) && !empty($userPlanData['arm_current_plan_detail'])) ? $userPlanData['arm_current_plan_detail'] : array();
                                                                    $arm_user_old_plan_details['arm_user_old_payment_mode'] = $userPlanData['arm_payment_mode'];
                                                                    if (!empty($old_subscription_id) && $entry_values['arm_selected_payment_mode'] == 'auto_debit_subscription' && $arm_token == $old_subscription_id) {
                                                                       
                                                                        $userPlanData['arm_payment_mode'] = $entry_values['arm_selected_payment_mode'];
                                                                        $userPlanData['arm_payment_cycle'] = $entry_values['arm_selected_payment_cycle'];

                                                                        $is_update_plan = true;
                                                                        

                                                                        $recurring_data = $new_plan->prepare_recurring_data($payment_cycle);
                                                                        if (!empty($recurring_data['trial']) && empty($arm_user_old_plan)) {
                                                                            $extraParam['trial'] = array(
                                                                                'amount' => $recurring_data['trial']['amount'],
                                                                                'period' => $recurring_data['trial']['period'],
                                                                                'interval' => $recurring_data['trial']['interval'],
                                                                            );
                                                                            $extraParam['tax_amount'] = isset( $charge_details['tax_amount'] ) ? $charge_details['tax_amount'] : 0;
                                                                        }

                                                                        if( $arm_coupon_discount > 0){
                                                                            $extraParam['tax_amount'] = isset( $charge_details['tax_amount'] ) ? $charge_details['tax_amount'] : 0;
                                                                        }

                                                                        update_user_meta($user_id, 'arm_entry_id', $entry_id);
                                                                        $userPlanData['arm_user_gateway'] = 'stripe';

                                                                        if (!empty($arm_token)) {
                                                                            $userPlanData['arm_subscr_id'] = $arm_token;
                                                                        }
                                                                        update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);
                                                                        if ($is_update_plan) {
                                                                            $arm_subscription_plans->arm_update_user_subscription($user_id, $entry_plan);
                                                                        } else {
                                                                            $arm_subscription_plans->arm_add_membership_history($user_id, $entry_plan, 'new_subscription');
                                                                        }
                                                                    } else {
                                                                        $now = current_time('mysql');
                                                                        $arm_last_payment_status = $wpdb->get_var($wpdb->prepare("SELECT `arm_transaction_status` FROM `" . $ARMember->tbl_arm_payment_log . "` WHERE `arm_user_id`=%d AND `arm_plan_id`=%d AND `arm_created_date`<=%s ORDER BY `arm_log_id` DESC LIMIT 0,1", $user_id, $entry_plan, $now));
                                                                        

                                                                        $userPlanData['arm_current_plan_detail'] = $arm_user_old_plan_details;

                                                                        $userPlanData['arm_payment_mode'] = $entry_values['arm_selected_payment_mode'];
                                                                        $userPlanData['arm_payment_cycle'] = $entry_values['arm_selected_payment_cycle'];



                                                                        if (!empty($oldPlanDetail)) {
                                                                            $old_plan = new ARM_Plan(0);
                                                                            $old_plan->init((object) $oldPlanDetail);
                                                                        } else {
                                                                            $old_plan = new ARM_Plan($old_plan_id);
                                                                        }
                                                                        $is_update_plan = true;
                                                                        

                                                                        $recurring_data = $new_plan->prepare_recurring_data($payment_cycle);
                                                                        if (!empty($recurring_data['trial']) && empty($arm_user_old_plan)) {
                                                                            $extraParam['trial'] = array(
                                                                                'amount' => $recurring_data['trial']['amount'],
                                                                                'period' => $recurring_data['trial']['period'],
                                                                                'interval' => $recurring_data['trial']['interval'],
                                                                               
                                                                            );
                                                                            $extraParam['tax_amount'] = isset( $charge_details['tax_amount'] ) ? $charge_details['tax_amount'] : 0;
                                                                        }
                                                                        if( $arm_coupon_discount > 0){
                                                                            $extraParam['tax_amount'] = isset( $charge_details['tax_amount'] ) ? $charge_details['tax_amount'] : 0;
                                                                        }
                                                                        if ($old_plan->exists()) {
                                                                            if ($old_plan->is_lifetime() || $old_plan->is_free() || ($old_plan->is_recurring() && $new_plan->is_recurring())) {
                                                                                $is_update_plan = true;
                                                                            } else {
                                                                                $change_act = 'immediate';
                                                                                if ($old_plan->enable_upgrade_downgrade_action == 1) {
                                                                                    if (!empty($old_plan->downgrade_plans) && in_array($new_plan->ID, $old_plan->downgrade_plans)) {
                                                                                        $change_act = $old_plan->downgrade_action;
                                                                                    }
                                                                                    if (!empty($old_plan->upgrade_plans) && in_array($new_plan->ID, $old_plan->upgrade_plans)) {
                                                                                        $change_act = $old_plan->upgrade_action;
                                                                                    }
                                                                                }
                                                                                if ($change_act == 'on_expire' && !empty($subscr_effective)) {
                                                                                    $is_update_plan = false;
                                                                                    $oldPlanData['arm_subscr_effective'] = $subscr_effective;
                                                                                    $oldPlanData['arm_change_plan_to'] = $entry_plan;
                                                                                    update_user_meta($user_id, 'arm_user_plan_' . $old_plan_id, $oldPlanData);
                                                                                }
                                                                            }
                                                                        }

                                                                        update_user_meta($user_id, 'arm_entry_id', $entry_id);
                                                                        $userPlanData['arm_user_gateway'] = 'stripe';

                                                                        if (!empty($arm_token)) {
                                                                            $userPlanData['arm_subscr_id'] = $arm_token;
                                                                        }
                                                                        update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);
                                                                        if ($is_update_plan) {
                                                                           
                                                                            $arm_subscription_plans->arm_update_user_subscription($user_id, $entry_plan, '', true, $arm_last_payment_status);
                                                                        } else {
                                                                            
                                                                            $arm_subscription_plans->arm_add_membership_history($user_id, $entry_plan, 'change_subscription');
                                                                        }
                                                                        $suspended_plan_ids = get_user_meta($user_id, 'arm_user_suspended_plan_ids', true);
                                                                        $suspended_plan_id = (isset($suspended_plan_ids) && !empty($suspended_plan_ids)) ? $suspended_plan_ids : array();

                                                                        if (in_array($entry_plan, $suspended_plan_id)) {
                                                                            unset($suspended_plan_id[array_search($entry_plan, $suspended_plan_id)]);
                                                                            update_user_meta($user_id, 'arm_user_suspended_plan_ids', array_values($suspended_plan_id));
                                                                        }
                                                                    }
                                                                } else {

                                                                    
                                                                    $userPlanData['arm_payment_mode'] = $entry_values['arm_selected_payment_mode'];
                                                                    $userPlanData['arm_payment_cycle'] = $entry_values['arm_selected_payment_cycle'];
                                                                    $is_update_plan = true;
                                                                    
                                                                    $recurring_data = $new_plan->prepare_recurring_data($payment_cycle);
                                                                    if (!empty($recurring_data['trial']) && empty($arm_user_old_plan)) {
                                                                        $extraParam['trial'] = array(
                                                                            'amount' => $recurring_data['trial']['amount'],
                                                                            'period' => $recurring_data['trial']['period'],
                                                                            'interval' => $recurring_data['trial']['interval'],
                                                                           
                                                                        );
                                                                        $extraParam['tax_amount'] = isset( $charge_details['tax_amount'] ) ? $charge_details['tax_amount'] : 0;
                                                                    }
                                                                    if( $arm_coupon_discount > 0){
                                                                            $extraParam['tax_amount'] = isset( $charge_details['tax_amount'] ) ? $charge_details['tax_amount'] : 0;
                                                                        }
                                                                    update_user_meta($user_id, 'arm_entry_id', $entry_id);
                                                                    $userPlanData['arm_user_gateway'] = 'stripe';

                                                                    if (!empty($arm_token)) {
                                                                        $userPlanData['arm_subscr_id'] = $arm_token;
                                                                    }
                                                                    update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);
                                                                    if ($is_update_plan) {
                                                                       
                                                                        $arm_subscription_plans->arm_update_user_subscription($user_id, $entry_plan);
                                                                    } else {
                                                                        
                                                                        $arm_subscription_plans->arm_add_membership_history($user_id, $entry_plan, 'new_subscription');
                                                                    }
                                                                }
                                                            }
                                                            $is_log = true;
                                                        }
                                                    }

                                                    $stripe_response = $stripelog;
                                                        
                                                    $plan_id = $entry_plan;
                                                    $payer_email = $entry_email;
                                                    $extraVars = $extraParam;

                                                    $custom_var = $subscription_data->metadata->custom;
                                                    $customs = explode('|', $custom_var);
                                                    $entry_id = $customs[0];
                                                    $entry_email = $customs[1];
                                                    $form_id = $customs[2];
                                                    $arm_payment_type = $customs[3];
                                                    $tax_percentage = isset($subscription_data->metadata->tax_percentage) ? $subscription_data->metadata->tax_percentage : 0;
                                                    $zero_demial_currencies = $arm_payment_gateways->arm_stripe_zero_decimal_currency_array();

                                                    if (!empty($subscription_data->plan) && $subscription_data->object == 'subscription') {
                                                        
                                                        $amount = $subscription_data->plan->amount;
                                                        $currency = strtoupper($subscription_data->plan->currency);
                                                        if (!in_array($currency, $zero_demial_currencies)) {
                                                             $amount = $subscription_data->plan->amount / 100; 
                                                        }

                                                        $arm_payment_date = date('Y-m-d H:i:s', $subscription_data->current_period_start);
                                                        $arm_token = $subscription_data->customer;
                                                        $arm_payment_type = 'subscription';

                                                        if( $subscription_data->discount != null  && $subscription_data->discount != 'null') {
                                                            if( isset($subscription_data->discount->coupon)) {
                                                                if($subscription_data->discount->coupon->amount_off != null && $subscription_data->discount->coupon->amount_off != 'null') {

                                                                    $amount_off = $subscription_data->discount->coupon->amount_off;
                                                                  
                                                                    if($amount_off > 0) {

                                                                        if (!in_array($currency, $zero_demial_currencies)) {
                                                                            $amount_off = $amount_off/100;
                                                                        }

                                                                        $amount = $amount - $amount_off;
                                                                    }
                                                                }
                                                                else if($subscription_data->discount->coupon->percent_off != null && $subscription_data->discount->coupon->percent_off != 'null') {
                                                                    $percent_off = $subscription_data->discount->coupon->percent_off;
                                                                        
                                                                    if($percent_off > 0) {

                                                                        $coupon_amount = ($amount*$percent_off)/100;
                                                                        $coupon_amount = number_format((float)$coupon_amount, 2, '.', '');
                                                                        $amount = $amount - $coupon_amount;
                                                                    }
                                                                }
                                                            }
                                                        }

                                                        if($tax_percentage > 0) {
                                                            $tax_amount = ($amount*$tax_percentage)/100;
                                                            $tax_amount = number_format((float)$tax_amount, 2, '.', '');
                                                            $amount = $tax_amount + $amount;
                                                        }
                                                    } else {
                                                        
                                                        $currency = strtoupper($stripe_response->currency);
                                                        $amount = $stripe_response->amount_paid;
                                                        if (!in_array($currency, $zero_demial_currencies)) {
                                                            $amount = $stripe_response->amount_paid / 100;
                                                        }

                                                        if( !empty($stripe_response->created) ) {
                                                            $arm_payment_date = date('Y-m-d H:i:s', $stripe_response->created);
                                                        }
                                                        else {
                                                            $arm_payment_date = date('Y-m-d H:i:s');
                                                        }

                                                        $arm_token = $customer_id;
                                                        $arm_payment_type = 'subscription';
                                                    }

                                                    $coupon_code = '';
                                                    $coupon_discount = 0;
                                                    $coupon_discount_type = '';
                                                    $arm_coupon_on_each_subscriptions = '0';
                                                    if (isset($coupon_details) && !empty($coupon_details)) {
                                                        $coupon_code = $coupon_details['coupon_code'];
                                                        $coupon_discount = $coupon_details['arm_coupon_discount'];
                                                        $coupon_discount_type = $coupon_details['arm_coupon_discount_type'];
                                                        $arm_coupon_on_each_subscriptions = isset($coupon_details['arm_coupon_on_each_subscriptions']) ? $coupon_details['arm_coupon_on_each_subscriptions'] : '0';
                                                    }

                                                    if($amount < 0) {
                                                        $amount = 0;
                                                    }

                                                    $arm_response_text = array(
                                                        'invoice_data' => (array)$invoice_data,
                                                        'charge_data' => (array)$charge_data,
                                                        'subscription_data' => (array)$subscription_data
                                                    );
                                                    $arm_first_name='';
                                                    $arm_last_name='';
                                                    if($user_id){
                                                        $user_detail = get_userdata($user_id);
                                                        $arm_first_name=$user_detail->first_name;
                                                        $arm_last_name=$user_detail->last_name;
                                                    }
                                                    $payment_data = array(
                                                        'arm_user_id' => $user_id,
                                                        'arm_first_name'=> $arm_first_name,
                                                        'arm_last_name'=> $arm_last_name,
                                                        'arm_plan_id' => $plan_id,
                                                        'arm_payment_gateway' => 'stripe',
                                                        'arm_payment_type' => $arm_payment_type,
                                                        'arm_token' => $arm_token,
                                                        'arm_payer_email' => $payer_email,
                                                        'arm_receiver_email' => '',
                                                        'arm_transaction_id' => $subscription_id,
                                                        'arm_transaction_payment_type' => $subscription_data->object,
                                                        'arm_transaction_status' => $invoice_data->status,
                                                        'arm_payment_mode' => $payment_mode,
                                                        'arm_payment_date' => $arm_payment_date,
                                                        'arm_amount' => 0,
                                                        'arm_currency' => $currency,
                                                        'arm_coupon_code' => $coupon_code,
                                                        'arm_coupon_discount' => $coupon_discount,
                                                        'arm_coupon_discount_type' => $coupon_discount_type,
                                                        'arm_response_text' => utf8_encode(maybe_serialize($arm_response_text)),
                                                        'arm_extra_vars' => maybe_serialize($extraVars),
                                                        'arm_is_trial' => isset($extraVars['arm_is_trial']) ? $extraVars['arm_is_trial'] : '0',
                                                        'arm_created_date' => current_time('mysql'),
                                                        'arm_coupon_on_each_subscriptions' => $arm_coupon_on_each_subscriptions,
                                                        'arm_display_log' => 1
                                                    );

                                                    $wpdb->update(
                                                        $ARMember->tbl_arm_payment_log,
                                                        $payment_data,
                                                        array(
                                                            'arm_log_id' => $arm_token_id
                                                        )
                                                    );

                                                    if ($arm_token_id) {
                                                        $payment_done = array('status' => TRUE, 'log_id' => $arm_token_id, 'entry_id' => $entry_id);
                                                    }

                                                    if( $create_new_user ){
                                                        $user_id = $arm_member_forms->arm_register_new_member($entry_values, $armform);
                                                    }

                                                    if (is_numeric($user_id) && !is_array($user_id)) {
                                                        
                                                        if ($arm_payment_type == 'subscription') {
                                                            $userPlanDatameta = get_user_meta($user_id, 'arm_user_plan_' . $entry_plan, true);
                                                            $userPlanDatameta = !empty($userPlanDatameta) ? $userPlanDatameta : array();
                                                            $userPlanData = shortcode_atts($defaultPlanData, $userPlanDatameta);

                                                            $userPlanData['arm_subscr_id'] = $arm_token;
                                                            $userPlanData['arm_stripe'] = array(
                                                                'customer_id' => $customer_id,
                                                                'transaction_id' => $subscription_id
                                                            );
                                                            update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);

                                                            $pgateway = 'stripe';
                                                            $arm_manage_coupons->arm_coupon_apply_to_subscription($user_id, $payment_log_id, $pgateway, $userPlanData);
                                                        }
                                                        update_user_meta($user_id, 'arm_entry_id', $entry_id);
                                                    }
                                                    if($plan_action=="recurring_payment")
                                                    {
                                                        do_action('arm_after_recurring_payment_success_outside', $user_id, $entry_plan, 'stripe', $entry_values['arm_selected_payment_mode']);
                                                    }

                                                }
                                            }
                                        }
                                    }
                                }

                            }
                        } 
                    }
                }
            }
        }

        function arm_display_update_card_button( $display, $pg, $planData ){

            if( 'stripe' == $pg ){
                global $arm_payment_gateways;
                $active_gateways = $arm_payment_gateways->arm_get_active_payment_gateways();

                $pg_options = isset( $active_gateways['stripe'] ) ? $active_gateways['stripe'] : array();

                if( isset( $pg_options['stripe_payment_method'] ) && 'popup' == $pg_options['stripe_payment_method'] ){
                    $display = true;
                }
            }

            return $display;
        }

        function arm_render_update_card_button(  $content, $pg, $planData, $user_plan, $arm_disable_button, $update_card_text ){
            
            if( 'stripe' == $pg ){
                global $ARMember, $arm_payment_gateways;

                $active_gateways = $arm_payment_gateways->arm_get_active_payment_gateways();

                $pg_options = $active_gateways['stripe'];

                if( isset( $pg_options['stripe_payment_method'] ) && 'popup' == $pg_options['stripe_payment_method'] ){

                    if( 'test' == $pg_options['stripe_payment_mode'] ){

                        $secret_key = $pg_options['stripe_test_secret_key'];
                        $stripe_publishable_key = $pg_options['stripe_test_pub_key'];

                    } else {

                        $secret_key = $pg_options['stripe_secret_key'];
                        $stripe_publishable_key = $pg_options['stripe_pub_key'];

                    }

                    $content .= '<div class="arm_cm_update_btn_div"><button type="button" class="arm_update_card_button_style arm_update_stripe_card" data-secret-key="' . base64_encode( strrev( $secret_key ) ) . '" data-plan_id="' . $user_plan . '" ' . $arm_disable_button .'>' . $update_card_text . '</button></div>';
                }
            }
            return $content;
        }


        function arm_stripe_made_update_card_function(){
            global $ARMember,$arm_payment_gateways;

            $active_gateways = $arm_payment_gateways->arm_get_active_payment_gateways();

            $pg_options = $active_gateways['stripe'];
            $customer_id = isset( $_POST['customer_id'] ) ? $_POST['customer_id'] : '';
            $token_id = isset( $_POST['token_id'] ) ? $_POST['token_id'] : '';

            $success_msg = esc_html__('Your card has been updated successfully', 'ARMember');

            if( '' == $customer_id || '' == $token_id ){
                echo 'error';
            }

            if ( 'live' == $pg_options['stripe_payment_mode'] ) {
                $stripe_secret_key = $pg_options['stripe_secret_key'];
                $stripe_pub_key = $pg_options['stripe_pub_key'];
            } else {
                $stripe_secret_key = $pg_options['stripe_test_secret_key'];
                $stripe_pub_key = $pg_options['stripe_test_pub_key'];
            }


            $api_url = 'https://api.stripe.com/v1/payment_methods/' . $token_id . '/attach';
            $headers = array(
                'Authorization' => 'Bearer '.$stripe_secret_key,
                'Content-Type' => 'application/x-www-form-urlencoded'
            );

            $request_body = 'customer=' . $customer_id;

            $update_customer = wp_remote_post(
                $api_url,
                array(
                    'headers' => $headers,
                    'timeout' => 5000,
                    'body' => $request_body
                )
            );

            $api_url = 'https://api.stripe.com/v1/customers/' . $customer_id;

            $headers = array(
                'Authorization' => 'Bearer ' . $stripe_secret_key,
                'Content-Type' => 'application/x-www-form-urlencoded'
            );

            $request_body = 'invoice_settings[default_payment_method]=' . $token_id;

            $update_customer = wp_remote_post(
                $api_url,
                array(
                    'headers' => $headers,
                    'timeout' => 5000,
                    'body' => $request_body
                )
            );

            if( is_wp_error( $update_customer ) ){
                $response = array('status' => 'error', 'type' => 'message', 'message' => json_encode( $update_customer ) );
            } else {
                $customer_data = json_decode( $update_customer['body'] );

                if( isset( $customer_data->id ) ){
                    $response = array('status' => 'success', 'type' => 'message', 'message' => $success_msg);
                } else {
                    $response = array('status' => 'error', 'type' => 'message', 'message' => json_encode( $customer_data ) );
                }
            }
            echo json_encode( $response );
            die;
        }

        function arm_update_stripe_card_function(){
            
            if( is_user_logged_in() ){
                global $wpdb, $ARMember, $arm_member_forms, $arm_transaction, $arm_payment_gateways, $arm_membership_setup;
                $arm_capabilities = '';

                $ARMember->arm_check_user_cap($arm_capabilities, '0');
                
                $plan_id = isset($_POST['plan_id']) ? intval($_POST['plan_id']) : '';
                
                $setup_id = isset($_POST['setup_id']) ? intval($_POST['setup_id']) : '';
                
                $btn_text = isset( $_POST['btn_text'] ) ? $_POST['btn_text'] : esc_html__('Update Card', 'ARMember');

                $arm_user_id = get_current_user_id();
                $setup_data = $arm_membership_setup->arm_get_membership_setup($setup_id);

                $form_in_setup = !empty($setup_data['setup_modules']['modules']['forms']) ? $setup_data['setup_modules']['modules']['forms'] : '';

                $user_form_id = !empty($form_in_setup) ? $form_in_setup : get_user_meta($arm_user_id, 'arm_form_id', true);

                
                $active_gateways = $arm_payment_gateways->arm_get_active_payment_gateways();
                $planData = get_user_meta($arm_user_id, 'arm_user_plan_' . $plan_id, true);
                $arm_user_payment_gateway = $planData['arm_user_gateway'];
                $arm_user_payment_mode = $planData['arm_payment_mode'];
                $pg_options = $active_gateways[$arm_user_payment_gateway];

                $arm_stripe_enable_debug_mode = isset($pg_options['enable_debug_mode']) ? $pg_options['enable_debug_mode'] : 0;

                if( 'stripe' == $arm_user_payment_gateway && 'auto_debit_subscription' == $arm_user_payment_mode ){

                    if ( 'live' == $pg_options['stripe_payment_mode'] ) {
                        $stripe_secret_key = $pg_options['stripe_secret_key'];
                        $stripe_pub_key = $pg_options['stripe_pub_key'];
                    } else {
                        $stripe_secret_key = $pg_options['stripe_test_secret_key'];
                        $stripe_pub_key = $pg_options['stripe_test_pub_key'];
                    }

                    $arm_user_plan_stripe_details = $planData['arm_stripe'];



                    if(!empty($arm_user_plan_stripe_details['customer_id'])){
                       $arm_user_stripe_customer_id =  $arm_user_plan_stripe_details['customer_id'];
                    }

                    $setupIntent = wp_remote_post(
                        'https://api.stripe.com/v1/setup_intents',
                        array(
                            'headers' => array(
                                'Authorization' => 'Bearer '.$stripe_secret_key
                            ),
                            'timeout' => 5000
                        )
                    );

                    if( is_wp_error( $setupIntent ) ){

                        if( isset( $arm_stripe_enable_debug_mode ) && '1' == $arm_stripe_enable_debug_mode ){
                            echo json_encode(
                                array(
                                    'type' => 'error',
                                    'message' => $setupIntent['body']
                                )
                            );
                        } else {
                            echo json_encode(
                                array(
                                    'type' => 'error',
                                    'message' => esc_html__( 'Sorry, something went wrong while processing card', 'ARMember')
                                )
                            );
                        }
                        die;
                    } else {
                        $setiObj = json_decode( $setupIntent['body'] );

                        if( $setiObj->id ){
                            $setup_intent_id = $setiObj->id;
                            $client_secret = $setiObj->client_secret;
                            $return_message = '';

                            $stripe_title = isset( $pg_options['stripe_popup_title'] ) ? $pg_options['stripe_popup_title'] : '';
                            $stripe_button_lbl = isset( $pg_options['stripe_popup_button_lbl'] ) ? $pg_options['stripe_popup_button_lbl'] : '';
                            $stripe_title = str_replace( '{arm_selected_plan_title}', $planData['arm_current_plan_detail']['arm_subscription_plan_name'], $stripe_title );

                            $stripe_logo = isset( $pg_options['stripe_popup_icon'] ) ? $pg_options['stripe_popup_icon'] : '';

                            $return_form = $this->arm_get_stripe_form( $client_secret, $stripe_pub_key, 'update_card', $stripe_title, $btn_text, $stripe_logo );

                            $return_js  = 'jQuery("body").append("' . addslashes( $return_form ) . '");';
                            $return_js .= 'var stripe = Stripe("' . $stripe_pub_key .'");';
                            $return_js .= 'var elements = stripe.elements({fonts: [{cssSrc: "https://fonts.googleapis.com/css?family=Source+Code+Pro"}],locale: window.__exampleLocale});';

                            $return_js .= "var elementStyles = { base: { color: '#32325D', fontWeight: 500, fontFamily: 'Source Code Pro, Consolas, Menlo, monospace', fontSize: '16px', fontSmoothing: 'antialiased', '::placeholder': { color: '#CFD7DF', }, ':-webkit-autofill': { color: '#e39f48',},},invalid: {color: '#E25950','::placeholder': {color: '#FFCCA5',},},};";

                            $return_js .= "var elementClasses = { focus: 'focused', empty: 'empty', invalid: 'invalid', };";

                            $return_js .= " var cardNumber = elements.create('cardNumber', { style: elementStyles, classes: elementClasses, }); cardNumber.mount('#card-number');";
                            $return_js .= " var cardExpiry = elements.create('cardExpiry', { style: elementStyles, classes: elementClasses, }); cardExpiry.mount('#card-expiry');";
                            $return_js .= " var cardCvc = elements.create('cardCvc', { style: elementStyles, classes: elementClasses, }); cardCvc.mount('#card-cvc');";

                            $return_js .= 'var cardButton = document.getElementById("update-card-button"); var clientSecret = cardButton.dataset.secret;';

                            $return_js .= 'var closeIcon = document.getElementById("stripe_wrapper_close_icon");';

                            $return_js .= 'closeIcon.addEventListener("click", function(e){
                                jQuery(".stripe_element_wrapper").remove();
                                jQuery("#arm_stripe_js").remove();
                                jQuery("#arm_stripe_css").remove();
                            });';

                            $return_js .= 'cardButton.addEventListener("click", function(e) {
                                cardButton.setAttribute("disabled","disabled");
                                cardButton.style.cursor = "not-allowed";
                                var $this = jQuery(this);
                                stripe.handleCardSetup( clientSecret, cardNumber ).then(function(result) {
                                    if (result.error) {
                                        cardButton.removeAttribute("disabled");
                                        cardButton.style.cursor = "";
                                        var errorElement = document.getElementById("card-errors");
                                        errorElement.textContent = result.error.message;
                                    } else {
                                        var errorElement = document.getElementById("card-errors");
                                        errorElement.textContent = "";
                                        var token_id = result.setupIntent.payment_method;
                                        jQuery.ajax({
                                            url:__ARMAJAXURL,
                                            type:"POST",
                                            dataType:"json",
                                            data:"action=arm_stripe_made_update_card&token_id=" + token_id +"&customer_id='.$arm_user_stripe_customer_id.'",
                                            success:function(res){
                                                var $formContainer = jQuery(".arm_current_membership_form_container.active");
                                                var message = res.message;
                                                $formContainer.removeClass("active");
                                                if (res.status == "success") {
                                                    var message = \'<div class="arm_success_msg"><ul><li>\'+ message +\'</li></ul></div>\';
                                                    
                                                    $formContainer.find(\'.arm_setup_messages\').html(message).show().delay(5000).fadeOut(2000);
                                                    jQuery(window.opera ? \'html\' : \'html, body\').animate({scrollTop: $formContainer.find(\'.arm_setup_messages\').offset().top - 50}, 1000);

                                                    jQuery(\'.stripe_element_wrapper\').remove();
                                                    jQuery(\'#arm_stripe_js\').remove();
                                                    jQuery(\'#arm_stripe_css\').remove();
                                                } else {
                                                    errorElement.textContent = message;
                                                    cardButton.removeAttribute("disabled");
                                                    cardButton.style.cursor = "";
                                                }
                                            }
                                        });
                                    }
                                });
                            });';

                            $return_message .= '<script type="text/javascript" id="arm_stripe_js">' . $return_js . '</script>';

                            echo $return_message;
                        } else {

                        }
                    }
                }
            }
            die;
        }

        function arm_enqueue_stripe_js(){
            global $arm_payment_gateways;

            $active_payment_gateways = $arm_payment_gateways->arm_get_active_payment_gateways();

            if( array_key_exists( 'stripe', $active_payment_gateways) ) {
                $pg_options = $active_payment_gateways['stripe'];
                if( isset( $pg_options['stripe_payment_method'] ) && 'popup' == $pg_options['stripe_payment_method'] ) {
                    wp_enqueue_script( 'arm_stripe_v3', 'https://js.stripe.com/v3/', array(), rand(100,999) );
                }
            }
        }

        function arm_display_cc_fields_in_update_card( $display, $user_payment_gateway ){

            if( 'stripe' == $user_payment_gateway ){
                global $arm_payment_gateways;
                $active_gateways = $arm_payment_gateways->arm_get_active_payment_gateways();

                $pg_options = $active_gateways['stripe'];

                if( isset( $pg_options['stripe_payment_method'] ) && 'fields' == $pg_options['stripe_payment_method'] ){
                    $display = true;
                }
            }

            return $display;
        }

        function arm_hide_stripe_cc_fields( $is_hide, $gateway_name, $gateway_options ){

            if( 'stripe' == $gateway_name && isset( $gateway_options['stripe_payment_method'] ) && 'popup' == $gateway_options['stripe_payment_method']  ){
                $is_hide = true;
            }

            return $is_hide;
        }

        function arm_display_cc_fields_for_setup( $isDisplay, $payment_gateway, $gateway_options ){

            if( 'stripe' == $payment_gateway && isset( $gateway_options['stripe_payment_method'] ) && 'popup' == $gateway_options['stripe_payment_method'] ){
                $isDisplay = true;
            }
            return $isDisplay;

        }

        function arm_stripe_made_charge_function(){

            global $wpdb, $ARMember, $arm_global_settings, $arm_member_forms, $payment_done, $arm_payment_gateways, $arm_subscription_plans, $arm_manage_communication, $arm_members_class, $arm_manage_coupons, $arm_stripe;

            $token_id = $_POST['token_id'];
            $entry_id = $_POST['entry_id'];
            $amount = $_POST['amount'];
            $charge_details = json_decode( stripslashes_deep( $_POST['charge_details'] ), true );
            $isSubscription = $_POST['is_subscription'];
            $stripe_plan_id = $_POST['stripe_plan_id'];

            $payment_cycle = json_decode( stripslashes_deep( $_POST['payment_cycle'] ), true );
            $plan_action = $_POST['plan_action'];
            $payment_mode = $_POST['plan_mode'];

            $is_free_plan = isset( $_POST['isPlanFreeTrail'] ) ? $_POST['isPlanFreeTrail'] : false;

            $entry_details = $wpdb->get_row( $wpdb->prepare( "SELECT arm_plan_id,arm_entry_email,arm_entry_value FROM `" . $ARMember->tbl_arm_entries . "` WHERE arm_entry_id = %d", $entry_id ) );

            $plan_id = $entry_details->arm_plan_id;
            $entry_email = $entry_details->arm_entry_email;
	    
            $entry_values = maybe_unserialize($entry_details->arm_entry_value);
            $return_url = !empty($entry_values['setup_redirect']) ? $entry_values['setup_redirect'] : ARM_HOME_URL;
	    
            $plan = new ARM_Plan($plan_id);

            $active_payment_gateways = $arm_payment_gateways->arm_get_active_payment_gateways();

            $coupon_details = array();
            if (isset($charge_details['coupon_details'])) {
                $coupon_details = $charge_details['coupon_details'];
            }

            $payment_gateway_options = $active_payment_gateways['stripe'];

            $arm_stripe_enable_debug_mode = isset($payment_gateway_options['enable_debug_mode']) ? $payment_gateway_options['enable_debug_mode'] : 0;

            if( 'test' == $payment_gateway_options['stripe_payment_mode'] ){
                $sec_key = $payment_gateway_options['stripe_test_secret_key'];
                $pub_key = $payment_gateway_options['stripe_test_pub_key'];
            } else {
                $sec_key = $payment_gateway_options['stripe_secret_key'];
                $pub_key = $payment_gateway_options['stripe_pub_key'];
            }

            $currency = $arm_payment_gateways->arm_get_global_currency();

            $headers = array(
                'Authorization' => 'Bearer '.$sec_key
            );
            $metadata_str = '';

            if( isset( $charge_details['trial_period_days'] ) ){
                $metadata_str .= '&trial_period_days=' .$charge_details['trial_period_days'];
            }

            $charge_details['metadata']['tax_percentage'] = str_replace('%', '', $charge_details['metadata']['tax_percentage']);
            $metadata_string = '';
            foreach( $charge_details['metadata'] as $mkey => $mvalue ){
                if( $isPaidTrail ){
                    if( $mkey != 'custom' ){
                        $metadata_string .= '&metadata['.$mkey.']=' . $mvalue;
                    }
                } else {
                    $metadata_str .= '&metadata['.$mkey.']=' . $mvalue;
                }
            }

            if( !empty( $coupon_details ) ){
                $coupon_code = $coupon_details['coupon_code'];

                $coupon_discount_type = $coupon_details['arm_coupon_discount_type'];
                $arm_coupon_on_each_subscriptions = isset($coupon_details['arm_coupon_on_each_subscriptions']) ? $coupon_details['arm_coupon_on_each_subscriptions'] : '0';
                $coupon_duration = "once";
                if(!empty($arm_coupon_on_each_subscriptions))
                {
                    $coupon_duration = "forever";
                }

                $coupon_uri = 'https://api.stripe.com/v1/coupons/' . $coupon_code;

                $retrieve_coupon = wp_remote_post(
                    $coupon_uri,
                    array(
                        'timeout' => 5000,
                        'headers' => $headers
                    )
                );

                if( is_wp_error( $retrieve_coupon ) ){

                } else {

                    $coupon_data = json_decode( $retrieve_coupon['body'] );

                    if( ! $coupon_data->id ){

                        $coupon_body = '';
                        if( $coupon_discount_type == '%' ){
                            $coupon_amount = str_replace(",", "", $coupon_details['arm_coupon_discount']);
                            $coupon_amount = number_format((float) $coupon_amount, 0, '', '');
                            $coupon_body = 'percent_off=' . $coupon_amount . '&duration=' . $coupon_duration . '&id=' . $coupon_code;
                        } else {
                            $coupon_amount = str_replace(",", "", $coupon_details['arm_coupon_discount']);
                            $coupon_amount = number_format((float) $coupon_amount, 2, '.', '');

                            if (!empty($coupon_amount)) {
                                $zero_demial_currencies = $arm_payment_gateways->arm_stripe_zero_decimal_currency_array();
                                if (!in_array($currency, $zero_demial_currencies)) {
                                    $coupon_amount = $coupon_amount * 100;
                                }
                                else{
                                    $coupon_amount = number_format((float) $coupon_amount, 0);
                                    $coupon_amount = str_replace(",", "", $coupon_amount);
                                }
                            }

                            $coupon_body = 'amount_off=' . $coupon_amount . '&duration=' . $coupon_duration . '&id=' . $coupon_code . '&currency=' . $currency;
                        }
                        $create_coupon = wp_remote_post(
                            'https://api.stripe.com/v1/coupons',
                            array(
                                'headers' => $headers,
                                'timeout' => 5000,
                                'body' => $coupon_body
                            )
                        );

                        if( is_wp_error( $create_coupon ) ){

                        } else {

                            $coupon_data = json_decode( $create_coupon['body'] );

                            if( ! $coupon_data->id ){

                            } else {
                                $metadata_str .= '&coupon=' . $coupon_data->id;
                            }

                        }

                    } else {
                        $coupon_created_date = $coupon_data->created;
                        $coupon_updated_date = $wpdb->get_var($wpdb->prepare("SELECT `arm_coupon_added_date` FROM  `$ARMember->tbl_arm_coupons` WHERE `arm_coupon_code` = %s", $coupon_code));
                        if (strtotime($coupon_updated_date) > $coupon_created_date) {
                            $delete_coupon = wp_remote_request(
                                'https://api.stripe.com/v1/coupons/' . $coupon_code,
                                array(
                                    'headers' => $headers,
                                    'method' => 'DELETE',
                                    'timeout' => 5000
                                )
                            );

                            if( is_wp_error( $delete_coupon ) ){

                            } else {
                                $deleted_coupon = json_decode( $delete_coupon['body'] );

                                if( $deleted_coupon->deleted ){
                                    $coupon_body = '';
                                    if( $coupon_discount_type == '%' ){
                                        $coupon_amount = str_replace(",", "", $coupon_details['arm_coupon_discount']);
                                        $coupon_amount = number_format((float) $coupon_amount, 0, '', '');
                                        $coupon_body = 'percent_off=' . $coupon_amount . '&duration=' . $coupon_duration . '&id=' . $coupon_code;
                                    } else {
                                        $coupon_amount = str_replace(",", "", $coupon_details['arm_coupon_discount']);
                                        $coupon_amount = number_format((float) $coupon_amount, 2, '.', '');

                                        if (!empty($coupon_amount)) {
                                            $zero_demial_currencies = $arm_payment_gateways->arm_stripe_zero_decimal_currency_array();
                                            if (!in_array($currency, $zero_demial_currencies)) {
                                                $coupon_amount = $coupon_amount * 100;
                                            }
                                            else{
                                                $coupon_amount = number_format((float) $coupon_amount, 0);
                                                $coupon_amount = str_replace(",", "", $coupon_amount);
                                            }
                                        }

                                        $coupon_body = 'amount_off=' . $coupon_amount . '&duration=' . $coupon_duration . '&id=' . $coupon_code . '&currency=' . $currency;
                                    }
                                    $create_coupon = wp_remote_post(
                                        'https://api.stripe.com/v1/coupons',
                                        array(
                                            'headers' => $headers,
                                            'timeout' => 5000,
                                            'body' => $coupon_body
                                        )
                                    );

                                    if( is_wp_error( $create_coupon ) ){

                                    } else {

                                        $coupon_data = json_decode( $create_coupon['body'] );

                                        if( ! $coupon_data->id ){

                                        } else {
                                            $metadata_str .= '&coupon=' . $coupon_data->id;
                                        }

                                    }
                                }
                            }
                        } else {
                            $metadata_str .= '&coupon=' . $coupon_code;
                        }
                    }

                }
            }
            $metadata_str .= '&metadata[customer_email]=' . $entry_email;
            if( $isSubscription && isset( $charge_details['metadata']['tax_percentage'] ) && $charge_details['metadata']['tax_percentage'] > 0 ) {
                $tax_data = wp_remote_post(
                    'https://api.stripe.com/v1/tax_rates',
                    array(
                        'headers' => $headers,
                        'timeout' => 5000,
                        'body' => 'display_name=Tax&inclusive=false&percentage=' . $charge_details['metadata']['tax_percentage']
                    )
                );

                if( is_wp_error( $tax_data ) ){
                    if( isset( $arm_stripe_enable_debug_mode ) && '1' == $arm_stripe_enable_debug_mode ){
                        echo json_encode(
                            array(
                                'type' => 'error',
                                'message' => $tax_data['body']
                            )
                        );
                    } else {
                        echo json_encode(
                            array(
                                'type' => 'error',
                                'message' => esc_html__('Sorry, something went wrong while processing payment', 'ARMember')
                            )
                        );
                    }
                    die;
                } else {
                    $tax_response = json_decode( $tax_data['body'] );

                    if( $tax_response->id ){
                        $metadata_str .= '&default_tax_rates[0]=' . $tax_response->id;
                    } else {
                        if( isset( $arm_stripe_enable_debug_mode ) && '1' == $arm_stripe_enable_debug_mode ){
                            echo json_encode(
                                array(
                                    'type' => 'error',
                                    'message' => $tax_data['body']
                                )
                            );
                        } else {
                            echo json_encode(
                                array(
                                    'type' => 'error',
                                    'message' => esc_html__('Sorry, something went wrong while processing payment', 'ARMember')
                                )
                            );
                        }
                        die;
                    }
                }
            }
            $stripe_response1 = array();

            if( $isSubscription ){

                $api_url = 'https://api.stripe.com/v1/customers';
                if( $is_free_plan ){
                    $request_body = "email=" . $entry_email;
                } else {
                    $request_body = "source=". $token_id ."&email=".$entry_email;
                }
                $post_data = wp_remote_post(
                    $api_url,
                    array(
                        'headers' => $headers,
                        'body' => $request_body,
                        'timeout' => 5000
                    )
                );


                if( is_wp_error(  $post_data ) ){
                    if( isset( $arm_stripe_enable_debug_mode ) && '1' == $arm_stripe_enable_debug_mode ){
                        echo json_encode(
                            array(
                                'type' => 'error',
                                'message' => $post_data['body']
                            )
                        );
                    } else {
                        echo json_encode(
                            array(
                                'type' => 'error',
                                'message' => esc_html__('Sorry, something went wrong while processing payment', 'ARMember')
                            )
                        );
                    }
                    die;
                } else {
                    $customer = json_decode( $post_data['body'] );

                    if( isset( $customer->id ) ){
                        $customer_id = $customer->id;

                        if( $is_free_plan ){
                            $api_url = 'https://api.stripe.com/v1/payment_methods/' . $token_id . '/attach';
                            $request_body = 'customer=' . $customer_id;

                            $update_customer = wp_remote_post(
                                $api_url,
                                array(
                                    'headers' => $headers,
                                    'timeout' => 5000,
                                    'body' => $request_body
                                )
                            );

                            if( is_wp_error( $update_customer ) ){
                                if( isset( $arm_stripe_enable_debug_mode ) && '1' == $arm_stripe_enable_debug_mode ){
                                    echo json_encode(
                                        array(
                                            'type' => 'error',
                                            'message' => $update_customer['body']
                                        )
                                    );
                                } else {
                                    echo json_encode(
                                        array(
                                            'type' => 'error',
                                            'message' => esc_html__( 'Sorry, something went wrong while processing payment', 'ARMember' )
                                        )
                                    );
                                }
                                die;
                            } else {
                                $api_url = 'https://api.stripe.com/v1/customers/' . $customer_id;

                                $request_body = 'invoice_settings[default_payment_method]=' . $token_id;

                                $updated_customer = wp_remote_post(
                                    $api_url,
                                    array(
                                        'headers' => $headers,
                                        'timeout' => 5000,
                                        'body' => $request_body
                                    )
                                );

                                if( is_wp_error( $updated_customer ) ){
                                    if( isset( $arm_stripe_enable_debug_mode ) && '1' == $arm_stripe_enable_debug_mode ){
                                        echo json_encode(
                                            array(
                                                'type' => 'error',
                                                'message' => $update_customer['body']
                                            )
                                        );
                                    } else {
                                        echo json_encode(
                                            array(
                                                'type' => 'error',
                                                'message' => esc_html__( 'Sorry, something went wrong while processing payment', 'ARMember' )
                                            )
                                        );
                                    }
                                    die;
                                }
                            }
                        }

                        $api_url = 'https://api.stripe.com/v1/subscriptions';
                        $request_body = 'items[0][plan]=' . $stripe_plan_id . '&customer=' . $customer_id . $metadata_str . '&expand[]=latest_invoice.payment_intent&payment_behavior=allow_incomplete';
                        
                        $sub_data = wp_remote_post(
                            $api_url,
                            array(
                                'headers' => $headers,
                                'timeout' => 5000,
                                'body' => $request_body
                            )
                        );

                        if( is_wp_error( $sub_data ) ){
                            if( isset( $arm_stripe_enable_debug_mode ) && '1' == $arm_stripe_enable_debug_mode ){
                                echo json_encode(
                                    array(
                                        'type' => 'error',
                                        'message' => $sub_data['body']
                                    )
                                );
                            } else {
                                echo json_encode(
                                    array(
                                        'type' => 'error',
                                        'message' => esc_html__( 'Sorry, something went wrong while processing payment', 'ARMember' )
                                    )
                                );
                            }
                            die;
                        } else {
                            $subscription = json_decode( $sub_data['body'] );
                           
                            if( isset( $subscription->status ) && ( 'active' == $subscription->status || 'paid' == $subscription->status || 'trialing' == $subscription->status ) ){

                                if( isset( $subscription->latest_invoice ) && isset( $subscription->latest_invoice->payment_intent ) && $subscription->latest_invoice->paid == true ){
                                    echo json_encode(
                                        array(
                                            'status' => 'active_sub',
                                            'pi_id' => $subscription->latest_invoice->payment_intent->id
                                        )
                                    );
                                } else if(!empty($subscription->status) && ($subscription->status == "trialing" && empty($subscription->latest_invoice->amount_paid)) || empty($subscription->latest_invoice->amount_paid)){
                                    //If free trial or 100% discount coupon code applied
                                    $customs = explode('|', $subscription->metadata->custom);
                                    $entry_id = $customs[0];
                                    $entry_email = $customs[1];
                                    $arm_payment_type = $customs[2];

                                    $subscription_id = $subscription->id;
                                    $txn_id = $subscription_id;

                                    $user_id = 0;

                                    $entry_data = $wpdb->get_row("SELECT `arm_entry_id`, `arm_entry_email`, `arm_entry_value`, `arm_form_id`, `arm_user_id`, `arm_plan_id` FROM `" . $ARMember->tbl_arm_entries . "` WHERE `arm_entry_id`='" . $entry_id . "' AND `arm_entry_email`='" . $entry_email . "'", ARRAY_A);

                                    $entry_values = maybe_unserialize($entry_data['arm_entry_value']);
                                    $payment_mode = $entry_values['arm_selected_payment_mode'];
                                    $payment_cycle = $entry_values['arm_selected_payment_cycle'];
                                    $arm_user_old_plan = (isset($entry_values['arm_user_old_plan']) && !empty($entry_values['arm_user_old_plan'])) ? explode(",", $entry_values['arm_user_old_plan']) : array();
                                    $setup_id = $entry_values['setup_id'];
                                    $tax_percentage = $entry_values['tax_percentage'];

                                    if( !empty( $entry_data ) ){
                                        $is_log = false;
                                        if( !empty( $charge_details ) ){
                                            if (isset($charge_details['status']) && $charge_details['status'] == TRUE) {
                                                $payment_done = $charge_details;
                                                return $payment_done;
                                            }
                                            if (isset($charge_details['extraVars'])) {
                                                $extraParam = $charge_details['extraVars'];
                                                unset($charge_details['extraVars']);
                                            }
                                            $coupon_details = array();
                                            if (isset($charge_details['coupon_details'])) {
                                                $coupon_details = $charge_details['coupon_details'];
                                            }
                                            $charge_details['plan_action'] = $plan_action;
                                            $charge_details['expire_date'] = $plan_expiry_date;

                                            $charge_details['tax_percentage'] = $tax_percentage; 
                                            $extraParam['tax_percentage'] = $tax_percentage;
                                            $extraParam['tax_amount'] =  isset($charge_details['tax_amount'])? $charge_details['tax_amount']  : 0; 
                                            unset($charge_details['tax_amount']);
                                        }

                                        

                                        $entry_plan = $entry_data['arm_plan_id'];
                                        $stripelog->arm_coupon_code = $entry_values['arm_coupon_code'];
                                        $stripelog->arm_payment_type = $arm_payment_type;
                                        $extraParam['arm_is_trial'] = '0';
                                        $extraParam['tax_percentage'] = (isset($tax_percentage) && $tax_percentage > 0) ? $tax_percentage : 0; 

                                        $user_info = get_user_by('email', $entry_email);

                                        $do_not_update_user = true;
                                                    
                                        if ($user_info) {
                                            $user_id = $user_info->ID;

                                            $trxn_success_log_id = $wpdb->get_var("SELECT `arm_log_id` FROM `" . $ARMember->tbl_arm_payment_log . "` WHERE `arm_user_id`='" . $user_id . "' AND `arm_transaction_id`='" . $txn_id . "' AND `arm_transaction_status` = 'success' AND `arm_payment_gateway` = 'stripe'");
                                            if($trxn_success_log_id!='')
                                            {
                                                $do_not_update_user = false;
                                            }

                                            if($do_not_update_user)
                                            {
                                                $log_id = $wpdb->get_var("SELECT `arm_log_id` FROM `" . $ARMember->tbl_arm_payment_log . "` WHERE `arm_user_id`='" . $user_id . "' AND `arm_transaction_id`='" . $txn_id . "' AND `arm_transaction_status` = 'pending' AND `arm_payment_gateway` = 'stripe'");

                                                if ($log_id != '') {
                                                    $payment_history_data = array();
                                                    $payment_history_data['arm_transaction_status'] = 'success';
                                                    $field_update = $wpdb->update($ARMember->tbl_arm_payment_log, $payment_history_data, array('arm_log_id' => $log_id));
                                                    $do_not_update_user = false;
                                                }
                                            }
                                        }

                                        if ($do_not_update_user){
                                                        
                                            $form_id = $entry_data['arm_form_id'];
                                            $armform = new ARM_Form('id', $form_id);
                                            $user_info = get_user_by('email', $entry_email);
                                            $new_plan = new ARM_Plan($entry_plan);
                                            $plan_action = "new_subscription";
                                            if ($new_plan->is_recurring()) {
                                                $plan_action = "renew_subscription";
                                                if (in_array($entry_plan, $arm_user_old_plan)) {
                                                    $is_recurring_payment = $arm_subscription_plans->arm_is_recurring_payment_of_user($user_id, $entry_plan, $payment_mode);
                                                    if ($is_recurring_payment) {
                                                        $plan_action = 'recurring_payment';
                                                        $planData = get_user_meta($user_id, 'arm_user_plan_' . $entry_plan, true);
                                                        $oldPlanDetail = $planData['arm_current_plan_detail'];
                                                        if (!empty($oldPlanDetail)) {
                                                            $plan = new ARM_Plan(0);
                                                            $plan->init((object) $oldPlanDetail);
                                                            $plan_data = $plan->prepare_recurring_data($payment_cycle);
                                                            $extraParam['plan_amount'] = $plan_data['amount'];
                                                        }
                                                    } else {
                                                        $plan_data = $new_plan->prepare_recurring_data($payment_cycle);
                                                        $extraParam['plan_amount'] = $plan_data['amount'];
                                                    }
                                                } else {
                                                    $plan_data = $new_plan->prepare_recurring_data($payment_cycle);
                                                    $extraParam['plan_amount'] = $plan_data['amount'];
                                                }
                                            } else {
                                               
                                                $extraParam['plan_amount'] = $new_plan->amount;
                                            }

                                            $couponCode = isset($entry_values['arm_coupon_code']) ? $entry_values['arm_coupon_code'] : '';
                                            $arm_coupon_discount = 0;
                                            if (!empty($couponCode)) {
                                                $couponApply = $arm_manage_coupons->arm_apply_coupon_code($couponCode, $new_plan, $setup_id, $payment_cycle, $arm_user_old_plan);
                                                $coupon_amount = isset($couponApply['coupon_amt']) ? $couponApply['coupon_amt'] : 0;
                                                $arm_coupon_on_each_subscriptions = isset($couponApply['arm_coupon_on_each_subscriptions']) ? $couponApply['arm_coupon_on_each_subscriptions'] : 0;


                                                if ($coupon_amount != 0) {
                                                    $extraParam['coupon'] = array(
                                                        'coupon_code' => $couponCode,
                                                        'amount' => $coupon_amount,
                                                        'arm_coupon_on_each_subscriptions' => $arm_coupon_on_each_subscriptions,
                                                    );

                                                    $arm_coupon_discount = $couponApply['discount'];
                                                    $global_currency = $arm_payment_gateways->arm_get_global_currency();
                                                    $arm_coupon_discount_type = ($couponApply['discount_type'] != 'percentage') ? $global_currency : "%";
                                                    $stripelog->coupon_code = $couponCode;
                                                    $stripelog->arm_coupon_discount = $arm_coupon_discount;
                                                    $stripelog->arm_coupon_discount_type = $arm_coupon_discount_type;
                                                    $stripelog->arm_coupon_on_each_subscriptions = $arm_coupon_on_each_subscriptions;
                                                }
                                            }

                                            $defaultPlanData = $arm_subscription_plans->arm_default_plan_array();
                                            $userPlanDatameta = get_user_meta($user_id, 'arm_user_plan_' . $entry_plan, true);
                                            $userPlanDatameta = !empty($userPlanDatameta) ? $userPlanDatameta : array();
                                            $userPlanData = shortcode_atts($defaultPlanData, $userPlanDatameta);
                                            
                                            $create_new_user = false;

                                            if (!$user_info && in_array($armform->type, array('registration'))) {

                                                $payment_done = array();
                                                if ($payment_log_id) {
                                                    $payment_done = array('status' => TRUE, 'log_id' => $payment_log_id, 'entry_id' => $entry_id);
                                                }
                                                $entry_values['payment_done'] = '1';
                                                $entry_values['arm_entry_id'] = $entry_id;
                                                $entry_values['arm_update_user_from_profile'] = 0;
                                                $create_new_user = true;
                                                
                                            } else {

                                                $user_id = $user_info->ID;
                                                
                                                if (!empty($user_id)) {
                                                    
                                                    global $is_multiple_membership_feature;

                                                    $arm_is_paid_post = false;
                                                    if( !empty( $entry_values['arm_is_post_entry'] ) && !empty( $entry_values['arm_paid_post_id'] ) ){
                                                        $arm_is_paid_post = true;
                                                    }
                                                    
                                                    if (!$is_multiple_membership_feature->isMultipleMembershipFeature && !$arm_is_paid_post ) {

                                                        $old_plan_ids = get_user_meta($user_id, 'arm_user_plan_ids', true);
                                                        $old_plan_id = isset($old_plan_ids[0]) ? $old_plan_ids[0] : 0;
                                                        $oldPlanDetail = array();
                                                        $old_subscription_id = '';
                                                        
                                                        if (!empty($old_plan_id)) {
                                                            $oldPlanData = get_user_meta($user_id, 'arm_user_plan_' . $old_plan_id, true);
                                                            $oldPlanData = !empty($oldPlanData) ? $oldPlanData : array();
                                                            $oldPlanData = shortcode_atts($defaultPlanData, $oldPlanData);
                                                            $oldPlanDetail = $oldPlanData['arm_current_plan_detail'];
                                                            $subscr_effective = $oldPlanData['arm_expire_plan'];
                                                            $old_subscription_id = $oldPlanData['arm_subscr_id'];
                                                        }
                                                        
                                                        $arm_user_old_plan_details = (isset($userPlanData['arm_current_plan_detail']) && !empty($userPlanData['arm_current_plan_detail'])) ? $userPlanData['arm_current_plan_detail'] : array();
                                                        $arm_user_old_plan_details['arm_user_old_payment_mode'] = $userPlanData['arm_payment_mode'];
                                                        
                                                        if (!empty($old_subscription_id) && $entry_values['arm_selected_payment_mode'] == 'auto_debit_subscription' && $arm_token == $old_subscription_id) {

                                                            $arm_next_due_payment_date = $userPlanData['arm_next_due_payment'];
                                                            if (!empty($arm_next_due_payment_date)) {
                                                                if (strtotime(current_time('mysql')) >= $arm_next_due_payment_date) {
                                                                    $arm_user_completed_recurrence = $userPlanData['arm_completed_recurring'];
                                                                    $arm_user_completed_recurrence++;
                                                                    $userPlanData['arm_completed_recurring'] = $arm_user_completed_recurrence;
                                                                    update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);
                                                                    $arm_next_payment_date = $arm_members_class->arm_get_next_due_date($user_id, $entry_plan, false, $payment_cycle);
                                                                    if ($arm_next_payment_date != '') {
                                                                        $userPlanData['arm_next_due_payment'] = $arm_next_payment_date;
                                                                        update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);
                                                                    }
                                                                }
                                                                else{
                                                                    $now = current_time('mysql');
                                                                    $arm_last_payment_status = $wpdb->get_var($wpdb->prepare("SELECT `arm_transaction_status` FROM `" . $ARMember->tbl_arm_payment_log . "` WHERE `arm_user_id`=%d AND `arm_plan_id`=%d AND `arm_created_date`<=%s ORDER BY `arm_log_id` DESC LIMIT 0,1", $user_id, $entry_plan, $now));

                                                                       if(in_array($arm_last_payment_status, array('success','pending'))){
                                                                        $arm_user_completed_recurrence = $userPlanData['arm_completed_recurring'];
                                                                            $arm_user_completed_recurrence++;
                                                                            $userPlanData['arm_completed_recurring'] = $arm_user_completed_recurrence;
                                                                            update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);
                                                                            $arm_next_payment_date = $arm_members_class->arm_get_next_due_date($user_id, $entry_plan, false, $payment_cycle);
                                                                            if ($arm_next_payment_date != '') {
                                                                                $userPlanData['arm_next_due_payment'] = $arm_next_payment_date;
                                                                                update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);
                                                                            }
                                                                        
                                                                    }
                                                                }
                                                            }

                                                            $suspended_plan_ids = get_user_meta($user_id, 'arm_user_suspended_plan_ids', true);
                                                            $suspended_plan_id = (isset($suspended_plan_ids) && !empty($suspended_plan_ids)) ? $suspended_plan_ids : array();

                                                            if (in_array($entry_plan, $suspended_plan_id)) {
                                                                unset($suspended_plan_id[array_search($entry_plan, $suspended_plan_id)]);
                                                                update_user_meta($user_id, 'arm_user_suspended_plan_ids', array_values($suspended_plan_id));
                                                            }
                                                        } else {

                                                            $now = current_time('mysql');
                                                            $arm_last_payment_status = $wpdb->get_var($wpdb->prepare("SELECT `arm_transaction_status` FROM `" . $ARMember->tbl_arm_payment_log . "` WHERE `arm_user_id`=%d AND `arm_plan_id`=%d AND `arm_created_date`<=%s ORDER BY `arm_log_id` DESC LIMIT 0,1", $user_id, $entry_plan, $now));
                                                            
                                                            $userPlanData['arm_current_plan_detail'] = $arm_user_old_plan_details;

                                                            $userPlanData['arm_payment_mode'] = $entry_values['arm_selected_payment_mode'];
                                                            $userPlanData['arm_payment_cycle'] = $entry_values['arm_selected_payment_cycle'];



                                                            if (!empty($oldPlanDetail)) {
                                                                $old_plan = new ARM_Plan(0);
                                                                $old_plan->init((object) $oldPlanDetail);
                                                            } else {
                                                                $old_plan = new ARM_Plan($old_plan_id);
                                                            }
                                                            $is_update_plan = true;

                                                            $recurring_data = $new_plan->prepare_recurring_data($payment_cycle);
                                                            if (!empty($recurring_data['trial']) && empty($arm_user_old_plan)) {
                                                                $extraParam['trial'] = array(
                                                                    'amount' => $recurring_data['trial']['amount'],
                                                                    'period' => $recurring_data['trial']['period'],
                                                                    'interval' => $recurring_data['trial']['interval'],
                                                                   
                                                                );
                                                                $extraParam['tax_amount'] = isset( $charge_details['tax_amount'] ) ? $charge_details['tax_amount'] : 0;
                                                            }
                                                            if( $arm_coupon_discount > 0){
                                                                $extraParam['tax_amount'] = isset( $charge_details['tax_amount'] ) ? $charge_details['tax_amount'] : 0;
                                                            }
                                                            if ($old_plan->exists()) {
                                                                if ($old_plan->is_lifetime() || $old_plan->is_free() || ($old_plan->is_recurring() && $new_plan->is_recurring())) {
                                                                    $is_update_plan = true;
                                                                } else {
                                                                    $change_act = 'immediate';
                                                                    if ($old_plan->enable_upgrade_downgrade_action == 1) {
                                                                        if (!empty($old_plan->downgrade_plans) && in_array($new_plan->ID, $old_plan->downgrade_plans)) {
                                                                            $change_act = $old_plan->downgrade_action;
                                                                        }
                                                                        if (!empty($old_plan->upgrade_plans) && in_array($new_plan->ID, $old_plan->upgrade_plans)) {
                                                                            $change_act = $old_plan->upgrade_action;
                                                                        }
                                                                    }
                                                                    if ($change_act == 'on_expire' && !empty($subscr_effective)) {
                                                                        $is_update_plan = false;
                                                                        $oldPlanData['arm_subscr_effective'] = $subscr_effective;
                                                                        $oldPlanData['arm_change_plan_to'] = $entry_plan;
                                                                        update_user_meta($user_id, 'arm_user_plan_' . $old_plan_id, $oldPlanData);
                                                                    }
                                                                }
                                                            }

                                                            update_user_meta($user_id, 'arm_entry_id', $entry_id);
                                                            $userPlanData['arm_user_gateway'] = 'stripe';

                                                            if (!empty($arm_token)) {
                                                                $userPlanData['arm_subscr_id'] = $arm_token;
                                                            }
                                                            update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);
                                                            if ($is_update_plan) {
                                                                $arm_subscription_plans->arm_update_user_subscription($user_id, $entry_plan, '', true, $arm_last_payment_status);
                                                            } else {
                                                                
                                                                $arm_subscription_plans->arm_add_membership_history($user_id, $entry_plan, 'change_subscription');
                                                            }
                                                        }
                                                        
                                                    } else {
                                                        
                                                        $old_plan_ids = get_user_meta($user_id, 'arm_user_plan_ids', true);

                                                        $oldPlanDetail = array();
                                                        $old_subscription_id = '';
                                                        if (in_array($entry_plan, $old_plan_ids)) {

                                                            $oldPlanData = get_user_meta($user_id, 'arm_user_plan_' . $entry_plan, true);
                                                            $oldPlanDetail = $oldPlanData['arm_current_plan_detail'];
                                                            $subscr_effective = $oldPlanData['arm_expire_plan'];
                                                            $old_subscription_id = $oldPlanData['arm_subscr_id'];
                                                            
                                                            $arm_user_old_plan_details = (isset($userPlanData['arm_current_plan_detail']) && !empty($userPlanData['arm_current_plan_detail'])) ? $userPlanData['arm_current_plan_detail'] : array();
                                                            $arm_user_old_plan_details['arm_user_old_payment_mode'] = $userPlanData['arm_payment_mode'];
                                                            if (!empty($old_subscription_id) && $entry_values['arm_selected_payment_mode'] == 'auto_debit_subscription' && $arm_token == $old_subscription_id) {
                                                               
                                                                $userPlanData['arm_payment_mode'] = $entry_values['arm_selected_payment_mode'];
                                                                $userPlanData['arm_payment_cycle'] = $entry_values['arm_selected_payment_cycle'];

                                                                $is_update_plan = true;

                                                                $recurring_data = $new_plan->prepare_recurring_data($payment_cycle);
                                                                if (!empty($recurring_data['trial']) && empty($arm_user_old_plan)) {
                                                                    $extraParam['trial'] = array(
                                                                        'amount' => $recurring_data['trial']['amount'],
                                                                        'period' => $recurring_data['trial']['period'],
                                                                        'interval' => $recurring_data['trial']['interval'],
                                                                    );
                                                                    $extraParam['tax_amount'] = isset( $charge_details['tax_amount'] ) ? $charge_details['tax_amount'] : 0;
                                                                }

                                                                if( $arm_coupon_discount > 0){
                                                                    $extraParam['tax_amount'] = isset( $charge_details['tax_amount'] ) ? $charge_details['tax_amount'] : 0;
                                                                }

                                                                update_user_meta($user_id, 'arm_entry_id', $entry_id);
                                                                $userPlanData['arm_user_gateway'] = 'stripe';

                                                                if (!empty($arm_token)) {
                                                                    $userPlanData['arm_subscr_id'] = $arm_token;
                                                                }
                                                                update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);
                                                                if ($is_update_plan) {
                                                                    $arm_subscription_plans->arm_update_user_subscription($user_id, $entry_plan);
                                                                } else {
                                                                    $arm_subscription_plans->arm_add_membership_history($user_id, $entry_plan, 'new_subscription');
                                                                }
                                                            } else {
                                                                $now = current_time('mysql');
                                                                $arm_last_payment_status = $wpdb->get_var($wpdb->prepare("SELECT `arm_transaction_status` FROM `" . $ARMember->tbl_arm_payment_log . "` WHERE `arm_user_id`=%d AND `arm_plan_id`=%d AND `arm_created_date`<=%s ORDER BY `arm_log_id` DESC LIMIT 0,1", $user_id, $entry_plan, $now));
                                                                

                                                                $userPlanData['arm_current_plan_detail'] = $arm_user_old_plan_details;

                                                                $userPlanData['arm_payment_mode'] = $entry_values['arm_selected_payment_mode'];
                                                                $userPlanData['arm_payment_cycle'] = $entry_values['arm_selected_payment_cycle'];



                                                                if (!empty($oldPlanDetail)) {
                                                                    $old_plan = new ARM_Plan(0);
                                                                    $old_plan->init((object) $oldPlanDetail);
                                                                } else {
                                                                    $old_plan = new ARM_Plan($old_plan_id);
                                                                }
                                                                $is_update_plan = true;

                                                                $recurring_data = $new_plan->prepare_recurring_data($payment_cycle);
                                                                if (!empty($recurring_data['trial']) && empty($arm_user_old_plan)) {
                                                                    $extraParam['trial'] = array(
                                                                        'amount' => $recurring_data['trial']['amount'],
                                                                        'period' => $recurring_data['trial']['period'],
                                                                        'interval' => $recurring_data['trial']['interval'],
                                                                       
                                                                    );
                                                                    $extraParam['tax_amount'] = isset( $charge_details['tax_amount'] ) ? $charge_details['tax_amount'] : 0;
                                                                }
                                                                if( $arm_coupon_discount > 0){
                                                                    $extraParam['tax_amount'] = isset( $charge_details['tax_amount'] ) ? $charge_details['tax_amount'] : 0;
                                                                }
                                                                if ($old_plan->exists()) {
                                                                    if ($old_plan->is_lifetime() || $old_plan->is_free() || ($old_plan->is_recurring() && $new_plan->is_recurring())) {
                                                                        $is_update_plan = true;
                                                                    } else {
                                                                        $change_act = 'immediate';
                                                                        if ($old_plan->enable_upgrade_downgrade_action == 1) {
                                                                            if (!empty($old_plan->downgrade_plans) && in_array($new_plan->ID, $old_plan->downgrade_plans)) {
                                                                                $change_act = $old_plan->downgrade_action;
                                                                            }
                                                                            if (!empty($old_plan->upgrade_plans) && in_array($new_plan->ID, $old_plan->upgrade_plans)) {
                                                                                $change_act = $old_plan->upgrade_action;
                                                                            }
                                                                        }
                                                                        if ($change_act == 'on_expire' && !empty($subscr_effective)) {
                                                                            $is_update_plan = false;
                                                                            $oldPlanData['arm_subscr_effective'] = $subscr_effective;
                                                                            $oldPlanData['arm_change_plan_to'] = $entry_plan;
                                                                            update_user_meta($user_id, 'arm_user_plan_' . $old_plan_id, $oldPlanData);
                                                                        }
                                                                    }
                                                                }

                                                                update_user_meta($user_id, 'arm_entry_id', $entry_id);
                                                                $userPlanData['arm_user_gateway'] = 'stripe';

                                                                if (!empty($arm_token)) {
                                                                    $userPlanData['arm_subscr_id'] = $arm_token;
                                                                }
                                                                update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);
                                                                if ($is_update_plan) {
                                                                   
                                                                    $arm_subscription_plans->arm_update_user_subscription($user_id, $entry_plan, '', true, $arm_last_payment_status);
                                                                } else {
                                                                    
                                                                    $arm_subscription_plans->arm_add_membership_history($user_id, $entry_plan, 'change_subscription');
                                                                }
                                                                $suspended_plan_ids = get_user_meta($user_id, 'arm_user_suspended_plan_ids', true);
                                                                $suspended_plan_id = (isset($suspended_plan_ids) && !empty($suspended_plan_ids)) ? $suspended_plan_ids : array();

                                                                if (in_array($entry_plan, $suspended_plan_id)) {
                                                                    unset($suspended_plan_id[array_search($entry_plan, $suspended_plan_id)]);
                                                                    update_user_meta($user_id, 'arm_user_suspended_plan_ids', array_values($suspended_plan_id));
                                                                }
                                                            }
                                                        } else {
                                                            
                                                            $userPlanData['arm_payment_mode'] = $entry_values['arm_selected_payment_mode'];
                                                            $userPlanData['arm_payment_cycle'] = $entry_values['arm_selected_payment_cycle'];
                                                            $is_update_plan = true;
                                                            
                                                            $recurring_data = $new_plan->prepare_recurring_data($payment_cycle);
                                                            if (!empty($recurring_data['trial']) && empty($arm_user_old_plan)) {
                                                                $extraParam['trial'] = array(
                                                                    'amount' => $recurring_data['trial']['amount'],
                                                                    'period' => $recurring_data['trial']['period'],
                                                                    'interval' => $recurring_data['trial']['interval'],
                                                                );
                                                                $extraParam['tax_amount'] = isset( $charge_details['tax_amount'] ) ? $charge_details['tax_amount'] : 0;
                                                            }
                                                            if( $arm_coupon_discount > 0){
                                                                    $extraParam['tax_amount'] = isset( $charge_details['tax_amount'] ) ? $charge_details['tax_amount'] : 0;
                                                                }
                                                            update_user_meta($user_id, 'arm_entry_id', $entry_id);
                                                            $userPlanData['arm_user_gateway'] = 'stripe';

                                                            if (!empty($arm_token)) {
                                                                $userPlanData['arm_subscr_id'] = $arm_token;
                                                            }
                                                            update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);
                                                            if ($is_update_plan) {
                                                                $arm_subscription_plans->arm_update_user_subscription($user_id, $entry_plan);
                                                            } else {
                                                                $arm_subscription_plans->arm_add_membership_history($user_id, $entry_plan, 'new_subscription');
                                                            }
                                                        }
                                                    }
                                                    $is_log = true;
                                                }
                                            }
                                            $stripe_response = $stripelog;

                                            $plan_id = $entry_plan;
                                            $payer_email = $entry_email;
                                            $extraVars = $extraParam;

                                            $custom_var = $subscription->metadata->custom;
                                            $customs = explode('|', $custom_var);
                                            $entry_id = $customs[0];
                                            $entry_email = $customs[1];
                                            $form_id = $customs[2];
                                            $arm_payment_type = $customs[3];
                                            $tax_percentage = isset($subscription->metadata->tax_percentage) ? $subscription->metadata->tax_percentage : 0;
                                            $zero_demial_currencies = $arm_payment_gateways->arm_stripe_zero_decimal_currency_array();

                                            if (!empty($subscription->plan) && $subscription->object == 'subscription') {
                                                
                                                $amount = $subscription->plan->amount;
                                                $currency = strtoupper($subscription->plan->currency);
                                                if (!in_array($currency, $zero_demial_currencies)) {
                                                     $amount = $subscription->plan->amount / 100;
                                                }

                                                $arm_payment_date = date('Y-m-d H:i:s', $subscription->current_period_start);
                                                $arm_token = $subscription->customer;
                                                $arm_payment_type = 'subscription';

                                                if( $subscription->discount != null  && $subscription->discount != 'null') {
                                                    if( isset($subscription->discount->coupon)) {
                                                        if($subscription->discount->coupon->amount_off != null && $subscription->discount->coupon->amount_off != 'null') {

                                                            $amount_off = $subscription->discount->coupon->amount_off;
                                                          
                                                            if($amount_off > 0) {

                                                                if (!in_array($currency, $zero_demial_currencies)) {
                                                                    $amount_off = $amount_off/100;
                                                                }

                                                                $amount = $amount - $amount_off;
                                                            }
                                                        }
                                                        else if($subscription->discount->coupon->percent_off != null && $subscription->discount->coupon->percent_off != 'null') {
                                                            $percent_off = $subscription->discount->coupon->percent_off;
                                                                
                                                            if($percent_off > 0) {

                                                                $coupon_amount = ($amount*$percent_off)/100;
                                                                $coupon_amount = number_format((float)$coupon_amount, 2, '.', '');
                                                                $amount = $amount - $coupon_amount;
                                                            }
                                                        }
                                                    }
                                                }

                                                if($tax_percentage > 0) {
                                                    $tax_amount = ($amount*$tax_percentage)/100;
                                                    $tax_amount = number_format((float)$tax_amount, 2, '.', '');
                                                    $amount = $tax_amount + $amount;
                                                }
                                            } else {
                                                
                                                $currency = strtoupper($stripe_response->currency);
                                                $amount = $stripe_response->amount_paid;
                                                if (!in_array($currency, $zero_demial_currencies)) {
                                                    $amount = $stripe_response->amount_paid / 100;
                                                }

                                                if( !empty($stripe_response->created) ) {
                                                    $arm_payment_date = date('Y-m-d H:i:s', $stripe_response->created);
                                                }
                                                else {
                                                    $arm_payment_date = date('Y-m-d H:i:s');
                                                }

                                                $arm_token = $charge_data->source->id;
                                                $arm_payment_type = 'subscription';
                                            }

                                            $coupon_code = '';
                                            $coupon_discount = 0;
                                            $coupon_discount_type = '';
                                            $arm_coupon_on_each_subscriptions = '0';
                                            if (isset($coupon_details) && !empty($coupon_details)) {
                                                $coupon_code = $coupon_details['coupon_code'];
                                                $coupon_discount = $coupon_details['arm_coupon_discount'];
                                                $coupon_discount_type = $coupon_details['arm_coupon_discount_type'];
                                                $arm_coupon_on_each_subscriptions = isset($coupon_details['arm_coupon_on_each_subscriptions']) ? $coupon_details['arm_coupon_on_each_subscriptions'] : '0';
                                            }

                                            if($amount < 0) {
                                                $amount = 0;
                                            }

                                            if(($subscription->discount == null  || $subscription->discount == 'null') && !empty($coupon_code) && !empty($coupon_discount) && !empty($coupon_discount_type))
                                            {
                                                if($coupon_discount_type == '%'){
                                                    $amount = $amount - (($amount * $coupon_discount)/100);
                                                }else{
                                                    $amount = $amount - $coupon_discount;
                                                }
                                            }

                                            $arm_response_text = array(
                                                'invoice_data' => (array)$invoiceObj,
                                                'charge_data' => (array)$charge_data,
                                                'subscription_data' => (array)$subscription,
                                            );

                                            $payment_data = array(
                                                'arm_user_id' => $user_id,
                                                'arm_first_name'=> $user_info->first_name,
                                                'arm_last_name'=> $user_info->last_name,
                                                'arm_plan_id' => $plan_id,
                                                'arm_payment_gateway' => 'stripe',
                                                'arm_payment_type' => $arm_payment_type,
                                                'arm_token' => $arm_token,
                                                'arm_payer_email' => $payer_email,
                                                'arm_receiver_email' => '',
                                                'arm_transaction_id' => $subscription_id,
                                                'arm_transaction_payment_type' => $subscription->object,
                                                'arm_transaction_status' => 'success',
                                                'arm_payment_mode' => $payment_mode,
                                                'arm_payment_date' => $arm_payment_date,
                                                'arm_amount' => $amount,
                                                'arm_currency' => $currency,
                                                'arm_coupon_code' => $coupon_code,
                                                'arm_coupon_discount' => $coupon_discount,
                                                'arm_coupon_discount_type' => $coupon_discount_type,
                                                'arm_response_text' => utf8_encode(maybe_serialize($arm_response_text)),
                                                'arm_extra_vars' => maybe_serialize($extraVars),
                                                'arm_is_trial' => isset($extraVars['arm_is_trial']) ? $extraVars['arm_is_trial'] : '0',
                                                'arm_created_date' => current_time('mysql'),
                                                'arm_coupon_on_each_subscriptions' => $arm_coupon_on_each_subscriptions,
                                                'arm_display_log' => 1
                                            );

                                            $payment_log_id = $arm_payment_gateways->arm_save_payment_log($payment_data);

                                            if ($payment_log_id) {
                                                $payment_done = array('status' => TRUE, 'log_id' => $payment_log_id, 'entry_id' => $entry_id);
                                            }

                                            if( $create_new_user ){
                                                $user_id = $arm_member_forms->arm_register_new_member($entry_values, $armform);
                                            }

                                            if (is_numeric($user_id) && !is_array($user_id)) {
                                                
                                                if ($arm_payment_type == 'subscription') {
                                                    $userPlanDatameta = get_user_meta($user_id, 'arm_user_plan_' . $entry_plan, true);
                                                    $userPlanDatameta = !empty($userPlanDatameta) ? $userPlanDatameta : array();
                                                    $userPlanData = shortcode_atts($defaultPlanData, $userPlanDatameta);

                                                    $userPlanData['arm_subscr_id'] = $arm_token;
                                                    $userPlanData['arm_stripe'] = array(
                                                        'customer_id' => $customer_id,
                                                        'transaction_id' => $subscription_id
                                                    );
                                                    update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);

                                                    $pgateway = 'stripe';
                                                    $arm_manage_coupons->arm_coupon_apply_to_subscription($user_id, $payment_log_id, $pgateway, $userPlanData);
                                                }
                                                update_user_meta($user_id, 'arm_entry_id', $entry_id);
                                            }
                                            if($plan_action=="recurring_payment")
                                            {
                                                do_action('arm_after_recurring_payment_success_outside', $user_id, $entry_plan, 'stripe', $entry_values['arm_selected_payment_mode']);
                                            }

                                        }
                                    }

                                    echo json_encode(
                                        array(
                                            'type' => 'redirect',
                                            'url' => $return_url
                                        )
                                    );

                                } else {
                                    $wpdb->query( $wpdb->prepare( "INSERT INTO `". $ARMember->tbl_arm_payment_log."` (arm_token,arm_payment_gateway,arm_display_log,arm_extra_vars) VALUES (%s,%s,%d,%s)", $customer_id.'|'.$subscription->id, 'stripe', 0, json_encode($charge_details) ) );
                                    
                                    echo json_encode(
                                        array(
                                            'type' => 'redirect',
                                            'url' => $return_url
                                        )
                                    );
                                }
                                die;
                            } else if( isset( $subscription->status ) && 'incomplete' == $subscription->status ){
                                $appendStart = 'rptrespstart_'.time().'(:)';
                                $appendEnd = '(:)rptrespend_'.time();
                                $sub_decoded_data = json_decode( $sub_data['body'] );
                                $response_str = json_encode(
                                    array(
                                        'status' => $sub_decoded_data->status,
                                        'secret' => $sub_decoded_data->latest_invoice->payment_intent->client_secret
                                    )
                                );
                                echo json_encode(
                                    array(
                                        'status' => 'incomplete',
                                        'message' => base64_encode( $appendStart . $response_str . $appendEnd )
                                    )
                                );
                            } else {
                                if( isset( $arm_stripe_enable_debug_mode ) && '1' == $arm_stripe_enable_debug_mode ){
                                    echo json_encode(
                                        array(
                                            'type' => 'error',
                                            'message' => $sub_data['body']
                                        )
                                    );
                                    die;
                                } else {
                                    echo json_encode(
                                        array(
                                            'type' => 'error',
                                            'message' => esc_html__('Sorry, Something went wrong while processing payment', 'ARMember')
                                        )
                                    );
                                    die;
                                }
                            }

                        }

                    } else {
                        $customer = json_decode( $post_data['body'] );
                        $error_msg = esc_html__('Sorry, something went wrong while processing payment', 'ARMember');
                        if( isset( $arm_stripe_enable_debug_mode ) && '1' == $arm_stripe_enable_debug_mode ){
                            if(!empty($customer->error->message)) {
                                $error_msg = $customer->error->message;
                            } 
                        } 
                        
                        echo json_encode(
                            array(
                                'type' => 'error',
                                'message' => $error_msg
                            )
                        );
                        die;
                    }
                }

            } else {
                $api_url = 'https://api.stripe.com/v1/charges';
                
                $metadata_str .= '&metadata[email]=' . $entry_email;

                $request_body = "amount=" . $amount . "&currency=". strtolower($currency) . "&source=" . $token_id . $metadata_str;

                $post_data = wp_remote_post(
                    $api_url,
                    array(
                        'headers' => $headers,
                        'body' => $request_body,
                        'sslverify' => false,
                        'timeout' => 5000
                    )
                );


                if( is_wp_error( $post_data ) ){
                    if( isset( $arm_stripe_enable_debug_mode ) && '1' == $arm_stripe_enable_debug_mode ){
                        echo json_encode(
                            array(
                                'type' => 'error',
                                'message' => $post_data['body']
                            )
                        );
                    } else {
                        echo json_encode(
                            array(
                                'type' => 'error',
                                'message' => esc_html__( 'Sorry, something went wrong while processing payment', 'ARMember')
                            )
                        );
                    }
                    die;
                } else {
                    
                    $charge_data = json_decode( $post_data['body'] );

                    if( isset( $charge_data->paid ) && true == $charge_data->paid ){
                        $extraVars = array();
                        if (isset($charge_details['extraVars'])) {
                            $extraVars = $charge_details['extraVars'];
                            unset($charge_details['extraVars']);
                        }

                        $custom_var = explode( '|', $charge_data->metadata->custom );

                        $entry_id = $custom_var[0];
                        $entry_email = $custom_var[1];
                        $form_id = $custom_var[2];
                        $payment_mode = $custom_var[3];

                        if ($plan_action == 'new_subscription' && $plan->is_recurring() && $payment_mode == 'auto_debit_subscription' && $plan->has_trial_period()){
                            
                        } else {

                            $card_number = $charge_data->payment_method_details->card->last4;
                            $stripelog = new stdClass();
                            foreach( $charge_data as $k => $v ){
                                $stripelog->$k = $v;
                            }
                            $txn_id = $charge_data->id;

                            $user_id = 0;

                            $entry_data = $wpdb->get_row("SELECT `arm_entry_id`, `arm_entry_email`, `arm_entry_value`, `arm_form_id`, `arm_user_id`, `arm_plan_id` FROM `" . $ARMember->tbl_arm_entries . "` WHERE `arm_entry_id`='" . $entry_id . "' AND `arm_entry_email`='" . $entry_email . "'", ARRAY_A);
                            
                            if( !empty( $entry_data ) ){
                                
                                $is_log = false;
                                $extraParam = array('plan_amount' => $charge_data->amount, 'paid_amount' => $charge_data->amount );
                                $extraParam['card_number'] = 'xxxx-xxxx-xxxx-'.$card_number;
                                $entry_values = maybe_unserialize($entry_data['arm_entry_value']);
                                $payment_mode = $entry_values['arm_selected_payment_mode'];
                                $payment_cycle = $entry_values['arm_selected_payment_cycle'];
                                $arm_user_old_plan = (isset($entry_values['arm_user_old_plan']) && !empty($entry_values['arm_user_old_plan'])) ? explode(",", $entry_values['arm_user_old_plan']) : array();
                                $setup_id = $entry_values['setup_id'];
                                $tax_percentage = $entry_values['tax_percentage'];

                                $entry_plan = $entry_data['arm_plan_id'];
                                $stripelog->arm_coupon_code = $entry_values['arm_coupon_code'];
                                $stripelog->arm_payment_type = $arm_payment_type;
                                $extraParam['arm_is_trial'] = '0';
                                $extraParam['tax_percentage'] = (isset($tax_percentage) && $tax_percentage > 0) ? $tax_percentage : 0; 

                                $user_info = get_user_by('email', $entry_email);
                                $do_not_update_user = true;
                                if ($user_info) {
                                    $user_id = $user_info->ID;

                                    $trxn_success_log_id = $wpdb->get_var("SELECT `arm_log_id` FROM `" . $ARMember->tbl_arm_payment_log . "` WHERE `arm_user_id`='" . $user_id . "' AND `arm_transaction_id`='" . $txn_id . "' AND `arm_transaction_status` = 'success' AND `arm_payment_gateway` = 'stripe'");
                                    if($trxn_success_log_id!='')
                                    {
                                        $do_not_update_user = false;
                                    }

                                    if($do_not_update_user)
                                    {
                                        $log_id = $wpdb->get_var("SELECT `arm_log_id` FROM `" . $ARMember->tbl_arm_payment_log . "` WHERE `arm_user_id`='" . $user_id . "' AND `arm_transaction_id`='" . $txn_id . "' AND `arm_transaction_status` = 'pending' AND `arm_payment_gateway` = 'stripe'");

                                        if ($log_id != '') {
                                            $payment_history_data = array();
                                            $payment_history_data['arm_transaction_status'] = 'success';
                                            $field_update = $wpdb->update($ARMember->tbl_arm_payment_log, $payment_history_data, array('arm_log_id' => $log_id));
                                            $do_not_update_user = false;
                                        }
                                    }
                                }

                                if ($do_not_update_user){

                                    $form_id = $entry_data['arm_form_id'];
                                    $armform = new ARM_Form('id', $form_id);
                                    $user_info = get_user_by('email', $entry_email);
                                    $new_plan = new ARM_Plan($entry_plan);
                                    $plan_action = "new_subscription";
                                    if ($new_plan->is_recurring()) {
                                        $plan_action = "renew_subscription";
                                        if (in_array($entry_plan, $arm_user_old_plan)) {
                                            $is_recurring_payment = $arm_subscription_plans->arm_is_recurring_payment_of_user($user_id, $entry_plan, $payment_mode);
                                            if ($is_recurring_payment) {
                                                $plan_action = 'recurring_payment';
                                                $planData = get_user_meta($user_id, 'arm_user_plan_' . $entry_plan, true);
                                                $oldPlanDetail = $planData['arm_current_plan_detail'];
                                                if (!empty($oldPlanDetail)) {
                                                    $plan = new ARM_Plan(0);
                                                    $plan->init((object) $oldPlanDetail);
                                                    $plan_data = $plan->prepare_recurring_data($payment_cycle);
                                                    $extraParam['plan_amount'] = $plan_data['amount'];
                                                }
                                            } else {
                                                $plan_data = $new_plan->prepare_recurring_data($payment_cycle);
                                                $extraParam['plan_amount'] = $plan_data['amount'];
                                            }
                                        } else {
                                            $plan_data = $new_plan->prepare_recurring_data($payment_cycle);
                                            $extraParam['plan_amount'] = $plan_data['amount'];
                                        }
                                    } else {
                                        $extraParam['plan_amount'] = $new_plan->amount;
                                    }
                                    $couponCode = isset($entry_values['arm_coupon_code']) ? $entry_values['arm_coupon_code'] : '';
                                    $arm_coupon_discount = 0;
                                    if (!empty($couponCode)) {
                                        $couponApply = $arm_manage_coupons->arm_apply_coupon_code($couponCode, $new_plan, $setup_id, $payment_cycle, $arm_user_old_plan);
                                        $coupon_amount = isset($couponApply['coupon_amt']) ? $couponApply['coupon_amt'] : 0;
                                        $arm_coupon_on_each_subscriptions = isset($couponApply['arm_coupon_on_each_subscriptions']) ? $couponApply['arm_coupon_on_each_subscriptions'] : 0;


                                        if ($coupon_amount != 0) {
                                            $extraParam['coupon'] = array(
                                                'coupon_code' => $couponCode,
                                                'amount' => $coupon_amount,
                                                'arm_coupon_on_each_subscriptions' => $arm_coupon_on_each_subscriptions,
                                            );

                                            $arm_coupon_discount = $couponApply['discount'];
                                            $global_currency = $arm_payment_gateways->arm_get_global_currency();
                                            $arm_coupon_discount_type = ($couponApply['discount_type'] != 'percentage') ? $global_currency : "%";
                                            $stripelog->coupon_code = $couponCode;
                                            $stripelog->arm_coupon_discount = $arm_coupon_discount;
                                            $stripelog->arm_coupon_discount_type = $arm_coupon_discount_type;
                                            $stripelog->arm_coupon_on_each_subscriptions = $arm_coupon_on_each_subscriptions;
                                        }
                                    }

                                    $defaultPlanData = $arm_subscription_plans->arm_default_plan_array();
                                    $userPlanDatameta = get_user_meta($user_id, 'arm_user_plan_' . $entry_plan, true);
                                    $userPlanDatameta = !empty($userPlanDatameta) ? $userPlanDatameta : array();
                                    $userPlanData = shortcode_atts($defaultPlanData, $userPlanDatameta);
                                    $create_new_user = false;
                                    if (!$user_info && in_array($armform->type, array('registration'))) {
                                        

                                        if($new_plan->is_recurring()){
                                            $recurring_data = $new_plan->prepare_recurring_data($payment_cycle);
                                            if (!empty($recurring_data['trial'])) {
                                                $extraParam['trial'] = array(
                                                    'amount' => $recurring_data['trial']['amount'],
                                                    'period' => $recurring_data['trial']['period'],
                                                    'interval' => $recurring_data['trial']['interval'],
                                                  
                                                );
                                                $extraParam['arm_is_trial'] = '1';
                                                $extraParam['tax_amount'] = isset( $charge_details['tax_amount'] ) ? $charge_details['tax_amount'] : 0;
                                                

                                            }

                                            if( $arm_coupon_discount > 0){
                                                $extraParam['tax_amount'] = isset( $charge_details['tax_amount'] ) ? $charge_details['tax_amount'] : 0;
                                            }
                                        }
                                        

                                        $payment_done = array();
                                        if ($payment_log_id) {
                                            $payment_done = array('status' => TRUE, 'log_id' => $payment_log_id, 'entry_id' => $entry_id);
                                        }
                                        $entry_values['payment_done'] = '1';
                                        $entry_values['arm_entry_id'] = $entry_id;
                                        $entry_values['arm_update_user_from_profile'] = 0;

                                        $create_new_user = true;
                                        
                                    } else {
                                        $user_id = $user_info->ID;
                                        if (!empty($user_id)) {
                                            global $is_multiple_membership_feature;
                                            $arm_is_paid_post = false;
                                            if( !empty( $entry_values['arm_is_post_entry'] ) && !empty( $entry_values['arm_paid_post_id'] ) ){
                                                $arm_is_paid_post = true;
                                            }
                                            if (!$is_multiple_membership_feature->isMultipleMembershipFeature && !$arm_is_paid_post ) {
                                                
                                                $old_plan_ids = get_user_meta($user_id, 'arm_user_plan_ids', true);
                                                $old_plan_id = isset($old_plan_ids[0]) ? $old_plan_ids[0] : 0;
                                                $oldPlanDetail = array();
                                                $old_subscription_id = '';
                                                if (!empty($old_plan_id)) {
                                                    $oldPlanData = get_user_meta($user_id, 'arm_user_plan_' . $old_plan_id, true);
                                                    $oldPlanData = !empty($oldPlanData) ? $oldPlanData : array();
                                                    $oldPlanData = shortcode_atts($defaultPlanData, $oldPlanData);
                                                    $oldPlanDetail = $oldPlanData['arm_current_plan_detail'];
                                                    $subscr_effective = $oldPlanData['arm_expire_plan'];
                                                    $old_subscription_id = $oldPlanData['arm_subscr_id'];
                                                }
                                                
                                                $arm_user_old_plan_details = (isset($userPlanData['arm_current_plan_detail']) && !empty($userPlanData['arm_current_plan_detail'])) ? $userPlanData['arm_current_plan_detail'] : array();
                                                $arm_user_old_plan_details['arm_user_old_payment_mode'] = $userPlanData['arm_payment_mode'];

                                                if (!empty($old_subscription_id) && $entry_values['arm_selected_payment_mode'] == 'auto_debit_subscription' && $arm_token == $old_subscription_id) {

                                                    
                                                    $arm_next_due_payment_date = $userPlanData['arm_next_due_payment'];
                                                    if (!empty($arm_next_due_payment_date)) {
                                                        if (strtotime(current_time('mysql')) >= $arm_next_due_payment_date) {
                                                            $arm_user_completed_recurrence = $userPlanData['arm_completed_recurring'];
                                                            $arm_user_completed_recurrence++;
                                                            $userPlanData['arm_completed_recurring'] = $arm_user_completed_recurrence;
                                                            update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);
                                                            $arm_next_payment_date = $arm_members_class->arm_get_next_due_date($user_id, $entry_plan, false, $payment_cycle);
                                                            if ($arm_next_payment_date != '') {
                                                                $userPlanData['arm_next_due_payment'] = $arm_next_payment_date;
                                                                update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);
                                                            }

                                                           
                                                        }
                                                        else{

                                                                $now = current_time('mysql');
                                                                $arm_last_payment_status = $wpdb->get_var($wpdb->prepare("SELECT `arm_transaction_status` FROM `" . $ARMember->tbl_arm_payment_log . "` WHERE `arm_user_id`=%d AND `arm_plan_id`=%d AND `arm_created_date`<=%s ORDER BY `arm_log_id` DESC LIMIT 0,1", $user_id, $entry_plan, $now));

                                                                   if(in_array($arm_last_payment_status, array('success','pending'))){
                                                                    $arm_user_completed_recurrence = $userPlanData['arm_completed_recurring'];
                                                                        $arm_user_completed_recurrence++;
                                                                        $userPlanData['arm_completed_recurring'] = $arm_user_completed_recurrence;
                                                                        update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);
                                                                        $arm_next_payment_date = $arm_members_class->arm_get_next_due_date($user_id, $entry_plan, false, $payment_cycle);
                                                                        if ($arm_next_payment_date != '') {
                                                                            $userPlanData['arm_next_due_payment'] = $arm_next_payment_date;
                                                                            update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);
                                                                        }
                                                                    
                                                                }
                                                            }
                                                    }

                                                    $suspended_plan_ids = get_user_meta($user_id, 'arm_user_suspended_plan_ids', true);
                                                    $suspended_plan_id = (isset($suspended_plan_ids) && !empty($suspended_plan_ids)) ? $suspended_plan_ids : array();

                                                    if (in_array($entry_plan, $suspended_plan_id)) {
                                                        unset($suspended_plan_id[array_search($entry_plan, $suspended_plan_id)]);
                                                        update_user_meta($user_id, 'arm_user_suspended_plan_ids', array_values($suspended_plan_id));
                                                    }
                                                } else {

                                                    $now = current_time('mysql');
                                                    $arm_last_payment_status = $wpdb->get_var($wpdb->prepare("SELECT `arm_transaction_status` FROM `" . $ARMember->tbl_arm_payment_log . "` WHERE `arm_user_id`=%d AND `arm_plan_id`=%d AND `arm_created_date`<=%s ORDER BY `arm_log_id` DESC LIMIT 0,1", $user_id, $entry_plan, $now));
                                                    

                                                    $userPlanData['arm_current_plan_detail'] = $arm_user_old_plan_details;

                                                    $userPlanData['arm_payment_mode'] = $entry_values['arm_selected_payment_mode'];
                                                    $userPlanData['arm_payment_cycle'] = $entry_values['arm_selected_payment_cycle'];



                                                    if (!empty($oldPlanDetail)) {
                                                        $old_plan = new ARM_Plan(0);
                                                        $old_plan->init((object) $oldPlanDetail);
                                                    } else {
                                                        $old_plan = new ARM_Plan($old_plan_id);
                                                    }
                                                    $is_update_plan = true;
                                                    

                                                    $recurring_data = $new_plan->prepare_recurring_data($payment_cycle);
                                                    if (!empty($recurring_data['trial']) && empty($arm_user_old_plan)) {
                                                        $extraParam['trial'] = array(
                                                            'amount' => $recurring_data['trial']['amount'],
                                                            'period' => $recurring_data['trial']['period'],
                                                            'interval' => $recurring_data['trial']['interval'],
                                                           
                                                        );
                                                        $extraParam['tax_amount'] = isset( $charge_details['tax_amount'] ) ? $charge_details['tax_amount'] : 0;
                                                    }
                                                    if( $arm_coupon_discount > 0){
                                                        $extraParam['tax_amount'] = isset( $charge_details['tax_amount'] ) ? $charge_details['tax_amount'] : 0;
                                                    }
                                                    if ($old_plan->exists()) {
                                                        if ($old_plan->is_lifetime() || $old_plan->is_free() || ($old_plan->is_recurring() && $new_plan->is_recurring())) {
                                                            $is_update_plan = true;
                                                        } else {
                                                            $change_act = 'immediate';
                                                            if ($old_plan->enable_upgrade_downgrade_action == 1) {
                                                                if (!empty($old_plan->downgrade_plans) && in_array($new_plan->ID, $old_plan->downgrade_plans)) {
                                                                    $change_act = $old_plan->downgrade_action;
                                                                }
                                                                if (!empty($old_plan->upgrade_plans) && in_array($new_plan->ID, $old_plan->upgrade_plans)) {
                                                                    $change_act = $old_plan->upgrade_action;
                                                                }
                                                            }
                                                            if ($change_act == 'on_expire' && !empty($subscr_effective)) {
                                                                $is_update_plan = false;
                                                                $oldPlanData['arm_subscr_effective'] = $subscr_effective;
                                                                $oldPlanData['arm_change_plan_to'] = $entry_plan;
                                                                update_user_meta($user_id, 'arm_user_plan_' . $old_plan_id, $oldPlanData);
                                                            }
                                                        }
                                                    }

                                                    update_user_meta($user_id, 'arm_entry_id', $entry_id);
                                                    $userPlanData['arm_user_gateway'] = 'stripe';

                                                    if (!empty($arm_token)) {
                                                        $userPlanData['arm_subscr_id'] = $arm_token;
                                                    }
                                                    update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);
                                                    if ($is_update_plan) {
                                                       
                                                        $arm_subscription_plans->arm_update_user_subscription($user_id, $entry_plan, '', true, $arm_last_payment_status);
                                                    } else {
                                                        
                                                        $arm_subscription_plans->arm_add_membership_history($user_id, $entry_plan, 'change_subscription');
                                                    }
                                                }
                                            } else {
                                                
                                                $old_plan_ids = get_user_meta($user_id, 'arm_user_plan_ids', true);

                                                $oldPlanDetail = array();
                                                $old_subscription_id = '';
                                                
                                                if (in_array($entry_plan, $old_plan_ids)) {

                                                   
                                                    $oldPlanData = get_user_meta($user_id, 'arm_user_plan_' . $entry_plan, true);
                                                    $oldPlanDetail = $oldPlanData['arm_current_plan_detail'];
                                                    $subscr_effective = $oldPlanData['arm_expire_plan'];
                                                    $old_subscription_id = $oldPlanData['arm_subscr_id'];
                                                    
                                                    $arm_user_old_plan_details = (isset($userPlanData['arm_current_plan_detail']) && !empty($userPlanData['arm_current_plan_detail'])) ? $userPlanData['arm_current_plan_detail'] : array();
                                                    $arm_user_old_plan_details['arm_user_old_payment_mode'] = $userPlanData['arm_payment_mode'];
                                                    if (!empty($old_subscription_id) && $entry_values['arm_selected_payment_mode'] == 'auto_debit_subscription' && $arm_token == $old_subscription_id) {
                                                       
                                                        $userPlanData['arm_payment_mode'] = $entry_values['arm_selected_payment_mode'];
                                                        $userPlanData['arm_payment_cycle'] = $entry_values['arm_selected_payment_cycle'];

                                                        $is_update_plan = true;
                                                        

                                                        $recurring_data = $new_plan->prepare_recurring_data($payment_cycle);
                                                        if (!empty($recurring_data['trial']) && empty($arm_user_old_plan)) {
                                                            $extraParam['trial'] = array(
                                                                'amount' => $recurring_data['trial']['amount'],
                                                                'period' => $recurring_data['trial']['period'],
                                                                'interval' => $recurring_data['trial']['interval'],
                                                            );
                                                            $extraParam['tax_amount'] = isset( $charge_details['tax_amount'] ) ? $charge_details['tax_amount'] : 0;
                                                        }

                                                        if( $arm_coupon_discount > 0){
                                                            $extraParam['tax_amount'] = isset( $charge_details['tax_amount'] ) ? $charge_details['tax_amount'] : 0;
                                                        }

                                                        update_user_meta($user_id, 'arm_entry_id', $entry_id);
                                                        $userPlanData['arm_user_gateway'] = 'stripe';

                                                        if (!empty($arm_token)) {
                                                            $userPlanData['arm_subscr_id'] = $arm_token;
                                                        }
                                                        update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);
                                                        if ($is_update_plan) {
                                                            $arm_subscription_plans->arm_update_user_subscription($user_id, $entry_plan);
                                                        } else {
                                                            $arm_subscription_plans->arm_add_membership_history($user_id, $entry_plan, 'new_subscription');
                                                        }
                                                    } else {
                                                        $now = current_time('mysql');
                                                        $arm_last_payment_status = $wpdb->get_var($wpdb->prepare("SELECT `arm_transaction_status` FROM `" . $ARMember->tbl_arm_payment_log . "` WHERE `arm_user_id`=%d AND `arm_plan_id`=%d AND `arm_created_date`<=%s ORDER BY `arm_log_id` DESC LIMIT 0,1", $user_id, $entry_plan, $now));
                                                        

                                                        $userPlanData['arm_current_plan_detail'] = $arm_user_old_plan_details;

                                                        $userPlanData['arm_payment_mode'] = $entry_values['arm_selected_payment_mode'];
                                                        $userPlanData['arm_payment_cycle'] = $entry_values['arm_selected_payment_cycle'];



                                                        if (!empty($oldPlanDetail)) {
                                                            $old_plan = new ARM_Plan(0);
                                                            $old_plan->init((object) $oldPlanDetail);
                                                        } else {
                                                            $old_plan = new ARM_Plan($old_plan_id);
                                                        }
                                                        $is_update_plan = true;
                                                        

                                                        $recurring_data = $new_plan->prepare_recurring_data($payment_cycle);
                                                        if (!empty($recurring_data['trial']) && empty($arm_user_old_plan)) {
                                                            $extraParam['trial'] = array(
                                                                'amount' => $recurring_data['trial']['amount'],
                                                                'period' => $recurring_data['trial']['period'],
                                                                'interval' => $recurring_data['trial']['interval'],
                                                               
                                                            );
                                                            $extraParam['tax_amount'] = isset( $charge_details['tax_amount'] ) ? $charge_details['tax_amount'] : 0;
                                                        }
                                                        if( $arm_coupon_discount > 0){
                                                            $extraParam['tax_amount'] = isset( $charge_details['tax_amount'] ) ? $charge_details['tax_amount'] : 0;
                                                        }
                                                        if ($old_plan->exists()) {
                                                            if ($old_plan->is_lifetime() || $old_plan->is_free() || ($old_plan->is_recurring() && $new_plan->is_recurring())) {
                                                                $is_update_plan = true;
                                                            } else {
                                                                $change_act = 'immediate';
                                                                if ($old_plan->enable_upgrade_downgrade_action == 1) {
                                                                    if (!empty($old_plan->downgrade_plans) && in_array($new_plan->ID, $old_plan->downgrade_plans)) {
                                                                        $change_act = $old_plan->downgrade_action;
                                                                    }
                                                                    if (!empty($old_plan->upgrade_plans) && in_array($new_plan->ID, $old_plan->upgrade_plans)) {
                                                                        $change_act = $old_plan->upgrade_action;
                                                                    }
                                                                }
                                                                if ($change_act == 'on_expire' && !empty($subscr_effective)) {
                                                                    $is_update_plan = false;
                                                                    $oldPlanData['arm_subscr_effective'] = $subscr_effective;
                                                                    $oldPlanData['arm_change_plan_to'] = $entry_plan;
                                                                    update_user_meta($user_id, 'arm_user_plan_' . $old_plan_id, $oldPlanData);
                                                                }
                                                            }
                                                        }

                                                        update_user_meta($user_id, 'arm_entry_id', $entry_id);
                                                        $userPlanData['arm_user_gateway'] = 'stripe';

                                                        if (!empty($arm_token)) {
                                                            $userPlanData['arm_subscr_id'] = $arm_token;
                                                        }
                                                        update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);
                                                        if ($is_update_plan) {
                                                           
                                                            $arm_subscription_plans->arm_update_user_subscription($user_id, $entry_plan, '', true, $arm_last_payment_status);
                                                        } else {
                                                            
                                                            $arm_subscription_plans->arm_add_membership_history($user_id, $entry_plan, 'change_subscription');
                                                        }
                                                        $suspended_plan_ids = get_user_meta($user_id, 'arm_user_suspended_plan_ids', true);
                                                        $suspended_plan_id = (isset($suspended_plan_ids) && !empty($suspended_plan_ids)) ? $suspended_plan_ids : array();

                                                        if (in_array($entry_plan, $suspended_plan_id)) {
                                                            unset($suspended_plan_id[array_search($entry_plan, $suspended_plan_id)]);
                                                            update_user_meta($user_id, 'arm_user_suspended_plan_ids', array_values($suspended_plan_id));
                                                        }
                                                    }
                                                } else {

                                                    
                                                    $userPlanData['arm_payment_mode'] = $entry_values['arm_selected_payment_mode'];
                                                    $userPlanData['arm_payment_cycle'] = $entry_values['arm_selected_payment_cycle'];
                                                    $is_update_plan = true;
                                                    
                                                    $recurring_data = $new_plan->prepare_recurring_data($payment_cycle);
                                                    if (!empty($recurring_data['trial']) && empty($arm_user_old_plan)) {
                                                        $extraParam['trial'] = array(
                                                            'amount' => $recurring_data['trial']['amount'],
                                                            'period' => $recurring_data['trial']['period'],
                                                            'interval' => $recurring_data['trial']['interval'],
                                                           
                                                        );
                                                        $extraParam['tax_amount'] = isset( $charge_details['tax_amount'] ) ? $charge_details['tax_amount'] : 0;
                                                    }
                                                    if( $arm_coupon_discount > 0){
                                                            $extraParam['tax_amount'] = isset( $charge_details['tax_amount'] ) ? $charge_details['tax_amount'] : 0;
                                                        }
                                                    update_user_meta($user_id, 'arm_entry_id', $entry_id);
                                                    $userPlanData['arm_user_gateway'] = 'stripe';

                                                    if (!empty($arm_token)) {
                                                        $userPlanData['arm_subscr_id'] = $arm_token;
                                                    }
                                                    update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);
                                                    if ($is_update_plan) {
                                                       
                                                        $arm_subscription_plans->arm_update_user_subscription($user_id, $entry_plan);
                                                    } else {
                                                        
                                                        $arm_subscription_plans->arm_add_membership_history($user_id, $entry_plan, 'new_subscription');
                                                    }
                                                }
                                            }
                                            $is_log = true;
                                        }
                                    }
                                    $payment_log_id = $arm_stripe->arm_store_stripe_log( $stripelog, $entry_plan, $user_id, $entry_email, $extraParam, $payment_mode );

                                    if ($payment_log_id) {
                                        $payment_done = array('status' => TRUE, 'log_id' => $payment_log_id, 'entry_id' => $entry_id);
                                    }

                                    if( $create_new_user){
                                        $user_id = $arm_member_forms->arm_register_new_member($entry_values, $armform);
                                    }

                                    if (is_numeric($user_id) && !is_array($user_id)) {
                                        if ($arm_payment_type == 'subscription') {

                                            $userPlanDatameta = get_user_meta($user_id, 'arm_user_plan_' . $entry_plan, true);
                                            $userPlanDatameta = !empty($userPlanDatameta) ? $userPlanDatameta : array();
                                            $userPlanData = shortcode_atts($defaultPlanData, $userPlanDatameta);

                                            $userPlanData['arm_subscr_id'] = $arm_token;
                                            update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);

                                            $pgateway = 'stripe';
                                            $arm_manage_coupons->arm_coupon_apply_to_subscription($user_id, $payment_log_id, $pgateway, $userPlanData);
                                        }
                                        update_user_meta($user_id, 'arm_entry_id', $entry_id);
                                    }
                                    if($plan_action=="recurring_payment")
                                    {
                                        do_action('arm_after_recurring_payment_success_outside', $user_id, $entry_plan, 'stripe', $entry_values['arm_selected_payment_mode']);
                                    }

                                }
                            }
                        }
                        echo json_encode(
                            array(
                                'type' => 'redirect',
                                'url' => $return_url
                            )
                        );
                        die;
                    } else {
                        if( isset( $arm_stripe_enable_debug_mode ) && '1' == $arm_stripe_enable_debug_mode ){
                            echo json_encode(
                                array(
                                    'type' => 'error',
                                    'message' => $charge_data
                                )
                            );
                            die;
                        } else {
                            echo json_encode(
                                array(
                                    'type' => 'error',
                                    'message' => esc_html__('Sorry, something went wrong while processing payment', 'ARMember')
                                )
                            );
                            die;
                        }
                    }
                }
            }

            die;
        }

        function arm_stripe_made_charge_onetime_function(){
            global $wpdb, $ARMember, $arm_global_settings, $arm_member_forms, $arm_payment_gateways, $payment_done, $arm_subscription_plans, $arm_manage_communication, $arm_members_class, $arm_manage_coupons, $arm_stripe;

            $pi_id = $_POST['pi_id'];
            $entry_id = $_POST['entry_id'];
            $charge_details = json_decode( stripslashes_deep( $_POST['charge_details'] ), true );

            $entry_details = $wpdb->get_row( $wpdb->prepare( "SELECT arm_plan_id,arm_entry_email FROM `" . $ARMember->tbl_arm_entries . "` WHERE arm_entry_id = %d", $entry_id ) );
            $plan_id = $entry_details->arm_plan_id;
            $entry_email = $entry_data['arm_entry_email'];

            $entry_data = $arm_payment_gateways->arm_get_entry_data_by_id($entry_id);
            $user_id = $entry_data['arm_user_id'];
            $entry_values = maybe_unserialize($entry_data['arm_entry_value']);
            $arm_user_old_plan = (isset($entry_values['arm_user_old_plan']) && !empty($entry_values['arm_user_old_plan'])) ? explode(",", $entry_values['arm_user_old_plan']) : array();

            $plan_action = 'new_subscription';
            $plan_expiry_date = "now";

            if (!empty($arm_user_old_plan)) {
                if (in_array($plan_id, $arm_user_old_plan)) {

                    $user_plan_data = get_user_meta($user_id, 'arm_user_plan_' . $plan_id, true);
                    $user_plan_data = !empty($user_plan_data) ? $user_plan_data : array();
                    $plan_expiry_date = (isset($user_plan_data['arm_expire_plan']) && !empty($user_plan_data['arm_expire_plan'])) ? $user_plan_data['arm_expire_plan'] : "now";
                    $plan_action = 'renew_subscription';
                    $is_recurring_payment = $arm_subscription_plans->arm_is_recurring_payment_of_user($user_id, $plan_id, $payment_mode);
                    if ($is_recurring_payment) {
                        $plan_action = 'recurring_payment';
                        $planData = get_user_meta($user_id, 'arm_user_plan_' . $plan_id, true);
                        $oldPlanDetail = $planData['arm_current_plan_detail'];
                        $user_subsdata = $planData['arm_stripe'];
                        if (!empty($oldPlanDetail)) {
                            $plan = new ARM_Plan(0);
                            $plan->init((object) $oldPlanDetail);
                        }
                    }
                } else {
                    $plan_action = 'change_subscription';
                }
            }

            $plan = new ARM_Plan($plan_id);

            $active_payment_gateways = $arm_payment_gateways->arm_get_active_payment_gateways();

            $coupon_details = array();
            if (isset($charge_details['coupon_details'])) {
                $coupon_details = $charge_details['coupon_details'];
            }

            $payment_gateway_options = $active_payment_gateways['stripe'];

            $arm_stripe_enable_debug_mode = isset($payment_gateway_options['enable_debug_mode']) ? $payment_gateway_options['enable_debug_mode'] : 0;

            if( 'test' == $payment_gateway_options['stripe_payment_mode'] ){
                $sec_key = $payment_gateway_options['stripe_test_secret_key'];
                $pub_key = $payment_gateway_options['stripe_test_pub_key'];
            } else {
                $sec_key = $payment_gateway_options['stripe_secret_key'];
                $pub_key = $payment_gateway_options['stripe_pub_key'];
            }

            $currency = $arm_payment_gateways->arm_get_global_currency();

            $headers = array(
                'Authorization' => 'Bearer '.$sec_key
            );

            $api_url = 'https://api.stripe.com/v1/payment_intents/' . $pi_id;

            $data = wp_remote_post(
                $api_url,
                array(
                    'headers' => $headers,
                    'timeout' => 5000
                )
            );

            if( is_wp_error( $data ) ){
                if( isset( $arm_stripe_enable_debug_mode ) && '1' == $arm_stripe_enable_debug_mode ){
                    echo json_encode(
                        array(
                            'type' => 'error',
                            'message' => $data['body']
                        )
                    );
                } else {
                    echo json_encode(
                        array(
                            'type' => 'error',
                            'error' => true,
                            'message' => esc_html__( 'Sorry something went wrong while processing payment', 'ARMember')
                        )
                    );
                }
                die;
            } else {
                $piData = json_decode( $data['body'] );

                if( ! $piData->id ){
                    if( isset( $arm_stripe_enable_debug_mode ) && '1' == $arm_stripe_enable_debug_mode ){
                        echo json_encode(
                            array(
                                'type' => 'error',
                                'message' => $piData
                            )
                        );
                    } else {
                        echo json_encode(
                            array(
                                'type' => 'error',
                                'error' => true,
                                'message' => esc_html__( 'Sorry something went wrong while processing payment', 'ARMember')
                            )
                        );
                    }
                    die;
                } else {
                    $charge_data = $piData->charges->data[0];

                    if( isset( $charge_data->paid ) && true == $charge_data->paid ){
                        $extraParam = array();
                        if (isset($charge_details['extraVars'])) {
                            $extraParam = $charge_details['extraVars'];
                            unset($charge_details['extraVars']);
                        }

                        $extraParam['tax_amount'] =  isset($charge_details['tax_amount'])? $charge_details['tax_amount']  : 0; 

                        $custom_var = explode( '|', $charge_data->metadata->custom );

                        $entry_id = $custom_var[0];
                        $entry_email = $custom_var[1];
                        $form_id = $custom_var[2];
                        $payment_mode = $custom_var[3];

                        

                        $card_number = $charge_data->payment_method_details->card->last4;
                        $stripelog = new stdClass();
                        foreach( $charge_data as $k => $v ){
                            $stripelog->$k = $v;
                        }
                        
                        $txn_id = $charge_data->id;

                        $user_id = 0;

                        $entry_data = $wpdb->get_row("SELECT `arm_entry_id`, `arm_entry_email`, `arm_entry_value`, `arm_form_id`, `arm_user_id`, `arm_plan_id` FROM `" . $ARMember->tbl_arm_entries . "` WHERE `arm_entry_id`='" . $entry_id . "' AND `arm_entry_email`='" . $entry_email . "'", ARRAY_A);
                        
                        if( !empty( $entry_data ) ){
                            
                            $is_log = false;
                            $extraParam['plan_amount'] = $charge_data->amount;
                            $extraParam['paid_amount'] = $charge_data->amount;
                            $extraParam['card_number'] = 'xxxx-xxxx-xxxx-'.$card_number;
                            $entry_values = maybe_unserialize($entry_data['arm_entry_value']);
                            $payment_mode = $entry_values['arm_selected_payment_mode'];
                            $payment_cycle = $entry_values['arm_selected_payment_cycle'];
                            $arm_user_old_plan = (isset($entry_values['arm_user_old_plan']) && !empty($entry_values['arm_user_old_plan'])) ? explode(",", $entry_values['arm_user_old_plan']) : array();
                            $setup_id = $entry_values['setup_id'];
                            $tax_percentage = $entry_values['tax_percentage'];

                            $entry_plan = $entry_data['arm_plan_id'];
                            $stripelog->arm_coupon_code = $entry_values['arm_coupon_code'];
                            $stripelog->arm_payment_type = $arm_payment_type;
                            $extraParam['arm_is_trial'] = '0';
                            $extraParam['tax_percentage'] = (isset($tax_percentage) && $tax_percentage > 0) ? $tax_percentage : 0; 

                            $user_info = get_user_by('email', $entry_email);
                            $do_not_update_user = true;
                            if ($user_info) {
                                $user_id = $user_info->ID;

                                $trxn_success_log_id = $wpdb->get_var("SELECT `arm_log_id` FROM `" . $ARMember->tbl_arm_payment_log . "` WHERE `arm_user_id`='" . $user_id . "' AND `arm_transaction_id`='" . $txn_id . "' AND `arm_transaction_status` = 'success' AND `arm_payment_gateway` = 'stripe'");
                                if($trxn_success_log_id!='')
                                {
                                    $do_not_update_user = false;
                                }

                                if($do_not_update_user)
                                {
                                    $log_id = $wpdb->get_var("SELECT `arm_log_id` FROM `" . $ARMember->tbl_arm_payment_log . "` WHERE `arm_user_id`='" . $user_id . "' AND `arm_transaction_id`='" . $txn_id . "' AND `arm_transaction_status` = 'pending' AND `arm_payment_gateway` = 'stripe'");

                                    if ($log_id != '') {
                                        $payment_history_data = array();
                                        $payment_history_data['arm_transaction_status'] = 'success';
                                        $field_update = $wpdb->update($ARMember->tbl_arm_payment_log, $payment_history_data, array('arm_log_id' => $log_id));
                                        $do_not_update_user = false;
                                    }
                                }
                            }
                            
                            if ($do_not_update_user){

                                $form_id = $entry_data['arm_form_id'];
                                $armform = new ARM_Form('id', $form_id);
                                $user_info = get_user_by('email', $entry_email);
                                $new_plan = new ARM_Plan($entry_plan);
                                $plan_action = "new_subscription";
                                if ($new_plan->is_recurring()) {
                                    $plan_action = "renew_subscription";
                                    if (in_array($entry_plan, $arm_user_old_plan)) {
                                        $is_recurring_payment = $arm_subscription_plans->arm_is_recurring_payment_of_user($user_id, $entry_plan, $payment_mode);
                                        if ($is_recurring_payment) {
                                            $plan_action = 'recurring_payment';
                                            $planData = get_user_meta($user_id, 'arm_user_plan_' . $entry_plan, true);
                                            $oldPlanDetail = $planData['arm_current_plan_detail'];
                                            if (!empty($oldPlanDetail)) {
                                                $plan = new ARM_Plan(0);
                                                $plan->init((object) $oldPlanDetail);
                                                $plan_data = $plan->prepare_recurring_data($payment_cycle);
                                                $extraParam['plan_amount'] = $plan_data['amount'];
                                            }
                                        } else {
                                            $plan_data = $new_plan->prepare_recurring_data($payment_cycle);
                                            $extraParam['plan_amount'] = $plan_data['amount'];
                                        }
                                    } else {
                                        $plan_data = $new_plan->prepare_recurring_data($payment_cycle);
                                        $extraParam['plan_amount'] = $plan_data['amount'];
                                    }
                                } else {
                                    $extraParam['plan_amount'] = $new_plan->amount;
                                }
                                $couponCode = isset($entry_values['arm_coupon_code']) ? $entry_values['arm_coupon_code'] : '';
                                $arm_coupon_discount = 0;
                                if (!empty($couponCode)) {
                                    $couponApply = $arm_manage_coupons->arm_apply_coupon_code($couponCode, $new_plan, $setup_id, $payment_cycle, $arm_user_old_plan);
                                    $coupon_amount = isset($couponApply['coupon_amt']) ? $couponApply['coupon_amt'] : 0;
                                    $arm_coupon_on_each_subscriptions = isset($couponApply['arm_coupon_on_each_subscriptions']) ? $couponApply['arm_coupon_on_each_subscriptions'] : 0;


                                    if ($coupon_amount != 0) {
                                        $extraParam['coupon'] = array(
                                            'coupon_code' => $couponCode,
                                            'amount' => $coupon_amount,
                                            'arm_coupon_on_each_subscriptions' => $arm_coupon_on_each_subscriptions,
                                        );

                                        $arm_coupon_discount = $couponApply['discount'];
                                        $global_currency = $arm_payment_gateways->arm_get_global_currency();
                                        $arm_coupon_discount_type = ($couponApply['discount_type'] != 'percentage') ? $global_currency : "%";
                                        $stripelog->coupon_code = $couponCode;
                                        $stripelog->arm_coupon_discount = $arm_coupon_discount;
                                        $stripelog->arm_coupon_discount_type = $arm_coupon_discount_type;
                                        $stripelog->arm_coupon_on_each_subscriptions = $arm_coupon_on_each_subscriptions;
                                    }
                                }

                                $defaultPlanData = $arm_subscription_plans->arm_default_plan_array();
                                $userPlanDatameta = get_user_meta($user_id, 'arm_user_plan_' . $entry_plan, true);
                                $userPlanDatameta = !empty($userPlanDatameta) ? $userPlanDatameta : array();
                                $userPlanData = shortcode_atts($defaultPlanData, $userPlanDatameta);

                                $create_new_user = false;

                                if (!$user_info && in_array($armform->type, array('registration'))) {
                                    
                                    $payment_done = array();
                                    if ($payment_log_id) {
                                        $payment_done = array('status' => TRUE, 'log_id' => $payment_log_id, 'entry_id' => $entry_id);
                                    }
                                    $entry_values['payment_done'] = '1';
                                    $entry_values['arm_entry_id'] = $entry_id;
                                    $entry_values['arm_update_user_from_profile'] = 0;

                                    $create_new_user = true;
                                                                       
                                    
                                } else {
                                    $user_id = $user_info->ID;

                                    if (!empty($user_id)) {
                                        global $is_multiple_membership_feature;
                                        $arm_is_paid_post = false;
                                        if( !empty( $entry_values['arm_is_post_entry'] ) && !empty( $entry_values['arm_paid_post_id'] ) ){
                                            $arm_is_paid_post = true;
                                        }
                                        if ( !$is_multiple_membership_feature->isMultipleMembershipFeature && !$arm_is_paid_post ) {
                                            
                                            $old_plan_ids = get_user_meta($user_id, 'arm_user_plan_ids', true);
                                            $old_plan_id = isset($old_plan_ids[0]) ? $old_plan_ids[0] : 0;
                                            $oldPlanDetail = array();
                                            $old_subscription_id = '';
                                            if (!empty($old_plan_id)) {
                                                $oldPlanData = get_user_meta($user_id, 'arm_user_plan_' . $old_plan_id, true);
                                                $oldPlanData = !empty($oldPlanData) ? $oldPlanData : array();
                                                $oldPlanData = shortcode_atts($defaultPlanData, $oldPlanData);
                                                $oldPlanDetail = $oldPlanData['arm_current_plan_detail'];
                                                $subscr_effective = $oldPlanData['arm_expire_plan'];
                                                $old_subscription_id = $oldPlanData['arm_subscr_id'];
                                            }
                                            
                                            $arm_user_old_plan_details = (isset($userPlanData['arm_current_plan_detail']) && !empty($userPlanData['arm_current_plan_detail'])) ? $userPlanData['arm_current_plan_detail'] : array();
                                            $arm_user_old_plan_details['arm_user_old_payment_mode'] = $userPlanData['arm_payment_mode'];

                                            if (!empty($old_subscription_id) && $entry_values['arm_selected_payment_mode'] == 'auto_debit_subscription' && $arm_token == $old_subscription_id) {

                                                
                                                $arm_next_due_payment_date = $userPlanData['arm_next_due_payment'];
                                                if (!empty($arm_next_due_payment_date)) {
                                                    if (strtotime(current_time('mysql')) >= $arm_next_due_payment_date) {
                                                        $arm_user_completed_recurrence = $userPlanData['arm_completed_recurring'];
                                                        $arm_user_completed_recurrence++;
                                                        $userPlanData['arm_completed_recurring'] = $arm_user_completed_recurrence;
                                                        update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);
                                                        $arm_next_payment_date = $arm_members_class->arm_get_next_due_date($user_id, $entry_plan, false, $payment_cycle);
                                                        if ($arm_next_payment_date != '') {
                                                            $userPlanData['arm_next_due_payment'] = $arm_next_payment_date;
                                                            update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);
                                                        }
                                                    } else {

                                                        $now = current_time('mysql');
                                                        $arm_last_payment_status = $wpdb->get_var($wpdb->prepare("SELECT `arm_transaction_status` FROM `" . $ARMember->tbl_arm_payment_log . "` WHERE `arm_user_id`=%d AND `arm_plan_id`=%d AND `arm_created_date`<=%s ORDER BY `arm_log_id` DESC LIMIT 0,1", $user_id, $entry_plan, $now));

                                                           if(in_array($arm_last_payment_status, array('success','pending'))){
                                                            $arm_user_completed_recurrence = $userPlanData['arm_completed_recurring'];
                                                                $arm_user_completed_recurrence++;
                                                                $userPlanData['arm_completed_recurring'] = $arm_user_completed_recurrence;
                                                                update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);
                                                                $arm_next_payment_date = $arm_members_class->arm_get_next_due_date($user_id, $entry_plan, false, $payment_cycle);
                                                                if ($arm_next_payment_date != '') {
                                                                    $userPlanData['arm_next_due_payment'] = $arm_next_payment_date;
                                                                    update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);
                                                                }
                                                            
                                                        }
                                                    }
                                                }

                                                $suspended_plan_ids = get_user_meta($user_id, 'arm_user_suspended_plan_ids', true);
                                                $suspended_plan_id = (isset($suspended_plan_ids) && !empty($suspended_plan_ids)) ? $suspended_plan_ids : array();

                                                if (in_array($entry_plan, $suspended_plan_id)) {
                                                    unset($suspended_plan_id[array_search($entry_plan, $suspended_plan_id)]);
                                                    update_user_meta($user_id, 'arm_user_suspended_plan_ids', array_values($suspended_plan_id));
                                                }
                                            } else {

                                                $now = current_time('mysql');
                                                $arm_last_payment_status = $wpdb->get_var($wpdb->prepare("SELECT `arm_transaction_status` FROM `" . $ARMember->tbl_arm_payment_log . "` WHERE `arm_user_id`=%d AND `arm_plan_id`=%d AND `arm_created_date`<=%s ORDER BY `arm_log_id` DESC LIMIT 0,1", $user_id, $entry_plan, $now));
                                                

                                                $userPlanData['arm_current_plan_detail'] = $arm_user_old_plan_details;

                                                $userPlanData['arm_payment_mode'] = $entry_values['arm_selected_payment_mode'];
                                                $userPlanData['arm_payment_cycle'] = $entry_values['arm_selected_payment_cycle'];



                                                if (!empty($oldPlanDetail)) {
                                                    $old_plan = new ARM_Plan(0);
                                                    $old_plan->init((object) $oldPlanDetail);
                                                } else {
                                                    $old_plan = new ARM_Plan($old_plan_id);
                                                }
                                                $is_update_plan = true;

                                                $recurring_data = $new_plan->prepare_recurring_data($payment_cycle);
                                                if (!empty($recurring_data['trial']) && empty($arm_user_old_plan)) {
                                                    $extraParam['trial'] = array(
                                                        'amount' => $recurring_data['trial']['amount'],
                                                        'period' => $recurring_data['trial']['period'],
                                                        'interval' => $recurring_data['trial']['interval'],
                                                       
                                                    );
                                                    $extraParam['tax_amount'] = isset( $charge_details['tax_amount'] ) ? $charge_details['tax_amount'] : 0;
                                                }
                                                if( $arm_coupon_discount > 0){
                                                    $extraParam['tax_amount'] = isset( $charge_details['tax_amount'] ) ? $charge_details['tax_amount'] : 0;
                                                }
                                                if ($old_plan->exists()) {
                                                    if ($old_plan->is_lifetime() || $old_plan->is_free() || ($old_plan->is_recurring() && $new_plan->is_recurring())) {
                                                        $is_update_plan = true;
                                                    } else {
                                                        $change_act = 'immediate';
                                                        if ($old_plan->enable_upgrade_downgrade_action == 1) {
                                                            if (!empty($old_plan->downgrade_plans) && in_array($new_plan->ID, $old_plan->downgrade_plans)) {
                                                                $change_act = $old_plan->downgrade_action;
                                                            }
                                                            if (!empty($old_plan->upgrade_plans) && in_array($new_plan->ID, $old_plan->upgrade_plans)) {
                                                                $change_act = $old_plan->upgrade_action;
                                                            }
                                                        }
                                                        if ($change_act == 'on_expire' && !empty($subscr_effective)) {
                                                            $is_update_plan = false;
                                                            $oldPlanData['arm_subscr_effective'] = $subscr_effective;
                                                            $oldPlanData['arm_change_plan_to'] = $entry_plan;
                                                            update_user_meta($user_id, 'arm_user_plan_' . $old_plan_id, $oldPlanData);
                                                        }
                                                    }
                                                }

                                                update_user_meta($user_id, 'arm_entry_id', $entry_id);
                                                $userPlanData['arm_user_gateway'] = 'stripe';

                                                if (!empty($arm_token)) {
                                                    $userPlanData['arm_subscr_id'] = $arm_token;
                                                }
                                                update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);
                                                if ($is_update_plan) {
                                                   
                                                    $arm_subscription_plans->arm_update_user_subscription($user_id, $entry_plan, '', true, $arm_last_payment_status);
                                                } else {
                                                    
                                                    $arm_subscription_plans->arm_add_membership_history($user_id, $entry_plan, 'change_subscription');
                                                }
                                            }
                                        } else {
                                            
                                            $old_plan_ids = get_user_meta($user_id, 'arm_user_plan_ids', true);

                                            $oldPlanDetail = array();
                                            $old_subscription_id = '';
                                            
                                            if (in_array($entry_plan, $old_plan_ids)) {

                                               
                                                $oldPlanData = get_user_meta($user_id, 'arm_user_plan_' . $entry_plan, true);
                                                $oldPlanDetail = $oldPlanData['arm_current_plan_detail'];
                                                $subscr_effective = $oldPlanData['arm_expire_plan'];
                                                $old_subscription_id = $oldPlanData['arm_subscr_id'];
                                                
                                                $arm_user_old_plan_details = (isset($userPlanData['arm_current_plan_detail']) && !empty($userPlanData['arm_current_plan_detail'])) ? $userPlanData['arm_current_plan_detail'] : array();
                                                $arm_user_old_plan_details['arm_user_old_payment_mode'] = $userPlanData['arm_payment_mode'];
                                                if (!empty($old_subscription_id) && $entry_values['arm_selected_payment_mode'] == 'auto_debit_subscription' && $arm_token == $old_subscription_id) {
                                                   
                                                    $userPlanData['arm_payment_mode'] = $entry_values['arm_selected_payment_mode'];
                                                    $userPlanData['arm_payment_cycle'] = $entry_values['arm_selected_payment_cycle'];

                                                    $is_update_plan = true;

                                                    $recurring_data = $new_plan->prepare_recurring_data($payment_cycle);
                                                    if (!empty($recurring_data['trial']) && empty($arm_user_old_plan)) {
                                                        $extraParam['trial'] = array(
                                                            'amount' => $recurring_data['trial']['amount'],
                                                            'period' => $recurring_data['trial']['period'],
                                                            'interval' => $recurring_data['trial']['interval'],
                                                        );
                                                        $extraParam['tax_amount'] = isset( $charge_details['tax_amount'] ) ? $charge_details['tax_amount'] : 0;
                                                    }

                                                    if( $arm_coupon_discount > 0){
                                                        $extraParam['tax_amount'] = isset( $charge_details['tax_amount'] ) ? $charge_details['tax_amount'] : 0;
                                                    }

                                                    update_user_meta($user_id, 'arm_entry_id', $entry_id);
                                                    $userPlanData['arm_user_gateway'] = 'stripe';

                                                    if (!empty($arm_token)) {
                                                        $userPlanData['arm_subscr_id'] = $arm_token;
                                                    }
                                                    update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);
                                                    if ($is_update_plan) {
                                                        $arm_subscription_plans->arm_update_user_subscription($user_id, $entry_plan);
                                                    } else {
                                                        $arm_subscription_plans->arm_add_membership_history($user_id, $entry_plan, 'new_subscription');
                                                    }
                                                } else {
                                                    $now = current_time('mysql');
                                                    $arm_last_payment_status = $wpdb->get_var($wpdb->prepare("SELECT `arm_transaction_status` FROM `" . $ARMember->tbl_arm_payment_log . "` WHERE `arm_user_id`=%d AND `arm_plan_id`=%d AND `arm_created_date`<=%s ORDER BY `arm_log_id` DESC LIMIT 0,1", $user_id, $entry_plan, $now));
                                                    

                                                    $userPlanData['arm_current_plan_detail'] = $arm_user_old_plan_details;

                                                    $userPlanData['arm_payment_mode'] = $entry_values['arm_selected_payment_mode'];
                                                    $userPlanData['arm_payment_cycle'] = $entry_values['arm_selected_payment_cycle'];



                                                    if (!empty($oldPlanDetail)) {
                                                        $old_plan = new ARM_Plan(0);
                                                        $old_plan->init((object) $oldPlanDetail);
                                                    } else {
                                                        $old_plan = new ARM_Plan($old_plan_id);
                                                    }
                                                    $is_update_plan = true;

                                                    $recurring_data = $new_plan->prepare_recurring_data($payment_cycle);
                                                    if (!empty($recurring_data['trial']) && empty($arm_user_old_plan)) {
                                                        $extraParam['trial'] = array(
                                                            'amount' => $recurring_data['trial']['amount'],
                                                            'period' => $recurring_data['trial']['period'],
                                                            'interval' => $recurring_data['trial']['interval'],
                                                           
                                                        );
                                                        $extraParam['tax_amount'] = isset( $charge_details['tax_amount'] ) ? $charge_details['tax_amount'] : 0;
                                                    }
                                                    if( $arm_coupon_discount > 0){
                                                        $extraParam['tax_amount'] = isset( $charge_details['tax_amount'] ) ? $charge_details['tax_amount'] : 0;
                                                    }
                                                    if ($old_plan->exists()) {
                                                        if ($old_plan->is_lifetime() || $old_plan->is_free() || ($old_plan->is_recurring() && $new_plan->is_recurring())) {
                                                            $is_update_plan = true;
                                                        } else {
                                                            $change_act = 'immediate';
                                                            if ($old_plan->enable_upgrade_downgrade_action == 1) {
                                                                if (!empty($old_plan->downgrade_plans) && in_array($new_plan->ID, $old_plan->downgrade_plans)) {
                                                                    $change_act = $old_plan->downgrade_action;
                                                                }
                                                                if (!empty($old_plan->upgrade_plans) && in_array($new_plan->ID, $old_plan->upgrade_plans)) {
                                                                    $change_act = $old_plan->upgrade_action;
                                                                }
                                                            }
                                                            if ($change_act == 'on_expire' && !empty($subscr_effective)) {
                                                                $is_update_plan = false;
                                                                $oldPlanData['arm_subscr_effective'] = $subscr_effective;
                                                                $oldPlanData['arm_change_plan_to'] = $entry_plan;
                                                                update_user_meta($user_id, 'arm_user_plan_' . $old_plan_id, $oldPlanData);
                                                            }
                                                        }
                                                    }

                                                    update_user_meta($user_id, 'arm_entry_id', $entry_id);
                                                    $userPlanData['arm_user_gateway'] = 'stripe';

                                                    if (!empty($arm_token)) {
                                                        $userPlanData['arm_subscr_id'] = $arm_token;
                                                    }
                                                    update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);
                                                    if ($is_update_plan) {
                                                       
                                                        $arm_subscription_plans->arm_update_user_subscription($user_id, $entry_plan, '', true, $arm_last_payment_status);
                                                    } else {
                                                        
                                                        $arm_subscription_plans->arm_add_membership_history($user_id, $entry_plan, 'change_subscription');
                                                    }
                                                    $suspended_plan_ids = get_user_meta($user_id, 'arm_user_suspended_plan_ids', true);
                                                    $suspended_plan_id = (isset($suspended_plan_ids) && !empty($suspended_plan_ids)) ? $suspended_plan_ids : array();

                                                    if (in_array($entry_plan, $suspended_plan_id)) {
                                                        unset($suspended_plan_id[array_search($entry_plan, $suspended_plan_id)]);
                                                        update_user_meta($user_id, 'arm_user_suspended_plan_ids', array_values($suspended_plan_id));
                                                    }
                                                }
                                            } else {

                                                
                                                $userPlanData['arm_payment_mode'] = $entry_values['arm_selected_payment_mode'];
                                                $userPlanData['arm_payment_cycle'] = $entry_values['arm_selected_payment_cycle'];
                                                $is_update_plan = true;

                                                $recurring_data = $new_plan->prepare_recurring_data($payment_cycle);
                                                if (!empty($recurring_data['trial']) && empty($arm_user_old_plan)) {
                                                    $extraParam['trial'] = array(
                                                        'amount' => $recurring_data['trial']['amount'],
                                                        'period' => $recurring_data['trial']['period'],
                                                        'interval' => $recurring_data['trial']['interval'],
                                                       
                                                    );
                                                    $extraParam['tax_amount'] = isset( $charge_details['tax_amount'] ) ? $charge_details['tax_amount'] : 0;
                                                }
                                                if( $arm_coupon_discount > 0){
                                                        $extraParam['tax_amount'] = isset( $charge_details['tax_amount'] ) ? $charge_details['tax_amount'] : 0;
                                                    }
                                                update_user_meta($user_id, 'arm_entry_id', $entry_id);
                                                $userPlanData['arm_user_gateway'] = 'stripe';

                                                if (!empty($arm_token)) {
                                                    $userPlanData['arm_subscr_id'] = $arm_token;
                                                }
                                                update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);
                                                if ($is_update_plan) {
                                                   
                                                    $arm_subscription_plans->arm_update_user_subscription($user_id, $entry_plan);
                                                } else {
                                                    
                                                    $arm_subscription_plans->arm_add_membership_history($user_id, $entry_plan, 'new_subscription');
                                                }
                                            }
                                        }

                                        $is_log = true;
                                    }
                                }

                                $payment_log_id = $arm_stripe->arm_store_stripe_log( $stripelog, $entry_plan, $user_id, $entry_email, $extraParam, $payment_mode, $coupon_details );

                                if ($payment_log_id) {
                                    $payment_done = array('status' => TRUE, 'log_id' => $payment_log_id, 'entry_id' => $entry_id);
                                }

                                if( $create_new_user ){
                                    $user_id = $arm_member_forms->arm_register_new_member($entry_values, $armform);
                                }

                                if (is_numeric($user_id) && !is_array($user_id)) {
                                    if ($arm_payment_type == 'subscription') {

                                        $userPlanDatameta = get_user_meta($user_id, 'arm_user_plan_' . $entry_plan, true);
                                        $userPlanDatameta = !empty($userPlanDatameta) ? $userPlanDatameta : array();
                                        $userPlanData = shortcode_atts($defaultPlanData, $userPlanDatameta);

                                        $userPlanData['arm_subscr_id'] = $arm_token;
                                        update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);

                                        $pgateway = 'stripe';
                                        $arm_manage_coupons->arm_coupon_apply_to_subscription($user_id, $payment_log_id, $pgateway, $userPlanData);
                                    }
                                    update_user_meta($user_id, 'arm_entry_id', $entry_id);
                                }

                                if($plan_action=="recurring_payment")
                                {
                                    do_action('arm_after_recurring_payment_success_outside', $user_id, $entry_plan, 'stripe', $entry_values['arm_selected_payment_mode']);
                                }

                            }
                        }
                        
                        echo json_encode(
                            array(
                                'type' => 'redirect',
                                'url' => $return_url
                            )
                        );
                        die;
                    } else {
                        
                    }
                }
            }
        }

        function arm_store_stripe_subscription_payment(){
            global $wpdb, $ARMember, $arm_global_settings, $arm_member_forms, $arm_payment_gateways, $payment_done, $arm_subscription_plans, $arm_manage_communication, $arm_members_class, $arm_manage_coupons, $arm_stripe;

            $pi_id = $_POST['pi_id'];
            $entry_id = $_POST['entry_id'];
            $isPaidTrail = $_POST['isPaidTrail'];
            $charge_details1 = json_decode( stripslashes_deep( $_POST['charge_details1'] ), true );

            $charge_details = json_decode( stripslashes_deep( $_POST['charge_details'] ), true );

            $plan_action = $_POST['plan_action'];
            $payment_mode = $_POST['plan_mode'];

            $entry_details = $wpdb->get_row( $wpdb->prepare( "SELECT arm_plan_id,arm_entry_email FROM `" . $ARMember->tbl_arm_entries . "` WHERE arm_entry_id = %d", $entry_id ) );

            $plan_id = $entry_details->arm_plan_id;
            $entry_email = $entry_details->arm_entry_email;

            $plan = new ARM_Plan($plan_id);

            $active_payment_gateways = $arm_payment_gateways->arm_get_active_payment_gateways();

            $payment_gateway_options = $active_payment_gateways['stripe'];

            $arm_stripe_enable_debug_mode = isset( $payment_gateway_options['enable_debug_mode'] ) ? $payment_gateway_options['enable_debug_mode'] : 0;

            if( 'test' == $payment_gateway_options['stripe_payment_mode'] ){
                $sec_key = $payment_gateway_options['stripe_test_secret_key'];
                $pub_key = $payment_gateway_options['stripe_test_pub_key'];
            } else {
                $sec_key = $payment_gateway_options['stripe_secret_key'];
                $pub_key = $payment_gateway_options['stripe_pub_key'];
            }

            $currency = $arm_payment_gateways->arm_get_global_currency();

            $headers = array(
                'Authorization' => 'Bearer '.$sec_key
            );

            $api_url = 'https://api.stripe.com/v1/payment_intents/' . $pi_id;

            $data = wp_remote_post(
                $api_url,
                array(
                    'headers' => $headers,
                    'timeout' => 5000
                )
            );

            if( is_wp_error( $data ) ){
                if( isset( $arm_stripe_enable_debug_mode ) && '1' == $arm_stripe_enable_debug_mode ){
                    echo json_encode(
                        array(
                            'error' => true,
                            'message' => $data['body']
                        )
                    );
                } else {
                    echo json_encode(
                        array(
                            'error' => true,
                            'message' => esc_html__( 'Sorry something went wrong while processing payment', 'ARMember')
                        )
                    );
                }
                die;
            } else {
                $piData = json_decode( $data['body'] );

                if( 'succeeded' != $piData->status ){
                    if( isset( $arm_stripe_enable_debug_mode ) && '1' == $arm_stripe_enable_debug_mode ){
                        echo json_encode(
                            array(
                                'error' => true,
                                'message' => $piData
                            )
                        );
                    } else {
                        echo json_encode(
                            array(
                                'error' => true,
                                'message' => esc_html__( 'Sorry something went wrong while processing payment', 'ARMember')
                            )
                        );
                    }
                    die;
                } else {
                    $chargeObj = $piData->charges->data[0];

                    $invoice_id = $chargeObj->invoice;

                    $invoice_api_url = 'https://api.stripe.com/v1/invoices/' . $invoice_id;

                    $invoice_data = wp_remote_post(
                        $invoice_api_url,
                        array(
                            'headers' => $headers,
                            'timeout' => 5000
                        )
                    );

                    if( is_wp_error( $invoice_data ) ){
                        if( isset( $arm_stripe_enable_debug_mode ) && '1' == $arm_stripe_enable_debug_mode ){
                            echo json_encode(
                                array(
                                    'error' => true,
                                    'message' => $invoice_data['body']
                                )
                            );
                        } else {
                            echo json_encode(
                                array(
                                    'error' => true,
                                    'message' => esc_html__( 'Sorry something went wrong while processing payment', 'ARMember')
                                )
                            );
                        }
                        die;
                    } else {
                        $invoiceObj = json_decode( $invoice_data['body'] );
                        
                        if( $invoiceObj->id ){
                            $subscription_id = $invoiceObj->subscription;



                            if( null == $subscription_id || '' == $subscription_id ){
                                $subscription_id = $invoiceObj->id;
                            }
                            $subscription = $invoiceObj->lines->data[0];

                            $api_url = 'https://api.stripe.com/v1/subscriptions/' . $subscription_id;

                            $wp_post_data = wp_remote_post(
                                $api_url, array(
                                    'timeout' => 5000,
                                    'headers' => $headers
                                )
                            );

                            if( is_wp_error($wp_post_data) ){
                                echo json_encode(
                                    array(
                                        'error' => true,
                                        'message' => esc_html__( 'Sorry something went wrong while processing payment', 'ARMember')
                                    )
                                );
                                die;
                            } else {
                                $subscription_data = json_decode( $wp_post_data['body'] );
                                
                                if( isset( $subscription_data->status ) && 'active' == $subscription_data->status ) {

                                    $subscription_id = $subscription_data->id;

                                    $custom = $subscription_data->metadata->custom;
                                    
                                    if( isset( $invoiceObj->charge ) ){
                                        $charge_id = $invoiceObj->charge;

                                        $charge_obj = wp_remote_post(
                                            'https://api.stripe.com/v1/charges/' . $charge_id,
                                            array(
                                                'headers' => $headers,
                                                'timeout' => 5000
                                            )
                                        );

                                        if( is_wp_error( $charge_obj ) ){
                                            echo json_encode(
                                                array(
                                                    'error' => true,
                                                    'message' => esc_html__( 'Sorry something went wrong while processing payment', 'ARMember')
                                                )
                                            );
                                            die;                                            
                                        } else {
                                            $charge_data = json_decode( $charge_obj['body'] );

                                            if( isset( $charge_data->id ) ){

                                                $pi_id = $charge_data->payment_intent;

                                                $customer_id = $charge_data->customer;

                                                $stripelog = new stdClass();
                                                foreach( $charge_data as $k => $v ){
                                                    $stripelog->$k = $v;
                                                }

                                                $card_number = $charge_data->payment_method_details->card->last4;
                                                

                                                $customs = explode('|', $subscription_data->metadata->custom);
                                                $entry_id = $customs[0];
                                                $entry_email = $customs[1];
                                                $arm_payment_type = $customs[2];

                                                $txn_id = $subscription_id;

                                                $user_id = 0;

                                                $entry_data = $wpdb->get_row("SELECT `arm_entry_id`, `arm_entry_email`, `arm_entry_value`, `arm_form_id`, `arm_user_id`, `arm_plan_id` FROM `" . $ARMember->tbl_arm_entries . "` WHERE `arm_entry_id`='" . $entry_id . "' AND `arm_entry_email`='" . $entry_email . "'", ARRAY_A);

                                                if( !empty( $entry_data ) ){
                                                    
                                                    $is_log = false;

                                                    if( !empty( $charge_details ) ){

                                                        if (isset($charge_details['status']) && $charge_details['status'] == TRUE) {
                                                            $payment_done = $charge_details;
                                                            return $payment_done;
                                                        }
                                                        if (isset($charge_details['extraVars'])) {
                                                            $extraParam = $charge_details['extraVars'];
                                                            unset($charge_details['extraVars']);
                                                        }
                                                        $coupon_details = array();
                                                        if (isset($charge_details['coupon_details'])) {
                                                            $coupon_details = $charge_details['coupon_details'];
                                                        }
                                                        $charge_details['plan_action'] = $plan_action;
                                                        $charge_details['expire_date'] = $plan_expiry_date;

                                                        $charge_details['tax_percentage'] = $tax_percentage; 
                                                        $extraParam['tax_percentage'] = $tax_percentage;
                                                        $extraParam['tax_amount'] =  isset($charge_details['tax_amount'])? $charge_details['tax_amount']  : 0; 
                                                        unset($charge_details['tax_amount']);
                                                    }

                                                    $extraParam['plan_amount'] = $charge_data->amount;
                                                    $extraParam['paid_amount'] = $charge_data->amount;

                                                    $extraParam['card_number'] = 'xxxx-xxxx-xxxx-'.$card_number;
                                                    $entry_values = maybe_unserialize($entry_data['arm_entry_value']);
                                                    $payment_mode = $entry_values['arm_selected_payment_mode'];
                                                    $payment_cycle = $entry_values['arm_selected_payment_cycle'];
                                                    $arm_user_old_plan = (isset($entry_values['arm_user_old_plan']) && !empty($entry_values['arm_user_old_plan'])) ? explode(",", $entry_values['arm_user_old_plan']) : array();
                                                    $setup_id = $entry_values['setup_id'];
                                                    $tax_percentage = $entry_values['tax_percentage'];

                                                    $entry_plan = $entry_data['arm_plan_id'];
                                                    $stripelog->arm_coupon_code = $entry_values['arm_coupon_code'];
                                                    $stripelog->arm_payment_type = $arm_payment_type;
                                                    $extraParam['arm_is_trial'] = '0';
                                                    $extraParam['tax_percentage'] = (isset($tax_percentage) && $tax_percentage > 0) ? $tax_percentage : 0; 

                                                    
                                                    
                                                    $user_info = get_user_by('email', $entry_email);

                                                    $do_not_update_user = true;
                                                    
                                                    if ($user_info) {
                                                        $user_id = $user_info->ID;

                                                        $trxn_success_log_id = $wpdb->get_var("SELECT `arm_log_id` FROM `" . $ARMember->tbl_arm_payment_log . "` WHERE `arm_user_id`='" . $user_id . "' AND `arm_transaction_id`='" . $txn_id . "' AND `arm_transaction_status` = 'success' AND `arm_payment_gateway` = 'stripe'");
                                                        if($trxn_success_log_id!='')
                                                        {
                                                            $do_not_update_user = false;
                                                        }

                                                        if($do_not_update_user)
                                                        {
                                                            $log_id = $wpdb->get_var("SELECT `arm_log_id` FROM `" . $ARMember->tbl_arm_payment_log . "` WHERE `arm_user_id`='" . $user_id . "' AND `arm_transaction_id`='" . $txn_id . "' AND `arm_transaction_status` = 'pending' AND `arm_payment_gateway` = 'stripe'");

                                                            if ($log_id != '') {
                                                                $payment_history_data = array();
                                                                $payment_history_data['arm_transaction_status'] = 'success';
                                                                $field_update = $wpdb->update($ARMember->tbl_arm_payment_log, $payment_history_data, array('arm_log_id' => $log_id));
                                                                $do_not_update_user = false;
                                                            }
                                                        }
                                                    }

                                                    if ($do_not_update_user){
                                                        
                                                        $form_id = $entry_data['arm_form_id'];
                                                        $armform = new ARM_Form('id', $form_id);
                                                        $user_info = get_user_by('email', $entry_email);
                                                        $new_plan = new ARM_Plan($entry_plan);
                                                        $plan_action = "new_subscription";
                                                        if ($new_plan->is_recurring()) {
                                                            $plan_action = "renew_subscription";
                                                            if (in_array($entry_plan, $arm_user_old_plan)) {
                                                                $is_recurring_payment = $arm_subscription_plans->arm_is_recurring_payment_of_user($user_id, $entry_plan, $payment_mode);
                                                                if ($is_recurring_payment) {
                                                                    $plan_action = 'recurring_payment';
                                                                    $planData = get_user_meta($user_id, 'arm_user_plan_' . $entry_plan, true);
                                                                    $oldPlanDetail = $planData['arm_current_plan_detail'];
                                                                    if (!empty($oldPlanDetail)) {
                                                                        $plan = new ARM_Plan(0);
                                                                        $plan->init((object) $oldPlanDetail);
                                                                        $plan_data = $plan->prepare_recurring_data($payment_cycle);
                                                                        $extraParam['plan_amount'] = $plan_data['amount'];
                                                                    }
                                                                } else {
                                                                    $plan_data = $new_plan->prepare_recurring_data($payment_cycle);
                                                                    $extraParam['plan_amount'] = $plan_data['amount'];
                                                                }
                                                            } else {
                                                                $plan_data = $new_plan->prepare_recurring_data($payment_cycle);
                                                                $extraParam['plan_amount'] = $plan_data['amount'];
                                                            }
                                                        } else {
                                                           
                                                            $extraParam['plan_amount'] = $new_plan->amount;
                                                        }

                                                        $couponCode = isset($entry_values['arm_coupon_code']) ? $entry_values['arm_coupon_code'] : '';
                                                        $arm_coupon_discount = 0;
                                                        if (!empty($couponCode)) {
                                                            $couponApply = $arm_manage_coupons->arm_apply_coupon_code($couponCode, $new_plan, $setup_id, $payment_cycle, $arm_user_old_plan);
                                                            $coupon_amount = isset($couponApply['coupon_amt']) ? $couponApply['coupon_amt'] : 0;
                                                            $arm_coupon_on_each_subscriptions = isset($couponApply['arm_coupon_on_each_subscriptions']) ? $couponApply['arm_coupon_on_each_subscriptions'] : 0;


                                                            if ($coupon_amount != 0) {
                                                                $extraParam['coupon'] = array(
                                                                    'coupon_code' => $couponCode,
                                                                    'amount' => $coupon_amount,
                                                                    'arm_coupon_on_each_subscriptions' => $arm_coupon_on_each_subscriptions,
                                                                );

                                                                $arm_coupon_discount = $couponApply['discount'];
                                                                $global_currency = $arm_payment_gateways->arm_get_global_currency();
                                                                $arm_coupon_discount_type = ($couponApply['discount_type'] != 'percentage') ? $global_currency : "%";
                                                                $stripelog->coupon_code = $couponCode;
                                                                $stripelog->arm_coupon_discount = $arm_coupon_discount;
                                                                $stripelog->arm_coupon_discount_type = $arm_coupon_discount_type;
                                                                $stripelog->arm_coupon_on_each_subscriptions = $arm_coupon_on_each_subscriptions;
                                                            }
                                                        }

                                                        $defaultPlanData = $arm_subscription_plans->arm_default_plan_array();
                                                        $userPlanDatameta = get_user_meta($user_id, 'arm_user_plan_' . $entry_plan, true);
                                                        $userPlanDatameta = !empty($userPlanDatameta) ? $userPlanDatameta : array();
                                                        $userPlanData = shortcode_atts($defaultPlanData, $userPlanDatameta);
                                                        
                                                        $create_new_user = false;

                                                        if (!$user_info && in_array($armform->type, array('registration'))) {

                                                            $payment_done = array();
                                                            if ($payment_log_id) {
                                                                $payment_done = array('status' => TRUE, 'log_id' => $payment_log_id, 'entry_id' => $entry_id);
                                                            }
                                                            $entry_values['payment_done'] = '1';
                                                            $entry_values['arm_entry_id'] = $entry_id;
                                                            $entry_values['arm_update_user_from_profile'] = 0;
                                                            $create_new_user = true;
                                                            
                                                        } else {

                                                            $user_id = $user_info->ID;
                                                            
                                                            if (!empty($user_id)) {
                                                                
                                                                global $is_multiple_membership_feature;

                                                                $arm_is_paid_post = false;
                                                                if( !empty( $entry_values['arm_is_post_entry'] ) && !empty( $entry_values['arm_paid_post_id'] ) ){
                                                                    $arm_is_paid_post = true;
                                                                }
                                                                
                                                                if (!$is_multiple_membership_feature->isMultipleMembershipFeature && !$arm_is_paid_post ) {

                                                                    $old_plan_ids = get_user_meta($user_id, 'arm_user_plan_ids', true);
                                                                    $old_plan_id = isset($old_plan_ids[0]) ? $old_plan_ids[0] : 0;
                                                                    $oldPlanDetail = array();
                                                                    $old_subscription_id = '';
                                                                    
                                                                    if (!empty($old_plan_id)) {
                                                                        $oldPlanData = get_user_meta($user_id, 'arm_user_plan_' . $old_plan_id, true);
                                                                        $oldPlanData = !empty($oldPlanData) ? $oldPlanData : array();
                                                                        $oldPlanData = shortcode_atts($defaultPlanData, $oldPlanData);
                                                                        $oldPlanDetail = $oldPlanData['arm_current_plan_detail'];
                                                                        $subscr_effective = $oldPlanData['arm_expire_plan'];
                                                                        $old_subscription_id = $oldPlanData['arm_subscr_id'];
                                                                    }
                                                                    
                                                                    $arm_user_old_plan_details = (isset($userPlanData['arm_current_plan_detail']) && !empty($userPlanData['arm_current_plan_detail'])) ? $userPlanData['arm_current_plan_detail'] : array();
                                                                    $arm_user_old_plan_details['arm_user_old_payment_mode'] = $userPlanData['arm_payment_mode'];
                                                                    
                                                                    if (!empty($old_subscription_id) && $entry_values['arm_selected_payment_mode'] == 'auto_debit_subscription' && $arm_token == $old_subscription_id) {

                                                                        $arm_next_due_payment_date = $userPlanData['arm_next_due_payment'];
                                                                        if (!empty($arm_next_due_payment_date)) {
                                                                            if (strtotime(current_time('mysql')) >= $arm_next_due_payment_date) {
                                                                                $arm_user_completed_recurrence = $userPlanData['arm_completed_recurring'];
                                                                                $arm_user_completed_recurrence++;
                                                                                $userPlanData['arm_completed_recurring'] = $arm_user_completed_recurrence;
                                                                                update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);
                                                                                $arm_next_payment_date = $arm_members_class->arm_get_next_due_date($user_id, $entry_plan, false, $payment_cycle);
                                                                                if ($arm_next_payment_date != '') {
                                                                                    $userPlanData['arm_next_due_payment'] = $arm_next_payment_date;
                                                                                    update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);
                                                                                }
                                                                            }
                                                                            else{
                                                                                $now = current_time('mysql');
                                                                                $arm_last_payment_status = $wpdb->get_var($wpdb->prepare("SELECT `arm_transaction_status` FROM `" . $ARMember->tbl_arm_payment_log . "` WHERE `arm_user_id`=%d AND `arm_plan_id`=%d AND `arm_created_date`<=%s ORDER BY `arm_log_id` DESC LIMIT 0,1", $user_id, $entry_plan, $now));

                                                                                   if(in_array($arm_last_payment_status, array('success','pending'))){
                                                                                    $arm_user_completed_recurrence = $userPlanData['arm_completed_recurring'];
                                                                                        $arm_user_completed_recurrence++;
                                                                                        $userPlanData['arm_completed_recurring'] = $arm_user_completed_recurrence;
                                                                                        update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);
                                                                                        $arm_next_payment_date = $arm_members_class->arm_get_next_due_date($user_id, $entry_plan, false, $payment_cycle);
                                                                                        if ($arm_next_payment_date != '') {
                                                                                            $userPlanData['arm_next_due_payment'] = $arm_next_payment_date;
                                                                                            update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);
                                                                                        }
                                                                                    
                                                                                }
                                                                            }
                                                                        }

                                                                        $suspended_plan_ids = get_user_meta($user_id, 'arm_user_suspended_plan_ids', true);
                                                                        $suspended_plan_id = (isset($suspended_plan_ids) && !empty($suspended_plan_ids)) ? $suspended_plan_ids : array();

                                                                        if (in_array($entry_plan, $suspended_plan_id)) {
                                                                            unset($suspended_plan_id[array_search($entry_plan, $suspended_plan_id)]);
                                                                            update_user_meta($user_id, 'arm_user_suspended_plan_ids', array_values($suspended_plan_id));
                                                                        }
                                                                    } else {

                                                                        $now = current_time('mysql');
                                                                        $arm_last_payment_status = $wpdb->get_var($wpdb->prepare("SELECT `arm_transaction_status` FROM `" . $ARMember->tbl_arm_payment_log . "` WHERE `arm_user_id`=%d AND `arm_plan_id`=%d AND `arm_created_date`<=%s ORDER BY `arm_log_id` DESC LIMIT 0,1", $user_id, $entry_plan, $now));
                                                                        
                                                                        $userPlanData['arm_current_plan_detail'] = $arm_user_old_plan_details;

                                                                        $userPlanData['arm_payment_mode'] = $entry_values['arm_selected_payment_mode'];
                                                                        $userPlanData['arm_payment_cycle'] = $entry_values['arm_selected_payment_cycle'];



                                                                        if (!empty($oldPlanDetail)) {
                                                                            $old_plan = new ARM_Plan(0);
                                                                            $old_plan->init((object) $oldPlanDetail);
                                                                        } else {
                                                                            $old_plan = new ARM_Plan($old_plan_id);
                                                                        }
                                                                        $is_update_plan = true;

                                                                        $recurring_data = $new_plan->prepare_recurring_data($payment_cycle);
                                                                        if (!empty($recurring_data['trial']) && empty($arm_user_old_plan)) {
                                                                            $extraParam['trial'] = array(
                                                                                'amount' => $recurring_data['trial']['amount'],
                                                                                'period' => $recurring_data['trial']['period'],
                                                                                'interval' => $recurring_data['trial']['interval'],
                                                                               
                                                                            );
                                                                            $extraParam['tax_amount'] = isset( $charge_details['tax_amount'] ) ? $charge_details['tax_amount'] : 0;
                                                                        }
                                                                        if( $arm_coupon_discount > 0){
                                                                            $extraParam['tax_amount'] = isset( $charge_details['tax_amount'] ) ? $charge_details['tax_amount'] : 0;
                                                                        }
                                                                        if ($old_plan->exists()) {
                                                                            if ($old_plan->is_lifetime() || $old_plan->is_free() || ($old_plan->is_recurring() && $new_plan->is_recurring())) {
                                                                                $is_update_plan = true;
                                                                            } else {
                                                                                $change_act = 'immediate';
                                                                                if ($old_plan->enable_upgrade_downgrade_action == 1) {
                                                                                    if (!empty($old_plan->downgrade_plans) && in_array($new_plan->ID, $old_plan->downgrade_plans)) {
                                                                                        $change_act = $old_plan->downgrade_action;
                                                                                    }
                                                                                    if (!empty($old_plan->upgrade_plans) && in_array($new_plan->ID, $old_plan->upgrade_plans)) {
                                                                                        $change_act = $old_plan->upgrade_action;
                                                                                    }
                                                                                }
                                                                                if ($change_act == 'on_expire' && !empty($subscr_effective)) {
                                                                                    $is_update_plan = false;
                                                                                    $oldPlanData['arm_subscr_effective'] = $subscr_effective;
                                                                                    $oldPlanData['arm_change_plan_to'] = $entry_plan;
                                                                                    update_user_meta($user_id, 'arm_user_plan_' . $old_plan_id, $oldPlanData);
                                                                                }
                                                                            }
                                                                        }

                                                                        update_user_meta($user_id, 'arm_entry_id', $entry_id);
                                                                        $userPlanData['arm_user_gateway'] = 'stripe';

                                                                        if (!empty($arm_token)) {
                                                                            $userPlanData['arm_subscr_id'] = $arm_token;
                                                                        }
                                                                        update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);
                                                                        if ($is_update_plan) {
                                                                            $arm_subscription_plans->arm_update_user_subscription($user_id, $entry_plan, '', true, $arm_last_payment_status);
                                                                        } else {
                                                                            
                                                                            $arm_subscription_plans->arm_add_membership_history($user_id, $entry_plan, 'change_subscription');
                                                                        }
                                                                    }
                                                                    
                                                                } else {
                                                                    
                                                                    $old_plan_ids = get_user_meta($user_id, 'arm_user_plan_ids', true);

                                                                    $oldPlanDetail = array();
                                                                    $old_subscription_id = '';
                                                                    if (in_array($entry_plan, $old_plan_ids)) {

                                                                        $oldPlanData = get_user_meta($user_id, 'arm_user_plan_' . $entry_plan, true);
                                                                        $oldPlanDetail = $oldPlanData['arm_current_plan_detail'];
                                                                        $subscr_effective = $oldPlanData['arm_expire_plan'];
                                                                        $old_subscription_id = $oldPlanData['arm_subscr_id'];
                                                                        
                                                                        $arm_user_old_plan_details = (isset($userPlanData['arm_current_plan_detail']) && !empty($userPlanData['arm_current_plan_detail'])) ? $userPlanData['arm_current_plan_detail'] : array();
                                                                        $arm_user_old_plan_details['arm_user_old_payment_mode'] = $userPlanData['arm_payment_mode'];
                                                                        if (!empty($old_subscription_id) && $entry_values['arm_selected_payment_mode'] == 'auto_debit_subscription' && $arm_token == $old_subscription_id) {
                                                                           
                                                                            $userPlanData['arm_payment_mode'] = $entry_values['arm_selected_payment_mode'];
                                                                            $userPlanData['arm_payment_cycle'] = $entry_values['arm_selected_payment_cycle'];

                                                                            $is_update_plan = true;

                                                                            $recurring_data = $new_plan->prepare_recurring_data($payment_cycle);
                                                                            if (!empty($recurring_data['trial']) && empty($arm_user_old_plan)) {
                                                                                $extraParam['trial'] = array(
                                                                                    'amount' => $recurring_data['trial']['amount'],
                                                                                    'period' => $recurring_data['trial']['period'],
                                                                                    'interval' => $recurring_data['trial']['interval'],
                                                                                );
                                                                                $extraParam['tax_amount'] = isset( $charge_details['tax_amount'] ) ? $charge_details['tax_amount'] : 0;
                                                                            }

                                                                            if( $arm_coupon_discount > 0){
                                                                                $extraParam['tax_amount'] = isset( $charge_details['tax_amount'] ) ? $charge_details['tax_amount'] : 0;
                                                                            }

                                                                            update_user_meta($user_id, 'arm_entry_id', $entry_id);
                                                                            $userPlanData['arm_user_gateway'] = 'stripe';

                                                                            if (!empty($arm_token)) {
                                                                                $userPlanData['arm_subscr_id'] = $arm_token;
                                                                            }
                                                                            update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);
                                                                            if ($is_update_plan) {
                                                                                $arm_subscription_plans->arm_update_user_subscription($user_id, $entry_plan);
                                                                            } else {
                                                                                $arm_subscription_plans->arm_add_membership_history($user_id, $entry_plan, 'new_subscription');
                                                                            }
                                                                        } else {
                                                                            $now = current_time('mysql');
                                                                            $arm_last_payment_status = $wpdb->get_var($wpdb->prepare("SELECT `arm_transaction_status` FROM `" . $ARMember->tbl_arm_payment_log . "` WHERE `arm_user_id`=%d AND `arm_plan_id`=%d AND `arm_created_date`<=%s ORDER BY `arm_log_id` DESC LIMIT 0,1", $user_id, $entry_plan, $now));
                                                                            

                                                                            $userPlanData['arm_current_plan_detail'] = $arm_user_old_plan_details;

                                                                            $userPlanData['arm_payment_mode'] = $entry_values['arm_selected_payment_mode'];
                                                                            $userPlanData['arm_payment_cycle'] = $entry_values['arm_selected_payment_cycle'];



                                                                            if (!empty($oldPlanDetail)) {
                                                                                $old_plan = new ARM_Plan(0);
                                                                                $old_plan->init((object) $oldPlanDetail);
                                                                            } else {
                                                                                $old_plan = new ARM_Plan($old_plan_id);
                                                                            }
                                                                            $is_update_plan = true;

                                                                            $recurring_data = $new_plan->prepare_recurring_data($payment_cycle);
                                                                            if (!empty($recurring_data['trial']) && empty($arm_user_old_plan)) {
                                                                                $extraParam['trial'] = array(
                                                                                    'amount' => $recurring_data['trial']['amount'],
                                                                                    'period' => $recurring_data['trial']['period'],
                                                                                    'interval' => $recurring_data['trial']['interval'],
                                                                                   
                                                                                );
                                                                                $extraParam['tax_amount'] = isset( $charge_details['tax_amount'] ) ? $charge_details['tax_amount'] : 0;
                                                                            }
                                                                            if( $arm_coupon_discount > 0){
                                                                                $extraParam['tax_amount'] = isset( $charge_details['tax_amount'] ) ? $charge_details['tax_amount'] : 0;
                                                                            }
                                                                            if ($old_plan->exists()) {
                                                                                if ($old_plan->is_lifetime() || $old_plan->is_free() || ($old_plan->is_recurring() && $new_plan->is_recurring())) {
                                                                                    $is_update_plan = true;
                                                                                } else {
                                                                                    $change_act = 'immediate';
                                                                                    if ($old_plan->enable_upgrade_downgrade_action == 1) {
                                                                                        if (!empty($old_plan->downgrade_plans) && in_array($new_plan->ID, $old_plan->downgrade_plans)) {
                                                                                            $change_act = $old_plan->downgrade_action;
                                                                                        }
                                                                                        if (!empty($old_plan->upgrade_plans) && in_array($new_plan->ID, $old_plan->upgrade_plans)) {
                                                                                            $change_act = $old_plan->upgrade_action;
                                                                                        }
                                                                                    }
                                                                                    if ($change_act == 'on_expire' && !empty($subscr_effective)) {
                                                                                        $is_update_plan = false;
                                                                                        $oldPlanData['arm_subscr_effective'] = $subscr_effective;
                                                                                        $oldPlanData['arm_change_plan_to'] = $entry_plan;
                                                                                        update_user_meta($user_id, 'arm_user_plan_' . $old_plan_id, $oldPlanData);
                                                                                    }
                                                                                }
                                                                            }

                                                                            update_user_meta($user_id, 'arm_entry_id', $entry_id);
                                                                            $userPlanData['arm_user_gateway'] = 'stripe';

                                                                            if (!empty($arm_token)) {
                                                                                $userPlanData['arm_subscr_id'] = $arm_token;
                                                                            }
                                                                            update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);
                                                                            if ($is_update_plan) {
                                                                               
                                                                                $arm_subscription_plans->arm_update_user_subscription($user_id, $entry_plan, '', true, $arm_last_payment_status);
                                                                            } else {
                                                                                
                                                                                $arm_subscription_plans->arm_add_membership_history($user_id, $entry_plan, 'change_subscription');
                                                                            }
                                                                            $suspended_plan_ids = get_user_meta($user_id, 'arm_user_suspended_plan_ids', true);
                                                                            $suspended_plan_id = (isset($suspended_plan_ids) && !empty($suspended_plan_ids)) ? $suspended_plan_ids : array();

                                                                            if (in_array($entry_plan, $suspended_plan_id)) {
                                                                                unset($suspended_plan_id[array_search($entry_plan, $suspended_plan_id)]);
                                                                                update_user_meta($user_id, 'arm_user_suspended_plan_ids', array_values($suspended_plan_id));
                                                                            }
                                                                        }
                                                                    } else {
                                                                        
                                                                        $userPlanData['arm_payment_mode'] = $entry_values['arm_selected_payment_mode'];
                                                                        $userPlanData['arm_payment_cycle'] = $entry_values['arm_selected_payment_cycle'];
                                                                        $is_update_plan = true;
                                                                        
                                                                        $recurring_data = $new_plan->prepare_recurring_data($payment_cycle);
                                                                        if (!empty($recurring_data['trial']) && empty($arm_user_old_plan)) {
                                                                            $extraParam['trial'] = array(
                                                                                'amount' => $recurring_data['trial']['amount'],
                                                                                'period' => $recurring_data['trial']['period'],
                                                                                'interval' => $recurring_data['trial']['interval'],
                                                                            );
                                                                            $extraParam['tax_amount'] = isset( $charge_details['tax_amount'] ) ? $charge_details['tax_amount'] : 0;
                                                                        }
                                                                        if( $arm_coupon_discount > 0){
                                                                                $extraParam['tax_amount'] = isset( $charge_details['tax_amount'] ) ? $charge_details['tax_amount'] : 0;
                                                                            }
                                                                        update_user_meta($user_id, 'arm_entry_id', $entry_id);
                                                                        $userPlanData['arm_user_gateway'] = 'stripe';

                                                                        if (!empty($arm_token)) {
                                                                            $userPlanData['arm_subscr_id'] = $arm_token;
                                                                        }
                                                                        update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);
                                                                        if ($is_update_plan) {
                                                                            $arm_subscription_plans->arm_update_user_subscription($user_id, $entry_plan);
                                                                        } else {
                                                                            $arm_subscription_plans->arm_add_membership_history($user_id, $entry_plan, 'new_subscription');
                                                                        }
                                                                    }
                                                                }
                                                                $is_log = true;
                                                            }
                                                        }
                                                        $stripe_response = $stripelog;

                                                        $plan_id = $entry_plan;
                                                        $payer_email = $entry_email;
                                                        $extraVars = $extraParam;

                                                        $custom_var = $subscription_data->metadata->custom;
                                                        $customs = explode('|', $custom_var);
                                                        $entry_id = $customs[0];
                                                        $entry_email = $customs[1];
                                                        $form_id = $customs[2];
                                                        $arm_payment_type = $customs[3];
                                                        $tax_percentage = isset($subscription_data->metadata->tax_percentage) ? $subscription_data->metadata->tax_percentage : 0;
                                                        $zero_demial_currencies = $arm_payment_gateways->arm_stripe_zero_decimal_currency_array();

                                                        if (!empty($subscription_data->plan) && $subscription_data->object == 'subscription') {
                                                            
                                                            $amount = $subscription_data->plan->amount;
                                                            $currency = strtoupper($subscription_data->plan->currency);
                                                            if (!in_array($currency, $zero_demial_currencies)) {
                                                                 $amount = $subscription_data->plan->amount / 100;
                                                            }

                                                            $arm_payment_date = date('Y-m-d H:i:s', $subscription_data->current_period_start);
                                                            $arm_token = $subscription_data->customer;
                                                            $arm_payment_type = 'subscription';

                                                            if( $subscription_data->discount != null  && $subscription_data->discount != 'null') {
                                                                if( isset($subscription_data->discount->coupon)) {
                                                                    if($subscription_data->discount->coupon->amount_off != null && $subscription_data->discount->coupon->amount_off != 'null') {

                                                                        $amount_off = $subscription_data->discount->coupon->amount_off;
                                                                      
                                                                        if($amount_off > 0) {

                                                                            if (!in_array($currency, $zero_demial_currencies)) {
                                                                                $amount_off = $amount_off/100;
                                                                            }

                                                                            $amount = $amount - $amount_off;
                                                                        }
                                                                    }
                                                                    else if($subscription_data->discount->coupon->percent_off != null && $subscription_data->discount->coupon->percent_off != 'null') {
                                                                        $percent_off = $subscription_data->discount->coupon->percent_off;
                                                                            
                                                                        if($percent_off > 0) {

                                                                            $coupon_amount = ($amount*$percent_off)/100;
                                                                            $coupon_amount = number_format((float)$coupon_amount, 2, '.', '');
                                                                            $amount = $amount - $coupon_amount;
                                                                        }
                                                                    }
                                                                }
                                                            }

                                                            if($tax_percentage > 0) {
                                                                $tax_amount = ($amount*$tax_percentage)/100;
                                                                $tax_amount = number_format((float)$tax_amount, 2, '.', '');
                                                                $amount = $tax_amount + $amount;
                                                            }
                                                        } else {
                                                            
                                                            $currency = strtoupper($stripe_response->currency);
                                                            $amount = $stripe_response->amount_paid;
                                                            if (!in_array($currency, $zero_demial_currencies)) {
                                                                $amount = $stripe_response->amount_paid / 100;
                                                            }

                                                            if( !empty($stripe_response->created) ) {
                                                                $arm_payment_date = date('Y-m-d H:i:s', $stripe_response->created);
                                                            }
                                                            else {
                                                                $arm_payment_date = date('Y-m-d H:i:s');
                                                            }

                                                            $arm_token = $charge_data->source->id;
                                                            $arm_payment_type = 'subscription';
                                                        }

                                                        $coupon_code = '';
                                                        $coupon_discount = 0;
                                                        $coupon_discount_type = '';
                                                        $arm_coupon_on_each_subscriptions = '0';
                                                        if (isset($coupon_details) && !empty($coupon_details)) {
                                                            $coupon_code = $coupon_details['coupon_code'];
                                                            $coupon_discount = $coupon_details['arm_coupon_discount'];
                                                            $coupon_discount_type = $coupon_details['arm_coupon_discount_type'];
                                                            $arm_coupon_on_each_subscriptions = isset($coupon_details['arm_coupon_on_each_subscriptions']) ? $coupon_details['arm_coupon_on_each_subscriptions'] : '0';
                                                        }

                                                        if($amount < 0) {
                                                            $amount = 0;
                                                        }

                                                        if($subscription_data->discount == null && !empty($coupon_code) && !empty($coupon_discount) && !empty($coupon_discount_type))
                                                        {
                                                            if($coupon_discount_type == '%'){
                                                                $amount = $amount - (($amount * $coupon_discount)/100);
                                                            }else{
                                                                $amount = $amount - $coupon_discount;
                                                            }
                                                        }

                                                        $arm_response_text = array(
                                                            'invoice_data' => (array)$invoiceObj,
                                                            'charge_data' => (array)$charge_data,
                                                            'subscription_data' => (array)$subscription_data,
                                                        );

                                                        $payment_data = array(
                                                            'arm_user_id' => $user_id,
                                                            'arm_first_name'=> (isset($user_info->first_name))?$user_info->first_name:'',
                                                            'arm_last_name'=> (isset($user_info->last_name))?$user_info->last_name:'',
                                                            'arm_plan_id' => $plan_id,
                                                            'arm_payment_gateway' => 'stripe',
                                                            'arm_payment_type' => $arm_payment_type,
                                                            'arm_token' => $arm_token,
                                                            'arm_payer_email' => $payer_email,
                                                            'arm_receiver_email' => '',
                                                            'arm_transaction_id' => $subscription_id,
                                                            'arm_transaction_payment_type' => $subscription_data->object,
                                                            'arm_transaction_status' => $stripe_response->status,
                                                            'arm_payment_mode' => $payment_mode,
                                                            'arm_payment_date' => $arm_payment_date,
                                                            'arm_amount' => $amount,
                                                            'arm_currency' => $currency,
                                                            'arm_coupon_code' => $coupon_code,
                                                            'arm_coupon_discount' => $coupon_discount,
                                                            'arm_coupon_discount_type' => $coupon_discount_type,
                                                            'arm_response_text' => utf8_encode(maybe_serialize($arm_response_text)),
                                                            'arm_extra_vars' => maybe_serialize($extraVars),
                                                            'arm_is_trial' => isset($extraVars['arm_is_trial']) ? $extraVars['arm_is_trial'] : '0',
                                                            'arm_created_date' => current_time('mysql'),
                                                            'arm_coupon_on_each_subscriptions' => $arm_coupon_on_each_subscriptions,
                                                            'arm_display_log' => 1
                                                        );

                                                        $payment_log_id = $arm_payment_gateways->arm_save_payment_log($payment_data);

                                                        if ($payment_log_id) {
                                                            $payment_done = array('status' => TRUE, 'log_id' => $payment_log_id, 'entry_id' => $entry_id);
                                                        }

                                                        if( $create_new_user ){
                                                            $user_id = $arm_member_forms->arm_register_new_member($entry_values, $armform);
                                                        }

                                                        if (is_numeric($user_id) && !is_array($user_id)) {
                                                            
                                                            if ($arm_payment_type == 'subscription') {
                                                                $userPlanDatameta = get_user_meta($user_id, 'arm_user_plan_' . $entry_plan, true);
                                                                $userPlanDatameta = !empty($userPlanDatameta) ? $userPlanDatameta : array();
                                                                $userPlanData = shortcode_atts($defaultPlanData, $userPlanDatameta);

                                                                $userPlanData['arm_subscr_id'] = $arm_token;
                                                                $userPlanData['arm_stripe'] = array(
                                                                    'customer_id' => $customer_id,
                                                                    'transaction_id' => $subscription_id
                                                                );
                                                                update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);

                                                                $pgateway = 'stripe';
                                                                $arm_manage_coupons->arm_coupon_apply_to_subscription($user_id, $payment_log_id, $pgateway, $userPlanData);
                                                            }
                                                            update_user_meta($user_id, 'arm_entry_id', $entry_id);
                                                        }
                                                        if($plan_action=="recurring_payment")
                                                        {
                                                            do_action('arm_after_recurring_payment_success_outside', $user_id, $entry_plan, 'stripe', $entry_values['arm_selected_payment_mode']);
                                                        }

                                                    }
                                                }
                                            } else {
                                                
                                            }
                                        }

                                    } else {
                                        echo json_encode(
                                            array(
                                                'error' => true,
                                                'message' => esc_html__( 'Sorry something went wrong while processing payment', 'ARMember')
                                            )
                                        );
                                        die;
                                    }
                                } else {
                                    echo json_encode(
                                        array(
                                            'error' => true,
                                            'message' => esc_html__( 'Sorry something went wrong while processing payment', 'ARMember')
                                        )
                                    );
                                    die;
                                }

                            }

                        } else {
                            if( isset( $arm_stripe_enable_debug_mode ) && '1' == $arm_stripe_enable_debug_mode ){
                                echo json_encode(
                                    array(
                                        'error' => true,
                                        'message' => $invoiceObj
                                    )
                                );
                            } else {
                                echo json_encode(
                                    array(
                                        'error' => true,
                                        'message' => esc_html__( 'Sorry something went wrong while processing payment', 'ARMember')
                                    )
                                );
                            }
                            die;
                        }
                    }
                }
            }
        }

        function arm_store_paid_trial_subscription_payment(){
            global $wpdb, $ARMember, $arm_global_settings, $arm_member_forms, $arm_payment_gateways, $payment_done, $arm_subscription_plans, $arm_manage_communication, $arm_members_class, $arm_manage_coupons, $arm_stripe;

            $pi_id = $_POST['pi_id'];

            $pm_id = $_POST['pm_id'];
            
            $entry_id = $_POST['entry_id'];

            $source_id = $_POST['sourceId'];

            $stripe_plan_id = $_POST['stripe_plan_id'];

            $charge_details = json_decode( stripslashes_deep( $_POST['charge_details'] ), true );

            $charge_details1 = json_decode( stripslashes_deep( $_POST['charge_details1'] ), true );

            $payment_cycle = json_decode( stripslashes_deep( $_POST['payment_cycle'] ) );

            $isFreePaidTrail = $_POST['isFreePaidTrail'];

            $plan_action = $_POST['plan_action'];

            $payment_mode = $_POST['plan_mode'];

            $entry_details = $wpdb->get_row( $wpdb->prepare( "SELECT arm_plan_id,arm_entry_email FROM `" . $ARMember->tbl_arm_entries . "` WHERE arm_entry_id = %d", $entry_id ) );
            
            $plan_id = $entry_details->arm_plan_id;
            $entry_email = $entry_details->arm_entry_email;

            $entry_data = $arm_payment_gateways->arm_get_entry_data_by_id($entry_id);

            $entry_values = maybe_unserialize($entry_data['arm_entry_value']);

            $tax_percentage = isset($entry_values['tax_percentage']) ? $entry_values['tax_percentage'] : 0 ;
            
            $plan = new ARM_Plan($plan_id);

            $active_payment_gateways = $arm_payment_gateways->arm_get_active_payment_gateways();

            $extraParam = array();

            if( !empty( $charge_details ) ){

                if (isset($charge_details['status']) && $charge_details['status'] == TRUE) {
                    $payment_done = $charge_details;
                    return $payment_done;
                }
                if (isset($charge_details['extraVars'])) {
                    $extraParam = $charge_details['extraVars'];
                    unset($charge_details['extraVars']);
                }
                $coupon_details = array();
                if (isset($charge_details['coupon_details'])) {
                    $coupon_details = $charge_details['coupon_details'];
                }
                $charge_details['plan_action'] = $plan_action;
                $charge_details['expire_date'] = $plan_expiry_date;

                $charge_details['tax_percentage'] = $tax_percentage; 
                $extraParam['tax_percentage'] = $tax_percentage;
                $extraParam['tax_amount'] =  isset($charge_details['tax_amount'])? $charge_details['tax_amount']  : 0; 
                unset($charge_details['tax_amount']);
            }
            

            $payment_gateway_options = $active_payment_gateways['stripe'];

            $arm_stripe_enable_debug_mode = isset($payment_gateway_options['enable_debug_mode']) ? $payment_gateway_options['enable_debug_mode'] : 0;

            if( 'test' == $payment_gateway_options['stripe_payment_mode'] ){
                $sec_key = $payment_gateway_options['stripe_test_secret_key'];
                $pub_key = $payment_gateway_options['stripe_test_pub_key'];
            } else {
                $sec_key = $payment_gateway_options['stripe_secret_key'];
                $pub_key = $payment_gateway_options['stripe_pub_key'];
            }

            $currency = $arm_payment_gateways->arm_get_global_currency();

            $headers = array(
                'Authorization' => 'Bearer '.$sec_key
            );
            $extraVars1 = array();
            if( !empty( $charge_details1 ) ){
                if (isset($charge_details1['extraVars'])) {
                    $extraVars1 = $charge_details1['extraVars'];
                    unset($charge_details1['extraVars']);
                }

                $coupon_details = array();
                if (isset($charge_details1['coupon_details'])) {
                    $coupon_details = $charge_details1['coupon_details'];
                    if(empty($charge_details1['coupon_details']['arm_coupon_on_each_subscriptions']))
                    {
                        unset($charge_details1['coupon_details']);
                    }
                }
                unset($charge_details1['source']);
                $extraVars1['tax_percentage'] = $charge_details1['tax_percentage'] = $tax_percentage;
                $extraVars1['tax_amount'] =  isset($charge_details1['tax_amount'])? $charge_details1['tax_amount']  : 0; 
            }

            $metadata_str1 = '';
            $charge_details1['metadata']['tax_percentage'] = str_replace('%', '', $charge_details1['metadata']['tax_percentage']);
            foreach( $charge_details1['metadata'] as $mkey => $mvalue ){
                $metadata_str1 .= '&metadata['.$mkey.']=' . $mvalue;
            }

            if( isset( $charge_details1['metadata']['tax_percentage'] ) && $charge_details1['metadata']['tax_percentage'] > 0 ){
                $tax_data = wp_remote_post(
                    'https://api.stripe.com/v1/tax_rates',
                    array(
                        'headers' => $headers,
                        'timeout' => 5000,
                        'body' => 'display_name=Tax&inclusive=false&percentage=' . $charge_details1['metadata']['tax_percentage']
                    )
                );
                if( is_wp_error( $tax_data ) ){
                    if( isset( $arm_stripe_enable_debug_mode ) && '1' == $arm_stripe_enable_debug_mode ){
                        echo json_encode(
                            array(
                                'type' => 'error',
                                'message' => $tax_data['body']
                            )
                        );
                    } else {
                        echo json_encode(
                            array(
                                'type' => 'error',
                                'message' => esc_html__( 'Sorry, something went wrong while creating tax', 'ARMember' )
                            )
                        );
                    }
                    die;
                } else {
                    $tax_response = json_decode( $tax_data['body'] );

                    if( $tax_response->id ){
                        $metadata_str .= '&default_tax_rates[0]=' . $tax_response->id;
                    } else {
                        if( isset( $arm_stripe_enable_debug_mode ) && '1' == $arm_stripe_enable_debug_mode ){
                            echo json_encode(
                                array(
                                    'type' => 'error',
                                    'message' => $tax_response->error->message
                                )
                            );
                            die;
                        }  else {
                            echo json_encode(
                                array(
                                    'type' => 'error',
                                    'message' => esc_html__('Sorry, something went wrong while processing payment', 'ARMember')
                                )
                            );
                            die;
                       }
                    }
                }
            }

            $api_url = 'https://api.stripe.com/v1/payment_intents/' . $pi_id;

            $data = wp_remote_post(
                $api_url,
                array(
                    'headers' => $headers,
                    'timeout' => 5000
                )
            );

            if( is_wp_error( $data ) ){
                if( isset( $arm_stripe_enable_debug_mode ) && '1' == $arm_stripe_enable_debug_mode ){
                    echo json_encode(
                        array(
                            'type' => 'error',
                            'message' => $data['body']
                        )
                    );
                } else {
                    echo json_encode(
                        array(
                            'type' => 'error',
                            'error' => true,
                            'message' => esc_html__( 'Sorry something went wrong while processing payment', 'ARMember')
                        )
                    );
                }
                die;
            } else {
                $piData = json_decode( $data['body'] );

                if( ! $piData->id ){
                    if( isset( $arm_stripe_enable_debug_mode ) && '1' == $arm_stripe_enable_debug_mode ){
                        echo json_encode(
                            array(
                                'type' => 'error',
                                'message' => $piData['body']
                            )
                        );
                    } else {
                        echo json_encode(
                            array(
                                'type' => 'error',
                                'error' => true,
                                'message' => esc_html__( 'Sorry something went wrong while processing payment', 'ARMember')
                            )
                        );
                    }
                    die;
                } else {
                    $charge_data = $piData->charges->data[0];
                    if( isset( $charge_data->paid ) && true == $charge_data->paid ){

                        $card_number = $charge_data->payment_method_details->card->last4;
                        if( !$isFreePaidTrail ){
                            $stripelog1 = new stdClass();
                            foreach( $charge_data as $k => $v ){
                                $stripelog1->$k = $v;
                            }
                        } else {
                            $stripelog1 = new stdClass();
                        }

                        $txn_id = $charge_data->id;

                        $entry_data = $wpdb->get_row("SELECT `arm_entry_id`, `arm_entry_email`, `arm_entry_value`, `arm_form_id`, `arm_user_id`, `arm_plan_id` FROM `" . $ARMember->tbl_arm_entries . "` WHERE `arm_entry_id`='" . $entry_id . "' AND `arm_entry_email`='" . $entry_email . "'", ARRAY_A);
                        if( !empty( $entry_data ) ){

                            if( $isFreePaidTrail ){
                                $opt_trial = $plan->options['trial'];
                                $extraVars1['plan_amount'] = $opt_trial['amount'];
                                $extraVars1['paid_amount'] = '0.00';
                            } else {
                                $opt_trial = $plan->options['trial'];
                                $extraVars1['plan_amount'] = $opt_trial['amount'];
                                $extraVars1['paid_amount'] = $charge_data->amount;
                                $extraVars1['card_number'] = 'xxxx-xxxx-xxxx-'.$card_number;
                            }


                            $api_url = 'https://api.stripe.com/v1/customers';
                            $request_body = "&email=".$entry_email."&source=" . $source_id;
                            
                            $customer = wp_remote_post(
                                $api_url,
                                array(
                                    'headers' => $headers,
                                    'body' => $request_body,
                                    'timeout' => 5000
                                )
                            );

                            if( is_wp_error( $customer ) ){
                                if( isset( $arm_stripe_enable_debug_mode ) && '1' == $arm_stripe_enable_debug_mode ){
                                    echo json_encode(
                                        array(
                                            'type' => 'error',
                                            'message' => $customer['body']
                                        )
                                    );
                                } else {
                                    echo json_encode(
                                        array(
                                            'type' => 'error',
                                            'error' => true,
                                            'message' => esc_html__( 'Sorry something went wrong while processing payment', 'ARMember')
                                        )
                                    );
                                }
                                die;
                            } else {

                                $cusData = json_decode( $customer['body'] );
                               
                                if( $cusData->id ){

                                    $customer_id = $cusData->id;

                                    $piUpdateUrl = 'https://api.stripe.com/v1/payment_intents/'. $piData->id;

                                    $update_pi = wp_remote_post(
                                        $piUpdateUrl,
                                        array(
                                            'headers' => $headers,
                                            'body' => 'customer='.$customer_id,
                                            'timeout' => 5000
                                        )
                                    );

                                    $opt_trial_days = $plan->options['trial']['days'];

                                    $subUrl = 'https://api.stripe.com/v1/subscriptions';

                                    $charge_details['metadata']['tax_percentage'] = str_replace('%', '', $charge_details['metadata']['tax_percentage']);

                                    foreach( $charge_details['metadata'] as $mkey => $mvalue ){
                                        $metadata_str .= '&metadata['.$mkey.']=' . $mvalue;
                                    }

                                    $metadata_str .= '&metadata[email]=' . $entry_email;

                                    if( !empty( $coupon_details ) ){
                                        $coupon_code = $coupon_details['coupon_code'];

                                        $coupon_discount_type = $coupon_details['arm_coupon_discount_type'];
                                        $arm_coupon_on_each_subscriptions = isset($coupon_details['arm_coupon_on_each_subscriptions']) ? $coupon_details['arm_coupon_on_each_subscriptions'] : '0';
                                        $coupon_duration = "once";
                                        if(!empty($arm_coupon_on_each_subscriptions))
                                        {
                                            $coupon_duration = "forever";
                                        }

                                        if( $isFreePaidTrail && "once" == $coupon_duration ){
                                            //don't apply coupon to subscription if already applied to trail and duration is for once
                                        } else {

                                            $coupon_uri = 'https://api.stripe.com/v1/coupons/' . $coupon_code;

                                            $retrieve_coupon = wp_remote_post(
                                                $coupon_uri,
                                                array(
                                                    'timeout' => 5000,
                                                    'headers' => $headers
                                                )
                                            );

                                            if( is_wp_error( $retrieve_coupon ) ){

                                            } else {

                                                $coupon_data = json_decode( $retrieve_coupon['body'] );

                                                if( ! $coupon_data->id ){

                                                    $coupon_body = '';
                                                    if( $coupon_discount_type == '%' ){
                                                        $coupon_amount = str_replace(",", "", $coupon_details['arm_coupon_discount']);
                                                        $coupon_amount = number_format((float) $coupon_amount, 0, '', '');
                                                        $coupon_body = 'percent_off=' . $coupon_amount . '&duration=' . $coupon_duration . '&id=' . $coupon_code;
                                                    } else {
                                                        $coupon_amount = str_replace(",", "", $coupon_details['arm_coupon_discount']);
                                                        $coupon_amount = number_format((float) $coupon_amount, 2, '.', '');

                                                        if (!empty($coupon_amount)) {
                                                            $zero_demial_currencies = $arm_payment_gateways->arm_stripe_zero_decimal_currency_array();
                                                            if (!in_array($currency, $zero_demial_currencies)) {
                                                                $coupon_amount = $coupon_amount * 100;
                                                            }
                                                            else{
                                                                $coupon_amount = number_format((float) $coupon_amount, 0);
                                                                $coupon_amount = str_replace(",", "", $coupon_amount);
                                                            }
                                                        }

                                                        $coupon_body = 'amount_off=' . $coupon_amount . '&duration=' . $coupon_duration . '&id=' . $coupon_code . '&currency=' . $currency;
                                                    }
                                                    $create_coupon = wp_remote_post(
                                                        'https://api.stripe.com/v1/coupons',
                                                        array(
                                                            'headers' => $headers,
                                                            'timeout' => 5000,
                                                            'body' => $coupon_body
                                                        )
                                                    );

                                                    if( is_wp_error( $create_coupon ) ){

                                                    } else {

                                                        $coupon_data = json_decode( $create_coupon['body'] );

                                                        if( ! $coupon_data->id ){

                                                        } else {
                                                            $metadata_str .= '&coupon=' . $coupon_data->id;
                                                        }

                                                    }

                                                } else {
                                                    $coupon_created_date = $coupon_data->created;
                                                    $coupon_updated_date = $wpdb->get_var($wpdb->prepare("SELECT `arm_coupon_added_date` FROM  `$ARMember->tbl_arm_coupons` WHERE `arm_coupon_code` = %s", $coupon_code));
                                                    if (strtotime($coupon_updated_date) > $coupon_created_date) {
                                                        $delete_coupon = wp_remote_request(
                                                            'https//api.stripe.com/v1/coupons/' . $coupon_code,
                                                            array(
                                                                'headers' => $headers,
                                                                'method' => 'DELETE',
                                                                'timeout' => 5000
                                                            )
                                                        );

                                                        if( is_wp_error( $delete_coupon ) ){

                                                        } else {
                                                            $deleted_coupon = json_decode( $delete_coupon['body'] );

                                                            if( $deleted_coupon->deleted ){
                                                                $coupon_body = '';
                                                                if( $coupon_discount_type == '%' ){
                                                                    $coupon_amount = str_replace(",", "", $coupon_details['arm_coupon_discount']);
                                                                    $coupon_amount = number_format((float) $coupon_amount, 0, '', '');
                                                                    $coupon_body = 'percent_off=' . $coupon_amount . '&duration=' . $coupon_duration . '&id=' . $coupon_code;
                                                                } else {
                                                                    $coupon_amount = str_replace(",", "", $coupon_details['arm_coupon_discount']);
                                                                    $coupon_amount = number_format((float) $coupon_amount, 2, '.', '');

                                                                    if (!empty($coupon_amount)) {
                                                                        $zero_demial_currencies = $arm_payment_gateways->arm_stripe_zero_decimal_currency_array();
                                                                        if (!in_array($currency, $zero_demial_currencies)) {
                                                                            $coupon_amount = $coupon_amount * 100;
                                                                        }
                                                                        else{
                                                                            $coupon_amount = number_format((float) $coupon_amount, 0);
                                                                            $coupon_amount = str_replace(",", "", $coupon_amount);
                                                                        }
                                                                    }

                                                                    $coupon_body = 'amount_off=' . $coupon_amount . '&duration=' . $coupon_duration . '&id=' . $coupon_code . '&currency=' . $currency;
                                                                }
                                                                $create_coupon = wp_remote_post(
                                                                    'https://api.stripe.com/v1/coupons',
                                                                    array(
                                                                        'headers' => $headers,
                                                                        'timeout' => 5000,
                                                                        'body' => $coupon_body
                                                                    )
                                                                );

                                                                if( is_wp_error( $create_coupon ) ){

                                                                } else {

                                                                    $coupon_data = json_decode( $create_coupon['body'] );

                                                                    if( ! $coupon_data->id ){

                                                                    } else {
                                                                        $metadata_str .= '&coupon=' . $coupon_data->id;
                                                                    }

                                                                }
                                                            }
                                                        }
                                                    } else {
                                                        $metadata_str .= '&coupon=' . $coupon_code;
                                                    }
                                                }

                                            }
                                        }
                                    }

                                    if( isset( $charge_details['metadata']['tax_percentage'] ) && $charge_details['metadata']['tax_percentage'] > 0 ){
                                        $tax_data = wp_remote_post(
                                            'https://api.stripe.com/v1/tax_rates',
                                            array(
                                                'headers' => $headers,
                                                'timeout' => 5000,
                                                'body' => 'display_name=Tax&inclusive=false&percentage=' . $charge_details['metadata']['tax_percentage']
                                            )
                                        );

                                        if( is_wp_error( $tax_data ) ){
                                            if( isset( $arm_stripe_enable_debug_mode ) && '' == $arm_stripe_enable_debug_mode ){
                                                echo json_encode(
                                                    array(
                                                        'type' => 'error',
                                                        'message' => $tax_data['body']
                                                    )
                                                );
                                            } else {
                                                echo json_encode(
                                                    array(
                                                        'type' => 'error',
                                                        'error' => true,
                                                        'message' => esc_html__( 'Sorry something went wrong while processing payment', 'ARMember')
                                                    )
                                                );
                                            }
                                            die;
                                        } else {
                                            $tax_response = json_decode( $tax_data['body'] );
                                            if( $tax_response->id ){
                                                $metadata_str .= '&default_tax_rates[0]=' . $tax_response->id;
                                            } else {
                                                if( isset( $arm_stripe_enable_debug_mode ) && '1' == $arm_stripe_enable_debug_mode ){
                                                    echo json_encode(
                                                        array(
                                                            'type' => 'error',
                                                            'message' => $tax_data['body']
                                                        )
                                                    );
                                                } else {
                                                    echo json_encode(
                                                        array(
                                                            'type' => 'error',
                                                            'error' => true,
                                                            'message' => esc_html__( 'Sorry something went wrong while processing payment', 'ARMember')
                                                        )
                                                    );
                                                }
                                                die;
                                            }
                                        }
                                    }

                                    $request_body = 'items[0][plan]=' . $stripe_plan_id . '&trial_period_days=' .$opt_trial_days . '&customer=' . $customer_id . $metadata_str;

                                    $subscription = wp_remote_post(
                                        $subUrl,
                                        array(
                                            'headers' => $headers,
                                            'timeout' => 5000,
                                            'body' => $request_body
                                        )
                                    );

                                    if( is_wp_error( $subscription ) ){
                                        if( isset( $arm_stripe_enable_debug_mode ) && '1' == $arm_stripe_enable_debug_mode ){
                                            echo json_encode(
                                                array(
                                                    'type' => 'error',
                                                    'message' => $subscription['body']
                                                )
                                            );
                                        } else {
                                            echo json_encode(
                                                array(
                                                    'type' => 'error',
                                                    'error' => true,
                                                    'message' => esc_html__( 'Sorry something went wrong while processing payment', 'ARMember')
                                                )
                                            );
                                        }
                                        die;
                                    } else {
                                        $subObj = json_decode( $subscription['body'] );

                                        if( $subObj->id ){
                                            if( $subObj->status && 'trialing' == $subObj->status ){
                                                $subscription_id = $subObj->id;
                                                
                                                $invoice_obj = wp_remote_post(
                                                    'https://api.stripe.com/v1/invoices/' . $subObj->latest_invoice,
                                                    array(
                                                        'headers' => $headers,
                                                        'timeout' => 5000
                                                    )
                                                );

                                                if( is_wp_error( $invoice_obj ) ){
                                                    if( isset( $arm_stripe_enable_debug_mode ) && '1' == $arm_stripe_enable_debug_mode ){
                                                        echo json_encode(
                                                            array(
                                                                'type' => 'error',
                                                                'message' => $invoice_obj['body']
                                                            )
                                                        );
                                                    } else {
                                                        echo json_encode(
                                                            array(
                                                                'type' => 'error',
                                                                'error' => true,
                                                                'message' => esc_html__( 'Sorry something went wrong while processing payment', 'ARMember')
                                                            )
                                                        );
                                                    }
                                                    die;
                                                } else {
                                                    $invoiceObj = json_decode( $invoice_obj['body'] );

                                                    if( $invoiceObj->id ){
                                                        
                                                        $stripelog = new stdClass();
                                                        foreach( $invoiceObj->lines->data[0] as $k => $v ){
                                                            $stripelog->$k = $v;
                                                        }

                                                        $customs = explode('|', $subObj->metadata->custom);
                                                        $entry_id = $customs[0];
                                                        $entry_email = $customs[1];

                                                        $txn_id = $subscription_id;

                                                        $user_id = 0;

                                                        $entry_data = $wpdb->get_row("SELECT `arm_entry_id`, `arm_entry_email`, `arm_entry_value`, `arm_form_id`, `arm_user_id`, `arm_plan_id` FROM `" . $ARMember->tbl_arm_entries . "` WHERE `arm_entry_id`='" . $entry_id . "' AND `arm_entry_email`='" . $entry_email . "'", ARRAY_A);

                                                        if( !empty( $entry_data ) ){
                                                            $is_log = false;
                                                            
                                                            $extraParam['plan_amount'] = $subObj->plan->amount;
                                                            $extraParam['paid_amount'] = $subObj->plan->amount;
                                                            $extraParam['card_number'] = 'xxxx-xxxx-xxxx-'.$card_number;
                                                            $entry_values = maybe_unserialize($entry_data['arm_entry_value']);
                                                            $payment_mode = $entry_values['arm_selected_payment_mode'];
                                                            $payment_cycle = $entry_values['arm_selected_payment_cycle'];
                                                            $arm_user_old_plan = (isset($entry_values['arm_user_old_plan']) && !empty($entry_values['arm_user_old_plan'])) ? explode(",", $entry_values['arm_user_old_plan']) : array();
                                                            $setup_id = $entry_values['setup_id'];
                                                            $tax_percentage = $entry_values['tax_percentage'];

                                                            $entry_plan = $entry_data['arm_plan_id'];
                                                            $stripelog->arm_coupon_code = $entry_values['arm_coupon_code'];
                                                            $stripelog->arm_payment_type = $arm_payment_type;
                                                            $extraParam['arm_is_trial'] = '0';
                                                            $extraParam['tax_percentage'] = (isset($tax_percentage) && $tax_percentage > 0) ? $tax_percentage : 0;

                                                            $user_info = get_user_by('email', $entry_email);

                                                            $do_not_update_user = true;

                                                            if ($user_info) {
                                                                $user_id = $user_info->ID;

                                                                $trxn_success_log_id = $wpdb->get_var("SELECT `arm_log_id` FROM `" . $ARMember->tbl_arm_payment_log . "` WHERE `arm_user_id`='" . $user_id . "' AND `arm_transaction_id`='" . $txn_id . "' AND `arm_transaction_status` = 'success' AND `arm_payment_gateway` = 'stripe'");
                                                                if($trxn_success_log_id!='')
                                                                {
                                                                    $do_not_update_user = false;
                                                                }

                                                                if($do_not_update_user)
                                                                {
                                                                    $log_id = $wpdb->get_var("SELECT `arm_log_id` FROM `" . $ARMember->tbl_arm_payment_log . "` WHERE `arm_user_id`='" . $user_id . "' AND `arm_transaction_id`='" . $txn_id . "' AND `arm_transaction_status` = 'pending' AND `arm_payment_gateway` = 'stripe'");

                                                                    if ($log_id != '') {
                                                                        $payment_history_data = array();
                                                                        $payment_history_data['arm_transaction_status'] = 'success';
                                                                        $field_update = $wpdb->update($ARMember->tbl_arm_payment_log, $payment_history_data, array('arm_log_id' => $log_id));
                                                                        $do_not_update_user = false;
                                                                    }
                                                                }
                                                            }

                                                            if ($do_not_update_user){
                                                                
                                                                $form_id = $entry_data['arm_form_id'];
                                                                $armform = new ARM_Form('id', $form_id);
                                                                $user_info = get_user_by('email', $entry_email);
                                                                $new_plan = new ARM_Plan($entry_plan);
                                                                $plan_action = "new_subscription";
                                                                if ($new_plan->is_recurring()) {
                                                                    $plan_action = "renew_subscription";
                                                                    if (in_array($entry_plan, $arm_user_old_plan)) {
                                                                        $is_recurring_payment = $arm_subscription_plans->arm_is_recurring_payment_of_user($user_id, $entry_plan, $payment_mode);
                                                                        if ($is_recurring_payment) {
                                                                            $plan_action = 'recurring_payment';
                                                                            $planData = get_user_meta($user_id, 'arm_user_plan_' . $entry_plan, true);
                                                                            $oldPlanDetail = $planData['arm_current_plan_detail'];
                                                                            if (!empty($oldPlanDetail)) {
                                                                                $plan = new ARM_Plan(0);
                                                                                $plan->init((object) $oldPlanDetail);
                                                                                $plan_data = $plan->prepare_recurring_data($payment_cycle);
                                                                                $extraParam['plan_amount'] = $plan_data['amount'];
                                                                            }
                                                                        } else {
                                                                            $plan_data = $new_plan->prepare_recurring_data($payment_cycle);
                                                                            $extraParam['plan_amount'] = $plan_data['amount'];
                                                                        }
                                                                    } else {
                                                                        $plan_data = $new_plan->prepare_recurring_data($payment_cycle);
                                                                        $extraParam['plan_amount'] = $plan_data['amount'];
                                                                    }
                                                                } else {
                                                                   
                                                                    $extraParam['plan_amount'] = $new_plan->amount;
                                                                }

                                                                $couponCode = isset($entry_values['arm_coupon_code']) ? $entry_values['arm_coupon_code'] : '';
                                                                $arm_coupon_discount = 0;
                                                                if (!empty($couponCode)) {
                                                                    $couponApply = $arm_manage_coupons->arm_apply_coupon_code($couponCode, $new_plan, $setup_id, $payment_cycle, $arm_user_old_plan);
                                                                    $coupon_amount = isset($couponApply['coupon_amt']) ? $couponApply['coupon_amt'] : 0;
                                                                    $arm_coupon_on_each_subscriptions = isset($couponApply['arm_coupon_on_each_subscriptions']) ? $couponApply['arm_coupon_on_each_subscriptions'] : 0;


                                                                    if ($coupon_amount != 0) {
                                                                        $extraParam['coupon'] = array(
                                                                            'coupon_code' => $couponCode,
                                                                            'amount' => $coupon_amount,
                                                                            'arm_coupon_on_each_subscriptions' => $arm_coupon_on_each_subscriptions,
                                                                        );

                                                                        $arm_coupon_discount = $couponApply['discount'];
                                                                        $global_currency = $arm_payment_gateways->arm_get_global_currency();
                                                                        $arm_coupon_discount_type = ($couponApply['discount_type'] != 'percentage') ? $global_currency : "%";
                                                                        $stripelog->coupon_code = $couponCode;
                                                                        $stripelog->arm_coupon_discount = $arm_coupon_discount;
                                                                        $stripelog->arm_coupon_discount_type = $arm_coupon_discount_type;
                                                                        $stripelog->arm_coupon_on_each_subscriptions = $arm_coupon_on_each_subscriptions;
                                                                    }
                                                                }

                                                                $defaultPlanData = $arm_subscription_plans->arm_default_plan_array();
                                                                $userPlanDatameta = get_user_meta($user_id, 'arm_user_plan_' . $entry_plan, true);
                                                                $userPlanDatameta = !empty($userPlanDatameta) ? $userPlanDatameta : array();
                                                                $userPlanData = shortcode_atts($defaultPlanData, $userPlanDatameta);
                                                                $create_new_user = false;
                                                                if (!$user_info && in_array($armform->type, array('registration'))) {

                                                                    $payment_done = array();
                                                                    if ($payment_log_id) {
                                                                        $payment_done = array('status' => TRUE, 'log_id' => $payment_log_id, 'entry_id' => $entry_id);
                                                                    }
                                                                    $entry_values['payment_done'] = '1';
                                                                    $entry_values['arm_entry_id'] = $entry_id;
                                                                    $entry_values['arm_update_user_from_profile'] = 0;
                                                                    
                                                                    $create_new_user = true;
                                                                    
                                                                } else {

                                                                    $user_id = $user_info->ID;
                                                                    if (!empty($user_id)) {
                                                                        global $is_multiple_membership_feature;
                                                                        $arm_is_paid_post = false;
                                                                        if( !empty( $entry_values['arm_is_post_entry'] ) && !empty( $entry_values['arm_paid_post_id'] ) ){
                                                                            $arm_is_paid_post = true;
                                                                        }
                                                                        if ( !$is_multiple_membership_feature->isMultipleMembershipFeature && !$arm_is_paid_post ) {
                                                                            
                                                                            $old_plan_ids = get_user_meta($user_id, 'arm_user_plan_ids', true);
                                                                            $old_plan_id = isset($old_plan_ids[0]) ? $old_plan_ids[0] : 0;
                                                                            $oldPlanDetail = array();
                                                                            $old_subscription_id = '';
                                                                            if (!empty($old_plan_id)) {
                                                                                $oldPlanData = get_user_meta($user_id, 'arm_user_plan_' . $old_plan_id, true);
                                                                                $oldPlanData = !empty($oldPlanData) ? $oldPlanData : array();
                                                                                $oldPlanData = shortcode_atts($defaultPlanData, $oldPlanData);
                                                                                $oldPlanDetail = $oldPlanData['arm_current_plan_detail'];
                                                                                $subscr_effective = $oldPlanData['arm_expire_plan'];
                                                                                $old_subscription_id = $oldPlanData['arm_subscr_id'];
                                                                            }
                                                                            
                                                                            $arm_user_old_plan_details = (isset($userPlanData['arm_current_plan_detail']) && !empty($userPlanData['arm_current_plan_detail'])) ? $userPlanData['arm_current_plan_detail'] : array();
                                                                            $arm_user_old_plan_details['arm_user_old_payment_mode'] = $userPlanData['arm_payment_mode'];

                                                                            if (!empty($old_subscription_id) && $entry_values['arm_selected_payment_mode'] == 'auto_debit_subscription' && $arm_token == $old_subscription_id) {

                                                                                
                                                                                $arm_next_due_payment_date = $userPlanData['arm_next_due_payment'];
                                                                                if (!empty($arm_next_due_payment_date)) {
                                                                                    if (strtotime(current_time('mysql')) >= $arm_next_due_payment_date) {
                                                                                        $arm_user_completed_recurrence = $userPlanData['arm_completed_recurring'];
                                                                                        $arm_user_completed_recurrence++;
                                                                                        $userPlanData['arm_completed_recurring'] = $arm_user_completed_recurrence;
                                                                                        update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);
                                                                                        $arm_next_payment_date = $arm_members_class->arm_get_next_due_date($user_id, $entry_plan, false, $payment_cycle);
                                                                                        if ($arm_next_payment_date != '') {
                                                                                            $userPlanData['arm_next_due_payment'] = $arm_next_payment_date;
                                                                                            update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);
                                                                                        }

                                                                                       
                                                                                    }
                                                                                    else{

                                                                                            $now = current_time('mysql');
                                                                                            $arm_last_payment_status = $wpdb->get_var($wpdb->prepare("SELECT `arm_transaction_status` FROM `" . $ARMember->tbl_arm_payment_log . "` WHERE `arm_user_id`=%d AND `arm_plan_id`=%d AND `arm_created_date`<=%s ORDER BY `arm_log_id` DESC LIMIT 0,1", $user_id, $entry_plan, $now));

                                                                                               if(in_array($arm_last_payment_status, array('success','pending'))){
                                                                                                $arm_user_completed_recurrence = $userPlanData['arm_completed_recurring'];
                                                                                                    $arm_user_completed_recurrence++;
                                                                                                    $userPlanData['arm_completed_recurring'] = $arm_user_completed_recurrence;
                                                                                                    update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);
                                                                                                    $arm_next_payment_date = $arm_members_class->arm_get_next_due_date($user_id, $entry_plan, false, $payment_cycle);
                                                                                                    if ($arm_next_payment_date != '') {
                                                                                                        $userPlanData['arm_next_due_payment'] = $arm_next_payment_date;
                                                                                                        update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);
                                                                                                    }
                                                                                                
                                                                                            }
                                                                                        }
                                                                                }

                                                                                $suspended_plan_ids = get_user_meta($user_id, 'arm_user_suspended_plan_ids', true);
                                                                                $suspended_plan_id = (isset($suspended_plan_ids) && !empty($suspended_plan_ids)) ? $suspended_plan_ids : array();

                                                                                if (in_array($entry_plan, $suspended_plan_id)) {
                                                                                    unset($suspended_plan_id[array_search($entry_plan, $suspended_plan_id)]);
                                                                                    update_user_meta($user_id, 'arm_user_suspended_plan_ids', array_values($suspended_plan_id));
                                                                                }
                                                                            } else {

                                                                                $now = current_time('mysql');
                                                                                $arm_last_payment_status = $wpdb->get_var($wpdb->prepare("SELECT `arm_transaction_status` FROM `" . $ARMember->tbl_arm_payment_log . "` WHERE `arm_user_id`=%d AND `arm_plan_id`=%d AND `arm_created_date`<=%s ORDER BY `arm_log_id` DESC LIMIT 0,1", $user_id, $entry_plan, $now));
                                                                                

                                                                                $userPlanData['arm_current_plan_detail'] = $arm_user_old_plan_details;

                                                                                $userPlanData['arm_payment_mode'] = $entry_values['arm_selected_payment_mode'];
                                                                                $userPlanData['arm_payment_cycle'] = $entry_values['arm_selected_payment_cycle'];



                                                                                if (!empty($oldPlanDetail)) {
                                                                                    $old_plan = new ARM_Plan(0);
                                                                                    $old_plan->init((object) $oldPlanDetail);
                                                                                } else {
                                                                                    $old_plan = new ARM_Plan($old_plan_id);
                                                                                }
                                                                                $is_update_plan = true;

                                                                                $recurring_data = $new_plan->prepare_recurring_data($payment_cycle);
                                                                                if (!empty($recurring_data['trial']) && empty($arm_user_old_plan)) {
                                                                                    $extraParam['trial'] = array(
                                                                                        'amount' => $recurring_data['trial']['amount'],
                                                                                        'period' => $recurring_data['trial']['period'],
                                                                                        'interval' => $recurring_data['trial']['interval'],
                                                                                       
                                                                                    );
                                                                                    $extraParam['tax_amount'] = isset( $charge_details['tax_amount'] ) ? $charge_details['tax_amount'] : 0;
                                                                                }
                                                                                if( $arm_coupon_discount > 0){
                                                                                    $extraParam['tax_amount'] = isset( $charge_details['tax_amount'] ) ? $charge_details['tax_amount'] : 0;
                                                                                }
                                                                                if ($old_plan->exists()) {
                                                                                    if ($old_plan->is_lifetime() || $old_plan->is_free() || ($old_plan->is_recurring() && $new_plan->is_recurring())) {
                                                                                        $is_update_plan = true;
                                                                                    } else {
                                                                                        $change_act = 'immediate';
                                                                                        if ($old_plan->enable_upgrade_downgrade_action == 1) {
                                                                                            if (!empty($old_plan->downgrade_plans) && in_array($new_plan->ID, $old_plan->downgrade_plans)) {
                                                                                                $change_act = $old_plan->downgrade_action;
                                                                                            }
                                                                                            if (!empty($old_plan->upgrade_plans) && in_array($new_plan->ID, $old_plan->upgrade_plans)) {
                                                                                                $change_act = $old_plan->upgrade_action;
                                                                                            }
                                                                                        }
                                                                                        if ($change_act == 'on_expire' && !empty($subscr_effective)) {
                                                                                            $is_update_plan = false;
                                                                                            $oldPlanData['arm_subscr_effective'] = $subscr_effective;
                                                                                            $oldPlanData['arm_change_plan_to'] = $entry_plan;
                                                                                            update_user_meta($user_id, 'arm_user_plan_' . $old_plan_id, $oldPlanData);
                                                                                        }
                                                                                    }
                                                                                }

                                                                                update_user_meta($user_id, 'arm_entry_id', $entry_id);
                                                                                $userPlanData['arm_user_gateway'] = 'stripe';

                                                                                if (!empty($arm_token)) {
                                                                                    $userPlanData['arm_subscr_id'] = $arm_token;
                                                                                }
                                                                                update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);
                                                                                if ($is_update_plan) {
                                                                                   
                                                                                    $arm_subscription_plans->arm_update_user_subscription($user_id, $entry_plan, '', true, $arm_last_payment_status);
                                                                                } else {
                                                                                    
                                                                                    $arm_subscription_plans->arm_add_membership_history($user_id, $entry_plan, 'change_subscription');
                                                                                }
                                                                            }
                                                                        } else {
                                                                            
                                                                            $old_plan_ids = get_user_meta($user_id, 'arm_user_plan_ids', true);

                                                                            $oldPlanDetail = array();
                                                                            $old_subscription_id = '';
                                                                            
                                                                            if (in_array($entry_plan, $old_plan_ids)) {

                                                                               
                                                                                $oldPlanData = get_user_meta($user_id, 'arm_user_plan_' . $entry_plan, true);
                                                                                $oldPlanDetail = $oldPlanData['arm_current_plan_detail'];
                                                                                $subscr_effective = $oldPlanData['arm_expire_plan'];
                                                                                $old_subscription_id = $oldPlanData['arm_subscr_id'];
                                                                                
                                                                                $arm_user_old_plan_details = (isset($userPlanData['arm_current_plan_detail']) && !empty($userPlanData['arm_current_plan_detail'])) ? $userPlanData['arm_current_plan_detail'] : array();
                                                                                $arm_user_old_plan_details['arm_user_old_payment_mode'] = $userPlanData['arm_payment_mode'];
                                                                                if (!empty($old_subscription_id) && $entry_values['arm_selected_payment_mode'] == 'auto_debit_subscription' && $arm_token == $old_subscription_id) {
                                                                                   
                                                                                    $userPlanData['arm_payment_mode'] = $entry_values['arm_selected_payment_mode'];
                                                                                    $userPlanData['arm_payment_cycle'] = $entry_values['arm_selected_payment_cycle'];

                                                                                    $is_update_plan = true;

                                                                                    $recurring_data = $new_plan->prepare_recurring_data($payment_cycle);
                                                                                    if (!empty($recurring_data['trial']) && empty($arm_user_old_plan)) {
                                                                                        $extraParam['trial'] = array(
                                                                                            'amount' => $recurring_data['trial']['amount'],
                                                                                            'period' => $recurring_data['trial']['period'],
                                                                                            'interval' => $recurring_data['trial']['interval'],
                                                                                        );
                                                                                        $extraParam['tax_amount'] = isset( $charge_details['tax_amount'] ) ? $charge_details['tax_amount'] : 0;
                                                                                    }

                                                                                    if( $arm_coupon_discount > 0){
                                                                                        $extraParam['tax_amount'] = isset( $charge_details['tax_amount'] ) ? $charge_details['tax_amount'] : 0;
                                                                                    }

                                                                                    update_user_meta($user_id, 'arm_entry_id', $entry_id);
                                                                                    $userPlanData['arm_user_gateway'] = 'stripe';

                                                                                    if (!empty($arm_token)) {
                                                                                        $userPlanData['arm_subscr_id'] = $arm_token;
                                                                                    }
                                                                                    update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);
                                                                                    if ($is_update_plan) {
                                                                                        $arm_subscription_plans->arm_update_user_subscription($user_id, $entry_plan);
                                                                                    } else {
                                                                                        $arm_subscription_plans->arm_add_membership_history($user_id, $entry_plan, 'new_subscription');
                                                                                    }
                                                                                } else {
                                                                                    $now = current_time('mysql');
                                                                                    $arm_last_payment_status = $wpdb->get_var($wpdb->prepare("SELECT `arm_transaction_status` FROM `" . $ARMember->tbl_arm_payment_log . "` WHERE `arm_user_id`=%d AND `arm_plan_id`=%d AND `arm_created_date`<=%s ORDER BY `arm_log_id` DESC LIMIT 0,1", $user_id, $entry_plan, $now));
                                                                                    

                                                                                    $userPlanData['arm_current_plan_detail'] = $arm_user_old_plan_details;

                                                                                    $userPlanData['arm_payment_mode'] = $entry_values['arm_selected_payment_mode'];
                                                                                    $userPlanData['arm_payment_cycle'] = $entry_values['arm_selected_payment_cycle'];



                                                                                    if (!empty($oldPlanDetail)) {
                                                                                        $old_plan = new ARM_Plan(0);
                                                                                        $old_plan->init((object) $oldPlanDetail);
                                                                                    } else {
                                                                                        $old_plan = new ARM_Plan($old_plan_id);
                                                                                    }
                                                                                    $is_update_plan = true;

                                                                                    $recurring_data = $new_plan->prepare_recurring_data($payment_cycle);
                                                                                    if (!empty($recurring_data['trial']) && empty($arm_user_old_plan)) {
                                                                                        $extraParam['trial'] = array(
                                                                                            'amount' => $recurring_data['trial']['amount'],
                                                                                            'period' => $recurring_data['trial']['period'],
                                                                                            'interval' => $recurring_data['trial']['interval'],
                                                                                           
                                                                                        );
                                                                                        $extraParam['tax_amount'] = isset( $charge_details['tax_amount'] ) ? $charge_details['tax_amount'] : 0;
                                                                                    }
                                                                                    if( $arm_coupon_discount > 0){
                                                                                        $extraParam['tax_amount'] = isset( $charge_details['tax_amount'] ) ? $charge_details['tax_amount'] : 0;
                                                                                    }
                                                                                    if ($old_plan->exists()) {
                                                                                        if ($old_plan->is_lifetime() || $old_plan->is_free() || ($old_plan->is_recurring() && $new_plan->is_recurring())) {
                                                                                            $is_update_plan = true;
                                                                                        } else {
                                                                                            $change_act = 'immediate';
                                                                                            if ($old_plan->enable_upgrade_downgrade_action == 1) {
                                                                                                if (!empty($old_plan->downgrade_plans) && in_array($new_plan->ID, $old_plan->downgrade_plans)) {
                                                                                                    $change_act = $old_plan->downgrade_action;
                                                                                                }
                                                                                                if (!empty($old_plan->upgrade_plans) && in_array($new_plan->ID, $old_plan->upgrade_plans)) {
                                                                                                    $change_act = $old_plan->upgrade_action;
                                                                                                }
                                                                                            }
                                                                                            if ($change_act == 'on_expire' && !empty($subscr_effective)) {
                                                                                                $is_update_plan = false;
                                                                                                $oldPlanData['arm_subscr_effective'] = $subscr_effective;
                                                                                                $oldPlanData['arm_change_plan_to'] = $entry_plan;
                                                                                                update_user_meta($user_id, 'arm_user_plan_' . $old_plan_id, $oldPlanData);
                                                                                            }
                                                                                        }
                                                                                    }

                                                                                    update_user_meta($user_id, 'arm_entry_id', $entry_id);
                                                                                    $userPlanData['arm_user_gateway'] = 'stripe';

                                                                                    if (!empty($arm_token)) {
                                                                                        $userPlanData['arm_subscr_id'] = $arm_token;
                                                                                    }
                                                                                    update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);
                                                                                    if ($is_update_plan) {
                                                                                       
                                                                                        $arm_subscription_plans->arm_update_user_subscription($user_id, $entry_plan, '', true, $arm_last_payment_status);
                                                                                    } else {
                                                                                        
                                                                                        $arm_subscription_plans->arm_add_membership_history($user_id, $entry_plan, 'change_subscription');
                                                                                    }
                                                                                    $suspended_plan_ids = get_user_meta($user_id, 'arm_user_suspended_plan_ids', true);
                                                                                    $suspended_plan_id = (isset($suspended_plan_ids) && !empty($suspended_plan_ids)) ? $suspended_plan_ids : array();

                                                                                    if (in_array($entry_plan, $suspended_plan_id)) {
                                                                                        unset($suspended_plan_id[array_search($entry_plan, $suspended_plan_id)]);
                                                                                        update_user_meta($user_id, 'arm_user_suspended_plan_ids', array_values($suspended_plan_id));
                                                                                    }
                                                                                }
                                                                            } else {

                                                                                
                                                                                $userPlanData['arm_payment_mode'] = $entry_values['arm_selected_payment_mode'];
                                                                                $userPlanData['arm_payment_cycle'] = $entry_values['arm_selected_payment_cycle'];
                                                                                $is_update_plan = true;

                                                                                $recurring_data = $new_plan->prepare_recurring_data($payment_cycle);
                                                                                if (!empty($recurring_data['trial']) && empty($arm_user_old_plan)) {
                                                                                    $extraParam['trial'] = array(
                                                                                        'amount' => $recurring_data['trial']['amount'],
                                                                                        'period' => $recurring_data['trial']['period'],
                                                                                        'interval' => $recurring_data['trial']['interval'],
                                                                                       
                                                                                    );
                                                                                    $extraParam['tax_amount'] = isset( $charge_details['tax_amount'] ) ? $charge_details['tax_amount'] : 0;
                                                                                }
                                                                                if( $arm_coupon_discount > 0){
                                                                                        $extraParam['tax_amount'] = isset( $charge_details['tax_amount'] ) ? $charge_details['tax_amount'] : 0;
                                                                                    }
                                                                                update_user_meta($user_id, 'arm_entry_id', $entry_id);
                                                                                $userPlanData['arm_user_gateway'] = 'stripe';

                                                                                if (!empty($arm_token)) {
                                                                                    $userPlanData['arm_subscr_id'] = $arm_token;
                                                                                }
                                                                                update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);
                                                                                if ($is_update_plan) {
                                                                                    $arm_subscription_plans->arm_update_user_subscription($user_id, $entry_plan);
                                                                                } else {
                                                                                    $arm_subscription_plans->arm_add_membership_history($user_id, $entry_plan, 'new_subscription');
                                                                                }
                                                                            }
                                                                        }
                                                                        $is_log = true;
                                                                        
                                                                    }
                                                                }

                                                                $stripe_response = $stripelog;
                                                                    
                                                                $plan_id = $entry_plan;
                                                                $payer_email = $entry_email;
                                                                $extraVars = $extraParam;

                                                                unset($charge_details['tax_amount']);


                                                                $custom_var = $subObj->metadata->custom;
                                                                $customs = explode('|', $custom_var);
                                                                $entry_id = $customs[0];
                                                                $entry_email = $customs[1];
                                                                $form_id = $customs[2];
                                                                $arm_payment_type = $customs[3];
                                                                $tax_percentage = isset($subObj->metadata->tax_percentage) ? $subObj->metadata->tax_percentage : 0;
                                                                $zero_demial_currencies = $arm_payment_gateways->arm_stripe_zero_decimal_currency_array();

                                                                if (!empty($subObj->plan) && $subObj->object == 'subscription') {
                                                                    
                                                                    $amount = $subObj->plan->amount;
                                                                    $currency = strtoupper($subObj->plan->currency);
                                                                    if (!in_array($currency, $zero_demial_currencies)) {
                                                                         $amount = $subObj->plan->amount / 100; 
                                                                    }

                                                                    $arm_payment_date = date('Y-m-d H:i:s', $subObj->current_period_start);
                                                                    $arm_token = $subObj->customer;
                                                                    $arm_payment_type = 'subscription';

                                                                    if( $subObj->discount != null  && $subObj->discount != 'null') {
                                                                        if( isset($subObj->discount->coupon)) {
                                                                            if($subObj->discount->coupon->amount_off != null && $subObj->discount->coupon->amount_off != 'null') {

                                                                                $amount_off = $subObj->discount->coupon->amount_off;
                                                                              
                                                                                if($amount_off > 0) {

                                                                                    if (!in_array($currency, $zero_demial_currencies)) {
                                                                                        $amount_off = $amount_off/100;
                                                                                    }

                                                                                    $amount = $amount - $amount_off;
                                                                                }
                                                                            }
                                                                            else if($subObj->discount->coupon->percent_off != null && $subObj->discount->coupon->percent_off != 'null') {
                                                                                $percent_off = $subObj->discount->coupon->percent_off;
                                                                                    
                                                                                if($percent_off > 0) {

                                                                                    $coupon_amount = ($amount*$percent_off)/100;
                                                                                    $coupon_amount = number_format((float)$coupon_amount, 2, '.', '');
                                                                                    $amount = $amount - $coupon_amount;
                                                                                }
                                                                            }
                                                                        }
                                                                    }

                                                                    if($tax_percentage > 0) {
                                                                        $tax_amount = ($amount*$tax_percentage)/100;
                                                                        $tax_amount = number_format((float)$tax_amount, 2, '.', '');
                                                                        $amount = $tax_amount + $amount;
                                                                    }
                                                                } else {
                                                                    
                                                                    $currency = strtoupper($stripe_response->currency);
                                                                    $amount = $stripe_response->amount_paid;
                                                                    if (!in_array($currency, $zero_demial_currencies)) {
                                                                        $amount = $stripe_response->amount_paid / 100;
                                                                    }

                                                                    if( !empty($stripe_response->created) ) {
                                                                        $arm_payment_date = date('Y-m-d H:i:s', $stripe_response->created);
                                                                    }
                                                                    else {
                                                                        $arm_payment_date = date('Y-m-d H:i:s');
                                                                    }

                                                                    $arm_token = $charge_data->source->id;
                                                                    $arm_payment_type = 'subscription';
                                                                }

                                                                $coupon_code = '';
                                                                $coupon_discount = 0;
                                                                $coupon_discount_type = '';
                                                                $arm_coupon_on_each_subscriptions = '0';
                                                                if (isset($coupon_details) && !empty($coupon_details)) {
                                                                    $coupon_code = $coupon_details['coupon_code'];
                                                                    $coupon_discount = $coupon_details['arm_coupon_discount'];
                                                                    $coupon_discount_type = $coupon_details['arm_coupon_discount_type'];
                                                                    $arm_coupon_on_each_subscriptions = isset($coupon_details['arm_coupon_on_each_subscriptions']) ? $coupon_details['arm_coupon_on_each_subscriptions'] : '0';
                                                                }

                                                                if($amount < 0) {
                                                                    $amount = 0;
                                                                }

                                                                $arm_response_text = array(
                                                                    'invoice_data' => (array)$invoiceObj,
                                                                    'subscription_data' => (array)$subObj
                                                                );

                                                                //$extraVars1['plan_amount'] = $amount;

                                                                $arm_first_name='';
                                                                $arm_last_name='';
                                                                if($user_id){
                                                                    $user_detail = get_userdata($user_id);
                                                                    $arm_first_name=$user_detail->first_name;
                                                                    $arm_last_name=$user_detail->last_name;
                                                                }
                                                                $payment_data = array(
                                                                    'arm_user_id' => $user_id,
                                                                    'arm_first_name'=> $arm_first_name,
                                                                    'arm_last_name'=> $arm_last_name,
                                                                    'arm_plan_id' => $plan_id,
                                                                    'arm_payment_gateway' => 'stripe',
                                                                    'arm_payment_type' => $arm_payment_type,
                                                                    'arm_token' => $arm_token,
                                                                    'arm_payer_email' => $payer_email,
                                                                    'arm_receiver_email' => '',
                                                                    'arm_transaction_id' => $subscription_id,
                                                                    'arm_transaction_payment_type' => $subObj->object,
                                                                    'arm_transaction_status' => $invoiceObj->status,
                                                                    'arm_payment_mode' => $payment_mode,
                                                                    'arm_payment_date' => $arm_payment_date,
                                                                    'arm_amount' => $amount,
                                                                    'arm_currency' => $currency,
                                                                    'arm_coupon_code' => $coupon_code,
                                                                    'arm_coupon_discount' => $coupon_discount,
                                                                    'arm_coupon_discount_type' => $coupon_discount_type,
                                                                    'arm_response_text' => utf8_encode(maybe_serialize($arm_response_text)),
                                                                    'arm_extra_vars' => maybe_serialize($extraVars),
                                                                    'arm_is_trial' => isset($extraVars['arm_is_trial']) ? $extraVars['arm_is_trial'] : '0',
                                                                    'arm_created_date' => current_time('mysql'),
                                                                    'arm_coupon_on_each_subscriptions' => $arm_coupon_on_each_subscriptions,
                                                                    'arm_display_log' => 1
                                                                );

                                                                $payment_log_id = $arm_payment_gateways->arm_save_payment_log($payment_data);

                                                                if ($payment_log_id) {
                                                                    $payment_done = array('status' => TRUE, 'log_id' => $payment_log_id, 'entry_id' => $entry_id);
                                                                }
                                                                
                                                                if( $create_new_user ){
                                                                    $user_id = $arm_member_forms->arm_register_new_member($entry_values, $armform);    
                                                                }

                                                                $payment_log_id1 = $arm_stripe->arm_store_stripe_log( $stripelog1, $entry_plan, $user_id, $entry_email, $extraVars1, $payment_mode );

                                                                $paid_trial_stripe_payment_done = array();
                                                                if ($payment_log_id1) {
                                                                    $paid_trial_stripe_payment_done = array('status' => TRUE, 'log_id' => $payment_log_id1, 'entry_id' => $entry_id, 'gateway' => 'stripe');
                                                                    
                                                                }

                                                                if (is_numeric($user_id) && !is_array($user_id)) {
                                                                    
                                                                    if ($arm_payment_type == 'subscription') {
                                                                        $userPlanDatameta = get_user_meta($user_id, 'arm_user_plan_' . $entry_plan, true);
                                                                        $userPlanDatameta = !empty($userPlanDatameta) ? $userPlanDatameta : array();
                                                                        $userPlanData = shortcode_atts($defaultPlanData, $userPlanDatameta);

                                                                        $userPlanData['arm_subscr_id'] = $arm_token;
                                                                        $userPlanData['arm_stripe'] = array(
                                                                            'customer_id' => $customer_id,
                                                                            'transaction_id' => $subscription_id
                                                                        );
                                                                        update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);

                                                                        $pgateway = 'stripe';
                                                                        $arm_manage_coupons->arm_coupon_apply_to_subscription($user_id, $payment_log_id, $pgateway, $userPlanData);
                                                                    }
                                                                    update_user_meta($user_id, 'arm_entry_id', $entry_id);
                                                                }

                                                                if($plan_action=="recurring_payment")
                                                                {
                                                                    do_action('arm_after_recurring_payment_success_outside', $user_id, $entry_plan, 'stripe', $entry_values['arm_selected_payment_mode']);
                                                                }
                                                                echo json_encode(
                                                                    array(
                                                                        'type' => 'success'
                                                                    )
                                                                );
                                                            }
                                                        }

                                                    } else {
                                                        if( isset( $arm_stripe_enable_debug_mode ) && '1' == $arm_stripe_enable_debug_mode ){
                                                            echo json_encode(
                                                                array(
                                                                    'type' => 'error',
                                                                    'message' => $invoiceObj
                                                                )
                                                            );
                                                        } else {
                                                            echo json_encode(
                                                                array(
                                                                    'type' => 'error',
                                                                    'error' => true,
                                                                    'message' => esc_html__( 'Sorry something went wrong while processing payment', 'ARMember')
                                                                )
                                                            );
                                                        }
                                                        die;
                                                    }
                                                }
                                            }
                                        } else {
                                            if( isset( $arm_stripe_enable_debug_mode ) && '1' == $arm_stripe_enable_debug_mode ){
                                                echo json_encode(
                                                    array(
                                                        'type' => 'error',
                                                        'message' => $subObj
                                                    )
                                                );
                                            } else {
                                                echo json_encode(
                                                    array(
                                                        'type' => 'error',
                                                        'error' => true,
                                                        'message' => esc_html__( 'Sorry something went wrong while processing payment', 'ARMember')
                                                    )
                                                );
                                            }
                                            die;
                                        }
                                    }

                                } else {
                                    if( isset( $arm_stripe_enable_debug_mode ) && '1' == $arm_stripe_enable_debug_mode ){
                                        echo json_encode(
                                            array(
                                                'type' => 'error',
                                                'message' => $cusData
                                            )
                                        );
                                    } else {
                                        echo json_encode(
                                            array(
                                                'type' => 'error',
                                                'error' => true,
                                                'message' => esc_html__( 'Sorry something went wrong while processing payment', 'ARMember')
                                            )
                                        );
                                    }
                                    die;
                                }
                            }
                        }
                    } else {
                        if( isset( $arm_stripe_enable_debug_mode ) && '1' == $arm_stripe_enable_debug_mode ){
                            echo json_encode(
                                array(
                                    'type' => 'error',
                                    'message' => $charge_data
                                )
                            );
                        } else {
                            echo json_encode(
                                array(
                                    'type' => 'error',
                                    'message' => esc_html__( 'Sorry, something went wrong while processing payment', 'ARMember')
                                )
                            );
                        }
                        die;
                    }
                }
            }
            die;
        }

        function arm_stripe_sca_form_render( $payment_gateway, $payment_gateway_options, $posted_data, $entry_id = 0 ){
            global $wpdb, $ARMember, $arm_global_settings, $payment_done, $paid_trial_stripe_payment_done, $arm_payment_gateways, $arm_membership_setup, $arm_subscription_plans, $arm_manage_communication, $arm_stripe;

            if( 'stripe' == $payment_gateway ){
                $entry_data = $arm_payment_gateways->arm_get_entry_data_by_id( $entry_id );

                if( ! empty( $entry_data ) ){
                    $posted_data['entry_email'] = $entry_data['arm_entry_email'];
                    $posted_data['entry_id'] = $entry_id;
                    $user_id = $entry_data['arm_user_id'];
                    $setup_id = $posted_data['setup_id'];
                    $entry_values = maybe_unserialize($entry_data['arm_entry_value']);
                    $return_url = $entry_values['setup_redirect'];
                    $arm_user_old_plan = (isset($entry_values['arm_user_old_plan']) && !empty($entry_values['arm_user_old_plan'])) ? explode(",", $entry_values['arm_user_old_plan']) : array();
                    $posted_data['tax_percentage'] = $tax_percentage = isset($entry_values['tax_percentage']) ? $entry_values['tax_percentage'] : 0 ;
                    
                    $posted_data['arm_user_old_plan_ids'] = $arm_user_old_plan;
                    $payment_cycle = $entry_values['arm_selected_payment_cycle'];
                    $setup_detail = $arm_membership_setup->arm_get_membership_setup($setup_id);
                    $plan_id = (!empty($posted_data['subscription_plan'])) ? $posted_data['subscription_plan'] : 0;
                    if ($plan_id == 0) {
                        $plan_id = (!empty($posted_data['_subscription_plan'])) ? $posted_data['_subscription_plan'] : 0;
                    }
                    $plan = new ARM_Plan($plan_id);


                    if (isset($plan->options['payment_cycles']) && !empty($plan->options['payment_cycles'])) {
                        $payment_cycle_key = $plan->options['payment_cycles'][$payment_cycle]['cycle_key'];
                        if (!empty($setup_detail['setup_modules']['modules']['stripe_plans'][$plan_id])) {
                            if (is_array($setup_detail['setup_modules']['modules']['stripe_plans'][$plan_id])) {
                                if (isset($setup_detail['setup_modules']['modules']['stripe_plans'][$plan_id][$payment_cycle_key])) {
                                    $stripePlanID = $setup_detail['setup_modules']['modules']['stripe_plans'][$plan_id][$payment_cycle_key];
                                } else {
                                    $stripePlanID = '';
                                }
                            } else {
                                $stripePlanID = $setup_detail['setup_modules']['modules']['stripe_plans'][$plan_id];
                            }
                            $posted_data['stripePlanID'] = $stripePlanID;
                        } else {
                            $posted_data['stripePlanID'] = '';
                        }
                    } else {
                        if (!empty($setup_detail['setup_modules']['modules']['stripe_plans'][$plan_id])) {
                            if (is_array($setup_detail['setup_modules']['modules']['stripe_plans'][$plan_id])) {
                                $stripePlanID = $setup_detail['setup_modules']['modules']['stripe_plans'][$plan_id]['arm0'];
                            } else {
                                $stripePlanID = $setup_detail['setup_modules']['modules']['stripe_plans'][$plan_id];
                            }
                            $posted_data['stripePlanID'] = $stripePlanID;
                        } else {
                            $posted_data['stripePlanID'] = '';
                        }
                    }

                    if ($plan->is_recurring()) {
                        $payment_mode = !empty($posted_data['arm_selected_payment_mode']) ? $posted_data['arm_selected_payment_mode'] : 'manual_subscription';
                    } else {
                        $payment_mode = '';
                    }


                    $plan_action = 'new_subscription';
                    $plan_expiry_date = "now";

                    if (!empty($arm_user_old_plan)) {
                        if (in_array($plan_id, $arm_user_old_plan)) {

                            $user_plan_data = get_user_meta($user_id, 'arm_user_plan_' . $plan_id, true);
                            $user_plan_data = !empty($user_plan_data) ? $user_plan_data : array();
                            $plan_expiry_date = (isset($user_plan_data['arm_expire_plan']) && !empty($user_plan_data['arm_expire_plan'])) ? $user_plan_data['arm_expire_plan'] : "now";
                            $plan_action = 'renew_subscription';
                            $is_recurring_payment = $arm_subscription_plans->arm_is_recurring_payment_of_user($user_id, $plan_id, $payment_mode);
                            if ($is_recurring_payment) {
                                $plan_action = 'recurring_payment';
                                $planData = get_user_meta($user_id, 'arm_user_plan_' . $plan_id, true);
                                $oldPlanDetail = $planData['arm_current_plan_detail'];
                                $user_subsdata = $planData['arm_stripe'];
                                if (!empty($oldPlanDetail)) {
                                    $plan = new ARM_Plan(0);
                                    $plan->init((object) $oldPlanDetail);
                                }
                            }
                        } else {
                            $plan_action = 'change_subscription';
                        }
                    }

                    if ($payment_mode == 'auto_debit_subscription') {
                        if ($plan->is_recurring()) {
                            if ($plan_action == 'new_subscription') {
                                if (!($plan->is_support_stripe($payment_cycle))) {

                                    $err_msg = __('Payment through Stripe is not supported for selected plan.', 'ARMember');
                                    return $payment_done = array('status' => FALSE, 'error' => $err_msg);
                                }
                            } else {
                                if (!($plan->is_support_stripe_without_trial($payment_cycle))) {
                                    $err_msg = __('Payment through Stripe is not supported for selected plan.', 'ARMember');
                                    return $payment_done = array('status' => FALSE, 'error' => $err_msg);
                                }
                            }
                        }
                    }

                    $charge_details = $arm_stripe->arm_prepare_stripe_charge_details($posted_data, $plan, $setup_id, $payment_cycle, $plan_action);

                    $stripe_response = array();
                    $stripe_error_msg = (isset($arm_global_settings->common_message['arm_payment_fail_stripe'])) ? $arm_global_settings->common_message['arm_payment_fail_stripe'] : __('Sorry something went wrong while processing payment with Stripe.', 'ARMember');
                    $payment_done = array('status' => FALSE, 'error' => $stripe_error_msg, 'gateway' => 'stripe');

                    if (!empty($charge_details)) {
                        if (isset($charge_details['status']) && $charge_details['status'] == TRUE) {
                            $payment_done = $charge_details;
                            $payment_done['status2'] = true;
                            return $payment_done;
                        }
                    }

                    $updated_charge_details = $charge_details;
                    
                    
                    unset( $updated_charge_details['card']['number'] );
                    unset( $updated_charge_details['card']['exp_month'] );
                    unset( $updated_charge_details['card']['exp_year'] );
                    unset( $updated_charge_details['card']['cvc'] );
                    unset( $updated_charge_details['card']['name'] );
                    unset( $updated_charge_details['card']['email'] );
                    unset( $updated_charge_details['extraVars']['card_number'] );

                    $arm_stripe_enable_debug_mode = isset($payment_gateway_options['enable_debug_mode']) ? $payment_gateway_options['enable_debug_mode'] : 0;

                    if( 'test' == $payment_gateway_options['stripe_payment_mode'] ){
                        $sec_key = $payment_gateway_options['stripe_test_secret_key'];
                        $pub_key = $payment_gateway_options['stripe_test_pub_key'];
                    } else {
                        $sec_key = $payment_gateway_options['stripe_secret_key'];
                        $pub_key = $payment_gateway_options['stripe_pub_key'];
                    }

                    
                    $isSubscription = false;
                    $stripe_plan_id = '';
                    $isPaidTrail = false;

                    if( isset( $updated_charge_details['plan'] ) ){
                        $isSubscription = true;
                        $stripe_plan_id = $updated_charge_details['plan'];
                        $updated_charge_details['amount'] = str_replace(',','',($updated_charge_details['extraVars']['paid_amount'] * 100));
                        
                        $charge_details['amount'] = $updated_charge_details['amount'];
                        $updated_charge_details['currency'] = $currency = $arm_payment_gateways->arm_get_global_currency();
                        $charge_details['currency'] = $updated_charge_details['currency'];

                        if( $plan_action == 'new_subscription' && $plan->is_recurring() && $payment_mode == 'auto_debit_subscription' && $plan->has_trial_period() ){
                            $opt_trial = $plan->options['trial'];
                            $opt_trial_days = $plan->options['trial']['days'];
                            $updated_charge_details["trial_period_days"] = $opt_trial_days;
                            if( $opt_trial['amount'] > 0 ){
                                $isPaidTrail = true;
                                $charge_details1 = $arm_stripe->arm_prepare_stripe_charge_details_for_single_payment($posted_data, $setup_id, $payment_cycle);
                            }
                        }
                    }

                    if( empty( $charge_details1 ) ){
                        $charge_details1 = array();
                    }

                    $isPaymentIntent = true;
                    $isFreePaidTrail = false;
                    $paidTrailAmount = isset( $charge_details1['amount'] ) ? $charge_details1['amount'] : 0;
                    $isPlanFreeTrail = false;
                    if( $isSubscription && ! $isPaidTrail && ( '00' == $updated_charge_details['amount'] || 0 == $updated_charge_details ) ){
                        $isPaymentIntent = false;
                    } else if( $isSubscription && $isPaidTrail && ( '00' == $charge_details1['amount'] || 0 == $charge_details1['amount'] ) && ( '00' == $updated_charge_details['amount'] || 0 == $updated_charge_details ) ){
                        $isPaymentIntent = false;
                    } else if( $isSubscription && $isPaidTrail && ( '00' == $charge_details1['amount'] || 0 == $charge_details1['amount'] ) && ( '00' != $updated_charge_details['amount'] ) ){
                        $isPaymentIntent = true;
                        $isFreePaidTrail = true;
                        $paidTrailAmount = $updated_charge_details['amount'];
                    }

                    if( !$isPaidTrail && isset( $updated_charge_details['trial_period_days'] ) ){
                        $isPlanFreeTrail = true;
                    }


                    if( $isPaidTrail ){
                        $request_string = 'amount=' . $paidTrailAmount .'&currency=' . strtolower( $charge_details1['currency'] ) . '&metadata[custom]=' . $charge_details['metadata']['custom'].'&receipt_email='.$charge_details1['receipt_email'];
                        if( isset( $updated_charge_details['metadata']['tax_percentage'] ) ){
                        	$request_string .= '&metadata[tax_percentage]=' . str_replace('%', '', $updated_charge_details['metadata']['tax_percentage']);
                        }
                    } else {
                        $request_string = 'amount=' . $updated_charge_details['amount'] .'&currency=' . strtolower( $updated_charge_details['currency'] ) . '&metadata[custom]=' . $charge_details['metadata']['custom'];
                        if( isset( $updated_charge_details['receipt_email'] ) ){
                            $request_string .= '&receipt_email='.$updated_charge_details['receipt_email'];
                        }

                        if( isset( $updated_charge_details['metadata']['tax_percentage'] ) ){
                        	$request_string .= '&metadata[tax_percentage]=' . str_replace('%', '', $updated_charge_details['metadata']['tax_percentage']);
                        }
                    }


                    if (strpos($posted_data['entry_email'], '+') !== FALSE)
                    {
                        return $payment_done = array('status' => FALSE, 'error' => esc_html__( 'Invalid email address for Stripe Payment Gateway:', 'ARMember').' '.$posted_data['entry_email'], 'gateway' => 'stripe');
                    }

                    $request_string .= '&metadata[customer_email]=' . $posted_data['entry_email'];

                    if( !$isSubscription || ( $isSubscription && $isPaidTrail ) ){
                        $request_string .= '&description='.urlencode( $plan->name );
                    }


                    if( !$isSubscription ){
                        $api_url = 'https://api.stripe.com/v1/customers';
                        $headers = array(
                            'Authorization' => 'Bearer ' . $sec_key,
                            'Content-Type' => 'application/x-www-form-urlencoded'
                        );

                        $request_body = "email=" . $posted_data['entry_email'];

                        $customerObj = wp_remote_post(
                            $api_url,
                            array(
                                'body' => $request_body,
                                'timeout' => 5000,
                                'headers' => $headers
                            )
                        );

                        if( is_wp_error( $customerObj ) ){

                        } else {
                            $customerRes = json_decode( $customerObj['body'] );

                            if( $customerRes->id ){
                                $request_string .= '&customer='.$customerRes->id;
                            }

                        }
                    }

                    if( $isPlanFreeTrail ){
                        $stripe_api_url = 'https://api.stripe.com/v1/setup_intents';

                        $headers = array(
                            'Authorization' => 'Bearer ' . $sec_key
                        );

                        $stripe_payment = wp_remote_post(
                            $stripe_api_url,
                            array(
                                'headers' => $headers,
                                'body' => 'usage=off_session',
                                'timeout' => 5000
                            )
                        );
                    } else {
                        $stripe_api_url = 'https://api.stripe.com/v1/payment_intents';

                        $headers = array(
                            'Authorization' => 'Bearer '.$sec_key,
                            'Content-Type' => 'application/x-www-form-urlencoded'
                        );

                        $stripe_payment = wp_remote_post(
                            $stripe_api_url,
                            array(
                                'headers' => $headers,
                                'body' => $request_string,
                                'sslverify' => false,
                                'timeout' => 5000
                            )
                        );
                    }

                    
                    if( is_wp_error( $stripe_payment ) ){
                        if( isset( $arm_stripe_enable_debug_mode ) && '1' == $arm_stripe_enable_debug_mode ){
                            return $payment_done = array('status' => FALSE, 'error' => json_encode($stripe_payment), 'gateway' => 'stripe');
                        } else {
                            return $payment_done = array('status' => FALSE, 'error' => esc_html__( 'Sorry, something went wrong while processing payment', 'ARMember'), 'gateway' => 'stripe');
                        }
                    } else {
                        $response = json_decode( $stripe_payment['body'] );

                        if( !isset( $response->id) && $isPaymentIntent ){
                            if( isset( $arm_stripe_enable_debug_mode ) && '1' == $arm_stripe_enable_debug_mode ){
                                return $payment_done = array('status' => FALSE, 'error' => json_encode($stripe_payment), 'gateway' => 'stripe');
                            } else {
                                return $payment_done = array('status' => FALSE, 'error' => esc_html__( 'Sorry, something went wrong while processing payment', 'ARMember'), 'gateway' => 'stripe');
                            }
                            
                        } else {
                            $client_secret = $response->client_secret;
                            $return_message = '';
                            
                            $stripe_title = isset( $payment_gateway_options['stripe_popup_title'] ) ? $payment_gateway_options['stripe_popup_title'] : '';
                            $stripe_button_lbl = isset( $payment_gateway_options['stripe_popup_button_lbl'] ) ? $payment_gateway_options['stripe_popup_button_lbl'] : '';
                            $stripe_title = str_replace( '{arm_selected_plan_title}', $plan->name, $stripe_title );

                            $stripe_logo = isset( $payment_gateway_options['stripe_popup_icon'] ) ? $payment_gateway_options['stripe_popup_icon'] : '';

                            $return_form = $this->arm_get_stripe_form( $client_secret, $pub_key, '', $stripe_title, $stripe_button_lbl, $stripe_logo );


                            $return_js  = 'jQuery("body").append("' . addslashes( $return_form ) . '");';
                            $return_js .= 'var stripe = Stripe("' . $pub_key .'");';
                            $return_js .= 'var elements = stripe.elements({fonts: [{cssSrc: "https://fonts.googleapis.com/css?family=Source+Code+Pro"}],locale: window.__exampleLocale});';

                            $return_js .= "var elementStyles = { base: { color: '#32325D', fontWeight: 500, fontFamily: 'Source Code Pro, Consolas, Menlo, monospace', fontSize: '16px', fontSmoothing: 'antialiased', '::placeholder': { color: '#CFD7DF', }, ':-webkit-autofill': { color: '#e39f48',},},invalid: {color: '#E25950','::placeholder': {color: '#FFCCA5',},},};";

                            $return_js .= "var elementClasses = { focus: 'focused', empty: 'empty', invalid: 'invalid', };";

                            $return_js .= " var cardNumber = elements.create('cardNumber', { style: elementStyles, classes: elementClasses, }); cardNumber.mount('#card-number');";
                            $return_js .= " var cardExpiry = elements.create('cardExpiry', { style: elementStyles, classes: elementClasses, }); cardExpiry.mount('#card-expiry');";
                            $return_js .= " var cardCvc = elements.create('cardCvc', { style: elementStyles, classes: elementClasses, }); cardCvc.mount('#card-cvc');";

                            $return_js .= 'var cardButton = document.getElementById("card-button"); var clientSecret = cardButton.dataset.secret;';

                            $return_js .= 'var closeIcon = document.getElementById("stripe_wrapper_close_icon");';

                            $return_js .= 'closeIcon.addEventListener("click", function(e){
                                jQuery(".stripe_element_wrapper").remove();
                                jQuery("#arm_stripe_js").remove();
                                jQuery("#arm_stripe_css").remove();
                            });';
                            if( $isSubscription && ! $isPaidTrail ){

                                if( $isPlanFreeTrail ) {
                                    $return_js .= 'cardButton.addEventListener("click", function(e){
                                        var errorElement = document.getElementById("card-errors");
                                        errorElement.textContent = "";
                                        cardButton.setAttribute("disabled","disabled");
                                        cardButton.style.cursor = "not-allowed";
                                        stripe.handleCardSetup( clientSecret, cardNumber ).then(function(result){
                                            if( result.error ){
                                                cardButton.removeAttribute("disabled");
                                                cardButton.style.cursor = "";
                                                errorElement.textContent = result.error.message;
                                            } else {
                                                errorElement.textContent = "";
                                                var token_id = result.setupIntent.payment_method;
                                                jQuery.ajax({
                                                        url:__ARMAJAXURL,
                                                        type:"POST",
                                                        dataType:"json",
                                                        data:"action=arm_stripe_made_charge&token_id=" + token_id + "&entry_id='.$entry_id.'&amount='.$updated_charge_details['amount'].'&charge_details='.addslashes( json_encode( $updated_charge_details ) ).'&payment_cycle=' . addslashes( json_encode( $payment_cycle) ) .'&plan_action=' . $plan_action .'&plan_mode='.$payment_mode.'&is_subscription='.$isSubscription.'&stripe_plan_id='.$stripe_plan_id.'&isPaidTrail='.$isPaidTrail.'&charge_details1='.addslashes( json_encode( $charge_details1 ) ).'&isPlanFreeTrail=true",
                                                        success:function(response){
                                                            if( response.type != "error" ){
                                                                if( ! response.error ) {
                                                                    window.location.href = "' . $return_url . '";
                                                                } else if( response.error ){
                                                                    cardButton.removeAttribute("disabled");
                                                                    cardButton.style.cursor = "";
                                                                    errorElement.textContent = response.message;
                                                                }
                                                            } else if( response.type == "error" ){
                                                                cardButton.removeAttribute("disabled");
                                                                cardButton.style.cursor = "";
                                                                errorElement.textContent = response.message;
                                                            }
                                                        }
                                                    });
                                            }
                                        });
                                    });';
                                } else {
                                    $return_js .= 'cardButton.addEventListener("click", function(e) {
                                        var errorElement = document.getElementById("card-errors");
                                        errorElement.textContent = "";
                                        cardButton.setAttribute("disabled","disabled");
                                        cardButton.style.cursor = "not-allowed";

                                        stripe.createToken(cardNumber).then(
                                            function(result){
                                                if( result.error ){
                                                    cardButton.removeAttribute("disabled");
                                                    cardButton.style.cursor = "";
                                                    errorElement.textContent = result.error.message;
                                                } else {
                                                    var token_id = result.token.id;
                                                    jQuery.ajax({
                                                        url:__ARMAJAXURL,
                                                        type:"POST",
                                                        dataType:"json",
                                                        data:"action=arm_stripe_made_charge&token_id=" + token_id + "&entry_id='.$entry_id.'&amount='.$updated_charge_details['amount'].'&charge_details='.addslashes( json_encode( $updated_charge_details ) ).'&payment_cycle=' . addslashes( json_encode( $payment_cycle) ) .'&plan_action=' . $plan_action .'&plan_mode='.$payment_mode.'&is_subscription='.$isSubscription.'&stripe_plan_id='.$stripe_plan_id.'&isPaidTrail='.$isPaidTrail.'&charge_details1='.addslashes( json_encode( $charge_details1 ) ).'",
                                                        success:function(response){
                                                            if( response.type != "error" ){
                                                                if( response.status == "incomplete" ){
                                                                    var message = response.message;
                                                                    var resp = Base64.decode( message );
                                                                    var pidataArr = resp.split("(:)");
                                                                    var pidata = jQuery.parseJSON(pidataArr[1]);
                                                                    var paymentIntentSecret = pidata.secret;
                                                                    stripe.handleCardPayment(paymentIntentSecret).then(function(result) {
                                                                        if (result.error) {
                                                                            cardButton.removeAttribute("disabled");
                                                                            cardButton.style.cursor = "";
                                                                            errorElement.textContent = result.error.message;
                                                                        } else {
                                                                            jQuery.ajax({
                                                                                url:__ARMAJAXURL,
                                                                                type:"POST",
                                                                                dataType:"json",
                                                                                data:"action=arm_store_subscription_payment&pi_id=" + result.paymentIntent.id+ "&entry_id='.$entry_id.'&isPaidTrail='.$isPaidTrail.'&charge_details1='.addslashes( json_encode( $charge_details1 ) ).'&plan_action=' . $plan_action .'&plan_mode=' . $payment_mode . '&charge_details=' . addslashes( json_encode( $updated_charge_details ) ) .'",
                                                                                success:function(sub_response){
                                                                                    if( sub_response.error  ){
                                                                                        cardButton.removeAttribute("disabled");
                                                                                        cardButton.style.cursor = "";
                                                                                        errorElement.textContent = response.message;
                                                                                    } else {
                                                                                        errorElement.textContent = "";
                                                                                        window.location.href = "' . $return_url . '";
                                                                                    }
                                                                                }
                                                                            });
                                                                        }
                                                                    });
                                                                } else if( response.status == "active_sub" ){
                                                                    var pi_id = response.pi_id;
                                                                    jQuery.ajax({
                                                                        url:__ARMAJAXURL,
                                                                        type:"POST",
                                                                        dataType:"json",
                                                                        data:"action=arm_store_subscription_payment&pi_id=" + pi_id + "&entry_id='.$entry_id.'&isPaidTrail='.$isPaidTrail.'&charge_details1='.addslashes( json_encode( $charge_details1 ) ).'&plan_action=' . $plan_action .'&plan_mode=' . $payment_mode . '&charge_details=' . addslashes( json_encode( $updated_charge_details ) ) .'",
                                                                        success:function(sub_response){
                                                                            if( sub_response.error  ){
                                                                                cardButton.removeAttribute("disabled");
                                                                                cardButton.style.cursor = "";
                                                                                errorElement.textContent = response.message;
                                                                            } else {
                                                                                errorElement.textContent = "";
                                                                                window.location.href = "' . $return_url . '";
                                                                            }
                                                                        }
                                                                    });
                                                                } else if( ! response.error ) {
                                                                    window.location.href = "' . $return_url . '";
                                                                } else if( response.error ){
                                                                    cardButton.removeAttribute("disabled");
                                                                    cardButton.style.cursor = "";
                                                                    errorElement.textContent = response.message;
                                                                }
                                                            } else if( response.type == "error" ){
                                                                cardButton.removeAttribute("disabled");
                                                                cardButton.style.cursor = "";
                                                                errorElement.textContent = response.message;
                                                            }
                                                        }
                                                    });
                                                }
                                            });
                                    });';
                                }
                            } else if( $isSubscription && $isPaidTrail ){
                                $return_js .= 'cardButton.addEventListener("click", function(e){
                                    var errorElement = document.getElementById("card-errors");
                                    errorElement.textContent = "";
                                    cardButton.setAttribute("disabled","disabled");
                                    cardButton.style.cursor = "not-allowed";
                                    stripe.createSource( cardNumber ) . then( function(resp){
                                        if( resp.error ){
                                            cardButton.removeAttribute("disabled");
                                            cardButton.style.cursor = "";
                                            errorElement.textContent = resp.error.message;
                                        } else {
                                            var sourceId = resp.source.id;
                                            stripe.handleCardPayment( clientSecret, cardNumber ).then( function(result) {
                                                if( result.error ){
                                                    cardButton.removeAttribute("disabled");
                                                    cardButton.style.cursor = "";
                                                    errorElement.textContent = result.error.message;
                                                } else {
                                                    if( "succeeded" == result.paymentIntent.status ){
                                                        var pmethod_id = result.paymentIntent.payment_method;
                                                        jQuery.ajax({
                                                            url:__ARMAJAXURL,
                                                            type:"POST",
                                                            dataType:"json",
                                                            data:"action=arm_stripe_made_charge_subscription_paid_trial&pi_id=" + result.paymentIntent.id + "&pm_id=" + pmethod_id + "&sourceId="+sourceId+"&entry_id='.$entry_id.'&charge_details='.addslashes( json_encode( $updated_charge_details ) ).'&charge_details1='.addslashes( json_encode( $charge_details1 ) ).'&payment_cycle=' . addslashes( json_encode( $payment_cycle) ) .'&plan_action=' . $plan_action .'&stripe_plan_id='.$stripe_plan_id.'&plan_mode='.$payment_mode.'&isFreePaidTrail='.$isFreePaidTrail.'",
                                                            success:function(response){
                                                                if( response.type != "error" ){
                                                                    window.location.href = "' . $return_url . '";
                                                                } else if( response.type == "error" ){
                                                                    cardButton.removeAttribute("disabled");
                                                                    cardButton.style.cursor = "";
                                                                    errorElement.textContent = response.message;
                                                                }
                                                            }
                                                        });
                                                    }
                                                }
                                            });
                                        }
                                    })
                                    
                                });';
                            } else {
                                $return_js .= 'cardButton.addEventListener("click", function(e){
                                    var errorElement = document.getElementById("card-errors");
                                    errorElement.textContent = "";
                                    cardButton.setAttribute("disabled","disabled");
                                    cardButton.style.cursor = "not-allowed";
                                    stripe.handleCardPayment( clientSecret, cardNumber ).then( function(result) {
                                        if( result.error ){
                                            cardButton.removeAttribute("disabled");
                                            cardButton.style.cursor = "";
                                            errorElement.textContent = result.error.message;
                                        } else {
                                            if( "succeeded" == result.paymentIntent.status ){
                                                jQuery.ajax({
                                                    url:__ARMAJAXURL,
                                                    type:"POST",
                                                    dataType:"json",
                                                    data:"action=arm_stripe_made_charge_onetime&pi_id=" + result.paymentIntent.id + "&entry_id='.$entry_id.'&charge_details='.addslashes( json_encode( $updated_charge_details ) ).'&payment_cycle=' . addslashes( json_encode( $payment_cycle) ) .'&plan_action=' . $plan_action .'&plan_mode='.$payment_mode.'",
                                                    success:function(response){
                                                        if( response.type != "error" ){
                                                            window.location.href = "' . $return_url . '";
                                                        } else if( response.type == "error" ){
                                                            cardButton.removeAttribute("disabled");
                                                            cardButton.style.cursor = "";
                                                            errorElement.textContent = response.message;
                                                        }
                                                    }
                                                });
                                            }
                                        }
                                    });
                                });';
                            }

                            $return_message .= '<script type="text/javascript" id="arm_stripe_js">' . $return_js . '</script>';

                            echo json_encode(
                                array(
                                    'type' => 'script',
                                    'isHide' => false,
                                    'message' => $return_message
                                )
                            );
                            die;
                        }
                    }

                }
            }
        }

        function arm_get_stripe_form( $client_secret = '', $pub_key = '', $card_type = '', $title='', $button_lbl='', $stripe_logo = '' ){

            $return_message  = '<div class="stripe_element_wrapper">';
                $return_message .= '<div class="form-inner-row" data-locale-reversible>';

                    $return_message .= "<div class='site_info_row'>";
                        $return_message .= "<div class='site_info'>";
                            if( '' != $stripe_logo ){
                                //$return_message .= "<div class='arm_stripe_popup_logo_container'><div class='arm_stripe_popup_logo_wrapper'></div><span></span></div>";
                                $return_message .= "<div class='arm_stripe_popup_logo'>";
                                    $return_message .= "<div class='arm_stripe_popup_logo_wrap'>";
                                        $return_message .= "<div class='arm_stripe_popup_logo_bevel'></div>";
                                        $return_message .= "<div class='arm_stripe_popup_logo_border'></div>";
                                        $return_message .= "<div class='arm_stripe_popup_logo_image' style='background-image:url(".$stripe_logo.")'></div>";
                                    $return_message .= "</div>";
                                $return_message .= "</div>";
                            }
                            $return_message .= "<div class='site_title'>".(!empty($title) ? $title : get_bloginfo('name'))."</div>";
                            //$return_message .= "<div class='site_tag'>".get_bloginfo('description')."</div>";
                            $return_message .= "<div class='close_icon' id='stripe_wrapper_close_icon'></div>";
                        $return_message .= "</div>";
                    $return_message .= "</div>";

                    $return_message .= '<div class="field_wrapper">';

                        $return_message .= '<div class="arm_stripe_field_row">';
                            $return_message .= '<div class="field">';
                                $return_message .= '<div id="card-number" class="input empty"></div>';
                                $return_message .= '<div class="baseline"></div>';
                            $return_message .= '</div>';
                        $return_message .= '</div>';

                        $return_message .= '<div class="arm_stripe_field_row">';
                            $return_message .= '<div class="field half-width">';
                                $return_message .= '<div id="card-expiry" class="input empty"></div>';
                                $return_message .= '<div class="baseline"></div>';
                            $return_message .= '</div>';
                        $return_message .= '</div>';

                        $return_message .= '<div class="arm_stripe_field_row">';
                            $return_message .= '<div class="field half-width">';
                                $return_message .= '<div id="card-cvc" class="input empty"></div>';
                                $return_message .= '<div class="baseline"></div>';
                            $return_message .= '</div>';
                        $return_message .= '</div>';

                        $return_message .= '<div class="card-errors" id="card-errors" role="alert"></div>';
                        if( '' != $card_type && 'update_card' == $card_type ){
                            $return_message .= '<button id="update-card-button" type="button" data-secret="'.$client_secret.'"><span class="arm_stripe_loader"></span>'.(!empty($button_lbl) ? $button_lbl : esc_html__('Pay Now','ARMember')).'</button>';
                        } else {
                            $return_message .= '<button id="card-button" type="button" data-secret="'.$client_secret.'"><span class="arm_stripe_loader"></span>'.(!empty($button_lbl) ? $button_lbl : esc_html__('Pay Now','ARMember')).'</button>';
                        }
                    $return_message .= '</div>';
                $return_message .= '</div>';


            $return_message .= '</div>';

            

            $return_message .= '<style type="text/css" id="arm_stripe_css">.stripe_element_wrapper{position:fixed;top:0;left:0;width:100%;height:100%;text-align:center;background:rgba(0,0,0,0.6);z-index:999999;}.stripe_element_wrapper .form-inner-row{ float: left; width: 300px; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: #F5F5F7;text-align:left;border-radius:5px;overflow:hidden;}.stripe_element_wrapper #card-button,#update-card-button{ background:linear-gradient(#43B0E9,#3299DE); padding:0 !important; font-weight:normal; border:none; color: #fff; display: inline-block; margin-top: 25px; margin-bottom:15px; height: 40px; line-height: normal; float: left; border-radius:4px;width:100%;font-size:20px;}.stripe_element_wrapper .form-row{ float:left; width: 70%;}.stripe_element_wrapper iframe{position:relative;left:0}.StripeElement {box-sizing: border-box;height: 40px;padding: 10px 12px;border: 1px solid transparent;border-radius: 4px;background-color: white;box-shadow: 0 1px 3px 0 #e6ebf1;-webkit-transition: box-shadow 150ms ease;transition: box-shadow 150ms ease;}.card-errors{font-size: 14px;color: #ff0000;}.site_info_row {float: left;width: 100%;height: 95px;background: #E8E9EB;border-bottom: 1px solid #DBDBDD;box-sizing: border-box;text-align: center;padding: 25px 10px;}.field_wrapper{float:left;padding:30px;width:100%;box-sizing:border-box;}.form-inner-row .field_wrapper .arm_stripe_field_row{float:left;width:100%;margin-bottom:10px;}.site_title,.site_tag{float:left;width:100%;text-align:center;font-size:16px;} .site_title{font-weight:bold;}.site_info_row .close_icon{position: absolute;width: 20px;height: 20px;background: #cecccc;right: 10px;top: 10px;border-radius: 20px;cursor:pointer;}.site_info_row .close_icon::before{content: "";width: 12px;height: 2px;background: #fff;display: block;top: 50%;left: 50%;transform: translate(-50%,-50%) rotate(45deg);position: absolute;}.site_info_row .close_icon::after{content: "";width: 12px;height: 2px;background: #fff;display: block;top: 50%;left: 50%;transform: translate(-50%,-50%) rotate(-45deg);position: absolute;}.StripeElement--focus { box-shadow: 0 1px 3px 0 #cfd7df; }.StripeElement--invalid {border-color: #fa755a;}.StripeElement--webkit-autofill {background-color: #fefde5 !important;}.arm_stripe_loader{float:none;display:inline-block;width:15px;height:15px;border:3px solid #fff;border-radius:15px;border-top:3px solid transparent;margin-right:5px;position:relative;top:3px;display:none;animation:spin infinite 1.5s}@keyframes spin{0%{transform:rotate(0deg);}100%{transform:rotate(360deg)}} #card-button[disabled],#update-card-button[disabled]{opacity:0.7;} #card-button[disabled] .arm_stripe_loader,#update-card-button[disabled] .arm_stripe_loader{display:inline-block;}';
            if( '' != $stripe_logo ){
                $return_message .= '.arm_stripe_popup_logo{float:left;width:100%;position:relative;height:35px;margin-bottom:6px;box-sizing:border-box;}.arm_stripe_popup_logo *{box-sizing:border-box;}.arm_stripe_popup_logo_wrap{position:absolute;top:-38px;right:0;left:0;width:70px;height:70px;margin:0 auto;}.arm_stripe_popup_logo_bevel{border:1px solid rgba(0,0,0,0.2);width:64px;height:64px;border-radius:100%;box-shadow:inset 0 1px 0 0 hsla(0,0%,100%,.1);position:absolute;top:3px;left:3px;}.arm_stripe_popup_logo_border{border:3px solid #fff;width:70px;height:70px;border-radius:100%;box-shadow:0 0 0 1px rgba(0,0,0,.18), 0 2px 2px 0 rgba(0,0,0,0.08);position:absolute;top:0;left:0;}.arm_stripe_popup_logo_image{width:64px;height:64px;margin:3px;border-radius:100%;background:#fff;background-position:50% 50%; background-size:cover;display:inline-block;background-repeat:no-repeat;}.form-inner-row{overflow:visible !important;}.site_info_row{border-radius:5px 5px 0 0;height:115px;}';
            }
            $return_message .='</style>';

            
            return $return_message;
        }
    }
}
global $arm_stripe_sca;
$arm_stripe_sca = new ARM_Stripe_SCA();