$(function () {
    // Handles Site Selection Change in Analytics Tab
    $("body").on("change", "form#select_page_analytic_form_analytics_content select#analytics-content-side-id", function () {
        /**
         * Applying an overlay to prevent user from switching to different tab before
         * Content from the AJAX call is loaded
         */
        $('<div class="melis-cms-page-analytics-temp-overlay"></div>').insertAfter("#id_meliscms_page_analytics_content");

        var siteId = parseInt($(this).val());
        if (!isNaN(siteId)) {
            melisHelper.zoneReload("id_meliscms_page_analytics_site_analytics_tab_content", "meliscms_page_analytics_site_analytics_tab_content", {siteId: siteId}, function () {
                $body.find("div#id_meliscms_page_analytics_site_analytics_tab_content").addClass("active");
                $body.find('.melis-cms-page-analytics-temp-overlay').remove();  // Removes overlay after content is loaded
            });
        }
    });

    // Settings Tab: Handles Site Selection Change
    $("body").on("change", "form#select_page_analytic_form select#site_id", function () {
        var siteId = parseInt($(this).val());

        if (!isNaN(siteId)) {
            var page_analytics_id = $("#select_page_analytic_form").children("div.form-group").eq(1);
            melisHelper.loadingZone(page_analytics_id);

            // Retrieves Current Page Analytics Module for the selected site via siteId
            $.post('/melis/MelisCmsPageAnalytics/MelisCmsPageAnalyticsTool/getSiteAnalytics', {site_id: siteId}, function (data) {
                if (!data.response.analyticsModuleIsSet) {
                    $body.find("form#select_page_analytic_form select#page_analytics_id").val("").change();
                }
                else if (data.response.activeAnalytics) {
                    // when the set Page Analytics module for the selected site is active
                    var selAnalytics = $body.find("form#select_page_analytic_form select#page_analytics_id");
                    selAnalytics.val(data.response.page_analytics_id).change();
                }
                else {
                    // The analytics module for this site is deactivated.
                    $body.find('#page_analytics_id').append($('<option>', {
                        value: '',
                        disabled: true,
                        selected: true,
                        hidden: true,
                        text: translations.tr_meliscms_page_analytics_inactive_module
                    }));
                }
                melisHelper.removeLoadingZone(page_analytics_id);
            });
        }
    });

    // Settings Tab: Handles Analytics Module change
    $("body").on("change", "select#page_analytics_id", function () {
        melisCoreTool.pending("button");
        var analyticsKey = $body.find(this).val();
        var siteId = parseInt($body.find("form#select_page_analytic_form select#site_id").val());

        if (!isNaN(siteId) && analyticsKey === 'melis_cms_google_analytics') {
            $body.find('div#melis-cms-google-analytics-guidelines').removeClass('hidden');
            $body.find('div#melis-cms-google-analytics-guidelines').css('opacity', '0');
            melisHelper.loadingZone($body.find('#analytics-settings-form'));

            $.ajax({
                type: 'POST',
                url: '/melis/MelisCmsPageAnalytics/MelisCmsPageAnalyticsTool/getSettingsForm',
                data: {analytics_key: analyticsKey, site_id: siteId, fileChanged: privateKeyFileChange},
                dataType: 'html',
                encode: true
            }).success(function (data) {
                if (data) {
                    $body.find("div#analytics-settings-form").html(data);
                    // Get private key file name value
                    var privateKeyFileName = $body.find('#id_google_analytics_private_key_val');
                    if (privateKeyFileName.length) {
                        privateKeyFileName = privateKeyFileName.val();
                    } else {
                        privateKeyFileName = translations.tr_melis_cms_google_analytics_private_key_non;
                    }

                    var disbaleFlag = $body.find("#id_google_analytics_private_key");
                    if (disbaleFlag.length && disbaleFlag.attr("disabled") === "disabled") {
                        disbaleFlag = true;
                        privateKeyFileName = translations.tr_melis_cms_google_analytics_private_key_no_rights
                    } else {
                        disbaleFlag = false;
                    }
                    $body.find("#id_google_analytics_private_key").filestyle({
                        disabled: disbaleFlag,
                        buttonBefore: true,
                        input: true,
                        buttonText: translations.tr_melis_cms_google_analytics_private_key_upload,
                        placeholder: privateKeyFileName
                    });
                    melisHelper.removeLoadingZone($body.find('#analytics-settings-form'));

                    // Show Script Editor & apply loading screen while waiting for the script to load
                    $body.find("span#pads_js_analytics_cont").removeClass("hidden");
                    melisHelper.loadingZone($body.find('pre#pads_js_analytics'));

                    $.post('/melis/MelisCmsPageAnalytics/MelisCmsPageAnalyticsTool/getAnalyticsScript', {
                        site_id: siteId,
                        analytics_key: analyticsKey
                    }, function (data) {
                        if (data.response.pads_js_analytics) {
                            var editor = ace.edit("pads_js_analytics");
                            editor.setValue(data.response.pads_js_analytics);
                        }
                        melisHelper.removeLoadingZone($body.find('pre#pads_js_analytics'));
                    });
                }
                else {
                    $body.find("div#analytics-settings-form").html("");
                }
            });

            // Show Google Analytics Configuration Guidelines
            $body.find('div#melis-cms-google-analytics-guidelines').animate({opacity: 1}, 400);
        }
        else {
            $body.find("div#analytics-settings-form").html("");
            $body.find("span#pads_js_analytics_cont").addClass("hidden");
            $body.find('div#melis-cms-google-analytics-guidelines').addClass('hidden');
        }

        melisCoreTool.done("button");
    });

    // Private key changes
    var privateKeyFileChange = false;
    $body.on("change", "#id_google_analytics_private_key", function () {
        privateKeyFileChange = true;
    });

    // Save on Settings Tab
    $("body").on("submit", "form#select_page_analytic_form", function (e) {
        var formData = new FormData(this);
        var editor = ace.edit("pads_js_analytics");
        var script = editor.getValue();

        formData.append("pads_js_analytics", script);
        formData.append("fileChanged", privateKeyFileChange);

        melisCoreTool.pending("button");
        $.ajax({
            type: 'POST',
            url: '/melis/MelisCmsPageAnalytics/MelisCmsPageAnalyticsTool/save',
            data: formData,
            processData: false,
            cache: false,
            contentType: false,
            dataType: 'json'
        }).success(function (data) {
            if (data.success) {
                melisHelper.melisOkNotification(data.title, data.message);
                melisHelper.zoneReload("id_meliscms_page_analytics_display", "meliscms_page_analytics_display");
            }
            else {
                melisHelper.melisKoNotification(data.title, data.message, data.errors);
            }
            melisCore.flashMessenger();
            melisCoreTool.done("button");
        }).error(function () {
            melisCoreTool.done("button");
        });
        e.preventDefault();
    });

    // Refresh functionality in the Analytics Tab
    $('body').on('click', 'a.melis-cms-page-analytics-refresh-table-tool', function () {
        melisHelper.zoneReload('id_melis_cms_page_analytics_tool_table', 'melis_cms_page_analytics_tool_table');
    });

    // Table's Refresh button in MelisCms Page module system
    $("body").on("click", "a.melis-cms-page-analytics-refresh", function () {
        var pageId = activeTabId.split("_")[0];
        melisHelper.zoneReload(pageId + '_id_melis_cms_page_analytics_page_table', 'melis_cms_page_analytics_page_table', {idPage: pageId});
    });
});