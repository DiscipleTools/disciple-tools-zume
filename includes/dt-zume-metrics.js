jQuery(document).ready(function() {
    if('#zume_project' === window.location.hash) {
        show_zume_project()
    }
    if('#zume_pipeline' === window.location.hash) {
        show_zume_pipeline()
    }
    if('#zume_locations' === window.location.hash) {
        show_zume_locations()
    }
    if('#zume_languages' === window.location.hash) {
        show_zume_languages()
    }
})

function show_zume_project(){
    "use strict";
    let height = jQuery(window).height() - jQuery('header').height() - 150

    jQuery('#chart').empty().html('<span class="section-header">'+ wpApiZumeMetrics.translations.zume_project +'</span><hr />' +
        '')
}

function show_zume_pipeline(){
    "use strict";
    let height = jQuery(window).height() - jQuery('header').height() - 150

    jQuery('#chart').empty().html('<span class="section-header">'+ wpApiZumeMetrics.translations.zume_pipeline +'</span><hr />');

    zume_pipeline()

}

function show_zume_locations(){
    "use strict";
    let height = jQuery(window).height() - jQuery('header').height() - 150

    jQuery('#chart').empty().html('<span class="section-header">'+ wpApiZumeMetrics.translations.zume_locations +'</span><hr />' +
        '')
}

function show_zume_languages(){
    "use strict";
    let height = jQuery(window).height() - jQuery('header').height() - 150

    jQuery('#chart').empty().html('<span class="section-header">'+ wpApiZumeMetrics.translations.zume_languages +'</span><hr />' +
        '')
}

function zume_pipeline(){
    "use strict";
    let screen_height = jQuery(window).height()
    let chartDiv = jQuery('#chart')

    chartDiv.append(`<div id="zume-pipeline" style="height: ` + screen_height / 1.6 + `px; margin: 2.5em 1em; "></div>`)

    jQuery.ajax({
        type: "GET",
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        url: wpApiZumeMetrics.root + 'dt/v1/zume/zume_pipeline',
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', wpApiZumeMetrics.nonce);
        },
    })
        .done(function (data) {

            google.charts.load('current', {packages: ['corechart', 'bar']});
            google.charts.setOnLoadCallback(function() {

                let chartData = google.visualization.arrayToDataTable(data.chart);

                let options = {
                    bars: 'horizontal',
                    chartArea: {
                        left: '20%',
                        top: '0%',
                        width: "80%",
                        height: "90%" },
                    hAxis: {
                        scaleType: 'mirrorLog',
                        title: 'logarithmic scale'
                    },
                    legend: {
                        position: 'none'
                    },
                }

                let chart = new google.visualization.BarChart(document.getElementById('zume-pipeline'));
                chart.draw(chartData, options);

            });

            chartDiv.append(`<div><span class="small grey">( stats as of `+ data.timestamp +` )</span> <a onclick="refresh_critical_path_data()">Refresh</a></div>`)

        })
        .fail(function (err) {
            console.log("error")
            console.log(err)
            jQuery("#errors").append(err.responseText)
        })

}

function refresh_zume_pipeline(){
    jQuery.ajax({
        type: "GET",
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        url: wpApiMetricsPage.root + 'dt/v1/zume/refresh_zume_pipeline',
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', wpApiMetricsPage.nonce);
        },
    })
        .done(function (data) {
            show_critical_path()
        })
        .fail(function (err) {
            console.log("error")
            console.log(err)
            jQuery("#errors").append(err.responseText)
        })

}
