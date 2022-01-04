# ************************************************************
# Sequel Ace SQL dump
# Version 20021
#
# https://sequel-ace.com/
# https://github.com/Sequel-Ace/Sequel-Ace
#
# Host: 192.168.178.27 (MySQL 5.5.5-10.3.31-MariaDB-0+deb10u1)
# Database: PiDB
# Generation Time: 2022-01-04 20:56:59 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
SET NAMES utf8mb4;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE='NO_AUTO_VALUE_ON_ZERO', SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table DSNMonitor
# ------------------------------------------------------------

CREATE TABLE `DSNMonitor` (
  `timestamp` timestamp NULL DEFAULT NULL,
  `antenna` varchar(10) DEFAULT NULL,
  `spacecraft_name` varchar(50) DEFAULT NULL,
  `data_rate` float DEFAULT NULL,
  `frequency` float DEFAULT NULL,
  `power` float DEFAULT NULL,
  `azimuth` float DEFAULT NULL,
  `elevation` float DEFAULT NULL,
  `spacecraft_range` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table DSNMonitorDaily
# ------------------------------------------------------------

CREATE TABLE `DSNMonitorDaily` (
  `timestamp` timestamp NULL DEFAULT NULL,
  `antenna` varchar(10) DEFAULT NULL,
  `spacecraft_name` varchar(50) DEFAULT NULL,
  `data_rate` float DEFAULT NULL,
  `frequency` float DEFAULT NULL,
  `power` float DEFAULT NULL,
  `azimuth` float DEFAULT NULL,
  `elevation` float DEFAULT NULL,
  `spacecraft_range` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table DSNMonitorRecent
# ------------------------------------------------------------

CREATE TABLE `DSNMonitorRecent` (
  `timestamp` timestamp NULL DEFAULT NULL,
  `antenna` varchar(10) DEFAULT NULL,
  `spacecraft_name` varchar(50) DEFAULT NULL,
  `data_rate` float DEFAULT NULL,
  `frequency` float DEFAULT NULL,
  `power` float DEFAULT NULL,
  `azimuth` float DEFAULT NULL,
  `elevation` float DEFAULT NULL,
  `spacecraft_range` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
