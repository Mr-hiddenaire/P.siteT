!function(){var e=wp.element.createElement,t={};registerBlockType=wp.blocks.registerBlockType,RichText=wp.blocks.RichText,source=wp.blocks.source;var r=wp.i18n.__;const o=e("svg",{width:20,height:20,viewBox:"-3 -1 23 20.22",style:{fill:"#00B2F0"}},e("path",{d:"M4.407,20.231 C2.002,14.225 3.926,9.833 3.926,9.833 C8.781,0.839 22.999,10.111 22.999,10.111 C5.011,3.480 4.407,20.231 4.407,20.231 ZM3.520,6.918 C1.576,6.918 -0.000,5.368 -0.000,3.455 C-0.000,1.543 1.576,-0.007 3.520,-0.007 C5.464,-0.007 7.039,1.543 7.039,3.455 C7.039,5.368 5.464,6.918 3.520,6.918 Z"}));registerBlockType("armember/armember-shortcode",{title:r("Membership Shortcodes"),icon:o,category:"armember",keywords:[r("Membership"),r("ARMember"),r("Shortcode")],attributes:{ArmShortcode:{type:"string","default":""},content:{source:"html",selector:"h2"}},html:!0,insert:function(){arm_open_form_shortcode_popup()},edit:function(r){window.arm_props_selected="1",window.arm_props=r;var o=jQuery("#block-"+window.arm_props.clientId).find(".wp-block-armember-armember-shortcode").val(),n=jQuery("#block-"+window.arm_props.clientId).find(".wp-block-armember-armember-shortcode").length;if("armember/armember-shortcode"==r.name){if(""==o||void 0==o||"undefined"==o||0==n){if(!r.isSelected)return e("textarea",{className:"wp-block-armember-armember-shortcode",style:t,onChange:function(){r.setAttributes({ArmShortcode:jQuery("#block-"+window.arm_props.clientId).find(".wp-block-armember-armember-shortcode").val()})}},r.attributes.ArmShortcode);arm_open_form_shortcode_popup()}return e("textarea",{className:"wp-block-armember-armember-shortcode",style:t,onChange:function(){r.setAttributes({ArmShortcode:jQuery("#block-"+window.arm_props.clientId).find(".wp-block-armember-armember-shortcode").val()})}},r.attributes.ArmShortcode)}},save:function(e){return void 0!==window.arm_props&&null!==window.arm_props&&1==jQuery("#block-"+window.arm_props.clientId).find(".editor-block-list__block-html-textarea").is(":visible")&&(e.attributes.ArmShortcode=jQuery("#block-"+window.arm_props.clientId).find(".editor-block-list__block-html-textarea").val()),e.attributes.ArmShortcode}}),registerBlockType("armember/armember-restrict-content",{title:r("Restrict Content Shortcode"),icon:o,category:"armember",keywords:[r("Membership"),r("ARMember"),r("Restriction")],attributes:{ArmRestrictContent:{type:"string","default":""},content:{source:"html",selector:"h2"}},html:!0,insert:function(){arm_open_restriction_shortcode_popup()},edit:function(r){window.arm_props_selected="2",window.arm_restrict_content_props=r;var o=jQuery("#block-"+window.arm_restrict_content_props.clientId).find(".wp-block-armember-armember-restrict-content-textarea").val(),n=jQuery("#block-"+window.arm_restrict_content_props.clientId).find(".wp-block-armember-armember-restrict-content-textarea").length;if("armember/armember-restrict-content"==r.name){if(""==o||void 0==o||"undefined"==o||0==n){if(!r.isSelected)return e("textarea",{className:"wp-block-armember-armember-restrict-content-textarea",style:t,onChange:function(){r.setAttributes({ArmRestrictContent:jQuery("#block-"+window.arm_restrict_content_props.clientId).find(".wp-block-armember-armember-restrict-content-textarea").val()})}},r.attributes.ArmRestrictContent);arm_open_restriction_shortcode_popup()}return e("textarea",{className:"wp-block-armember-armember-restrict-content-textarea",style:t,onChange:function(){r.setAttributes({ArmRestrictContent:jQuery("#block-"+window.arm_restrict_content_props.clientId).find(".wp-block-armember-armember-restrict-content-textarea").val()})}},r.attributes.ArmRestrictContent)}},save:function(e){return void 0!==window.arm_restrict_content_props&&null!==window.arm_restrict_content_props&&1==jQuery("#block-"+window.arm_restrict_content_props.clientId).find(".editor-block-list__block-html-textarea").is(":visible")&&(e.attributes.ArmRestrictContent=jQuery("#block-"+window.arm_restrict_content_props.clientId).find(".editor-block-list__block-html-textarea").val()),e.attributes.ArmRestrictContent}})}(window.wp.blocks,window.wp.components,window.wp.i18n,window.wp.element,window.wp.editor);