jQuery(document).ready(function() {
    console.log(wpApiZumeMetrics.zume_stats)

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
    if('#zume_groups' === window.location.hash) {
        show_zume_groups()
    }
})

function show_zume_project(){
    "use strict";
    let screenHeight = jQuery(window).height()
    let chartHeight = screenHeight / 1.3
    let chartDiv = jQuery('#chart')
    chartDiv.empty().html('<span class="section-header">'+ wpApiZumeMetrics.translations.zume_project +'</span><hr />')

    chartDiv.append(`<div id="table_div"></div><br><br><p class="section-subheader text-center" >Overview</p>
        <span id='colchart_diff' style='width: 450px; height: 250px; display: inline-block'></span>
        <span id='barchart_diff' style='width: 450px; height: 250px; display: inline-block'></span>`)

    google.charts.load('current', {'packages':['corechart', 'controls', 'table']});

    google.charts.setOnLoadCallback(drawTable);
    google.charts.setOnLoadCallback(drawChart);

    function drawTable() {
        let data = google.visualization.arrayToDataTable(wpApiZumeMetrics.zume_stats.global.project_overview);
        let table = new google.visualization.Table(document.getElementById('table_div'));
        table.draw(data, {showRowNumber: true, width: '100%', height: '100%'});
    }

    function drawChart() {

        let projectOverview = wpApiZumeMetrics.zume_stats.global.project_overview
        var oldDataSplice = []
        projectOverview.forEach(function(currentValue, index, arr){
            let temp = currentValue.slice(0)
            temp.splice(1,1)
            temp.splice(2,5)
            oldDataSplice[index] = temp

        })

        var newDataSplice = []
        projectOverview.forEach(function(currentValue, index, arr){
            let temp = currentValue.slice(0)
            temp.splice(2, 6)
            newDataSplice[index] = temp
        })

        let oldData = google.visualization.arrayToDataTable(oldDataSplice);
        let newData = google.visualization.arrayToDataTable(newDataSplice);

        let colChartDiff = new google.visualization.ColumnChart(document.getElementById('colchart_diff'));
        let barChartDiff = new google.visualization.BarChart(document.getElementById('barchart_diff'));

        let options = { legend: { position: 'top' } };

        let diffData = colChartDiff.computeDiff(oldData, newData);
        colChartDiff.draw(diffData, options);
        barChartDiff.draw(diffData, options);
    }

    chartDiv.append(`<div><span class="small grey">( stats as of `+ wpApiZumeMetrics.zume_stats.timestamp +` )</span> 
            <a onclick="refresh_stats_data( 'show_zume_project' ); jQuery('.spinner').show();">Refresh</a>
            <span class="spinner" style="display: none;"><img src="`+wpApiZumeMetrics.plugin_uri+`includes/ajax-loader.gif" /></span> 
            </div>`)
}

function show_zume_pipeline(){
    "use strict";

    let screenHeight = jQuery(window).height()
    let chartHeight = screenHeight / 1.8
    let chartDiv = jQuery('#chart')

    chartDiv.empty().html('<span class="section-header">'+ wpApiZumeMetrics.translations.zume_pipeline +'</span><hr>');

    chartDiv.append(`<div id="table_div"></div><br><br><div id="zume-pipeline-site" style="height: ` + chartHeight + `px; margin: 0 1em; "></div>`)

    google.charts.load('current', {packages: ['corechart', 'bar', 'table']});
    google.charts.setOnLoadCallback(drawBarchart)
    google.charts.setOnLoadCallback(drawTable)

    function drawBarchart() {

        let chartData = google.visualization.arrayToDataTable( [
            ['Session', 'Groups', {'role': 'annotation'}],
            ['Session 1', 3000, 3000],
            ['Session 2', 2000, 2000],
            ['Session 3', 1900, 1900],
            ['Session 4', 1400, 1400],
            ['Session 5', 1200, 1200],
            ['Session 5', 900, 900],
            ['Session 6', 670, 670],
            ['Session 7', 550, 550],
            ['Session 8', 460, 460],
            ['Session 9', 100, 100],
            ['Session 10', 40, 40],
        ]);

        let options = {
            bars: 'horizontal',
            chartArea: {
                left: '15%',
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

        let chart = new google.visualization.BarChart(document.getElementById('zume-pipeline-site'));
        chart.draw(chartData, options);

    }

    function drawTable() {
        let data = new google.visualization.DataTable();
        data.addColumn('string', 'Label');
        data.addColumn('number', 'Last 7 Days');
        data.addColumn('number', 'Last 30 Days');
        data.addColumn('number', 'Last 90 Days');
        data.addColumn('number', 'All Time');
        data.addRows([
            ['Session 1', 100, 400, 1040, 3000],
            ['Session 2', 100, 400, 1040, 3000],
            ['Session 3', 100, 400, 1040, 3000],
            ['Session 4', 100, 400, 1040, 3000],
            ['Session 5', 100, 400, 1040, 3000],
            ['Session 5', 100, 400, 1040, 3000],
            ['Session 6', 100, 400, 1040, 3000],
            ['Session 7', 100, 400, 1040, 3000],
            ['Session 8', 100, 400, 1040, 3000],
            ['Session 9', 100, 400, 1040, 3000],
            ['Session 10', 100, 400, 1040, 3000],
        ]);

        let table = new google.visualization.Table(document.getElementById('table_div'));

        table.draw(data, {showRowNumber: true, width: '100%', height: '100%'});
    }

    chartDiv.append(`<div><span class="small grey">( stats as of `+ wpApiZumeMetrics.zume_stats.timestamp +` )</span> 
            <a onclick="refresh_stats_data( 'show_zume_pipeline' ); jQuery('.spinner').show();">Refresh</a>
            <span class="spinner" style="display: none;"><img src="`+wpApiZumeMetrics.plugin_uri+`includes/ajax-loader.gif" /></span> 
            </div>`)

}

function show_zume_groups(){
    "use strict";
    let screenHeight = jQuery(window).height()
    let chartHeight = screenHeight / 1.8
    let chartDiv = jQuery('#chart')


    chartDiv.empty().html('<span class="section-header">'+ wpApiZumeMetrics.translations.zume_groups +'</span><hr>');

    chartDiv.append(`<p class="section-subheader text-center" >Number of groups according to their member count</p>
            <div id="zume-groups" style="height: ` + chartHeight + `px; margin: 0 1em; "></div>`)

    google.charts.load('current', {packages: ['corechart', 'bar']});
    google.charts.setOnLoadCallback(drawBarchart)

    function drawBarchart() {

        let data = google.visualization.arrayToDataTable( wpApiZumeMetrics.zume_stats.global.members_per_group );
        let view = new google.visualization.DataView(data)

        let options = {
            bars: 'horizontal',
            chartArea: {
                left: '15%',
                top: '0%',
                width: "80%",
                height: "90%" },
            hAxis: {
                title: 'groups'
            },
            title: "Number of groups according to their member count",
            legend: {position: "none"},
        };

        let chart = new google.visualization.BarChart(document.getElementById('zume-groups'));
        chart.draw(view, options);
    }

    chartDiv.append(`<div><span class="small grey">( stats as of `+ wpApiZumeMetrics.zume_stats.timestamp +` )</span> 
            <a onclick="refresh_stats_data( 'show_zume_groups' ); jQuery('.spinner').show();">Refresh</a>
            <span class="spinner" style="display: none;"><img src="`+wpApiZumeMetrics.plugin_uri+`includes/ajax-loader.gif" /></span> 
            </div>`)
}

function show_zume_locations(){
    "use strict";
    let screenHeight = jQuery(window).height()
    let chartHeight = screenHeight / 1.8
    let chartDiv = jQuery('#chart')

    chartDiv.empty().html('<span class="section-header">'+ wpApiZumeMetrics.translations.zume_locations +'</span><hr />' +
        '<div class="grid-x grid-padding-x">' +
        '<div class="cell small-4"><span class="section-subheader">Top Countries</span><br><div id="drawTopCountries"></div></div>' +
        '<div class="cell small-4"><span class="section-subheader">Newest Countries</span><br><div id="drawNewestCountries"></div></div>' +
        '<div class="cell small-4"><span class="section-subheader">Newest Groups by Country</span><br><div id="drawNewestGroupsByCountry"></div></div>' +
        '</div>' +
        '<br><br><div id="zume-locations" style="height: ' + chartHeight + 'px; margin: 0 1em; "></div>')

    google.charts.load('current', {'packages':['geochart', 'table'], 'mapsApiKey': wpApiZumeMetrics.map_key });

    google.charts.setOnLoadCallback(drawTopCountries);
    google.charts.setOnLoadCallback(drawNewestCountries);
    google.charts.setOnLoadCallback(drawNewestGroupsByCountry);
    google.charts.setOnLoadCallback(drawVisualization);


    function drawTopCountries() {
        let data = new google.visualization.DataTable();
        data.addColumn('string', 'Country');
        data.addColumn('number', 'Groups');
        data.addColumn('number', 'Users');
        data.addRows([
            ['United States', 100, 400],
            ['England', 100, 400],
            ['Russia', 100, 400],
            ['Turkey', 100, 400],
            ['Venezuala', 100, 400],
        ]);

        let table = new google.visualization.Table(document.getElementById('drawTopCountries'));

        table.draw(data, {showRowNumber: true, width: '100%', height: '100%'});
    }

    function drawNewestCountries() {
        let data = new google.visualization.DataTable();
        data.addColumn('string', 'Country');
        data.addColumn('date', 'Date');
        data.addRows([
            ['Venezuala', new Date(2018, 2, 15)],
            ['Turkey', new Date(2018, 1, 15)],
            ['Russia', new Date(2017, 12, 15)],
            ['England', new Date(2017, 10, 15)],
            ['United States', new Date(2017, 2, 15)],
        ]);

        let table = new google.visualization.Table(document.getElementById('drawNewestCountries'));

        table.draw(data, {showRowNumber: true, width: '100%', height: '100%'});
    }

    function drawNewestGroupsByCountry() {
        let data = new google.visualization.DataTable();
        data.addColumn('string', 'Country');
        data.addColumn('date', 'Date');
        data.addRows([
            ['Venezuala', new Date(2018, 2, 15)],
            ['Turkey', new Date(2018, 1, 15)],
            ['Russia', new Date(2017, 12, 15)],
            ['England', new Date(2017, 10, 15)],
            ['United States', new Date(2017, 2, 15)],
        ]);

        let table = new google.visualization.Table(document.getElementById('drawNewestGroupsByCountry'));

        table.draw(data, {showRowNumber: true, width: '100%', height: '100%'});
    }

    function drawVisualization() {

        let data = google.visualization.arrayToDataTable(wpApiZumeMetrics.zume_stats.global.group_coordinates)

        let options = {
            tooltip: {trigger: 'none'}
        };

        let chart = new google.visualization.GeoChart(document.getElementById('zume-locations'));

        chart.draw(data, options);
    }

    chartDiv.append(`<div><span class="small grey">( stats as of `+ wpApiZumeMetrics.zume_stats.timestamp +` )</span> 
            <a onclick="refresh_stats_data( 'show_zume_locations' ); jQuery('.spinner').show();">Refresh</a>
            <span class="spinner" style="display: none;"><img src="`+wpApiZumeMetrics.plugin_uri+`includes/ajax-loader.gif" /></span> 
            </div>`)
}

function show_zume_languages(){
    "use strict";
    let screenHeight = jQuery(window).height()
    let chartHeight = screenHeight / 2
    let chartDiv = jQuery('#chart')
    var dataArray = [
        ['English', 100, 400, 1040, 3000],
        ['Farsi', 100, 400, 1040, 3000],
        ['Arabic', 100, 400, 1040, 3000],
        ['French', 100, 400, 1040, 3000],
    ]

    chartDiv.empty().html('<span class="section-header">'+ wpApiZumeMetrics.translations.zume_languages +'</span><hr />' +
        '<div id="table_div"></div><br><br><div id="zume-languages" style="height: ' + chartHeight + 'px; margin: 0 1em; "></div>')


    google.charts.load('current', {packages: ['corechart', 'bar', 'table']});
    google.charts.setOnLoadCallback(drawBarChart)
    google.charts.setOnLoadCallback(drawTable)

    function drawBarChart() {

        let chartData = google.visualization.arrayToDataTable(
            [
                ['Languages', 'Users', { "role": "annotation"}],
                ['English', 3000, 3000],
                ['Farsi', 400, 400 ],
                ['Arabic', 300, 300],
                ['French', 1200, 1200],
            ]
        );

        let options = {
            bars: 'horizontal',
            chartArea: {
                left: '10%',
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

        let chart = new google.visualization.BarChart(document.getElementById('zume-languages'));
        chart.draw(chartData, options);

    }

    function drawTable() {
        let data = new google.visualization.DataTable();
        data.addColumn('string', 'Label');
        data.addColumn('number', 'Last 7 Days');
        data.addColumn('number', 'Last 30 Days');
        data.addColumn('number', 'Last 90 Days');
        data.addColumn('number', 'All Time');
        data.addRows(dataArray);

        let table = new google.visualization.Table(document.getElementById('table_div'));

        table.draw(data, {showRowNumber: true, width: '100%', height: '100%'});
    }

    chartDiv.append(`<div><span class="small grey">( stats as of `+ wpApiZumeMetrics.zume_stats.timestamp +` )</span> 
            <a onclick="refresh_stats_data( 'show_zume_languages' ); jQuery('.spinner').show();">Refresh</a>
            <span class="spinner" style="display: none;"><img src="`+wpApiZumeMetrics.plugin_uri+`includes/ajax-loader.gif" /></span> 
            </div>`)
}

function refresh_stats_data( page ){
    jQuery.ajax({
        type: "GET",
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        url: wpApiMetricsPage.root + 'dt/v1/zume/reset_zume_stats',
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', wpApiMetricsPage.nonce);
        },
    })
        .done(function (data) {
            wpApiZumeMetrics.zume_stats = data
            switch( page ) {
                case 'show_zume_languages':
                    show_zume_languages()
                    break;
                case 'show_zume_locations':
                    show_zume_locations()
                    break;
                case 'show_zume_groups':
                    show_zume_groups()
                    break;
                case 'show_zume_pipeline':
                    show_zume_pipeline()
                    break;
                case 'show_zume_project':
                    show_zume_project()
                    break;

                default:
                    break;
            }
        })
        .fail(function (err) {
            console.log("error")
            console.log(err)
            jQuery("#errors").append(err.responseText)
        })

}