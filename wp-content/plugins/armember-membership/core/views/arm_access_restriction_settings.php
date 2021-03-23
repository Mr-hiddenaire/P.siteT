<?php
global $wpdb, $ARMember, $arm_global_settings, $arm_access_rules, $arm_subscription_plans, $arm_restriction;
$all_global_settings = $arm_global_settings->arm_get_all_global_settings();

$general_settings = $all_global_settings['general_settings'];
$page_settings = $all_global_settings['page_settings'];

$all_plans_data = $arm_subscription_plans->arm_get_all_subscription_plans('arm_subscription_plan_id, arm_subscription_plan_name, arm_subscription_plan_type', ARRAY_A, true);
$defaultRulesTypes = $arm_access_rules->arm_get_access_rule_types();
$default_rules = $arm_access_rules->arm_get_default_access_rules();
$all_roles = $arm_global_settings->arm_get_all_roles();

?>

<div class="arm_global_settings_main_wrapper armPageContainer">
	<div class="page_sub_content">
		<form method="post" action="#" id="arm_access_restriction" class="arm_access_restriction arm_admin_form" onsubmit="return false;">
                        <?php do_action('arm_before_access_restriction_settings_html', $general_settings);?>
			
		
                        <div class="page_sub_title" id="arm_global_default_access_rules">
                            <?php _e('Default Access Rules for newly added Content', 'ARMember'); ?>
                            <i class="arm_helptip_icon armfa armfa-question-circle" title="<?php _e("Please configure default rules to restrict any newly added page, post, category, custom post, etc for which there is no rule defined at Access Rules.", 'ARMember'); ?>"></i>
                        </div>
                        <?php
                        $ruleTypes = array(
                            'page' => __('New Pages', 'ARMember'),
                            'post' => __('New Posts', 'ARMember'),
                            'category' => __('New Categories', 'ARMember'),
                        
                        );
                        if (isset($defaultRulesTypes['post_type']) && !empty($defaultRulesTypes['post_type'])) {
                            foreach ($defaultRulesTypes['post_type'] as $postType => $title) {
                                if (!in_array($postType, $ruleTypes)) {
                                    $ruleTypes[$postType] = __('New', 'ARMember'). ' '. $title;
                                }
                            }
                        }
                        if (isset($defaultRulesTypes['taxonomy']) && !empty($defaultRulesTypes['taxonomy'])) {
                            foreach ($defaultRulesTypes['taxonomy'] as $taxonomy => $title) {
                                if ($taxonomy != 'category') {
                                    $ruleTypes[$taxonomy] = __('New', 'ARMember'). ' '. $title;
                                }
                            }
                        }
                        ?>
                        <table class="form-table">
                            <?php if (!empty($ruleTypes)): ?>
                                <?php
                                $arm_default_ar_cntr = 0;
                                foreach ($ruleTypes as $rtype => $rtitle):
                                    $default_rules[$rtype] = (!empty($default_rules[$rtype])) ? $default_rules[$rtype] : array();
                                    $arm_default_restriction_option = '';
                                    if(empty($default_rules[$rtype]))
                                    {
                                        $arm_default_restriction_option = '';
                                    }
                                    else if(is_array($default_rules[$rtype]) && in_array('-2', $default_rules[$rtype]))
                                    {
                                        $arm_default_restriction_option = '-2';
                                    }
                                    else if(!empty($default_rules[$rtype])) {
                                        $arm_default_restriction_option = '1';
                                    } ?>
                                    <tr class="form-field">
                                        <th><?php echo $rtitle; ?></th>
                                        <td>
                                            <label style="min-width: 100px;">
                                                    <input type="radio" name="arm_default_restriction_option[<?php echo $rtype; ?>]" value="" class="arm_default_restriction_option arm_iradio" <?php checked($arm_default_restriction_option, '');?>  data-cntr="<?php echo $arm_default_ar_cntr; ?>">
                                                    <?php _e('Everyone','ARMember');?>
                                            </label>
                                            <label style="min-width: 150px;">
                                                    <input type="radio" name="arm_default_restriction_option[<?php echo $rtype; ?>]" value="-2" class="arm_default_restriction_option arm_iradio" <?php checked($arm_default_restriction_option, '-2');?>  data-cntr="<?php echo $arm_default_ar_cntr; ?>">
                                                   <?php _e('Only logged in member (Everyone)','ARMember');?>
                                            </label>
                                            <label style="min-width: 150px;">
                                                    <input type="radio" name="arm_default_restriction_option[<?php echo $rtype; ?>]" value="1" class="arm_default_restriction_option arm_iradio" <?php checked($arm_default_restriction_option, '1');?> data-cntr="<?php echo $arm_default_ar_cntr; ?>">
                                                    <?php _e('Selected Plan(s) Only','ARMember');?><br>
                                            </label>
                                        </td>
                                    </tr>

                                    <tr class="form-field arm_default_access_restrictions_row arm_default_restriction_option_<?php echo $arm_default_ar_cntr; ?>" style="<?php if($arm_default_restriction_option!=1) { ?> display: none; <?php } ?>">
                                        <th>&nbsp;</th>
                                        <td>
                                            <select name="arm_default_rules[<?php echo $rtype; ?>][]" class="arm_default_rule_select arm_chosen_selectbox" multiple data-placeholder="<?php _e('Select Plan', 'ARMember'); ?>" tabindex="-1">
                                                <?php
                                                if (!empty($all_plans_data)) {
                                                    $default_rules[$rtype] = (!empty($default_rules[$rtype])) ? $default_rules[$rtype] : array();
                                                    foreach ($all_plans_data as $plan) {
                                                        if($plan['arm_subscription_plan_id']!='-2')
                                                        {
                                                        ?><option value="<?php echo $plan['arm_subscription_plan_id']; ?>" <?php echo (in_array($plan['arm_subscription_plan_id'], $default_rules[$rtype])) ? 'selected="selected"' : ''; ?>><?php echo stripslashes($plan['arm_subscription_plan_name']); ?></option>
                                            <?php
                                                        }
                                                    }
                                                }
                                            ?>
                                            </select>
                                            <?php $da_tooltip = __("Please select plan(s) for members can access", 'ARMember') . " {$rtitle} " . __("by default.", 'ARMember'); ?>
                                            <i class="arm_helptip_icon armfa armfa-question-circle" title="<?php echo $da_tooltip; ?>"></i>
                                        </td>
                                    </tr>
                                <?php
                                $arm_default_ar_cntr++;
                            endforeach; ?>
                            <?php endif; ?>
                        </table>
                        
			<?php do_action('arm_after_access_restriction_settings_html', $general_settings);?>
			<div class="arm_submit_btn_container">
				<button id="arm_access_restriction_settings_btn" class="arm_save_btn" name="arm_access_restriction_settings_btn" type="submit"><?php _e('Save', 'ARMember') ?></button>&nbsp;<img src="<?php echo MEMBERSHIPLITE_IMAGES_URL.'/arm_loader.gif' ?>" id="arm_loader_img" style="position:relative;top:8px;display:none;" width="24" height="24" />
                <?php wp_nonce_field( 'arm_wp_nonce' );?>
			</div>
		</form>
	</div>
</div>