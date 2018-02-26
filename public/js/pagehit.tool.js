var _pageId = null;
window.melisCmsPageAnalyticsAppendLoadedFlag = function () {
    $('<div id="id_melis_cms_page_analytics_loaded_flag"></div>').insertAfter("#tableMelisCmsPageAnalytics_wrapper");
}
window.setPageId = function(d) {
	d.pageId = activeTabId.split("_")[0];
}
$(function() {
    $body = $("body");
    var zoneId   = "";
    var melisKey = "";


   //For Opening to the other tab
   $body.on('click' , "#page_hit_view" , function(){
   		var tabName = $(this).closest('tr').find("td:nth-child(3)").text() + " / Page Analytics";
		var pageId = activeTabId.split("_")[0];
		toolPageDetails.tabOpen(tabName, pageId);
		_pageId = pageId;

   });

   //this will do the initialization of the page_details
	var toolPageDetails = {
			
        tabOpen: function(name, id){
            melisHelper.tabOpen(name, 'fa fa-list-ul', id+'_id_meliscms_page_analytics_page_details', 'meliscms_page_analytics_page_details', { pageHitId : id , tabName : name});

		}, 	
	}

});