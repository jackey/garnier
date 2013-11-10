-- MySQL dump 10.13  Distrib 5.5.29, for debian-linux-gnu (i686)
--
-- Host: localhost    Database: garnier
-- ------------------------------------------------------
-- Server version	5.5.29-0ubuntu0.12.04.1-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `photo`
--

DROP TABLE IF EXISTS `photo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `photo` (
  `photo_id` int(10) NOT NULL AUTO_INCREMENT,
  `path` varchar(200) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `vote` int(11) DEFAULT NULL,
  `datetime` datetime DEFAULT NULL,
  PRIMARY KEY (`photo_id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `photo`
--

LOCK TABLES `photo` WRITE;
/*!40000 ALTER TABLE `photo` DISABLE KEYS */;
INSERT INTO `photo` VALUES (7,'/uploads/tmp/tmp541321383744027.jpg',23,0,'2013-11-06 09:11:10'),(8,'/uploads/陈子文/1383744260_5d6034a85edf8db12056faaf0923dd54574e7485.jpg',23,0,'2013-11-06 09:11:21'),(9,'/uploads/陈子文/1383744297_5d6034a85edf8db12056faaf0923dd54574e7485.jpg',23,0,'2013-11-06 09:11:57'),(10,'/uploads/陈子文/1383745035_5d6034a85edf8db12056faaf0923dd54574e7485.jpg',23,0,'2013-11-06 09:11:15'),(11,'/uploads/陈子文/1383745180_5d6034a85edf8db12056faaf0923dd54574e7485.jpg',23,0,'2013-11-06 09:11:41'),(12,'/uploads/陈子文/1383745218_CategoryPicture_23.jpg',23,0,'2013-11-06 09:11:18'),(13,'/uploads/陈子文/1383746711_Screenshot from 2013-09-01 17:20:51.png',23,0,'2013-11-06 10:11:12'),(14,'/uploads/tmp/tmp430391383749289.png',23,0,'2013-11-06 10:11:16'),(15,'/uploads/陈子文/1383749484_Screenshot from 2013-09-01 17:20:51.png',23,0,'2013-11-06 10:11:24'),(16,'/uploads/陈子文/1383749635_Screenshot from 2013-09-01 17:20:51.png',23,0,'2013-11-06 10:11:55'),(17,'/uploads/陈子文/1383749673_Screenshot from 2013-09-01 17:20:51.png',23,0,'2013-11-06 10:11:34'),(18,'/uploads/陈子文/1383749730_Screenshot from 2013-09-01 17:20:51.png',23,0,'2013-11-06 10:11:30'),(19,'/uploads/陈子文/1383749778_Screenshot from 2013-09-01 17:20:51.png',23,0,'2013-11-06 10:11:18'),(20,'/uploads/陈子文/1383749867_Screenshot from 2013-09-01 17:20:51.png',23,0,'2013-11-06 10:11:47'),(21,'/uploads/陈子文/1383749925_Screenshot from 2013-09-01 17:20:51.png',23,0,'2013-11-06 10:11:45'),(22,'/uploads/陈子文/1383749957_Screenshot from 2013-09-01 17:20:51.png',23,0,'2013-11-06 10:11:17');
/*!40000 ALTER TABLE `photo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `user_id` int(10) NOT NULL AUTO_INCREMENT,
  `nickname` varchar(45) DEFAULT NULL,
  `password` varchar(45) DEFAULT NULL,
  `from` varchar(45) DEFAULT NULL,
  `email` varchar(45) DEFAULT NULL,
  `tel` varchar(45) DEFAULT NULL,
  `datetime` datetime DEFAULT NULL,
  `avadar` varchar(100) DEFAULT '0',
  `weibo_auth_code` varchar(30) DEFAULT '',
  `tencent_auth_code` varchar(30) DEFAULT '',
  `renren_auth_code` varchar(30) DEFAULT '',
  `weibo_name` varchar(200) DEFAULT '',
  `tencent_name` varchar(200) DEFAULT '',
  `renren_name` varchar(200) DEFAULT '',
  `sns_user_id` varchar(50) DEFAULT '',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (23,'陈子文','21232f297a57a5a743894a0e4a801fc3','tencent','admin','1523232','2013-11-06 11:09:09','','','7ce68caf99777740b5c30057e39bce','','','','','3AFAFCBA888C78C9478A98B62F6A5A3B');
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vote`
--

DROP TABLE IF EXISTS `vote`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vote` (
  `user_id` int(11) NOT NULL,
  `photo_id` varchar(45) DEFAULT NULL,
  `datetime` datetime DEFAULT NULL,
  `vote_id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`vote_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vote`
--

LOCK TABLES `vote` WRITE;
/*!40000 ALTER TABLE `vote` DISABLE KEYS */;
/*!40000 ALTER TABLE `vote` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2013-11-10 10:47:01
