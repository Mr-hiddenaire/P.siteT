function arm_change_graph_type(r,e){jQuery(".armgraphtype_"+e).removeClass("selected"),jQuery("#armgraphtype_"+e+"_div_"+r).addClass("selected"),jQuery("#armgraphtype_"+e+"_"+r).prop("checked",!0);var a=jQuery("#armgraphval_"+e).val();arm_change_graph(a,e)}function arm_change_graph(r,e,a,t,_){("undefined"==typeof a||""==a||null==a||1>a)&&(a=1),"undefined"!=typeof t&&""!=t&&null!=t||(t=!1),"undefined"!=typeof _&&""!=_&&null!=_||(_="0"),jQuery("#armgraphval_"+e).val(r);var y=r,n=jQuery("input[name=armgraphtype_"+e+"]:checked").val(),m=jQuery("#arm_plan_filter").val(),l=jQuery("#arm_year_filter").val(),p="",u="",i="",o=jQuery("#arm_report_type").val();"payment_report"==o&&(i=jQuery("#arm_gateway_filter").val()),"daily"==r?(u=jQuery("#arm_date_filter").val(),jQuery("#monthly_"+e+", #yearly_"+e).removeClass("active"),jQuery("#daily_"+e).addClass("active"),jQuery("#arm_year_filter_item").hide(),jQuery("#arm_month_filter_item").hide(),jQuery("#arm_date_filter_item").show()):"monthly"==r?(p=jQuery("#arm_month_filter").val(),jQuery("#daily_"+e+", #yearly_"+e).removeClass("active"),jQuery("#monthly_"+e).addClass("active"),jQuery("#arm_year_filter_item").show(),jQuery("#arm_month_filter_item").show(),jQuery("#arm_date_filter_item").hide()):"yearly"==r&&(jQuery("#monthly_"+e+", #daily_"+e).removeClass("active"),jQuery("#yearly_"+e).addClass("active"),jQuery("#arm_year_filter_item").show(),jQuery("#arm_month_filter_item").hide(),jQuery("#arm_date_filter_item").hide());var h="armupdatecharts";if(1==t)var h="armupdatereportgrid";return"1"==_?(jQuery(".arm_members_table_container .arm_all_loginhistory_wrapper .form-table.arm_member_last_subscriptions_table tbody tr").length>0&&(jQuery(".arm_loading").show(),jQuery("#arm_report_analytics_form").attr("onsubmit","").attr("action","#").attr("method","post"),jQuery("input[name='is_export_to_csv']").val("1"),jQuery("input[name='current_page']").val(a),jQuery("input[name='gateway_filter']").val(i),jQuery("input[name='date_filter']").val(u),jQuery("input[name='month_filter']").val(p),jQuery("input[name='year_filter']").val(l),jQuery("input[name='plan_id']").val(m),jQuery("input[name='plan_type']").val(e),jQuery("input[name='graph_type']").val(n),jQuery("input[name='type']").val(y),jQuery("input[name='action']").val(h),jQuery("input[name='arm_export_report_data']").val("1"),jQuery("#arm_report_analytics_form").submit(),jQuery("input[name='is_export_to_csv']").val("0"),jQuery("input[name='current_page']").val(""),jQuery("input[name='gateway_filter']").val(""),jQuery("input[name='date_filter']").val(""),jQuery("input[name='month_filter']").val(""),jQuery("input[name='year_filter']").val(""),jQuery("input[name='plan_id']").val(""),jQuery("input[name='plan_type']").val(""),jQuery("input[name='graph_type']").val(""),jQuery("input[name='type']").val(""),jQuery("input[name='action']").val(""),jQuery("input[name='arm_export_report_data']").val("0"),jQuery(".arm_loading").hide()),!1):void jQuery.ajax({type:"POST",url:ajaxurl,beforeSend:function(){jQuery(".arm_loading").show()},data:"action="+h+"&type="+y+"&graph_type="+n+"&plan_type="+e+"&plan_id="+m+"&year_filter="+l+"&month_filter="+p+"&date_filter="+u+"&gateway_filter="+i+"&current_page="+a,success:function(r){if(jQuery(".arm_loading").hide(),t){var a=r.split("[ARM_REPORT_SEPARATOR]");"payment_history"==e?(jQuery(".arm_payments_table_body_content").html(a[0]),jQuery("#arm_payments_table_paging").html(a[1])):"pay_per_post_report"==e?(jQuery(".arm_pay_per_post_report_table_body_content").html(a[0]),jQuery("#arm_payments_table_paging").html(a[1])):(jQuery(".arm_members_table_body_content").html(a[0]),jQuery("#arm_members_table_paging").html(a[1]))}else jQuery("#chart_container_"+e).html(r)}})}function arm_change_graph_pre(r,e,a){if(0==e)return!1;var t=jQuery("input[name=armgraphtype_"+a+"]:checked").val(),_=jQuery("#arm_plan_filter").val(),y=jQuery("#arm_year_filter").val(),n="",m="",l="",p=jQuery("#arm_report_type").val();if("payment_report"==p&&(l=jQuery("#arm_gateway_filter").val()),"yearly"==r){y="";var u=jQuery("#"+r+"_"+a+" #current_year").val(),i=u-1;jQuery("#arm_year_filter_item .arm_year_filter:input").val(i);var o=jQuery("#arm_year_filter_item").find('[data-value="'+i+'"]').html();jQuery("#arm_year_filter_item").find("span").html(o),jQuery("#current_year").val(i);var h="&new_year="+i}else if("monthly"==r){n=jQuery("#arm_month_filter").val();var u=jQuery("#"+r+"_"+a+" #current_month").val(),v=jQuery("#"+r+"_"+a+" #current_month_year").val(),j=parseInt(u),Q=parseInt(v);1==j?(Q-=1,j=12):j-=1,jQuery("#arm_month_filter_item .arm_month_filter:input").val(j);var d=jQuery("#arm_month_filter_item").find('[data-value="'+j+'"]').html();jQuery("#arm_month_filter_item").find("span").html(d),jQuery("#arm_year_filter_item .arm_year_filter:input").val(Q);var o=jQuery("#arm_year_filter_item").find('[data-value="'+Q+'"]').html();jQuery("#arm_year_filter_item").find("span").html(o),jQuery("#"+r+"_"+a+" #current_month").val(j),jQuery("#"+r+"_"+a+" #current_month_year").val(Q);var h="&new_month="+j+"&new_month_year="+Q}else if("daily"==r){var u=jQuery("#"+r+"_"+a+" #current_day").val(),s=jQuery("#"+r+"_"+a+" #current_day_month").val(),c=jQuery("#"+r+"_"+a+" #current_day_year").val(),f=parseInt(u),g=parseInt(s),b=parseInt(c);if(1==u){g-=1,0==g&&(g=12);var w=new Date(b,g,0);f=w.getDate()}else f=parseInt(u)-1;1==s&&1==u?b-=1:b=b,jQuery("#"+r+"_"+a+" #current_day").val(f),jQuery("#"+r+"_"+a+" #current_day_month").val(g),jQuery("#"+r+"_"+a+" #current_day_year").val(b);var h="&new_day="+f+"&new_day_month="+g+"&new_day_year="+b}jQuery.ajax({type:"POST",url:ajaxurl,beforeSend:function(){jQuery(".arm_loading").show()},data:"action=armupdatecharts&type="+r+"&calculate=pre"+h+"&graph_type="+t+"&plan_type="+a+"&plan_id="+_+"&year_filter="+y+"&month_filter="+n+"&date_filter="+m+"&gateway_filter="+l,success:function(r){jQuery(".arm_loading").hide(),jQuery("#chart_container_"+a).html(r)}})}function arm_change_graph_next(r,e,a){if(0==e)return!1;var t,_=jQuery("input[name=armgraphtype_"+a+"]:checked").val(),y=jQuery("#arm_plan_filter").val(),n=jQuery("#arm_year_filter").val(),m="",l="",p="",u=jQuery("#arm_report_type").val();if("payment_report"==u&&(p=jQuery("#arm_gateway_filter").val()),"yearly"==r){n="";var i=jQuery("#"+r+"_"+a+" #current_year").val(),o=parseInt(i)+1;jQuery("#arm_year_filter_item .arm_year_filter:input").val(o);var h=jQuery("#arm_year_filter_item").find('[data-value="'+o+'"]').html();jQuery("#arm_year_filter_item").find("span").html(h),jQuery("#"+r+"_"+a+" #current_year").val(o);var t="&new_year="+o}else if("monthly"==r){m=jQuery("#arm_month_filter").val();var i=jQuery("#"+r+"_"+a+" #current_month").val(),v=jQuery("#"+r+"_"+a+" #current_month_year").val(),j=parseInt(i),Q=parseInt(v);12==j?(Q+=1,j=1):j+=1,jQuery("#arm_month_filter_item .arm_month_filter:input").val(j);var d=jQuery("#arm_month_filter_item").find('[data-value="'+j+'"]').html();jQuery("#arm_month_filter_item").find("span").html(d),jQuery("#arm_year_filter_item .arm_year_filter:input").val(Q);var h=jQuery("#arm_year_filter_item").find('[data-value="'+Q+'"]').html();jQuery("#arm_year_filter_item").find("span").html(h),jQuery("#"+r+"_"+a+" #current_month").val(j),jQuery("#"+r+"_"+a+" #current_month_year").val(Q);var t="&new_month="+j+"&new_month_year="+Q}else if("daily"==r){var i=jQuery("#"+r+"_"+a+" #current_day").val(),s=jQuery("#"+r+"_"+a+" #current_day_month").val(),c=jQuery("#"+r+"_"+a+" #current_day_year").val(),f=parseInt(i),g=parseInt(s),b=parseInt(c),w=new Date(b,g,0),x=w.getDate();if(i==x)var g=g+1,f=1;else var f=parseInt(i)+1;if(12==s&&i==x)var b=b+1,f=1,g=1;else b=b;jQuery("#"+r+"_"+a+" #current_day").val(f),jQuery("#"+r+"_"+a+" #current_day_month").val(g),jQuery("#"+r+"_"+a+" #current_day_year").val(b);var t="&new_day="+f+"&new_day_month="+g+"&new_day_year="+b}jQuery.ajax({type:"POST",url:ajaxurl,beforeSend:function(){jQuery(".arm_loading").show()},data:"action=armupdatecharts&type="+r+"&calculate=next"+t+"&graph_type="+_+"&plan_type="+a+"&plan_id="+y+"&year_filter="+n+"&month_filter="+m+"&date_filter="+l+"&gateway_filter="+p,success:function(r){jQuery(".arm_loading").hide(),jQuery("#chart_container_"+a).html(r)}})}function arm_change_login_hisotry_report(r){jQuery("#arm_login_history_type").val(r),jQuery(".btn_chart_type").removeClass("active"),jQuery("#"+r).addClass("active"),jQuery(".arm_login_history_page_search_btn").trigger("click")}jQuery(document).ready(function(){var r=jQuery("#arm_report_type").val(),e=(jQuery("#armgraphval_members").val(),jQuery("#armgraphval_members_plan").val(),jQuery("#armgraphval_payment_history").val(),"");if("member_report"==r?(e=jQuery("#armgraphval_members").val(),arm_change_graph(e,"members")):"payment_report"==r?(e=jQuery("#armgraphval_payment_history").val(),arm_change_graph(e,"payment_history")):"pay_per_post_report"==r&&(e=jQuery("#armgraphval_pay_per_post_report").val(),arm_change_graph(e,"pay_per_post_report")),jQuery.isFunction(jQuery().datetimepicker)){var a=new Date,t="YYYY-MM-DD";jQuery(".arm_datepicker_filter").datetimepicker({defaultDate:a,useCurrent:!1,format:t})}jQuery("#arm_report_export_button").on("click",function(){var a="1";"member_report"==r?jQuery("tbody.arm_members_table_body_content tr").length>0&&jQuery("tbody.arm_members_table_body_content tr .arm_report_grid_no_data").length<=0&&(e=jQuery("#armgraphval_members").val(),arm_change_graph(e,"members","","",a)):"payment_report"==r?jQuery("tbody.arm_payments_table_body_content tr").length>0&&jQuery("tbody.arm_payments_table_body_content tr .arm_report_grid_no_data").length<=0&&(e=jQuery("#armgraphval_payment_history").val(),arm_change_graph(e,"payment_history","","",a)):"pay_per_post_report"==r&&jQuery("tbody.arm_pay_per_post_report_table_body_content tr").length>0&&jQuery("tbody.arm_pay_per_post_report_table_body_content tr .arm_report_grid_no_data").length<=0&&(e=jQuery("#armgraphval_pay_per_post_report").val(),arm_change_graph(e,"pay_per_post_report","","",a))})}),jQuery(document).on("click","#arm_report_apply_filter_button",function(){var r=jQuery("#arm_report_type").val(),e=(jQuery("#armgraphval_members").val(),jQuery("#armgraphval_members_plan").val(),jQuery("#armgraphval_payment_history").val(),"");"member_report"==r?(e=jQuery("#armgraphval_members").val(),arm_change_graph(e,"members")):"payment_report"==r?(e=jQuery("#armgraphval_payment_history").val(),arm_change_graph(e,"payment_history")):"pay_per_post_report"==r&&(e=jQuery("#armgraphval_pay_per_post_report").val(),arm_change_graph(e,"pay_per_post_report"))}),jQuery(document).on("click",".arm_report_analytics_content .arm_page_numbers:not(.dots)",function(){var r=jQuery("#arm_report_type").val(),e=jQuery(this).attr("data-page");"member_report"==r?(arm_graph_type=jQuery("#armgraphval_members").val(),arm_change_graph(arm_graph_type,"members",e,!0)):"payment_report"==r?(arm_graph_type=jQuery("#armgraphval_payment_history").val(),arm_change_graph(arm_graph_type,"payment_history",e,!0)):"pay_per_post_report"==r&&(arm_graph_type=jQuery("#armgraphval_pay_per_post_report").val(),arm_change_graph(arm_graph_type,"pay_per_post_report"))});