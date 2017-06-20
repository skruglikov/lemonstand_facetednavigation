function facets_selected()
{
	return $('listFacetedNavigation_Settings_index_list_body').getElements('tr td.checkbox input').some(function(element){
		return element.checked;
	});
}

function delete_selected()
{
	if (!facets_selected())
	{
		alert('Please select facet(s) to delete.');
		return false;
	}
	
	$('listFacetedNavigation_Settings_index_list_body').getForm().sendPhpr(
		'onDeleteFacetSelected',
		{
			confirm: 'Do you really want to delete selected facet(s)?',
			loadIndicator: {show: false},
			onBeforePost: LightLoadingIndicator.show.pass('Loading...'),
			onComplete: LightLoadingIndicator.hide,
			update: 'settings_page_content',
			onAfterUpdate: update_scrollable_toolbars
		}
	);
	return false;
}

function init_facets_sortables()
{
	var list = $('listFacetedNavigation_Settings_index_list_body');

	if (list)
	{
		list.makeListSortable('onSetFacetsOrders', 'facet_order', 'facet_id', 'sort_handle');
		list.addEvent('dragComplete', fix_group_zebra);
	}
}

function fix_group_zebra()
{
	$('listFacetedNavigation_Settings_index_list_body').getChildren().each(function(element, index){
		if (index % 2)
			element.addClass('even');
		else
			element.removeClass('even');
	});
}

function make_values_sortable()
{
	if ($('facet_values_list_body'))
		$('facet_values_list_body').makeListSortable('onSetFacetValuesOrders', 'value_order', 'value_id', 'value_sort_handle');
}

window.addEvent('domready', function(){
	
	var container = $('settings_page_content');
	
	if (container)
		container.addEvent('listUpdate', init_facets_sortables);

	make_values_sortable();
	init_facets_sortables();
	
});