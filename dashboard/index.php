<?php
	set_time_limit(0);
	
	$re = '/DB_HOST = "(?P<DB_HOST>.*)"\nDB_NAME = "(?P<DB_NAME>.*)"\nDB_USER = "(?P<DB_USER>.*)"\nDB_PASSWORD = "(?P<DB_PASSWORD>.*)\nBOOTSTRAP_URL = "(?P<BOOTSTRAP_URL>.*)"/m';
	$config_file_content = file_get_contents("../config.py");
	preg_match_all($re, $config_file_content, $matches, PREG_SET_ORDER, 0);
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<!-- Meta, title, CSS, favicons, etc. -->
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="icon" href="favicon.ico" type="image/ico" />

		<title>DSN Monitor</title>

		<!-- Bootstrap -->
		<link href="<?php echo $matches[0]["BOOTSTRAP_URL"]; ?>/vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
		<!-- Font Awesome -->
		<link href="<?php echo $matches[0]["BOOTSTRAP_URL"]; ?>/vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet">
		<!-- NProgress -->
		<link href="<?php echo $matches[0]["BOOTSTRAP_URL"]; ?>/vendors/nprogress/nprogress.css" rel="stylesheet">
		<!-- iCheck -->
		<link href="<?php echo $matches[0]["BOOTSTRAP_URL"]; ?>/vendors/iCheck/skins/flat/green.css" rel="stylesheet">
	
		<!-- bootstrap-progressbar -->
		<link href="<?php echo $matches[0]["BOOTSTRAP_URL"]; ?>/vendors/bootstrap-progressbar/css/bootstrap-progressbar-3.3.4.min.css" rel="stylesheet">
		<!-- JQVMap -->
		<link href="<?php echo $matches[0]["BOOTSTRAP_URL"]; ?>/vendors/jqvmap/dist/jqvmap.min.css" rel="stylesheet"/>
		<!-- bootstrap-daterangepicker -->
		<link href="<?php echo $matches[0]["BOOTSTRAP_URL"]; ?>/vendors/bootstrap-daterangepicker/daterangepicker.css" rel="stylesheet">

		<!-- Custom Theme Style -->
		<link href="<?php echo $matches[0]["BOOTSTRAP_URL"]; ?>/build/css/custom.min.css" rel="stylesheet">
		
		<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.20.1/moment.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.1/Chart.min.js"></script>
		
		<!-- Google Charts -->
		<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
		<script type="text/javascript">
			google.charts.load("current", {"packages":["timeline", "line", "scatter"]});
			google.charts.setOnLoadCallback(drawChart);
			
			function drawChart() {
				// Timeline Charts
				var stationTimelineDataJSON = $.ajax({
					url: "stationTimelineData.json",
					dataType: "json",
					cache: false,
					async: false,
					timeout: 0
					}).responseText;
				var stationTimelineDataTable = new google.visualization.DataTable(stationTimelineDataJSON);
				var stationTimelineOptions = {
					title: "DSN Monitor",
					hAxis: {
					format: "MMM dd, H:00"
					},
					height: 500
				};
				var stationTimelineContainer = document.getElementById("stationTimeline");
				var stationTimelineChart = new google.visualization.Timeline(stationTimelineContainer);
				stationTimelineChart.draw(stationTimelineDataTable, stationTimelineOptions);
				
				var spacecraftTimelineDataJSON = $.ajax({
					url: "spacecraftTimelineData.json",
					dataType: "json",
					cache: false,
					async: false,
					timeout: 0
					}).responseText;
				var spacecraftTimelineDataTable = new google.visualization.DataTable(spacecraftTimelineDataJSON);
				var spacecraftTimelineOptions = {
					title: "DSN Monitor",
					hAxis: {
					format: "MMM dd, H:00"
					},
					height: 500,
					timeline: { colorByRowLabel: true }
				};
				var spacecraftTimelineContainer = document.getElementById("spacecraftTimeline");
				var spacecraftTimelineChart = new google.visualization.Timeline(spacecraftTimelineContainer);
				spacecraftTimelineChart.draw(spacecraftTimelineDataTable, spacecraftTimelineOptions);
				
				
				// Timeline select event
				google.visualization.events.addListener(stationTimelineChart, "select", stationTimelineSelectHandler);
				google.visualization.events.addListener(spacecraftTimelineChart, "select", spacecraftTimelineSelectHandler);
				
				function stationTimelineSelectHandler() {
					dataTable = stationTimelineDataTable;
					selection = stationTimelineChart.getSelection();
					antenna = dataTable.getValue(selection[0].row, 0);
					spacecraft_name = dataTable.getValue(selection[0].row, 1);
					start = dataTable.getValue(selection[0].row, 2);
					end = dataTable.getValue(selection[0].row, 3);
					getLinkData(antenna, spacecraft_name, start, end);
				}
				
				function spacecraftTimelineSelectHandler() {
					dataTable = spacecraftTimelineDataTable;
					selection = spacecraftTimelineChart.getSelection();
					spacecraft_name = dataTable.getValue(selection[0].row, 0);
					antenna = dataTable.getValue(selection[0].row, 1);
					start = dataTable.getValue(selection[0].row, 2);
					end = dataTable.getValue(selection[0].row, 3);
					getLinkData(antenna, spacecraft_name, start, end);
				}
				
				function getLinkData(antenna, spacecraft_name, start, end) {
					var linkCurrentData = $.ajax({
						url: "get_link_current_data.php?debug=0&antenna="+antenna+"&spacecraft_name="+spacecraft_name+"&start="+start+"&end="+end,
						dataType: "text",
						cache: false,
						async: false,
						timeout: 0
						}).responseText;
					$("#link_current_data").html("").html(linkCurrentData);
					
					var linkHistoryData_range = $.ajax({
						url: "get_link_history_data.php?debug=0&spacecraft_name="+spacecraft_name+"&field=spacecraft_range",
						dataType: "txt",
						cache: false,
						async: false,
						timeout: 0
					}).responseText;
					var linkHistoryData_datarate = $.ajax({
						url: "get_link_history_data.php?debug=0&spacecraft_name="+spacecraft_name+"&field=data_rate",
						dataType: "txt",
						cache: false,
						async: false,
						timeout: 0
					}).responseText;
					var linkHistoryData_power = $.ajax({
						url: "get_link_history_data.php?debug=0&spacecraft_name="+spacecraft_name+"&field=power",
						dataType: "txt",
						cache: false,
						async: false,
						timeout: 0
					}).responseText;
					
					var chartConfig = {
						type: "line",
					    data: { labels: [],
						    datasets: [{
							    label: "range",
							    borderColor: "#ff8047",
							    borderWidth: 1,
							    lineTension: 0,
							    fill: false,
							    pointRadius: 5, 
							    pointBackgroundColor: "#ffffff",
							    pointBorderWidth: 2,
							    spanGaps: false,
							    yAxisID: "A",
								data: JSON.parse(linkHistoryData_range)
							},
							{
							    label: "data rate",
							    borderColor: "#4773ff",
							    borderWidth: 1,
							    lineTension: 0,
							    fill: false,
							    pointRadius: 5, 
							    pointBackgroundColor: "#ffffff",
							    pointBorderWidth: 2,
							    spanGaps: false,
							    yAxisID: "B",
								data: JSON.parse(linkHistoryData_datarate)
							},
							{
							    label: "power",
							    borderColor: "#00a100",
							    borderWidth: 1,
							    lineTension: 0,
							    fill: false,
							    pointRadius: 5, 
							    pointBackgroundColor: "#ffffff",
							    pointBorderWidth: 2,
							    spanGaps: false,
							    yAxisID: "C",
								data: JSON.parse(linkHistoryData_power)
							}] 
						}
					};
					link_history_data_chart.config = chartConfig;
					link_history_data_chart.update();
				}
				
				
				// Scatter Charts
				var rangeDataEarthJSON = $.ajax({
					url: "rangeDataEarth.json",
					dataType: "json",
					cache: false,
					async: false,
					timeout: 0
					}).responseText;
				var rangeDataEarth = new google.visualization.DataTable(rangeDataEarthJSON);
				
				var rangeDataSolarSystemJSON = $.ajax({
					url: "rangeDataSolarSystem.json",
					dataType: "json",
					cache: false,
					async: false,
					timeout: 0
					}).responseText;
				var rangeDataSolarSystem = new google.visualization.DataTable(rangeDataSolarSystemJSON);
				
				var rangeDataBeyondJSON = $.ajax({
					url: "rangeDataBeyond.json",
					dataType: "json",
					cache: false,
					async: false,
					timeout: 0
					}).responseText;
				var rangeDataBeyond = new google.visualization.DataTable(rangeDataBeyondJSON);
								    
				var rangeChartEarthOptions = {
				    hAxis: {title: "Range [km]"},
					vAxis: {title: "Earth Orbit"},
					orientation: "vertical"
				};
				var rangeChartSolarSystemOptions = {
				    hAxis: {title: "Range [km]"},
					vAxis: {title: "Solar System"},
					orientation: "vertical"
				};
				var rangeChartBeyondOptions = {
				    hAxis: {title: "Range [km]"},
					vAxis: {title: "Beyond"},
					orientation: "vertical"
				};
				
				var rangeChartEarth = new google.charts.Scatter(document.getElementById("range_chart_earth"));
				rangeChartEarth.draw(rangeDataEarth, google.charts.Scatter.convertOptions(rangeChartEarthOptions));
				var rangeChartSolarSystem = new google.charts.Scatter(document.getElementById("range_chart_solar_system"));
				rangeChartSolarSystem.draw(rangeDataSolarSystem, google.charts.Scatter.convertOptions(rangeChartSolarSystemOptions));
				var rangeChartBeyond = new google.charts.Scatter(document.getElementById("range_chart_beyond"));
				rangeChartBeyond.draw(rangeDataBeyond, google.charts.Scatter.convertOptions(rangeChartBeyondOptions));
			}
		</script>
		<style>
			a.sidebar:link, a.sidebar:visited, a.sidebar:active {
				color: white;
				font-weight: bold;
			}
			
			/* Solves Google Charts width issue inside tabs */
			.tab-content>.tab-pane {
				height: 1px;
				overflow: hidden;
				display: block;
				visibility: hidden;
			}
			.tab-content>.active {
				height: auto;
				overflow: auto;
				visibility: visible;
			}
		</style>
		
		<!-- Google Analytics -->
		<?php require("google_analytics.php"); ?>
	</head>

	<body class="nav-md">
		<div class="container body">
			<div class="main_container">
				<div class="col-md-3 left_col">
					<div class="left_col scroll-view">
						<div class="clearfix"></div>

						<!-- menu profile quick info -->
						<div class="profile clearfix">
							<div class="profile_pic">
								<img src="antenna.png" class="img-circle profile_img">
							</div>
							<div class="profile_info" style="padding: 23px 5px 10px">
								<h5 style="padding: 10px 0px 0px 0px; color: white;">DSN Monitor</h5>
							</div>
						</div>
						<!-- /menu profile quick info -->

						<br />

						<!-- sidebar menu -->
						<div id=\"sidebar-menu\" class=\"main_menu_side hidden-print main_menu\">
							<div class=\"menu_section\">
								<p style="padding: 10px 16px 0px 12px; font-size: 16px; color: rgba(255,255,255,0.75)">
									Welcome!
								</p>
								<p style="padding: 0px 16px 0px 12px; color: rgba(255,255,255,0.75); text-align: justify">
									Data is fetched from <a class="sidebar" href="https://eyes.nasa.gov/dsn/dsn.html">DSN Now</a>, stored in a database and visualized with <a class="sidebar" href="https://developers.google.com/chart">Google Charts</a>.
									<br><br>
									Timeline tabs show both DSN stations recent scheduling and single spacecraft coverage.
									<br><br>
									Clicking on a time slot, link details and history graphs are shown.
									<br><br>
									Finally, Spacecraft Range tab shows current distance from Earth.
									<br><br>
									This website, along with the scripts that fetch and generate data to be visualized, runs 24/7 on a Raspberry Pi Zero.
									<br><br>
									The project is open-source, if you're interested please visit the repository on GitHub.
									<br><br>
									<center><a href="https://github.com/Vinz87/DSNMonitor"><img src="GitHub-Mark-Light-32px.png"></a></center>
								</p>
							</div>
						</div>
						<!-- /sidebar menu -->

						<!-- /menu footer buttons -->
						<!-- /menu footer buttons -->
					</div>
				</div>


				<!-- top navigation -->
				<!-- /top navigation -->


				<!-- page content -->
				<div class="right_col" role="main">
					<div class="">
						<div class="row">
							<div class="page-title">
								<div class="title_left" style="margin:0px 0px 0px 20px">
									<h3>DSN Monitor</h3>
								</div>
							</div>
						</div>

						<div class="clearfix"></div>

						<!-- Timeline -->
						<div class="row">
							<div class="col-md-12 col-sm-12 col-xs-12">
								<div class="x_content">
									<ul class="nav nav-tabs bar_tabs" id="myTab" role="tablist">
										<li class="nav-item">
											<a class="nav-link active" id="station-timeline-link" data-toggle="tab" href="#station-timeline-div" role="tab" aria-controls="station-timeline-div" aria-selected="true">By Station</a>
										</li>
										<li class="nav-item">
											<a class="nav-link" id="spacecraft-timeline-link" data-toggle="tab" href="#spacecraft-timeline-div" role="tab" aria-controls="spacecraft-timeline-div" aria-selected="false">By Spacecraft</a>
										</li>
									</ul>
									<div class="tab-content" id="myTabContent">
										<div class="tab-pane fade show active" id="station-timeline-div" role="tabpanel" aria-labelledby="station-timeline-link">
											<div id="stationTimeline"></div>
										</div>
										<div class="tab-pane fade" id="spacecraft-timeline-div" role="tabpanel" aria-labelledby="spacecraft-timeline-link">
											<div id="spacecraftTimeline"></div>
										</div>
									</div>
								</div>
							</div>
						</div>
						
						<!-- Link Details & Range -->
						<div class="row">
							<div class="col-md-12 col-sm-12 col-xs-12">
								<div class="x_content">
									<ul class="nav nav-tabs bar_tabs" id="myTab" role="tablist">
										<li class="nav-item">
											<a class="nav-link active" id="details-link" data-toggle="tab" href="#details-div" role="tab" aria-controls="details-div" aria-selected="true">Link Details</a>
										</li>
										<li class="nav-item">
											<a class="nav-link" id="range-link" data-toggle="tab" href="#range-div" role="tab" aria-controls="range-div" aria-selected="false">Spacecraft Range</a>
										</li>
									</ul>
									<div class="tab-content" id="myTabContent">
										<div class="tab-pane fade show active" id="details-div" role="tabpanel" aria-labelledby="details-link">
											<div class="col-md-3 col-sm-12 col-xs-12" style="margin-top: 30px" id="link_current_data">
			                                    <table class="table table-hover">
		                                            <tbody>
		                                                <tr>
		                                                    <th scope="row">Spacecraft</th>
		                                                    <td></td>
		                                                </tr>
		                                                <tr>
		                                                    <th scope="row">Data Rate</th>
		                                                    <td></td>
		                                                </tr>
		                                                <tr>
		                                                    <th scope="row">Received Power</th>
		                                                    <td></td>
		                                                </tr>
		                                                <tr>
		                                                    <th scope="row">Frequency</th>
		                                                    <td></td>
		                                                </tr>
		                                                <tr>
		                                                    <th scope="row">Range</th>
		                                                    <td></td>
		                                                </tr>
		                                            </tbody>
		                                        </table>
		                                    </div>
		                                    <div class="col-md-9 col-sm-12 col-xs-12" id="link_history_data">
			                                    <canvas id="link_history_data_canvas"></canvas>
			                                    <script>
	                                    			var context = document.getElementById("link_history_data_canvas").getContext("2d");
													var link_history_data_chart = new Chart(context, {
													    type: "line",
													    data: { datasets: [{
																    label: "",
																    fill: false,
																    pointRadius: 2, 
																    spanGaps: false,
																    data: []
																}] },
													    options: {
													        scales: {
													            xAxes: [{
													                type: "time",
													                time: {
													                    unit: "day"
													                }
													            }],
													            yAxes: [{
															        id: 'A',
															        type: 'linear',
															        position: 'left',
															      }, {
															        id: 'B',
															        type: 'linear',
															        position: 'right'
															      }, {
															        id: 'C',
															        type: 'linear',
															        position: 'right'
															      }]
													        },
													        tooltips: {
														        interset: false
													        },
													        legend: {
														        display: true
													        },
													        aspectRatio: 4,
													        maintainAspectRatio: true
													    }
													});
													console.log(link_history_data_chart)
			                                    </script>
		                                    </div>
										</div>
										<div class="tab-pane fade" id="range-div" role="tabpanel" aria-labelledby="range-link">
											<div id="range">
					                            <div class="col-md-12 col-sm-12 col-xs-12">
					                                <div id="range_chart_earth"></div>
				                                    <div id="range_chart_solar_system"></div>
				                                    <div id="range_chart_beyond"></div>
					                            </div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- /page content -->


				<!-- footer content -->
				<footer>
					<div class="pull-right">
						Gentelella - Bootstrap Admin Template by <a href="https://colorlib.com">Colorlib</a>
					</div>
					<div class="clearfix"></div>
				</footer>
				<!-- /footer content -->
			</div>
		</div>

		<!-- jQuery -->
		<script src="<?php echo $matches[0]["BOOTSTRAP_URL"]; ?>/vendors/jquery/dist/jquery.min.js"></script>
		<!-- Bootstrap -->
		<script src="<?php echo $matches[0]["BOOTSTRAP_URL"]; ?>/vendors/bootstrap/dist/js/bootstrap.min.js"></script>
		<!-- FastClick -->
		<script src="<?php echo $matches[0]["BOOTSTRAP_URL"]; ?>/vendors/fastclick/lib/fastclick.js"></script>
		<!-- NProgress -->
		<script src="<?php echo $matches[0]["BOOTSTRAP_URL"]; ?>/vendors/nprogress/nprogress.js"></script>
		<!-- Chart.js -->
		<script src="<?php echo $matches[0]["BOOTSTRAP_URL"]; ?>/vendors/Chart.js/dist/Chart.min.js"></script>
		<!-- gauge.js -->
		<script src="<?php echo $matches[0]["BOOTSTRAP_URL"]; ?>/vendors/gauge.js/dist/gauge.min.js"></script>
		<!-- bootstrap-progressbar -->
		<script src="<?php echo $matches[0]["BOOTSTRAP_URL"]; ?>/vendors/bootstrap-progressbar/bootstrap-progressbar.min.js"></script>
		<!-- iCheck -->
		<script src="<?php echo $matches[0]["BOOTSTRAP_URL"]; ?>/vendors/iCheck/icheck.min.js"></script>
		<!-- Skycons -->
		<script src="<?php echo $matches[0]["BOOTSTRAP_URL"]; ?>/vendors/skycons/skycons.js"></script>
		<!-- Flot -->
		<script src="<?php echo $matches[0]["BOOTSTRAP_URL"]; ?>/vendors/Flot/jquery.flot.js"></script>
		<script src="<?php echo $matches[0]["BOOTSTRAP_URL"]; ?>/vendors/Flot/jquery.flot.pie.js"></script>
		<script src="<?php echo $matches[0]["BOOTSTRAP_URL"]; ?>/vendors/Flot/jquery.flot.time.js"></script>
		<script src="<?php echo $matches[0]["BOOTSTRAP_URL"]; ?>/vendors/Flot/jquery.flot.stack.js"></script>
		<script src="<?php echo $matches[0]["BOOTSTRAP_URL"]; ?>/vendors/Flot/jquery.flot.resize.js"></script>
		<!-- Flot plugins -->
		<script src="<?php echo $matches[0]["BOOTSTRAP_URL"]; ?>/vendors/flot.orderbars/js/jquery.flot.orderBars.js"></script>
		<script src="<?php echo $matches[0]["BOOTSTRAP_URL"]; ?>/vendors/flot-spline/js/jquery.flot.spline.min.js"></script>
		<script src="<?php echo $matches[0]["BOOTSTRAP_URL"]; ?>/vendors/flot.curvedlines/curvedLines.js"></script>
		<!-- DateJS -->
		<script src="<?php echo $matches[0]["BOOTSTRAP_URL"]; ?>/vendors/DateJS/build/date.js"></script>
		<!-- JQVMap -->
		<script src="<?php echo $matches[0]["BOOTSTRAP_URL"]; ?>/vendors/jqvmap/dist/jquery.vmap.js"></script>
		<script src="<?php echo $matches[0]["BOOTSTRAP_URL"]; ?>/vendors/jqvmap/dist/maps/jquery.vmap.world.js"></script>
		<script src="<?php echo $matches[0]["BOOTSTRAP_URL"]; ?>/vendors/jqvmap/examples/js/jquery.vmap.sampledata.js"></script>
		<!-- bootstrap-daterangepicker -->
		<script src="<?php echo $matches[0]["BOOTSTRAP_URL"]; ?>/vendors/moment/min/moment.min.js"></script>
		<script src="<?php echo $matches[0]["BOOTSTRAP_URL"]; ?>/vendors/bootstrap-daterangepicker/daterangepicker.js"></script>

		<!-- Custom Theme Scripts -->
		<script src="<?php echo $matches[0]["BOOTSTRAP_URL"]; ?>/build/js/custom.min.js"></script>
	</body>
</html>
