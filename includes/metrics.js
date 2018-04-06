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

    chartDiv.append(`<div id="table_div"></div><br><br><div id="dashboard_div">
      <div id="filter_div"></div>
      <div id="chart_div"></div>
    </div>`)

    google.charts.load('current', {'packages':['corechart', 'controls', 'table']});

    google.charts.setOnLoadCallback(drawTable);
    google.charts.setOnLoadCallback(drawDashboard);

    // Callback that creates and populates a data table,
    // instantiates a dashboard, a range slider and a pie chart,
    // passes in the data and draws it.
    function drawDashboard() {

        // Create our data table.
        var data = google.visualization.arrayToDataTable([
            ['Name', 'Donuts eaten'],
            ['Michael' , 5],
            ['Elisa', 7],
            ['Robert', 3],
            ['John', 2],
            ['Jessica', 6],
            ['Aaron', 1],
            ['Margareth', 8]
        ]);

        // Create a dashboard.
        var dashboard = new google.visualization.Dashboard(
            document.getElementById('dashboard_div'));

        // Create a range slider, passing some options
        var donutRangeSlider = new google.visualization.ControlWrapper({
            'controlType': 'NumberRangeFilter',
            'containerId': 'filter_div',
            'options': {
                'filterColumnLabel': 'Donuts eaten'
            }
        });

        // Create a pie chart, passing some options
        var pieChart = new google.visualization.ChartWrapper({
            'chartType': 'PieChart',
            'containerId': 'chart_div',
            'options': {
                'width': 600,
                'height': 600,
                'pieSliceText': 'value',
                'legend': 'right'
            }
        });

        // Establish dependencies, declaring that 'filter' drives 'pieChart',
        // so that the pie chart will only display entries that are let through
        // given the chosen slider range.
        dashboard.bind(donutRangeSlider, pieChart);

        // Draw the dashboard.
        dashboard.draw(data);
    }

    function drawTable() {
        let data = new google.visualization.DataTable();
        data.addColumn('string', 'Label');
        data.addColumn('number', 'Last 7 Days');
        data.addColumn('number', 'Last 30 Days');
        data.addColumn('number', 'Last 90 Days');
        data.addColumn('number', 'All Time');
        data.addRows([
            ['New Trainees', 100, 400, 1040, 3000],
            ['New Groups', 100, 400, 1040, 3000],
            ['Trainees Completed Sessions', 100, 400, 1040, 3000],
            ['Sessions Completed', 100, 400, 1040, 3000],
            ['Zúme Course Completions', 100, 400, 1040, 3000],
        ]);

        let table = new google.visualization.Table(document.getElementById('table_div'));

        table.draw(data, {showRowNumber: true, width: '100%', height: '100%'});
    }


}

function show_zume_pipeline(){
    "use strict";
    jQuery.ajax({
        type: "GET",
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        url: wpApiZumeMetrics.root + 'dt/v1/zume/chart_zume_pipeline',
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', wpApiZumeMetrics.nonce);
        },
    })
        .done(function (data) {

            let screenHeight = jQuery(window).height()
            let chartHeight = screenHeight / 1.8
            let chartDiv = jQuery('#chart')


            chartDiv.empty().html('<span class="section-header">'+ wpApiZumeMetrics.translations.zume_pipeline +'</span><hr>');

            chartDiv.append(`
                    <div id="table_div"></div><br><br><div id="zume-pipeline-site" style="height: ` + chartHeight + `px; margin: 0 1em; "></div>
                `)

            google.charts.load('current', {packages: ['corechart', 'bar', 'table', 'sankey']});
            google.charts.setOnLoadCallback(drawBarchart)
            google.charts.setOnLoadCallback(drawTable)

            function drawBarchart() {

                let chartData = google.visualization.arrayToDataTable( data.chart_global );

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

        })
        .fail(function (err) {
            let screenHeight = jQuery(window).height()
            let chartHeight = screenHeight / 1.6
            let chartDiv = jQuery('#chart')

            let height = jQuery(window).height() - jQuery('header').height() - 150
            chartDiv.empty().html('<span class="section-header">'+ wpApiZumeMetrics.translations.zume_pipeline +'</span><span id="errors"></span><hr' +
                ' />');
            console.log("error")
            console.log(err)
            jQuery("#errors").append(err.responseText)
        })
}

function show_zume_groups(){
    "use strict";
    let screenHeight = jQuery(window).height()
    let chartHeight = screenHeight / 1.8
    let chartDiv = jQuery('#chart')


    chartDiv.empty().html('<span class="section-header">'+ wpApiZumeMetrics.translations.zume_groups +'</span><hr>');

    chartDiv.append(`<p class="section-subheader text-center" >Number of groups according to their member count</span>
            <br><div id="zume-groups" style="height: ` + chartHeight + `px; margin: 0 1em; "></div>`)

    google.charts.load('current', {packages: ['corechart', 'bar']});
    google.charts.setOnLoadCallback(drawBarchart)

    function drawBarchart() {
        console.log(wpApiZumeMetrics.zume_stats.global.members_per_group)

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

        wpApiZumeMetrics.zume_stats.global.group_coordinates.unshift(['number', 'number']) // adds column information to the beginning of the array.

        let data = google.visualization.arrayToDataTable(wpApiZumeMetrics.zume_stats.global.group_coordinates)

        let options = {
            tooltip: {trigger: 'none'}
        };

        let chart = new google.visualization.GeoChart(document.getElementById('zume-locations'));

        chart.draw(data, options);
    }
    console.log(wpApiZumeMetrics.zume_stats)

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
}