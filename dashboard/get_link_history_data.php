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

	$data = [];
	
	if ($_GET["field"] == "spacecraft_range") {
		$q_string = "SELECT * FROM DSNMonitorDaily WHERE spacecraft_name = \"".$_GET["spacecraft_name"]."\" AND spacecraft_range > 0";
	} else {
		$q_string = "SELECT * FROM DSNMonitorDaily WHERE spacecraft_name = \"".$_GET["spacecraft_name"]."\"";
	}
	if ($_GET["debug"]) echo $q_string."<br>";
	$q_history = $db->query($q_string);
	while ($row_history = $q_history->fetch_assoc()) {
		$data[] = array("x" => $row_history["timestamp"], "y" => $row_history[$_GET["field"]]);
	}
		
	if ($_GET["debug"]) {
		echo "<pre>";
	}
	echo json_encode($data, JSON_PRETTY_PRINT);
?>