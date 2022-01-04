import pytz
import pprint
import pymysql
import gviz_api
import argparse
import subprocess
from operator import itemgetter
from xml.etree import ElementTree
from datetime import datetime, timedelta

from config import *

parser = argparse.ArgumentParser()
parser.add_argument("--debug", help="Enable debug mode", action="store_true")

args = parser.parse_args()
args_dict = vars(args)

from logging_config import *
if args.debug:
	console_handler.setLevel(logging.DEBUG)
else:
	console_handler.setLevel(logging.INFO)


def script_running():
	script_name = os.path.basename(__file__)
	cmd1 = subprocess.Popen(["ps", "aux"], stdout=subprocess.PIPE)
	cmd2 = subprocess.Popen(["grep", "-e", script_name], stdin=cmd1.stdout, stdout=subprocess.PIPE)
	cmd3 = subprocess.Popen(["grep", "-v", "grep"], stdin=cmd2.stdout, stdout=subprocess.PIPE)
	cmd4 = subprocess.Popen(["awk", "{print $2}"], stdin=cmd3.stdout, stdout=subprocess.PIPE)
	cmd1.stdout.close()
	cmd2.stdout.close()
	cmd3.stdout.close()
	shell_output = cmd4.communicate()[0].decode("utf-8")
	console_logger.debug(shell_output)
	file_logger.info(shell_output.replace("\n", " "))
	is_running = len(shell_output.split("\n")) > 3
	if is_running:
		console_logger.info("Script already running.")
		file_logger.info("Script already running.")
	return is_running

def local_time_to_utc(local_timestamp, local_timezone_string):
	local_timezone = pytz.timezone(local_timezone_string)
	local_timestamp = local_timezone.localize(local_timestamp)
	utc_timezone = pytz.timezone("UTC")
	utc_timestamp = local_timestamp.astimezone(utc_timezone)
	return utc_timestamp

				

try:
	file_logger.info("Dashboard data generation started.")
	if not script_running():
		conn = pymysql.connect(host=DB_HOST, port=3306, user=DB_USER, passwd=DB_PASSWORD, db=DB_NAME, charset="utf8", cursorclass=pymysql.cursors.DictCursor)
		cursor = conn.cursor()
		
		
		# Station Timeline Chart
		chart_row_list = []	
		query = "SELECT DISTINCT antenna FROM DSNMonitorRecent ORDER BY antenna"
		console_logger.debug(query)
		cursor.execute(query)
		antenna_query_res = cursor.fetchall()
		for antenna_row in antenna_query_res:
			query = "SELECT DISTINCT spacecraft_name FROM DSNMonitorRecent WHERE antenna = '" + antenna_row["antenna"] + "'"
			console_logger.debug(query)
			cursor.execute(query)
			spacecraft_query_res = cursor.fetchall()
			for spacecraft_row in spacecraft_query_res:
				query = "SELECT * FROM DSNMonitorRecent where spacecraft_name = '" + spacecraft_row["spacecraft_name"] + "' AND antenna = '" + antenna_row["antenna"] + "' ORDER BY timestamp ASC"
				console_logger.debug(query)
				cursor.execute(query)
				timeline_query_res = cursor.fetchall()
				for i in range(len(timeline_query_res)):
					console_logger.debug(timeline_query_res[i])
					if i == 0:
						chart_row = [antenna_row["antenna"], spacecraft_row["spacecraft_name"], local_time_to_utc(timeline_query_res[i]["timestamp"], "Europe/Rome")]
					else:
						timeline_delta = timeline_query_res[i]["timestamp"] - timeline_query_res[i-1]["timestamp"]
						if timeline_delta > timedelta(minutes=2*FETCH_INTERVAL):
							chart_row.append(local_time_to_utc(timeline_query_res[i-1]["timestamp"], "Europe/Rome"))
							chart_row_list.append(chart_row)
							chart_row = [antenna_row["antenna"], spacecraft_row["spacecraft_name"], local_time_to_utc(timeline_query_res[i]["timestamp"], "Europe/Rome")]
						elif i == len(timeline_query_res)-1:
							chart_row.append(local_time_to_utc(timeline_query_res[i]["timestamp"], "Europe/Rome"))
							chart_row_list.append(chart_row)
		console_logger.debug(chart_row_list)
		
		description = [("station", "string"), ("spacecraft", "string"), ("start", "datetime"), ("end", "datetime")]
		data_table = gviz_api.DataTable(description)
		data_table.LoadData(chart_row_list)
		json_string = data_table.ToJSon()
		console_logger.debug(pprint.pformat(json_string))
		output_file = open(parent_folder + "dashboard/stationTimelineData.json", "w")
		output_file.write(json_string)
		output_file.close()
		
		
		# Spacecraft Timeline Chart
		spacecraft_timeline_rows = []
		for i in range(len(chart_row_list)):
			spacecraft_timeline_rows.append([chart_row_list[i][1], chart_row_list[i][0], chart_row_list[i][2], chart_row_list[i][3]])
		spacecraft_timeline_rows = sorted(spacecraft_timeline_rows, key=itemgetter(0))
		description = [("spacecraft", "string"), ("station", "string"), ("start", "datetime"), ("end", "datetime")]
		data_table = gviz_api.DataTable(description)
		data_table.LoadData(spacecraft_timeline_rows)
		json_string = data_table.ToJSon()
		console_logger.debug(pprint.pformat(json_string))
		output_file = open(parent_folder + "dashboard/spacecraftTimelineData.json", "w")
		output_file.write(json_string)
		output_file.close()
		
		
		# Range Chart
		description = [("spacecraft", "string"), ("", "number")]
		chart_row_list = []
		query = "SELECT DISTINCT spacecraft_name FROM DSNMonitorRecent ORDER BY spacecraft_name"
		cursor.execute(query)
		spacecraft_query_res = cursor.fetchall()
		for spacecraft_row in spacecraft_query_res:
			query = "SELECT * FROM DSNMonitorRecent WHERE spacecraft_name = \"" + spacecraft_row["spacecraft_name"] + "\" AND spacecraft_range > 0 AND spacecraft_range < 20000000 ORDER BY timestamp DESC LIMIT 1"
			cursor.execute(query)
			range_query_res = cursor.fetchall()
			for range_row in range_query_res:
				chart_row_list.append([range_row["spacecraft_name"], range_row["spacecraft_range"]])
		console_logger.debug(chart_row_list)
		
		data_table = gviz_api.DataTable(description)
		data_table.LoadData(chart_row_list)
		json_string = data_table.ToJSon()
		console_logger.debug(pprint.pformat(json_string))
		output_file = open(parent_folder + "dashboard/rangeDataEarth.json", "w")
		output_file.write(json_string)
		output_file.close()
		
		chart_row_list = []
		for spacecraft_row in spacecraft_query_res:
			query = "SELECT * FROM DSNMonitorRecent WHERE spacecraft_name = \"" + spacecraft_row["spacecraft_name"] + "\" AND spacecraft_range >= 2000000 AND spacecraft_range < 1000000000 ORDER BY timestamp DESC LIMIT 1"
			cursor.execute(query)
			range_query_res = cursor.fetchall()
			for range_row in range_query_res:
				chart_row_list.append([range_row["spacecraft_name"], range_row["spacecraft_range"]])
		console_logger.debug(chart_row_list)
		
		data_table = gviz_api.DataTable(description)
		data_table.LoadData(chart_row_list)
		json_string = data_table.ToJSon()
		console_logger.debug(pprint.pformat(json_string))
		output_file = open(parent_folder + "dashboard/rangeDataSolarSystem.json", "w")
		output_file.write(json_string)
		output_file.close()
		
		chart_row_list = []
		for spacecraft_row in spacecraft_query_res:
			query = "SELECT * FROM DSNMonitorRecent WHERE spacecraft_name = \"" + spacecraft_row["spacecraft_name"] + "\" AND spacecraft_range >= 1000000000 ORDER BY timestamp DESC LIMIT 1"
			cursor.execute(query)
			range_query_res = cursor.fetchall()
			for range_row in range_query_res:
				chart_row_list.append([range_row["spacecraft_name"], range_row["spacecraft_range"]])
		console_logger.debug(chart_row_list)
		
		data_table = gviz_api.DataTable(description)
		data_table.LoadData(chart_row_list)
		json_string = data_table.ToJSon()
		console_logger.debug(pprint.pformat(json_string))
		output_file = open(parent_folder + "dashboard/rangeDataBeyond.json", "w")
		output_file.write(json_string)
		output_file.close()
						
						
		console_logger.info("Dashboard data generated.")
		file_logger.info("Dashboard data generated.")
	

except Exception as e:
	console_logger.exception(e)
	file_logger.exception(e)