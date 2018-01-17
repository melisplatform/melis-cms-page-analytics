$(function() {
    // Handles Site Selection Change in Analytics Tab
    $("body").on("change", "form#select_page_analytic_form_analytics_content select#analytics-content-side-id", function() {
        /**
         * Applying an overlay to prevent user from switching to different tab before
         * Content from the AJAX call is loaded
         */
        $('<div class="melis-cms-page-analytics-temp-overlay"></div>').insertAfter("#id_meliscms_page_analytics_content");

        var siteId = parseInt($(this).val());
        if(!isNaN(siteId)) {
            melisHelper.zoneReload("id_meliscms_page_analytics_site_analytics_tab_content", "meliscms_page_analytics_site_analytics_tab_content", {siteId : siteId}, function() {
                $body.find("div#id_meliscms_page_analytics_site_analytics_tab_content").addClass("active");
                $body.find('.melis-cms-page-analytics-temp-overlay').remove();  // Removes overlay after content is loaded
            });
        }
    });

    // Settings Tab: Handles Site Selection Change
    $("body").on("change", "form#select_page_analytic_form select#site_id", function() {
        var siteId = parseInt($(this).val());

        if(!isNaN(siteId)) {
            // Retrieves Current Page Analytics Module for the selected site via siteId
            $.post('/melis/MelisCmsPageAnalytics/MelisCmsPageAnalyticsTool/getSiteAnalytics', {site_id: siteId}, function(data) {
                if (!data.response.analyticsModuleIsSet){
                    $body.find("form#select_page_analytic_form select#page_analytics_id").val("").change();
                }
                else if(data.response.activeAnalytics){
                    // when the set Page Analytics module for the selected site is active
                    var selAnalytics = $body.find("form#select_page_analytic_form select#page_analytics_id");
                    selAnalytics.val(data.response.page_analytics_id).change();
                }
                else {
                    // The analytics module for this site is deactivated.
                    $body.find('#page_analytics_id').append($('<option>', {
                        value   : '',
                        disabled: true,
                        selected: true,
                        hidden  : true,
                        text    : translations.tr_meliscms_page_analytics_inactive_module
                    }));
                }
            });
        }
    });

    // Settings Tab: Handles Analytics Module change
    $("body").on("change", "select#page_analytics_id", function() {
        melisCoreTool.pending("button");
        var analyticsKey  = $body.find(this).val();
        var siteId        = parseInt($body.find("form#select_page_analytic_form select#site_id").val());

        if(!isNaN(siteId) && analyticsKey === 'melis_cms_google_analytics') {
            $.ajax({
                type    : 'POST',
                url     : '/melis/MelisCmsPageAnalytics/MelisCmsPageAnalyticsTool/getSettingsForm',
                data    : {analytics_key : analyticsKey, site_id : siteId},
                dataType    : 'html',
                encode      : true
            }).success(function(data){
                if(data) {
                    $body.find("div#analytics-settings-form").html(data);

                    $.post('/melis/MelisCmsPageAnalytics/MelisCmsPageAnalyticsTool/getAnalyticsScript', {site_id: siteId, analytics_key : analyticsKey}, function(data) {
                        if(data.response.pads_js_analytics) {
                            var editor = ace.edit("pads_js_analytics");
                            editor.setValue(data.response.pads_js_analytics);
                        }
                    });
                }
                else {
                    $body.find("div#analytics-settings-form").html("");
                }
            });

            // Show Script Editor & Google Analytics Configuration Guidelines
            $body.find("span#pads_js_analytics_cont").removeClass("hidden");
            $body.find('div#melis-cms-google-analytics-guidelines').removeClass('hidden');
        }
        else{
            $body.find("div#analytics-settings-form").html("");
            $body.find("span#pads_js_analytics_cont").addClass("hidden");
            $body.find('div#melis-cms-google-analytics-guidelines').addClass('hidden');
        }

        melisCoreTool.done("button");
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

    // Refresh functionality in the Analytics Tab
    $('body').on('click', 'a.melis-cms-page-analytics-refresh-table-tool', function(){
        melisHelper.zoneReload('id_melis_cms_page_analytics_tool_table', 'melis_cms_page_analytics_tool_table');
    });

    // Table's Refresh button in MelisCms Page module system
    $("body").on("click", "a.melis-cms-page-analytics-refresh", function() {
        var pageId = activeTabId.split("_")[0];
        melisHelper.zoneReload(pageId+'_id_melis_cms_page_analytics_page_table', 'melis_cms_page_analytics_page_table', {idPage: pageId});
    });

});