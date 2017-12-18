$(function() {
    // Settings Tab: Handles the change in Site Analytics selection
    $("body").on("change", "select#page_analytics_id", function() {
        melisCoreTool.pending("button");
        var analyticsKey  = $(this).val();
        var siteId        = parseInt($("form#select_page_analytic_form select#site_id").val());

        if (analyticsKey == null) {
            // The analytics module for this site is deactivated.
            $body.find('#page_analytics_id').append($('<option>', {
                value   : '',
                disabled: true,
                selected: true,
                hidden  : true,
                text    : translations.tr_meliscms_page_analytics_inactive_module
            }));
        }
        else{
            if(!isNaN(siteId) && analyticsKey === 'melis_cms_google_analytics') {
                $.ajax({
                    type    : 'POST',
                    url     : '/melis/MelisCmsPageAnalytics/MelisCmsPageAnalyticsTool/getSettingsForm',
                    data    : {analytics_key : analyticsKey, site_id : siteId},
                    dataType    : 'html',
                    encode      : true
                }).success(function(data){
                    if(data) {
                        $("div#analytics-settings-form").html(data);

                        $.post('/melis/MelisCmsPageAnalytics/MelisCmsPageAnalyticsTool/getAnalyticsScript', {site_id: siteId, analytics_key : analyticsKey}, function(data) {
                            if(data.response.pads_js_analytics) {
                                var editor = ace.edit("pads_js_analytics");
                                editor.setValue(data.response.pads_js_analytics);
                            }
                        });
                    }
                    else {
                        $("div#analytics-settings-form").html("");
                    }
                });

                // Show Script Editor & Google Analytics Configuration Guidelines
                $("span#pads_js_analytics_cont").removeClass("hidden");
                $('div#melis-cms-google-analytics-guidelines').removeClass('hidden');
            }
            else{
                $("div#analytics-settings-form").html("");
                $("span#pads_js_analytics_cont").addClass("hidden");
                $('div#melis-cms-google-analytics-guidelines').addClass('hidden');
            }
        }
        melisCoreTool.done("button");
    });

    // Table's Refresh button in MelisCms Page module system
    $("body").on("click", "a.melis-cms-page-analytics-refresh", function() {
        var pageId = activeTabId.split("_")[0];
        melisHelper.zoneReload(pageId+"_id_meliscms_page_analytics", "meliscms_page_analytics_tab_display", {idPage : pageId}, function() {
            setTimeout(function() {
                $("#"+pageId+"_id_meliscms_page_analytics").addClass("active");
            }, 600);

        });

    });

    // Save on Settings Tab
    $("body").on("submit", "form#select_page_analytic_form", function(e) {

        var formData    = new FormData(this);
        var editor      = ace.edit("pads_js_analytics");
        var script      = editor.getValue();

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

    // Handles Site Selection Change in Settings Tab
    $("body").on("change", "form#select_page_analytic_form select#site_id", function() {
        var siteId = parseInt($(this).val());

        if(!isNaN(siteId)) {
            $.post('/melis/MelisCmsPageAnalytics/MelisCmsPageAnalyticsTool/getSiteAnalytics', {site_id: siteId}, function(data) {
                var selAnalytics = $("form#select_page_analytic_form select#page_analytics_id");
                selAnalytics.val(data.response.page_analytics_id).change();
            });
        }
    });

    // Handles Site Selection Change in Analytics Tab
    $("body").on("change", "form#select_page_analytic_form_analytics_content select#analytics-content-side-id", function() {
        var siteId = parseInt($(this).val());
        if(!isNaN(siteId)) {
            melisHelper.zoneReload("id_meliscms_page_analytics_site_analytics_tab_content", "meliscms_page_analytics_site_analytics_tab_content", {siteId : siteId}, function() {
                $("div#id_meliscms_page_analytics_site_analytics_tab_content").addClass("active");
            });
        }
    });

    // $('div#tableMelisCmsPageAnalytics_wrapper a.melis-refreshTable').on('click', function(){
    //     melisHelper.zoneReload('', '');
    // });

});