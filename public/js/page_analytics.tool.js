$(function() {
    $("body").on("change", "select#page_analytics_id", function() {
        melisCoreTool.pending("button");
        var analyticsKey  = $(this).val().toString();
        var siteId        = parseInt($("form#select_page_analytic_form select#site_id").val());

        if(!isNaN(siteId) && analyticsKey === 'melis_cms_google_analytics') {
            $.ajax({
                type    : 'POST',
                url     : '/melis/MelisCmsPageAnalytics/MelisCmsPageAnalyticsTool/getSettingsForm',
                data    : {analytics_key : analyticsKey, site_id : siteId},
                dataType    : 'html',
                encode		: true
            }).success(function(data){
                if(data) {
                    $("div#analytics-settings-form").html(data);

                    $.post('/melis/MelisCmsPageAnalytics/MelisCmsPageAnalyticsTool/getAnalyticsScript', {site_id: siteId, analytics_key : analyticsKey}, function(data) {
                        if(data.response.pads_js_analytics) {
                            var editor = ace.edit("pads_js_analytics");
                            editor.setValue(data.response.pads_js_analytics);
                            $("span#pads_js_analytics_cont").removeClass("hidden");
                        }
                    });
                }
                else {
                    $("div#analytics-settings-form").html("");
                }
            });
            melisCoreTool.done("button");
        }
        else{
            $("div#analytics-settings-form").html("");
            $("span#pads_js_analytics_cont").addClass("hidden");
            melisCoreTool.done("button");
        }
    });

    // $("body").on("click", "a#cms-page-anlytics-tab-settings-button",  function() {
    //     $("select#page_analytics_id").trigger("change");
    // });

    $("body").on("click", "a.melis-cms-page-analytics-refresh", function() {
        var pageId = activeTabId.split("_")[0];
        melisHelper.zoneReload(pageId+"_id_meliscms_page_analytics", "meliscms_page_analytics_tab_display", {idPage : pageId}, function() {
            setTimeout(function() {
                $("#"+pageId+"_id_meliscms_page_analytics").addClass("active");
            }, 600);

        });

    });

    $("body").on("submit", "form#select_page_analytic_form", function(e) {

        var formData = new FormData(this);
        // formData.push();
        var editor = ace.edit("pads_js_analytics");
        var script = editor.getValue();

        formData.append("pads_js_analytics", script);

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

    $("body").on("change", "form#select_page_analytic_form select#site_id", function() {
        var siteId = parseInt($(this).val());

        if(!isNaN(siteId)) {
            $.post('/melis/MelisCmsPageAnalytics/MelisCmsPageAnalyticsTool/getSiteAnalytics', {site_id: siteId}, function(data) {
                if (data.response.page_analytics_id === 'melis_cms_google_analytics'){
                    var editor = ace.edit("pads_js_analytics");
                    if(data.response.pads_js_analytics) {
                        editor.setValue(data.response.pads_js_analytics);
                    }
                    else {
                        editor.setValue("");
                    }
                    $("span#pads_js_analytics_cont").removeClass("hidden");
                }
                else{
                    $("span#pads_js_analytics_cont").addClass("hidden");
                }

                var selAnalytics = $("form#select_page_analytic_form select#page_analytics_id");
                selAnalytics.val(data.response.page_analytics_id).change();
            });
        }
    });

    $("body").on("change", "form#select_page_analytic_form_analytics_content select#analytics-content-side-id", function() {
        var siteId = parseInt($(this).val());
        if(!isNaN(siteId)) {
            melisHelper.zoneReload("id_meliscms_page_analytics_site_analytics_tab_content", "meliscms_page_analytics_site_analytics_tab_content", {siteId : siteId}, function() {
                $("div#id_meliscms_page_analytics_site_analytics_tab_content").addClass("active");
            });
        }
    });

});