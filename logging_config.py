import os
import sys
import pwd
import grp
import requests
import logging
import logging.handlers

# Disable requests warnings
requests.packages.urllib3.disable_warnings()
logging.getLogger("requests").setLevel(logging.WARNING)
logging.getLogger("requests").addHandler(logging.NullHandler())

# Create log folder
parent_folder = os.path.dirname(os.path.realpath(sys.argv[0])) + "/"
if not os.path.exists(parent_folder + "logs/"):
    os.makedirs(parent_folder + "logs/")

# Set log file name and permissions
log_filename = parent_folder + "logs/" + os.path.basename(os.path.normpath(parent_folder)) + ".log"
try:
    file = open(log_filename, "r")
except IOError:
    file = open(log_filename, "w")
if pwd.getpwuid(os.stat(log_filename).st_uid).pw_name != "pi":
	uid = pwd.getpwnam("pi").pw_uid
	gid = grp.getgrnam("staff").gr_gid
	os.chown(log_filename, uid, gid)

# File logger
file_logger = logging.getLogger("my_file_logger")
file_logger.setLevel(logging.DEBUG)
file_logger.propagate = 0
file_formatter = logging.Formatter("%(asctime)s\t%(message)s", datefmt="%Y-%m-%d %H:%M:%S")
file_handler = logging.handlers.TimedRotatingFileHandler(log_filename, when="midnight", backupCount=7)
file_handler.setLevel(logging.INFO)
file_handler.setFormatter(file_formatter)
file_logger.addHandler(file_handler)

# Console logger
console_logger = logging.getLogger("my_console_logger")
console_logger.setLevel(logging.DEBUG)
console_logger.propagate = 0
console_formatter = logging.Formatter("%(asctime)s\t%(message)s", datefmt="%Y-%m-%d %H:%M:%S")
console_handler = logging.StreamHandler(sys.stdout)
console_handler.setFormatter(console_formatter)
console_logger.addHandler(console_handler)