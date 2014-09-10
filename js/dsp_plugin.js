jQuery(document).ready(function() {     
	jQuery('#parent_cat').change(function(){
	
	var parentCat=jQuery('#parent_cat').val();
	var data = {
	action: 'category_select_action',
	dsp_ajax_gallery_nonce : dsp_plugin_vars.dsp_ajax_gallery_nonce,
	parent_cat_ID:parentCat
	};
	 jQuery.post(ajaxurl, data, function(response) {
		jQuery('#sub_cat_disabled').removeAttr("disabled").html(response);
		jQuery('#parent_cat_id_hidden').val(parentCat);
		return false;
		});
	});

	jQuery('#sub_cat_disabled').change(function(){
	
	var childCat=jQuery('#sub_cat_disabled').val();
	var data = {
	action: 'category_select_action2',
	dsp_ajax_gallery_nonce : dsp_plugin_vars.dsp_ajax_gallery_nonce,
	child_cat_ID:childCat
	};
	 jQuery.post(ajaxurl, data, function(response) {
		jQuery('#sub_child_disabled').removeAttr("disabled").html(response);
		jQuery('#child_cat_id_hidden').val(childCat);
		return false;
		});
	});

	jQuery('#sub_child_disabled').change(function(){
	
	var subchildCat=jQuery('#sub_child_disabled').val();
	var data = {
	//action: 'category_select_action3',
	//dsp_ajax_gallery_nonce : dsp_plugin_vars.dsp_ajax_gallery_nonce,
	subchild_cat_ID:subchildCat
	};
	 jQuery.post(ajaxurl, data, function(response) {
		jQuery('#subchild_cat_id_hidden').val(subchildCat);
		return false;
		});
	});  	
	
	jQuery('#reset').click(function(){
	jQuery('#parent_cat_id_hidden').val('');
	jQuery('#parent_cat_name').val('');
	jQuery('#child_cat_id_hidden').val('');
	jQuery('#child_cat_name').val('');
	jQuery('#subchild_cat_id_hidden').val('');
	jQuery('#subchild_cat_name').val('');
	
	});  
});    


