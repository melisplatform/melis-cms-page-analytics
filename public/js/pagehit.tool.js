var _pageId = null;
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

	$("body").on("submit", "form#select_page_analytic_form", function(e) {

        var formData = new FormData(this);
        melisCoreTool.pending("button");
        $.ajax({
            type    : 'POST',
            url     : '/melis/MelisCmsPageAnalytics/MelisCmsPageAnalyticsTool/save',
            data    : formData,
            processData : false,
            cache       : false,
            contentType : false,
            dataType    : 'json',
        }).success(function(data){
            if(data.success) {
                melisHelper.melisOkNotification(data.title, data.message);
                melisHelper.zoneReload("id_meliscms_page_analytics_display", "meliscms_page_analytics_display");
            }
            else {
                melisHelper.melisKoNotification(data.title, data.message, data.errors);
            }
            melisCore.flashMessenger();
            melisCoreTool.done("button");
        }).error(function(){
            melisCoreTool.done("button");
        });
		e.preventDefault();
	});

    $("body").on("change", "select#page_analytics_id", function() {
        var value = $(this).val().toString();

        $.ajax({
            type    : 'POST',
            url     : '/melis/MelisCmsPageAnalytics/MelisCmsPageAnalyticsTool/getSettingsForm',
            data    : {page_analytics_id : value},
            dataType    : 'html',
            encode		: true
        }).success(function(data){
            if(data) {
                $("div#analytics-settings-form").html(data);
            }
            else {
                $("div#analytics-settings-form").html("");
            }
        });

    });

    $("body").on("click", "a#cms-page-anlytics-tab-settings-button",  function() {
        $("select#page_analytics_id").trigger("change");
    });


});