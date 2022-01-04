import pprint
import pymysql
import argparse
import subprocess
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



try:
	file_logger.info("Fetch started.")
	if not script_running():
		conn = pymysql.connect(host=DB_HOST, port=3306, user=DB_USER, passwd=DB_PASSWORD, db=DB_NAME, charset="utf8", cursorclass=pymysql.cursors.DictCursor)
		cursor = conn.cursor()
		
		current_timestamp = datetime.now()
		if current_timestamp.minute <= 57:
			current_timestamp = current_timestamp.replace(minute=int(float(current_timestamp.minute)/FETCH_INTERVAL)*FETCH_INTERVAL)
		else:
			current_timestamp = current_timestamp + timedelta(minutes=60-current_timestamp.minute)
	
		DSNDict = list()
		DSNData = requests.get("https://eyes.nasa.gov/dsn/data/dsn.xml?r="+str(round(float(current_timestamp.strftime("%s"))/5.0)))
		console_logger.debug("https://eyes.nasa.gov/dsn/data/dsn.xml?r="+str(round(float(current_timestamp.strftime("%s"))/5.0)))
		DSNXML = ElementTree.fromstring(DSNData.content)
	
		for i in range(0,len(DSNXML)):
			console_logger.debug(DSNXML[i].tag)
			console_logger.debug(pprint.pformat(DSNXML[i].attrib))
			if DSNXML[i].tag=="dish":
				DSNDict.append(DSNXML[i].attrib)
				pprint.pprint(DSNDict)
				k = 0
				for j in range(0,len(DSNXML[i])):
					if DSNXML[i][j].tag == "downSignal":
						if DSNXML[i][j].attrib["signalType"] == "data":
							if k == 0:
								DSNDict[len(DSNDict)-1]["downSignal"] = []
							DSNDict[len(DSNDict)-1]["downSignal"].append(DSNXML[i][j].attrib)
							k = k + 1
					else:
						DSNDict[len(DSNDict)-1][DSNXML[i][j].tag] = DSNXML[i][j].attrib
		
		for antenna_data in DSNDict:
			console_logger.debug(pprint.pformat(antenna_data))
			if "downSignal" in antenna_data:
				for down_signal in antenna_data["downSignal"]:
					if down_signal["signalType"] == "data":
						data_rate = float(down_signal["dataRate"]) if down_signal["dataRate"] not in ["", "null", "none"] else None
						frequency = float(down_signal["frequency"]) if down_signal["frequency"] not in ["", "null", "none"] else None
						power = float(down_signal["power"]) if down_signal["power"] not in ["", "null", "none"] else None
						azimuth = float(antenna_data["azimuthAngle"]) if antenna_data["azimuthAngle"] not in ["", "null", "none"] else None
						elevation = float(antenna_data["elevationAngle"]) if antenna_data["elevationAngle"] not in ["", "null", "none"] else None
						spacecraft_range = float(antenna_data["target"]["downlegRange"]) if antenna_data["target"]["downlegRange"] not in ["", "null", "none"] else None
						query = "INSERT INTO DSNMonitor (timestamp, antenna, spacecraft_name, data_rate, frequency, power, azimuth, elevation, spacecraft_range) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)"
						console_logger.debug(query % (current_timestamp.strftime("%Y-%m-%d %H:%M"), antenna_data["name"], down_signal["spacecraft"], data_rate, frequency, power, azimuth, elevation, spacecraft_range))
						cursor.execute(query, (current_timestamp.strftime("%Y-%m-%d %H:%M"), antenna_data["name"], down_signal["spacecraft"], data_rate, frequency, power, azimuth, elevation, spacecraft_range))
						conn.commit()
						query = "INSERT INTO DSNMonitorRecent (timestamp, antenna, spacecraft_name, data_rate, frequency, power, azimuth, elevation, spacecraft_range) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)"
						cursor.execute(query, (current_timestamp.strftime("%Y-%m-%d %H:%M"), antenna_data["name"], down_signal["spacecraft"], data_rate, frequency, power, azimuth, elevation, spacecraft_range))
						conn.commit()
		
		query = "DELETE FROM DSNMonitorRecent WHERE timestamp < DATE_SUB(CURDATE(), INTERVAL 3 DAY)"
		cursor.execute(query)
		conn.commit()

						
		console_logger.info("DSNNow data fetched.")
		file_logger.info("DSNNow data fetched.")
	

except Exception as e:
	console_logger.exception(e)
	file_logger.exception(e)