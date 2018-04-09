jQuery(document).ready(function() {
    console.log(wpApiZumeMetrics.zume_stats)

    if('#zume_project' === window.location.hash) {
        show_zume_project()
    }
    if('#zume_locations' === window.location.hash) {
        show_zume_locations()
    }
    if('#zume_groups' === window.location.hash) {
        show_zume_groups()
    }
    if('#zume_people' === window.location.hash) {
        show_zume_people()
    }
})

function show_zume_project(){
    "use strict";
    let screenHeight = jQuery(window).height()
    let chartHeight = screenHeight / 1.3
    let chartDiv = jQuery('#chart')
    chartDiv.empty().html('<span class="section-header">'+ wpApiZumeMetrics.translations.zume_project +'</span>')

    chartDiv.append(`
        <div id="zume-locations" style="height: 500px; margin: 0 1em 1.2em; "></div>
        <div class="grid-x grid-padding-x grid-padding-y">
            <div class="cell">
                <div class="grid-x callout">
                    <div class="medium-3 cell center">
                    <h4>Trained Groups<br><span id="group_numbers"></span></h4>
                    </div>
                    <div class="medium-3 cell center">
                    <h4>Trained People<br><span id="people_numbers"></span></h4>
                    </div>
                    <div class="medium-3 cell center">
                    <h4>Hours of Training<br><span id="hours_trained"></span></h4>
                    </div>
                    <div class="medium-3 cell center">
                    <h4>Countries<br><span id="country_numbers"></span></h4>
                    </div>
                </div>
            </div>
            <div class="cell center">
                <p class="section-subheader" >Groups Trends</p>
                <div id="combo_trend_groups" style="width: 100%; height: 500px;"></div>
                <hr>
            </div>
            <div class="cell center">
                <span class="section-subheader" >People Trends</span>
                <div id="combo_trend_people" style="width: 100%; height: 500px;"></div>
                <hr>
            </div>
            <div class="cell center">
            <span class="section-subheader" >All Time</span>
            </div>
            <div class="cell">
            <div class="grid-x grid-padding-x ">
                <div class="cell medium-6">
                    <div id="table_totals_group_people"></div>
                </div>
                <div class="cell medium-6">
                    <div id="table_total_misc"></div>
                </div>
            </div>
        </div>
        `)

    // Add hero stats
    let hero = wpApiZumeMetrics.zume_stats.hero_stats
    jQuery('#group_numbers').append( hero.trained_groups )
    jQuery('#people_numbers').append( hero.trained_people )
    jQuery('#hours_trained').append( hero.hours_trained_as_group )
    jQuery('#country_numbers').append( hero.total_countries )

    // build charts
    google.charts.load('current', {'packages':['corechart', 'controls', 'table']});

    google.charts.setOnLoadCallback(drawWorld);
    google.charts.setOnLoadCallback(drawTable);
    google.charts.setOnLoadCallback(drawTableMisc);
    google.charts.setOnLoadCallback(drawComboTrendsGroups);
    google.charts.setOnLoadCallback(drawComboTrendsPeople);

    function drawWorld() {

        let data = google.visualization.arrayToDataTable(wpApiZumeMetrics.zume_stats.group_coordinates)
        let options = {
            tooltip: {trigger: 'none'}
        };
        let chart = new google.visualization.GeoChart(document.getElementById('zume-locations'));
        chart.draw(data, options);
    }


    function drawComboTrendsGroups() {
        // Some raw data (not necessarily accurate)
        var data = google.visualization.arrayToDataTable(wpApiZumeMetrics.zume_stats.groups_progress_by_month);

        var options = {
            hAxis: {title: 'Months'},
            seriesType: 'bars',
            chartArea:{left: '10%',top:'5px',width:'75%',height:'75%'},
            series: {4: {type: 'line'}},
            colors:['lightgreen', 'limegreen', 'green', 'darkgreen'],
        };

        var chart = new google.visualization.ComboChart(document.getElementById('combo_trend_groups'));
        chart.draw(data, options);
    }

    function drawComboTrendsPeople() {
        // Some raw data (not necessarily accurate)
        var data = google.visualization.arrayToDataTable(wpApiZumeMetrics.zume_stats.people_progress_by_month);

        var options = {
            hAxis: {title: 'Months'},
            seriesType: 'bars',
            chartArea:{left: '10%',top:'5px',width:'75%',height:'75%'},
            series: {4: {type: 'line'}},
            colors:['lightblue', 'skyblue', 'blue', 'darkblue'],
        };

        var chart = new google.visualization.ComboChart(document.getElementById('combo_trend_people'));
        chart.draw(data, options);
    }

    function drawTable() {
        let data = google.visualization.arrayToDataTable(wpApiZumeMetrics.zume_stats.table_totals_group_people);
        let table = new google.visualization.Table(document.getElementById('table_totals_group_people'));
        table.draw(data, {
            showRowNumber: true,
            width: '100%',
            height: '100%'
        });
    }
    function drawTableMisc() {
        let data = google.visualization.arrayToDataTable(wpApiZumeMetrics.zume_stats.table_total_misc);
        let table = new google.visualization.Table(document.getElementById('table_total_misc'));
        table.draw(data, {
            showRowNumber: true,
            width: '100%',
            height: '100%'
        });
    }

    chartDiv.append(`<hr><div><span class="small grey">( stats as of `+ wpApiZumeMetrics.zume_stats.timestamp +` )</span> 
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

    chartDiv.append(`<br><br>
            <div class="grid-x grid-padding-x grid-padding-y">
            <div class="cell">
                <div class="grid-x callout">
                    <div class="medium-3 cell center">
                    <h4>Registered Groups<br><span id="registered_groups"></span></h4>
                    </div>
                    <div class="medium-3 cell center">
                    <h4>Trained Groups<br><span id="trained_groups"></span></h4>
                    </div>
                    <div class="medium-3 cell center">
                    <h4>4+ Members<br><span id="over_4"></span></h4>
                    </div>
                    <div class="medium-3 cell center">
                    <h4>Countries<br><span id="country_numbers"></span></h4>
                    </div>
                </div>
            </div>
            
            <div class="cell center">
                <span class="section-subheader">Members in Groups</span>
                <div id="zume-groups" style="height: 500px; margin: 0 1em; "></div>
            </div>
            <div class="cell center">
            <hr>
                <span class="section-subheader">Next Session for Groups</span>
                <div id="groups-in-session" style="height: 400px;"></div>
            </div>
            <div class="cell center">
            <hr>
                <p class="section-subheader" >Sessions Completed by Groups</p>
                <div id="sessions_completed_by_groups" style="height: 400px; "></div>
            </div>
            <div class="cell center">
            <hr>
                <span class="section-subheader">Location of trained groups</span>
                <div id="trained_groups_coordinates" style="width: 100%; height: 400px;"></div>
            </div>
            
        </div>
        `)

    // Add hero stats
    let hero = wpApiZumeMetrics.zume_stats.hero_stats
    jQuery('#registered_groups').append( hero.total_groups_registered )
    jQuery('#trained_groups').append( hero.trained_groups )
    jQuery('#over_4').append( hero.groups_over_4_members )
    jQuery('#country_numbers').append( hero.total_countries )

    google.charts.load('current', {packages: ['corechart', 'bar']});
    google.charts.setOnLoadCallback(drawMembersPerGroup)
    google.charts.setOnLoadCallback(drawCurrentSessionChart)
    google.charts.setOnLoadCallback(drawWorld);
    google.charts.setOnLoadCallback(drawSessionsCompleted)

    function drawMembersPerGroup() {

        let data = google.visualization.arrayToDataTable( wpApiZumeMetrics.zume_stats.members_per_group );

        let options = {
            bars: 'horizontal',
            chartArea: {
                left: '15%',
                top: '0%',
                width: "80%",
                height: "90%" },
            vAxis: {
                title: 'group size',
            },
            hAxis: {
                title: 'number of groups',
            },
            title: "Number of groups according to their member count",
            legend: {position: "none"},
            colors: ['green'],
        };

        let chart = new google.visualization.BarChart(document.getElementById('zume-groups'));
        chart.draw(data, options);
    }

    function drawCurrentSessionChart() {
        // Members in Groups
        let data = google.visualization.arrayToDataTable( wpApiZumeMetrics.zume_stats.current_session_of_group );

        let options = {
            bars: 'vertical',
            chartArea: {
                left: '15%',
                top: '0%',
                width: "80%",
                height: "90%" },
            vAxis: {
                title: 'session'
            },
            hAxis: {
                title: 'number of groups at different stages',
                scaleType: 'mirrorLog'
            },
            legend: {
                position: 'none'
            },
            colors: ['green'],
        }

        let chart = new google.visualization.BarChart(document.getElementById('groups-in-session'));
        chart.draw(data, options);
    }

    function drawSessionsCompleted() {
        let data = google.visualization.arrayToDataTable( wpApiZumeMetrics.zume_stats.sessions_completed_by_groups );
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
            colors: ['lightgreen'],
        }

        let chart = new google.visualization.BarChart(document.getElementById('sessions_completed_by_groups'));
        chart.draw(data, options);
    }

    function drawWorld() {
        let data = google.visualization.arrayToDataTable(wpApiZumeMetrics.zume_stats.trained_groups_coordinates)
        let options = {
            tooltip: {trigger: 'none'}
        };
        let chart = new google.visualization.GeoChart(document.getElementById('trained_groups_coordinates'));
        chart.draw(data, options);
    }

    chartDiv.append(`<hr><div><span class="small grey">( stats as of `+ wpApiZumeMetrics.zume_stats.timestamp +` )</span> 
            <a onclick="refresh_stats_data( 'show_zume_groups' ); jQuery('.spinner').show();">Refresh</a>
            <span class="spinner" style="display: none;"><img src="`+wpApiZumeMetrics.plugin_uri+`includes/ajax-loader.gif" /></span> 
            </div>`)
}

function show_zume_people(){
    "use strict";
    let screenHeight = jQuery(window).height()
    let chartHeight = screenHeight / 1.3
    let chartDiv = jQuery('#chart')
    chartDiv.empty().html('<span class="section-header">'+ wpApiZumeMetrics.translations.zume_people +'</span>')

    chartDiv.append(`<br><br>
        <div class="grid-x grid-padding-x grid-padding-y">
            <div class="cell center">
                <div class="grid-x callout">
                    <div class="medium-3 cell center">
                    <h4>Engaged People<br><span id="engaged_people"></span></h4>
                    </div>
                    <div class="medium-3 cell center">
                    <h4>Active People<br><span id="active_people"></span></h4>
                    </div>
                    <div class="medium-3 cell center">
                    <h4>Trained People<br><span id="trained_people"></span></h4>
                    </div>
                    <div class="medium-3 cell center">
                    <h4>In Groups<br><span id="total_people_in_groups"></span></h4>
                    </div>
                </div>
            </div>
            <div class="cell center">
                <span class="section-subheader">Active People engaging Zúme Project</span>
                <div id="active_people_timeline" style="width: 100%; height: 400px;"></div>
                <hr>
            </div>
            <div class="cell center">
                <span class="section-subheader">Language Users in Zúme</span>
                <div id="people_languages" style="height: 500px; margin: 0 1em; "></div>
            </div>
            <div class="cell center">
                <span class="section-subheader center">Misc</span>
            </div>
            <div class="cell">
                <div id="table_div"></div>
            </div>
        </div>
        `)

    let hero = wpApiZumeMetrics.zume_stats.hero_stats
    jQuery('#engaged_people').append( hero.engaged_people )
    jQuery('#active_people').append( hero.active_people )
    jQuery('#trained_people').append( hero.trained_people )
    jQuery('#total_people_in_groups').append( hero.total_people_in_groups )

    google.charts.load('current', {'packages':['corechart', 'controls', 'table']});

    google.charts.setOnLoadCallback(drawTable);
    google.charts.setOnLoadCallback(drawProgress);
    google.charts.setOnLoadCallback(drawLanguagesChart)


    function drawProgress() {
        // LINE CHART
        var data = google.visualization.arrayToDataTable(wpApiZumeMetrics.zume_stats.active_people_timeline );
        var options = {
            curveType: 'function',
            legend: { position: 'bottom' },
            chartArea: {
                left: '10%',
                top: '10px',
                width: "80%",
                height: "90%" },
            vAxis: {
                title: 'active people'
            }
        };

        var chart = new google.visualization.LineChart(document.getElementById('active_people_timeline'));
        chart.draw(data, options);
    }

    function drawLanguagesChart() {
        let chartData = google.visualization.arrayToDataTable( wpApiZumeMetrics.zume_stats.people_languages );
        let options = {
            bars: 'horizontal',
            chartArea: {
                left: '10%',
                top: '10px',
                width: "80%",
                height: "90%" },
            pieHole: 0.4,
        }

        let chart = new google.visualization.PieChart(document.getElementById('people_languages'));
        chart.draw(chartData, options);
    }

    function drawTable() {
        let data = google.visualization.arrayToDataTable( wpApiZumeMetrics.zume_stats.people_info );
        let table = new google.visualization.Table(document.getElementById('table_div'));
        table.draw(data, {showRowNumber: true, width: '100%', height: '100%'});
    }

    chartDiv.append(`<hr><div><span class="small grey">( stats as of `+ wpApiZumeMetrics.zume_stats.timestamp +` )</span> 
            <a onclick="refresh_stats_data( 'show_zume_project' ); jQuery('.spinner').show();">Refresh</a>
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
    google.charts.setOnLoadCallback(drawWorld);
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

    function drawWorld() {

        let data = google.visualization.arrayToDataTable(wpApiZumeMetrics.zume_stats.group_coordinates)

        let options = {
            tooltip: {trigger: 'none'}
        };

        let chart = new google.visualization.GeoChart(document.getElementById('zume-locations'));

        chart.draw(data, options);
    }

    function drawRegions() {

        /* Codes for regions found at the bottom of https://developers.google.com/chart/interactive/docs/gallery/geochart */

        // USA
        let data = google.visualization.arrayToDataTable(wpApiZumeMetrics.zume_stats.group_coordinates)

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

    chartDiv.append(`<hr><div><span class="small grey">( stats as of `+ wpApiZumeMetrics.zume_stats.timestamp +` )</span> 
            <a onclick="refresh_stats_data( 'show_zume_locations' ); jQuery('.spinner').show();">Refresh</a>
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