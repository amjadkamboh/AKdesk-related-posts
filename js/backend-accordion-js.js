jQuery(document).ready(function() {
	//toggle the component with class accordion_body
	jQuery(".accordion_head").click(function() {

		if (jQuery('.accordion_body').is(':visible')) {
			jQuery(".accordion_body").slideUp(300);
			jQuery(".plusminus").text('+');
		}
		if (jQuery(this).next(".accordion_body").is(':visible')) {
			jQuery(this).next(".accordion_body").slideUp(300);
			jQuery(this).children(".plusminus").text('+');
		} else {
			jQuery(this).next(".accordion_body").slideDown(300);
			jQuery(this).children(".plusminus").text('-');
		}
	});
});
