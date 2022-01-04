<?php
	set_time_limit(0);
	if ($_GET["debug"]) {
		ini_set('display_errors',1);
		error_reporting(E_ALL | E_STRICT);
		print_r($_GET);
		echo "<p>";
	}

	$re = '/DB_HOST = "(?P<DB_HOST>.*)"\nDB_NAME = "(?P<DB_NAME>.*)"\nDB_USER = "(?P<DB_USER>.*)"\nDB_PASSWORD = "(?P<DB_PASSWORD>.*)"\nBOOTSTRAP_URL = "(?P<BOOTSTRAP_URL>.*)"/m';
	$config_file_content = file_get_contents("../config.py");
	preg_match_all($re, $config_file_content, $matches, PREG_SET_ORDER, 0);

	$db = mysqli_connect($matches[0]["DB_HOST"], $matches[0]["DB_USER"], $matches[0]["DB_PASSWORD"], $matches[0]["DB_NAME"]);
	
	$current_timestamp = new DateTimeImmutable("now");

	$start_timestamp = date_create_from_format("D M d Y H:i:s", substr($_GET["start"], 0, 24));
	$end_timestamp = date_create_from_format("D M d Y H:i:s", substr($_GET["end"], 0, 24));
	
	if ($_GET["debug"]) {
		echo substr($_GET["start"], 0, 24)."<br>";
		var_dump($start_timestamp);
		echo "<br>";
		echo substr($_GET["end"], 0, 24)."<br>";
		var_dump($end_timestamp);
		echo "<p>";
	}

	$q_string = "SELECT * FROM DSNMonitorRecent where spacecraft_name = \"".$_GET["spacecraft_name"]."\" AND antenna = \"".$_GET["antenna"]."\" AND timestamp = \"".$end_timestamp->format("Y-m-d H:i")."\" ORDER BY timestamp DESC LIMIT 1";
	if ($_GET["debug"]) echo $q_string."<br>";
	$q_timeline = $db->query($q_string);
	$row_timeline = $q_timeline->fetch_assoc();
	if ($_GET["debug"]) var_dump($row_timeline)."<br>";
	
	echo "<table class=\"table table-hover\">
            <tbody>
                <tr>
                    <th scope=\"row\">Spacecraft</th>
                    <td>".$row_timeline["spacecraft_name"]."</td>
                </tr>
                <tr>
                    <th scope=\"row\">Data Rate</th>
                    <td>".$row_timeline["data_rate"]." bps</td>
                </tr>
                <tr>
                    <th scope=\"row\">Received Power</th>
                    <td>".number_format(floatval($row_timeline["power"]),1,".","")." dBm</td>
                </tr>
                <tr>
                    <th scope=\"row\">Frequency</th>
                    <td>".number_format((floatval($row_timeline["frequency"])/1e9),2,".","")." GHz</td>
                </tr>
                <tr>
                    <th scope=\"row\">Range</th>
                    <td>".$row_timeline["spacecraft_range"]." km</td>
                </tr>
            </tbody>
        </table>";
?>