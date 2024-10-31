jQuery(function() {
	
	if (!jQuery("#rtf_dialog").get(0)) 
		return;
		
	var allTagsDialog = jQuery("#rtf_dialog").dialog({
		bgiframe: true,
		height: 140,
		modal: true,
		autoOpen: false,
		resizable: false,
		draggable: true,
		height: 500,
		width: 700,
		title: 'Sort All Tags by:'+
		'<span id="rtf_sort_tags">&nbsp;&nbsp;'+
		'	<a href="#" id="rtf_sort_name">Name</a>&nbsp;'+
		'	<a href="#" id="rtf_sort_count">Count</a>'+
		'</span>'
		
	});
	
	jQuery('.ui-widget-overlay').live('click',function(){
		allTagsDialog.dialog('close');	
	});
	
	jQuery('#rtf_modal').click(function(e) {
		e.preventDefault();
		allTagsDialog.dialog('open');
	});
	
	// Set the first active button
	var activeSortButton = jQuery('#rtf_sort_name');
	// Class name for the active button
	var activeButtonClass = 'active';
	// Set the active button's css class
	activeSortButton.addClass(activeButtonClass);
	
	
	jQuery('#rtf_sort_count').click(function(e) {
		e.preventDefault();
		jQuery('#rtf_tags_by_name').hide();
		jQuery('#rtf_tags_by_count').show();
		
		setButton(this);
	});
	jQuery('#rtf_sort_name').click(function(e) {
		e.preventDefault();
		jQuery('#rtf_tags_by_count').hide();
		jQuery('#rtf_tags_by_name').show();
		
		setButton(this);
	});
	
	function setButton(thisButton) {
		// Remove the old active button's class
		activeSortButton.removeClass(activeButtonClass);
		// Set this button's css class to active
		jQuery(thisButton).addClass(activeButtonClass);
		// Set this button to the new active button
		activeSortButton = jQuery(thisButton);
	}
});
