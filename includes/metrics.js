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
    if('#zume_individuals' === window.location.hash) {
        show_zume_individuals()
    }
})

function show_zume_project(){
    "use strict";
    let screenHeight = jQuery(window).height()
    let chartHeight = screenHeight / 1.3
    let chartDiv = jQuery('#chart')
    chartDiv.empty().html('<span class="section-header">'+ wpApiZumeMetrics.translations.zume_project +'</span>')

    chartDiv.append(`
        <div id="curve_chart" style="width: 100%; height: 400px"></div>
        <hr>
        <span id='colchart_diff_7' style='width: 47%; height: 350px; display: inline-block'></span>
        <span id='colchart_diff_30' style='width: 47%; height: 350px; display: inline-block'></span>
        <span id='colchart_diff_90' style='width: 47%; height: 350px; display: inline-block'></span>
        <span id='colchart_diff_All' style='width: 47%; height: 350px; display: inline-block'></span>
        <hr>
        <div id="table_div"></div>
        `)

    google.charts.load('current', {'packages':['corechart', 'controls', 'table']});

    google.charts.setOnLoadCallback(drawTable);
    google.charts.setOnLoadCallback(drawChart);
    google.charts.setOnLoadCallback(drawLineChart);

    function drawTable() {
        let data = google.visualization.arrayToDataTable(wpApiZumeMetrics.zume_stats.global.project_overview);
        let table = new google.visualization.Table(document.getElementById('table_div'));
        table.draw(data, {showRowNumber: true, width: '100%', height: '100%'});
    }

    function drawChart() {

        let projectOverview = wpApiZumeMetrics.zume_stats.global.project_overview

        // 7 Day
        var oldDataSplice7 = []
        projectOverview.forEach(function(currentValue, index, arr){
            let temp = currentValue.slice(0)
            temp.splice(1,1)
            temp.splice(2,5)
            oldDataSplice7[index] = temp

        })

        var newDataSplice7 = []
        projectOverview.forEach(function(currentValue, index, arr){
            let temp = currentValue.slice(0)
            temp.splice(2, 6)
            newDataSplice7[index] = temp
        })

        let oldData7 = google.visualization.arrayToDataTable(oldDataSplice7);
        let newData7 = google.visualization.arrayToDataTable(newDataSplice7);

        let colChartDiff7 = new google.visualization.ColumnChart(document.getElementById('colchart_diff_7'));
        colChartDiff7.draw(
            colChartDiff7.computeDiff(oldData7, newData7),
            { legend: { position: 'top' }, title: 'Last 7 Days' }
            );

     // 30 day
        var oldDataSplice30 = []
        projectOverview.forEach(function(currentValue, index, arr){
            let temp = currentValue.slice(0)
            temp.splice(1,3)
            temp.splice(2,3)
            oldDataSplice30[index] = temp

        })

        var newDataSplice30 = []
        projectOverview.forEach(function(currentValue, index, arr){
            let temp = currentValue.slice(0)
            temp.splice(1, 2)
            temp.splice(2,4)
            newDataSplice30[index] = temp
        })

        let oldData30 = google.visualization.arrayToDataTable(oldDataSplice30);
        let newData30 = google.visualization.arrayToDataTable(newDataSplice30);

        let colChartDiff30 = new google.visualization.ColumnChart(document.getElementById('colchart_diff_30'));
        colChartDiff30.draw(
            colChartDiff30.computeDiff(oldData30, newData30),
            { legend: { position: 'top' }, title: 'Last 30 Days' }
        );

    // 90 day
        var oldDataSplice90 = []
        projectOverview.forEach(function(currentValue, index, arr){
            let temp = currentValue.slice(0)
            temp.splice(1,5)
            temp.splice(-1,1)
            oldDataSplice90[index] = temp

        })

        var newDataSplice90 = []
        projectOverview.forEach(function(currentValue, index, arr){
            let temp = currentValue.slice(0)
            temp.splice(1, 4)
            temp.splice(2, 2)
            newDataSplice90[index] = temp
        })

        let oldData90 = google.visualization.arrayToDataTable(oldDataSplice90);
        let newData90 = google.visualization.arrayToDataTable(newDataSplice90);

        let colChartDiff90 = new google.visualization.ColumnChart(document.getElementById('colchart_diff_90'));
        colChartDiff90.draw(
            colChartDiff90.computeDiff(oldData90, newData90),
            { legend: { position: 'top' }, title: 'Last 90 Days' }
        );

    // All time
        var oldDataSpliceAll = []
        projectOverview.forEach(function(currentValue, index, arr){
            let temp = currentValue.slice(0)
            temp.splice(1,1)
            temp.splice(2,5)
            oldDataSpliceAll[index] = temp

        })

        var newDataSpliceAll = []
        projectOverview.forEach(function(currentValue, index, arr){
            let temp = currentValue.slice(0)
            temp.splice(1, 6)
            newDataSpliceAll[index] = temp
        })

        let oldDataAll = google.visualization.arrayToDataTable(oldDataSpliceAll);
        let newDataAll = google.visualization.arrayToDataTable(newDataSpliceAll);

        let colChartDiffAll = new google.visualization.ColumnChart(document.getElementById('colchart_diff_All'));
        colChartDiffAll.draw(
            colChartDiffAll.computeDiff(oldDataAll, newDataAll),
            { legend: { position: 'top' }, title: 'Title' }
        );

    }

    function drawLineChart() {
        // LINE CHART
        var data = google.visualization.arrayToDataTable([
            ['Month', 'New Trainees', 'New Groups', 'Sessions Completed', 'Course Completed'],
            ['Feb', 1000, 1000, 1000, 400],
            ['Mar', 1000, 1000, 1000, 400],
            ['Apr', 1000, 1000, 1000, 400],
            ['May', 1000, 1000, 1000, 400],
            ['Jun', 1000, 1000, 1000, 400],
            ['Jul', 1000, 1000, 1000, 400],
            ['Aug', 1000, 1000, 1000, 400],
            ['Sep', 1000, 1000, 1000, 400],
            ['Oct', 1000, 1000, 1000, 400],
            ['Nov', 1000, 1000, 1000, 400],
            ['Dec', 1000, 1000, 1000, 400],
            ['Jan', 1000, 1000, 1000, 400],
            ['Feb', 1170, 1170, 1170, 460],
            ['Mar', 660, 660, 660, 1120],
            ['Apr', 1030, 1030, 1030, 540]
        ]);

        var options = {
            title: 'Project Progress',
            curveType: 'function',
            legend: { position: 'bottom' }
        };

        var chart = new google.visualization.LineChart(document.getElementById('curve_chart'));

        chart.draw(data, options);
    }

    chartDiv.append(`<br><br><div><span class="small grey">( stats as of `+ wpApiZumeMetrics.zume_stats.timestamp +` )</span> 
            <a onclick="refresh_stats_data( 'show_zume_project' ); jQuery('.spinner').show();">Refresh</a>
            <span class="spinner" style="display: none;"><img src="`+wpApiZumeMetrics.plugin_uri+`includes/ajax-loader.gif" /></span> 
            </div>`)
}

function show_zume_individuals(){
    "use strict";
    let screenHeight = jQuery(window).height()
    let chartHeight = screenHeight / 1.3
    let chartDiv = jQuery('#chart')
    chartDiv.empty().html('<span class="section-header">'+ wpApiZumeMetrics.translations.zume_project +'</span>')

    chartDiv.append(`<div id="table_div"></div>
        <div id="curve_chart" style="width: 100%; height: 500px"></div>
        <span id='colchart_diff_7' style='width: 47%; height: 350px; display: inline-block'></span>
        <span id='colchart_diff_30' style='width: 47%; height: 350px; display: inline-block'></span>
        <span id='colchart_diff_90' style='width: 47%; height: 350px; display: inline-block'></span>
        <span id='colchart_diff_All' style='width: 47%; height: 350px; display: inline-block'></span>
        `)

    google.charts.load('current', {'packages':['corechart', 'controls', 'table']});

    google.charts.setOnLoadCallback(drawTable);
    google.charts.setOnLoadCallback(drawChart);
    google.charts.setOnLoadCallback(drawLineChart);

    function drawTable() {
        let data = google.visualization.arrayToDataTable(wpApiZumeMetrics.zume_stats.global.project_overview);
        let table = new google.visualization.Table(document.getElementById('table_div'));
        table.draw(data, {showRowNumber: true, width: '100%', height: '100%'});
    }

    function drawChart() {

        let projectOverview = wpApiZumeMetrics.zume_stats.global.project_overview

        // 7 Day
        var oldDataSplice7 = []
        projectOverview.forEach(function(currentValue, index, arr){
            let temp = currentValue.slice(0)
            temp.splice(1,1)
            temp.splice(2,5)
            oldDataSplice7[index] = temp

        })

        var newDataSplice7 = []
        projectOverview.forEach(function(currentValue, index, arr){
            let temp = currentValue.slice(0)
            temp.splice(2, 6)
            newDataSplice7[index] = temp
        })

        let oldData7 = google.visualization.arrayToDataTable(oldDataSplice7);
        let newData7 = google.visualization.arrayToDataTable(newDataSplice7);

        let colChartDiff7 = new google.visualization.ColumnChart(document.getElementById('colchart_diff_7'));
        colChartDiff7.draw(
            colChartDiff7.computeDiff(oldData7, newData7),
            { legend: { position: 'top' }, title: 'Last 7 Days' }
        );

        // 30 day
        var oldDataSplice30 = []
        projectOverview.forEach(function(currentValue, index, arr){
            let temp = currentValue.slice(0)
            temp.splice(1,3)
            temp.splice(2,3)
            oldDataSplice30[index] = temp

        })

        var newDataSplice30 = []
        projectOverview.forEach(function(currentValue, index, arr){
            let temp = currentValue.slice(0)
            temp.splice(1, 2)
            temp.splice(2,4)
            newDataSplice30[index] = temp
        })

        let oldData30 = google.visualization.arrayToDataTable(oldDataSplice30);
        let newData30 = google.visualization.arrayToDataTable(newDataSplice30);

        let colChartDiff30 = new google.visualization.ColumnChart(document.getElementById('colchart_diff_30'));
        colChartDiff30.draw(
            colChartDiff30.computeDiff(oldData30, newData30),
            { legend: { position: 'top' }, title: 'Last 30 Days' }
        );

        // 90 day
        var oldDataSplice90 = []
        projectOverview.forEach(function(currentValue, index, arr){
            let temp = currentValue.slice(0)
            temp.splice(1,5)
            temp.splice(-1,1)
            oldDataSplice90[index] = temp

        })

        var newDataSplice90 = []
        projectOverview.forEach(function(currentValue, index, arr){
            let temp = currentValue.slice(0)
            temp.splice(1, 4)
            temp.splice(2, 2)
            newDataSplice90[index] = temp
        })

        let oldData90 = google.visualization.arrayToDataTable(oldDataSplice90);
        let newData90 = google.visualization.arrayToDataTable(newDataSplice90);

        let colChartDiff90 = new google.visualization.ColumnChart(document.getElementById('colchart_diff_90'));
        colChartDiff90.draw(
            colChartDiff90.computeDiff(oldData90, newData90),
            { legend: { position: 'top' }, title: 'Last 90 Days' }
        );

        // All time
        var oldDataSpliceAll = []
        projectOverview.forEach(function(currentValue, index, arr){
            let temp = currentValue.slice(0)
            temp.splice(1,1)
            temp.splice(2,5)
            oldDataSpliceAll[index] = temp

        })

        var newDataSpliceAll = []
        projectOverview.forEach(function(currentValue, index, arr){
            let temp = currentValue.slice(0)
            temp.splice(1, 6)
            newDataSpliceAll[index] = temp
        })

        let oldDataAll = google.visualization.arrayToDataTable(oldDataSpliceAll);
        let newDataAll = google.visualization.arrayToDataTable(newDataSpliceAll);

        let colChartDiffAll = new google.visualization.ColumnChart(document.getElementById('colchart_diff_All'));
        colChartDiffAll.draw(
            colChartDiffAll.computeDiff(oldDataAll, newDataAll),
            { legend: { position: 'top' }, title: 'Title' }
        );

    }

    function drawLineChart() {
        // LINE CHART
        var data = google.visualization.arrayToDataTable([
            ['Month', 'New Trainees', 'New Groups', 'Sessions Completed', 'Course Completed'],
            ['Feb', 1000, 1000, 1000, 400],
            ['Mar', 1000, 1000, 1000, 400],
            ['Apr', 1000, 1000, 1000, 400],
            ['May', 1000, 1000, 1000, 400],
            ['Jun', 1000, 1000, 1000, 400],
            ['Jul', 1000, 1000, 1000, 400],
            ['Aug', 1000, 1000, 1000, 400],
            ['Sep', 1000, 1000, 1000, 400],
            ['Oct', 1000, 1000, 1000, 400],
            ['Nov', 1000, 1000, 1000, 400],
            ['Dec', 1000, 1000, 1000, 400],
            ['Jan', 1000, 1000, 1000, 400],
            ['Feb', 1170, 1170, 1170, 460],
            ['Mar', 660, 660, 660, 1120],
            ['Apr', 1030, 1030, 1030, 540]
        ]);

        var options = {
            title: 'Project Progress',
            curveType: 'function',
            legend: { position: 'bottom' }
        };

        var chart = new google.visualization.LineChart(document.getElementById('curve_chart'));

        chart.draw(data, options);
    }

    chartDiv.append(`<br><br><div><span class="small grey">( stats as of `+ wpApiZumeMetrics.zume_stats.timestamp +` )</span> 
            <a onclick="refresh_stats_data( 'show_zume_project' ); jQuery('.spinner').show();">Refresh</a>
            <span class="spinner" style="display: none;"><img src="`+wpApiZumeMetrics.plugin_uri+`includes/ajax-loader.gif" /></span> 
            </div>`)
}

function show_zume_groups(){
    "use strict";
    let screenHeight = jQuery(window).height()
    let chartHeight = screenHeight / 1.8
    let chartDiv = jQuery('#chart')


    chartDiv.empty().html('<span class="section-header">'+ wpApiZumeMetrics.translations.zume_groups +'</span>');

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

function show_zume_pipeline(){
    "use strict";

    let screenHeight = jQuery(window).height()
    let chartHeight = screenHeight / 1.8
    let chartDiv = jQuery('#chart')

    chartDiv.empty().html('<span class="section-header">'+ wpApiZumeMetrics.translations.zume_pipeline +'</span>');

    chartDiv.append(`
                    <div class="grid-x grid-padding-x">
                        <div class="cell center">
                            <p class="section-subheader" >Sessions Completed by Groups</p>
                            <div id="sessions-completed" style="height: 400px; margin: 0 1em; "></div>
                        </div>
                        <div class="cell center">
                        <hr>
                            <p class="section-subheader">Current Session for Groups</p>
                            <div id="groups-in-session" style="height: 400px; margin: 0 1em; "></div>
                        </div>
                        <div class="cell center">
                        <hr>
                            <p class="section-subheader">Pipeline Trends</p>
                            <div id="table_div"></div>
                        </div>
                    </div>
    `)

    google.charts.load('current', {packages: ['corechart', 'bar', 'table']});
    google.charts.setOnLoadCallback(drawTable)
    google.charts.setOnLoadCallback(drawBarchart)

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

    function drawBarchart() {

        // Sessions completed
        let chartData1 = google.visualization.arrayToDataTable( [
            ['Session', 'Groups', {'role': 'annotation'}],
            ['None', 3000, 3000],
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

        let options1 = {
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

        let chart1 = new google.visualization.BarChart(document.getElementById('sessions-completed'));
        chart1.draw(chartData1, options1);

        // Members in Groups
        let chartData2 = google.visualization.arrayToDataTable( [
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

        let options2 = {
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

        let chart2 = new google.visualization.BarChart(document.getElementById('groups-in-session'));
        chart2.draw(chartData2, options2);

    }

    chartDiv.append(`<br><div><span class="small grey">( stats as of `+ wpApiZumeMetrics.zume_stats.timestamp +` )</span> 
            <a onclick="refresh_stats_data( 'show_zume_pipeline' ); jQuery('.spinner').show();">Refresh</a>
            <span class="spinner" style="display: none;"><img src="`+wpApiZumeMetrics.plugin_uri+`includes/ajax-loader.gif" /></span> 
            </div>`)
}

function show_zume_locations(){
    "use strict";
    let screenHeight = jQuery(window).height()
    let chartHeight = screenHeight / 1.8
    let chartDiv = jQuery('#chart')

    chartDiv.empty().html(`<span class="section-header">`+ wpApiZumeMetrics.translations.zume_locations +`</span>
        
        <div id="zume-locations" style="height: 500px; margin: 0 1em 1.2em; "></div>
        <div class="grid-x grid-padding-x">
            <div class="cell small-4"><span class="section-subheader">Top Countries</span><br><div id="drawTopCountries"></div></div>
            <div class="cell small-4"><span class="section-subheader">Newest Countries</span><br><div id="drawNewestCountries"></div></div>
            <div class="cell small-4"><span class="section-subheader">Newest Groups by Country</span><br><div id="drawNewestGroupsByCountry"></div></div>
        </div>
        <div class="grid-x">
            <div class="cell center">
                <hr>
                <span class="section-subheader">U.S.A</span>
                <div id="zume-region-usa" style="height: 500px; margin: 1.2em 1em; "></div>
            </div>
            <div class="cell center">
            <hr>
                <span class="section-subheader">Africa</span>
                <div id="zume-region-africa" style="height: 500px; margin: 1.2em 1em; "></div>
            </div>
            <div class="cell center">
            <hr>
                <span class="section-subheader">Europe</span>
                <div id="zume-region-europe" style="height: 500px; margin: 1.2em 1em; "></div>
            </div>
            <div class="cell center">
            <hr>
                <span class="section-subheader">Asia</span>
                <div id="zume-region-asia" style="height: 500px; margin: 1.2em 1em; "></div>
            </div>
            <div class="cell center">
                <hr>
                <span class="section-subheader">South America</span>
                <div id="zume-region-americas" style="height: 500px; margin: 1.2em 1em; "></div>
            </div>
        </div>
        
    `)

    google.charts.load('current', {'packages':['geochart', 'table'], 'mapsApiKey': wpApiZumeMetrics.map_key });

    google.charts.setOnLoadCallback(drawTopCountries);
    google.charts.setOnLoadCallback(drawNewestCountries);
    google.charts.setOnLoadCallback(drawNewestGroupsByCountry);
    google.charts.setOnLoadCallback(drawVisualization);
    google.charts.setOnLoadCallback(drawRegions);


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

    function drawRegions() {

        /* Codes for regions found at the bottom of https://developers.google.com/chart/interactive/docs/gallery/geochart */

        // USA
        let data = google.visualization.arrayToDataTable(wpApiZumeMetrics.zume_stats.global.group_coordinates)

        let chart1 = new google.visualization.GeoChart(document.getElementById('zume-region-usa'));
        chart1.draw(data, {
            region: 'US',
            resolution: 'provinces',
            tooltip: {trigger: 'none'}
        });

        // AFRICA
        let chart2 = new google.visualization.GeoChart(document.getElementById('zume-region-africa'));
        chart2.draw(data, {
            region: '002',
            tooltip: {trigger: 'none'}
        });

        // EUROPE
        let chart3 = new google.visualization.GeoChart(document.getElementById('zume-region-europe'));
        chart3.draw(data, {
            region: '150',
            tooltip: {trigger: 'none'}
        });

        // ASIA
        let chart4 = new google.visualization.GeoChart(document.getElementById('zume-region-asia'));
        chart4.draw(data, {
            region: '142',
            tooltip: {trigger: 'none'}
        });

        // AMERICAS
        let chart6 = new google.visualization.GeoChart(document.getElementById('zume-region-americas'));
        chart6.draw(data, {
            region: '005',
            tooltip: {trigger: 'none'}
        });

    }

    chartDiv.append(`<br><br><div><span class="small grey">( stats as of `+ wpApiZumeMetrics.zume_stats.timestamp +` )</span> 
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

    chartDiv.empty().html('<span class="section-header">'+ wpApiZumeMetrics.translations.zume_languages +'</span>' +
        '<div id="zume-languages" style="height: 500px; margin: 0 1em; "></div>' +
        '<div id="table_div"></div>')


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