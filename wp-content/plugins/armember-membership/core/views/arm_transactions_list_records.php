<?php
global $wpdb, $ARMember, $arm_slugs, $arm_members_class, $arm_global_settings,  $arm_payment_gateways, $arm_subscription_plans, $arm_transaction;
$payment_gateways = $arm_payment_gateways->arm_get_all_payment_gateways();
$global_currency = $arm_payment_gateways->arm_get_global_currency();
$nowDate = current_time('mysql');
$filter_gateway = (!empty($_POST['gateway'])) ? $_POST['gateway'] : '0';
$filter_ptype = (!empty($_POST['ptype'])) ? $_POST['ptype'] : '0';
$filter_pmode = (!empty($_POST['pmode'])) ? $_POST['pmode'] : '0';
$filter_pstatus = (!empty($_POST['pstatus'])) ? $_POST['pstatus'] : '0';
$filter_search = (!empty($_POST['search'])) ? $_POST['search'] : '';
$default_hide = array(
	'arm_transaction_id' => 'Transaction ID',

        'arm_user_fname' => 'First Name',
        'arm_user_lname' => 'Last Name',
	'arm_user_id' => 'User',
	'arm_plan_id' => 'Membership',
	'arm_payment_gateway' => 'Gateway',
	'arm_payment_type' => 'Payment Type',
	'arm_payer_email' => 'Payer Email',
	'arm_transaction_status' => 'Transaction Status',
	'arm_created_date' => 'Payment Date',
	'arm_amount' => 'Amount',
	'arm_cc_number' => 'Credit Card Number',
);
$user_id = get_current_user_id();
$transaction_show_hide_column = maybe_unserialize(get_user_meta($user_id, 'arm_transaction_hide_show_columns', true));

$i = 1;
$column_hide = "";
if(!empty($transaction_show_hide_column)) {
	foreach ($transaction_show_hide_column as $value) {
		if ($value != 1) {
			$column_hide = $column_hide . $i . ',';
		}
		$i++;
	}
} else {
    $column_hide = '3,4';
}

?>
<style type="text/css">
    #armmanagesearch_new{
        width:150px;
    }
    @media all and ( min-width:1400px ){
        #armmanagesearch_new{
            width:200px;
        }
    }
    @media all and ( min-width:1600px ){
        #armmanagesearch_new{
            width:250px;
        }
    }
</style>
<script type="text/javascript" charset="utf-8">
// <![CDATA[

    jQuery(document).ready(function () {
        arm_load_transaction_list_grid(false);
    });

    function arm_load_trasaction_list_filtered_grid() {
        jQuery('#armember_datatable').dataTable().fnDestroy();
        arm_load_transaction_list_grid(true);
    }

    function arm_load_transaction_list_grid(is_filtered) {

    var __ARM_Showing = '<?php echo addslashes(__('Showing','ARMember')); ?>';
    var __ARM_Showing_empty = '<?php echo addslashes(__('Showing 0 to 0 of 0 entries','ARMember')); ?>';
    var __ARM_to = '<?php echo addslashes(__('to','ARMember')); ?>';
    var __ARM_of = '<?php echo addslashes(__('of','ARMember')); ?>';
    var __ARM_transactions = '<?php _e('entries','ARMember'); ?>';
    var __ARM_Show = '<?php echo addslashes(__('Show','ARMember')); ?>';
    var __ARM_NO_FOUNT = '<?php echo addslashes(__('No any transaction found yet.','ARMember')); ?>';
    var __ARM_NO_MATCHING = '<?php echo addslashes(__('No matching transactions found.','ARMember')); ?>';

        var payment_gateway = jQuery("#arm_filter_gateway").val();
        var payment_type = jQuery("#arm_filter_ptype").val();
        var payment_mode = jQuery("#arm_filter_pmode").val();
        var payment_status = jQuery("#arm_filter_pstatus").val();
        var search_term = jQuery("#armmanagesearch_new").val();
        var payment_start_date = jQuery("#arm_filter_pstart_date").val();
        var payment_end_date = jQuery("#arm_filter_pend_date").val();
        var ajaxurl = "<?php echo admin_url('admin-ajax.php') ?>";
        var filtered_data = (typeof is_filtered !== 'undefined' && is_filtered !== false) ? true : false;
        var _wpnonce = jQuery('input[name="_wpnonce"]').val();


        var nColVisCols = [];
        var arm_cols_hide = '<?php echo count($default_hide); ?>';
        for( var cv = 1; cv <= arm_cols_hide ; cv++ ){
            nColVisCols.push( cv );
        }

        var oTables = jQuery('#armember_datatable').dataTable({
            "bProcessing": false,
            "oLanguage": {
                    "sInfo": __ARM_Showing + " _START_ " + __ARM_to + " _END_ " + __ARM_of + " _TOTAL_ " + __ARM_transactions,
                    "sInfoEmpty": __ARM_Showing_empty,
                    "sLengthMenu": __ARM_Show + "_MENU_" + __ARM_transactions,
                    "sEmptyTable": __ARM_NO_FOUNT,
                    "sZeroRecords": __ARM_NO_MATCHING
                },
            "language":{
                "searchPlaceholder": "Search",
                "search":"",
            },
            "buttons":[{
                "extend":"colvis",
                "columns":nColVisCols,
                "className":"ColVis_Button TableTools_Button ui-button ui-state-default ColVis_MasterButton",
                "text":"<span class=\"armshowhideicon\" style=\"background-image: url(<?php echo MEMBERSHIPLITE_IMAGES_URL; ?>/show_hide_icon.png);background-repeat: no-repeat;background-position: 8px center;padding: 0 10px 0 30px;background-color: #FFF;\">Show / Hide columns</span>",
            }],    
            "bServerSide": true,
            "sAjaxSource": __ARMAJAXURL,
            "sServerMethod": "POST",
            "fnServerParams": function (aoData) {
                aoData.push({"name": "action", "value": "arm_load_transactions"});
                aoData.push({"name": "gateway", "value": payment_gateway});
                aoData.push({"name": "payment_type", "value": payment_type});
                aoData.push({"name": "payment_status", "value": payment_status});
                aoData.push({"name": "payment_mode", "value": payment_mode});
                aoData.push({"name": "payment_start_date", "value": payment_start_date});
                aoData.push({"name": "payment_end_date", "value": payment_end_date});
                aoData.push({"name": "sSearch", "value": search_term});
                aoData.push({"name": "sColumns", "value": null});
                aoData.push({"name": "_wpnonce", "value": _wpnonce});
            },
            "bRetrieve": false,
            "sDom": '<"H"CBfr>t<"footer"ipl>',
            "sPaginationType": "four_button",
            "bJQueryUI": true,
            "bPaginate": true,
            "bAutoWidth": false,
            "sScrollX": "100%",
            "bScrollCollapse": true,
            "oColVis": {
                "aiExclude": [0, <?php echo count($default_hide) + 1; ?>]
            },
            "aoColumnDefs": [
                {"aTargets":[0],"sClass":"noVis"},
                {"sType": "html", "bVisible": false, "aTargets": [<?php echo $column_hide; ?>]},
                {"bSortable": false, "aTargets": [0]}
            ],
            "bStateSave": true,
            "iCookieDuration": 60 * 60,
            "sCookiePrefix": "arm_datatable_",
            "aLengthMenu": [10, 25, 50, 100, 150, 200],
            "fnPreDrawCallback": function () {
                jQuery('#transactions_list_form .arm_loading_grid').show();
            },
            "fnCreatedRow": function( nRow, aData, iDataIndex ) {
                jQuery(nRow).find('.arm_grid_action_btn_container').each(function () {
                    jQuery(this).parent().addClass('armGridActionTD');
                    jQuery(this).parent().attr('data-key', 'armGridActionTD');
                });
            },
            "fnDrawCallback": function () {
                jQuery('#transactions_list_form .arm_loading_grid').hide();
                jQuery(".cb-select-all-th").removeClass('sorting_asc');
                jQuery("#cb-select-all-1").prop("checked", false);
                arm_selectbox_init();
                if (filtered_data == true) {
                    var filter_box = jQuery('#arm_filter_wrapper_after_filter').html();
                    jQuery('div#armember_datatable_filter').parent().append(filter_box);
                    jQuery('div#armember_datatable_filter').hide();
                }
                filtered_data = false;
                if (jQuery.isFunction(jQuery().tipso)) {
                    jQuery('.armhelptip').each(function () {
                        jQuery(this).tipso({
                            position: 'top',
                            size: 'small',
                            background: '#939393',
                            color: '#ffffff',
                            width: false,
                            maxWidth: 400,
                            useTitle: true
                        });
                    });
                }
            },
            "fnStateSave": function (oSettings, oData) {
                oData.aaSorting = [];
                oData.abVisCols = [];
                oData.aoSearchCols = [];
                oData.iStart = 0;
                this.oApi._fnCreateCookie(
                    oSettings.sCookiePrefix + oSettings.sInstance,
                    this.oApi._fnJsonString(oData),
                    oSettings.iCookieDuration,
                    oSettings.sCookiePrefix,
                    oSettings.fnCookieCallback
                    );
            },
            "fnStateLoadParams": function (oSettings, oData) {
                oData.iLength = 10;
               // oData.oSearch.sSearch = search_term;
            },
        });
        var filter_box = jQuery('#arm_filter_wrapper').html();
        jQuery('div#armember_datatable_filter').parent().append(filter_box);
        jQuery('div#armember_datatable_filter').hide();
        jQuery('#arm_filter_wrapper').remove();
        jQuery('#armmanagesearch_new').on('keyup', function (e) {
            e.stopPropagation();
            if (e.keyCode == 13) {
                var gateway = jQuery('#arm_filter_gateway').val();
                var ptype = jQuery('#arm_filter_ptype').val();
                var pstatus = jQuery('#arm_filter_pstatus').val();
                var search = jQuery('#armmanagesearch_new').val();
                arm_reload_log_list(gateway, ptype, pstatus, search);
                return false;
            }
        });
    }
    function ChangeID(id, type)
    {
        document.getElementById('delete_id').value = id;
        document.getElementById('delete_type').value = type;
    }
    function ChangeStatus(id, status)
    {
        document.getElementById('log_id').value = id;
        document.getElementById('log_status').value = status;
    }
// ]]>
</script>
<div class="arm_filter_wrapper" id="arm_filter_wrapper_after_filter" style="display:none;">

    <div class="arm_datatable_filters_options">
        <div class='sltstandard'>
            <input type='hidden' id='arm_transaction_bulk_action1' name="action1" value="-1" />
            <dl class="arm_selectbox column_level_dd">
                <dt style="width: 120px;"><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"  /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                <dd>
                    <ul data-id="arm_transaction_bulk_action1">
                        <li data-label="<?php _e('Bulk Actions', 'ARMember'); ?>" data-value="-1"><?php _e('Bulk Actions', 'ARMember'); ?></li>
                        <li data-label="<?php _e('Delete', 'ARMember'); ?>" data-value="delete_transaction"><?php _e('Delete', 'ARMember'); ?></li>
                    </ul>
                </dd>
            </dl>
        </div>
        <input type="submit" id="doaction1" class="armbulkbtn armemailaddbtn" value="<?php _e('Go', 'ARMember'); ?>"/>
    </div>

</div>
<div class="arm_transactions_list">
    <div class="arm_filter_wrapper" id="arm_filter_wrapper" style="display:none;">
        <div class="arm_datatable_filters_options">
            <div class='sltstandard'>
                <input type='hidden' id='arm_transaction_bulk_action1' name="action1" value="-1" />
                <dl class="arm_selectbox column_level_dd">
                    <dt style="width: 120px;"><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"  /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                    <dd>
                        <ul data-id="arm_transaction_bulk_action1">
                            <li data-label="<?php _e('Bulk Actions', 'ARMember'); ?>" data-value="-1"><?php _e('Bulk Actions', 'ARMember'); ?></li>
                            <li data-label="<?php _e('Delete', 'ARMember'); ?>" data-value="delete_transaction"><?php _e('Delete', 'ARMember'); ?></li>
                        </ul>
                    </dd>
                </dl>
            </div>
            <input type="submit" id="doaction1" class="armbulkbtn armemailaddbtn" value="<?php _e('Go', 'ARMember'); ?>"/>
        </div>
    </div>
    <form method="GET" id="transactions_list_form" class="data_grid_list" onsubmit="return arm_transactions_list_form_bulk_action();">
        <input type="hidden" name="page" value="<?php echo $arm_slugs->transactions; ?>" />
        <input type="hidden" name="armaction" value="list" />
        <div class="arm_datatable_filters">
            <div class="arm_dt_filter_block arm_datatable_searchbox">
                <label><input type="text" placeholder="<?php _e('Search', 'ARMember'); ?>" id="armmanagesearch_new" value="<?php echo $filter_search; ?>" tabindex="-1" ></label>
                <?php if (!empty($payment_gateways)) : ?>
                    <!--./====================Begin Filter By Payment Gateway Box====================/.-->
                    <div class="arm_datatable_filter_item arm_filter_gateway_label">
                        <span><?php _e('Gateway', 'ARMember') ?>:</span>
                        <input type="hidden" id="arm_filter_gateway" class="arm_filter_gateway" value="<?php echo $filter_gateway; ?>" />
                        <dl class="arm_selectbox">
                            <dt style="width: 110px;"><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                            <dd>
                                <ul data-id="arm_filter_gateway">
                                    <li data-label="<?php _e('All', 'ARMember'); ?>" data-value="0"><?php _e('All', 'ARMember'); ?></li>
                                    <li data-label="<?php _e('Manual', 'ARMember'); ?>" data-value="<?php _e('manual', 'ARMember'); ?>"><?php _e('Manual', 'ARMember'); ?></li>
                                    <?php foreach ($payment_gateways as $key => $pg): ?>
                                        <li data-label="<?php echo $pg['gateway_name']; ?>" data-value="<?php echo $key; ?>"><?php echo $pg['gateway_name']; ?></li>                                                                                
                                    <?php endforeach; ?>
                                </ul>
                            </dd>
                        </dl>
                    </div>
                    <!--./====================End Filter By Payment Gateway Box====================/.-->
                <?php endif; ?>
                <!--./====================Begin Filter By Payment Type Box====================/.-->
                <div class="arm_datatable_filter_item arm_filter_ptype_label">
                    <span><?php _e('Payment Type', 'ARMember') ?>:</span>
                    <input type="hidden" id="arm_filter_ptype" class="arm_filter_ptype" value="<?php echo $filter_ptype; ?>" />
                    <dl class="arm_selectbox">
                        <dt style="min-width:60px; width: 75px;"><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                        <dd>
                            <ul data-id="arm_filter_ptype">
                                <li data-label="<?php _e('All', 'ARMember'); ?>" data-value="0"><?php _e('All', 'ARMember'); ?></li>
                                <li data-label="<?php _e('One Time', 'ARMember'); ?>" data-value="one_time"><?php _e('One Time', 'ARMember'); ?></li>
                                <li data-label="<?php _e('Recurring', 'ARMember'); ?>" data-value="subscription"><?php _e('Recurring', 'ARMember'); ?></li>
                            </ul>
                        </dd>
                    </dl>
                </div>
                <!--./====================End Filter By Payment Type Box====================/.-->

                <!--./====================Begin Filter By Payment Mode Box====================/.-->
                <div class="arm_datatable_filter_item arm_filter_pmode_label">
                    <span><?php _e('Subscription', 'ARMember') ?>:</span>
                    <input type="hidden" id="arm_filter_pmode" class="arm_filter_pmode" value="<?php echo $filter_pmode; ?>" />
                    <dl class="arm_selectbox">
                        <dt style="min-width:80px; width: 80px;"><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                        <dd>
                            <ul data-id="arm_filter_pmode">
                                <li data-label="<?php _e('All', 'ARMember'); ?>" data-value="0"><?php _e('All', 'ARMember'); ?></li>
                                <li data-label="<?php _e('Automatic Subscription', 'ARMember'); ?>" data-value="auto_debit_subscription"><?php _e('Automatic Subscription', 'ARMember'); ?></li>
                                <li data-label="<?php _e('Semi Automatic Subscription', 'ARMember'); ?>" data-value="manual_subscription"><?php _e('Semi Automatic Subscription', 'ARMember'); ?></li>
                            </ul>
                        </dd>
                    </dl>
                </div>
                <!--./====================End Filter By Payment Mode Box====================/.-->
                <!--./====================Begin Filter By Payment Status Box====================/.-->
                <div class="arm_datatable_filter_item arm_filter_pstatus_label">
                    <span><?php _e('Status', 'ARMember') ?>:</span>
                    <input type="hidden" id="arm_filter_pstatus" class="arm_filter_pstatus" value="<?php echo $filter_pstatus; ?>" />
                    <dl class="arm_selectbox">
                        <dt style="min-width:60px; width: 75px;"><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                        <dd>
                            <ul data-id="arm_filter_pstatus">
                                <li data-label="<?php _e('All', 'ARMember'); ?>" data-value="0"><?php _e('All', 'ARMember'); ?></li>
                                <li data-label="<?php _e('Success', 'ARMember'); ?>" data-value="success"><?php _e('Success', 'ARMember'); ?></li>
                                <li data-label="<?php _e('Pending', 'ARMember'); ?>" data-value="pending"><?php _e('Pending', 'ARMember'); ?></li>
                                <li data-label="<?php _e('Cancelled', 'ARMember'); ?>" data-value="canceled"><?php _e('Cancelled', 'ARMember'); ?></li>
                                <li data-label="<?php _e('Failed', 'ARMember'); ?>" data-value="failed"><?php _e('Failed', 'ARMember'); ?></li>
                                <li data-label="<?php _e('Expired', 'ARMember'); ?>" data-value="expired"><?php _e('Expired', 'ARMember'); ?></li>
                            </ul>
                        </dd>
                    </dl>
                </div>
                <!--./====================End Filter By Payment Status Box====================/.-->
            </div>
            <div>
                <!--./====================Begin Filter By Date====================/.-->
                <div class="arm_datatable_filter_item arm_filter_pstatus_label" style="margin-left: 0;">
                    <input type="text" id="arm_filter_pstart_date" placeholder="start date" />
                </div>
                <div class="arm_datatable_filter_item arm_filter_pstatus_label">
                    <input type="text" id="arm_filter_pend_date" placeholder="end date" />
                </div>
                <!--./====================End Begin Filter By Date====================/.-->
            </div>
            <div class="arm_dt_filter_block arm_dt_filter_submit arm_payment_history_filter_submit">
                <input type="button" class="armemailaddbtn" id="arm_payment_grid_filter_btn" value="<?php _e('Filter', 'ARMember'); ?>" onClick="arm_load_trasaction_list_filtered_grid()"/>
            </div>
            <div class="armclear"></div>
        </div>
        <div id="armmainformnewlist" class="arm_filter_grid_list_container">
            <div class="arm_loading_grid" style="display: none;"><img src="<?php echo MEMBERSHIPLITE_IMAGES_URL; ?>/loader.gif" alt="Loading.."></div>
            <div class="response_messages"></div>
            <table cellpadding="0" cellspacing="0" border="0" class="display" id="armember_datatable">
                <thead>
                    <tr>
                        <th class="center cb-select-all-th" style="max-width:60px;"><input id="cb-select-all-1" type="checkbox" class="chkstanard"></th>
                        <th><?php _e('Transaction ID', 'ARMember'); ?></th>
                      
                        <th><?php _e('First Name', 'ARMember'); ?></th>
                        <th><?php _e('Last Name','ARMember'); ?></th>
                        <th><?php _e('User', 'ARMember'); ?></th>
                        <th style="min-width: 150px;"><?php _e('Membership', 'ARMember'); ?></th>
                        <th><?php _e('Gateway', 'ARMember'); ?></th>
                        <th><?php _e('Payment Type', 'ARMember'); ?></th>
                        <th><?php _e('Payer Email', 'ARMember'); ?></th>
                        <th class="center"><?php _e('Transaction Status', 'ARMember'); ?></th>
                        <th class="center" style="min-width: 150px;"><?php _e('Payment Date', 'ARMember'); ?></th>
                        <th class="center"><?php _e('Amount', 'ARMember'); ?></th>
                        <th class="center" style="min-width: 150px;"><?php _e('Credit Card Number', 'ARMember'); ?></th>
                        <th data-key="armGridActionTD" class="armGridActionTD" style="display: none;"></th>
                    </tr>
                </thead>
            </table>
            <div class="armclear"></div>
            <input type="hidden" name="show_hide_columns" id="show_hide_columns" value="<?php _e('Show / Hide columns', 'ARMember'); ?>"/>
            <input type="hidden" name="search_grid" id="search_grid" value="<?php _e('Search', 'ARMember'); ?>"/>
            <input type="hidden" name="entries_grid" id="entries_grid" value="<?php _e('transactions', 'ARMember'); ?>"/>
            <input type="hidden" name="show_grid" id="show_grid" value="<?php _e('Show', 'ARMember'); ?>"/>
            <input type="hidden" name="showing_grid" id="showing_grid" value="<?php _e('Showing', 'ARMember'); ?>"/>
            <input type="hidden" name="to_grid" id="to_grid" value="<?php _e('to', 'ARMember'); ?>"/>
            <input type="hidden" name="of_grid" id="of_grid" value="<?php _e('of', 'ARMember'); ?>"/>
            <input type="hidden" name="no_match_record_grid" id="no_match_record_grid" value="<?php _e('No matching transactions found', 'ARMember'); ?>"/>
            <input type="hidden" name="no_record_grid" id="no_record_grid" value="<?php _e('No any transaction found yet.', 'ARMember'); ?>"/>
            <input type="hidden" name="filter_grid" id="filter_grid" value="<?php _e('filtered from', 'ARMember'); ?>"/>
            <input type="hidden" name="totalwd_grid" id="totalwd_grid" value="<?php _e('total', 'ARMember'); ?>"/>
            <?php wp_nonce_field( 'arm_wp_nonce' );?>
        </div>
        <div class="footer_grid"></div>
    </form>
</div>

<div class="arm_member_view_detail_container"></div>