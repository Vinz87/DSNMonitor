import pymysql

from config import *

from logging_config import *
console_handler.setLevel(logging.INFO)



try:
	file_logger.info("History data generation started.")
	
	conn = pymysql.connect(host=DB_HOST, port=3306, user=DB_USER, passwd=DB_PASSWORD, db=DB_NAME, charset="utf8", cursorclass=pymysql.cursors.DictCursor)
	cursor = conn.cursor()
	cursor.execute("TRUNCATE DSNMonitorDaily")
	conn.commit()
	cursor.execute("INSERT INTO DSNMonitorDaily SELECT * FROM DSNMonitor GROUP BY date(timestamp), spacecraft_name")
	conn.commit()
	
	console_logger.info("History data generated.")
	file_logger.info("History data generated.")
	
	
except Exception as e:
	console_logger.exception(e)
	file_logger.exception(e)