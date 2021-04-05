jQuery(document).ready(function(){
	jQuery(".postlistthumb").hover(function(){
		vTagObj = jQuery(this).find("video");
		videoPreviewUrl = jQuery(vTagObj).attr("data-src");
		jQuery(vTagObj).attr("src", videoPreviewUrl);
		jQuery(vTagObj).attr("autoplay", true);
		jQuery(vTagObj).attr("loop", true);
		jQuery(vTagObj).attr("disableremoteplayback", true);
		jQuery(vTagObj).attr("playsinline", true);
	}, function(){
		jQuery(vTagObj).attr("src", "");
		jQuery(vTagObj).attr("loop", false);
		jQuery(vTagObj).attr("autoplay", false);
	});
	
	jQuery(".postlistthumb").on("click touchstart", function(){
		vTagObj = jQuery(this).find("video");
		videoPreviewUrl = jQuery(vTagObj).attr("data-src");
		jQuery(vTagObj).attr("src", videoPreviewUrl);
		jQuery(vTagObj).attr("loop", true);
		jQuery(vTagObj).attr("autoplay", true);
		jQuery(vTagObj).attr("disableremoteplayback", true);
		jQuery(vTagObj).attr("playsinline", true);
	});
});