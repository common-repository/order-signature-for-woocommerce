
(function($) {

	var sig = jQuery("#swph-woo-sign-signature-pad");

	jQuery("#swph-woo-sign-signature-pad").jSignature({color: swph_woo_sign_texts.sign_color  ,lineWidth:  swph_woo_sign_texts.sign_stroke  });
	
	if($("#swph_old_sign").length){
		importSignature();
	}
		
	jQuery('#swph-woo-sign-svgButton').click(function() { 
		var signature = jQuery('#swph-woo-sign-signature-pad').jSignature('getData', 'base30');
		jQuery('#swph_woo_sign_customer_signature').val(signature);
		if( jQuery('#swph-woo-sign-signature-pad').jSignature('getData', 'native').length == 0) {
				return false;
			} else {
				jQuery(this).addClass('swph-woo-sign-done-signing').html('<i class="fa fa-check"></i> ' + swph_woo_sign_texts.done_signing);
				sig.jSignature('disable');
				return true;
			}
	});
	
	jQuery('#swph-woo-sign-clearButton').click(function() { 
		jQuery('#swph_woo_sign_customer_signature').val("");
		jQuery('#swph-woo-sign-signature-pad').jSignature('clear'); 
		jQuery("#swph-woo-sign-svgButton").removeClass('swph-woo-sign-done-signing').html(swph_woo_sign_texts.done_signing);
		sig.jSignature('enable');
	}); 

	jQuery('#swph-woo-sign-signature-pad').click(function() { 
		var signature = jQuery('#swph-woo-sign-signature-pad').jSignature('getData', 'base30');
		jQuery('#swph_woo_sign_customer_signature').val(signature);
	});

}) (jQuery);

function importSignature()
{
	var sig = jQuery("#swph-woo-sign-signature-pad");	
	var dataurl= jQuery("#swph_old_sign").val();
			
	if (jQuery.trim(dataurl)) {
		sig.jSignature('importData',dataurl);
	}
	jQuery("#swph-woo-sign-svgButton").hide();
	jQuery("#swph-woo-sign-clearButton").hide();
	sig.jSignature('disable');
}
