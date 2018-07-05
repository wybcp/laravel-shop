-- MySQL dump 10.13  Distrib 5.7.20, for macos10.12 (x86_64)
--
-- Host: localhost    Database: laravel_shop
-- ------------------------------------------------------
-- Server version	5.7.20

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
-- Dumping data for table `admin_menu`
--

LOCK TABLES `admin_menu` WRITE;
/*!40000 ALTER TABLE `admin_menu` DISABLE KEYS */;
INSERT INTO `admin_menu` VALUES (1,0,1,'首页','fa-bar-chart','/',NULL,'2018-06-20 09:18:22'),(2,0,6,'系统管理','fa-tasks',NULL,NULL,'2018-07-03 02:12:06'),(3,2,7,'管理员','fa-users','auth/users',NULL,'2018-07-03 02:12:06'),(4,2,8,'角色','fa-user','auth/roles',NULL,'2018-07-03 02:12:06'),(5,2,9,'权限','fa-ban','auth/permissions',NULL,'2018-07-03 02:12:06'),(6,2,10,'菜单','fa-bars','auth/menu',NULL,'2018-07-03 02:12:06'),(7,2,11,'操作日志','fa-history','auth/logs',NULL,'2018-07-03 02:12:06'),(8,0,12,'运行日志','fa-database','logs','2018-06-21 07:00:43','2018-07-03 02:12:06'),(9,0,13,'Scheduling','fa-clock-o','scheduling','2018-06-21 07:04:09','2018-07-03 02:12:06'),(10,0,14,'Redis manager','fa-database','redis','2018-06-21 07:04:50','2018-07-03 02:12:06'),(11,0,2,'用户管理','fa-users','/users','2018-06-21 07:09:08','2018-06-21 07:09:15'),(12,0,3,'商品管理','fa-cubes','/products','2018-06-21 09:23:56','2018-06-21 09:24:03'),(13,0,4,'订单管理','fa-rmb','/orders','2018-06-29 07:16:20','2018-06-29 07:17:50'),(14,0,5,'优惠券','fa-bars','/coupon-codes','2018-07-03 02:11:58','2018-07-03 02:12:06'),(15,0,15,'Exception Reporter','fa-bug','exceptions','2018-07-05 01:35:03','2018-07-05 01:35:03');
/*!40000 ALTER TABLE `admin_menu` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `admin_permissions`
--

LOCK TABLES `admin_permissions` WRITE;
/*!40000 ALTER TABLE `admin_permissions` DISABLE KEYS */;
INSERT INTO `admin_permissions` VALUES (1,'All permission','*','','*',NULL,NULL),(2,'Dashboard','dashboard','GET','/',NULL,NULL),(3,'Login','auth.login','','/auth/login\r\n/auth/logout',NULL,NULL),(4,'User setting','auth.setting','GET,PUT','/auth/setting',NULL,NULL),(5,'Auth management','auth.management','','/auth/roles\r\n/auth/permissions\r\n/auth/menu\r\n/auth/logs',NULL,NULL),(6,'Logs','ext.log-viewer',NULL,'/logs*','2018-06-21 07:00:43','2018-06-21 07:00:43'),(7,'Scheduling','ext.scheduling',NULL,'/scheduling*','2018-06-21 07:04:09','2018-06-21 07:04:09'),(8,'Redis Manager','ext.redis-manager',NULL,'/redis*','2018-06-21 07:04:50','2018-06-21 07:04:50'),(9,'用户管理','users','','/user','2018-06-21 07:33:45','2018-06-21 07:33:45'),(10,'订单管理','orders','','/orders','2018-07-05 01:26:33','2018-07-05 01:26:33'),(11,'商品管理','products','','/products','2018-07-05 01:27:19','2018-07-05 01:27:19'),(12,'优惠券管理','coupon_codes','','/coupon_codes','2018-07-05 01:27:45','2018-07-05 01:27:45'),(13,'Exceptions reporter','ext.reporter',NULL,'/exceptions*','2018-07-05 01:35:03','2018-07-05 01:35:03');
/*!40000 ALTER TABLE `admin_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `admin_role_menu`
--

LOCK TABLES `admin_role_menu` WRITE;
/*!40000 ALTER TABLE `admin_role_menu` DISABLE KEYS */;
INSERT INTO `admin_role_menu` VALUES (1,2,NULL,NULL);
/*!40000 ALTER TABLE `admin_role_menu` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `admin_role_permissions`
--

LOCK TABLES `admin_role_permissions` WRITE;
/*!40000 ALTER TABLE `admin_role_permissions` DISABLE KEYS */;
INSERT INTO `admin_role_permissions` VALUES (1,1,NULL,NULL),(2,2,NULL,NULL),(2,3,NULL,NULL),(2,4,NULL,NULL),(2,6,NULL,NULL),(2,8,NULL,NULL),(2,9,NULL,NULL),(2,10,NULL,NULL),(2,11,NULL,NULL),(2,12,NULL,NULL);
/*!40000 ALTER TABLE `admin_role_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `admin_role_users`
--

LOCK TABLES `admin_role_users` WRITE;
/*!40000 ALTER TABLE `admin_role_users` DISABLE KEYS */;
INSERT INTO `admin_role_users` VALUES (1,1,NULL,NULL),(2,2,NULL,NULL);
/*!40000 ALTER TABLE `admin_role_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `admin_roles`
--

LOCK TABLES `admin_roles` WRITE;
/*!40000 ALTER TABLE `admin_roles` DISABLE KEYS */;
INSERT INTO `admin_roles` VALUES (1,'Administrator','administrator','2018-06-20 09:12:50','2018-06-20 09:12:50'),(2,'运营','operator','2018-06-21 07:35:46','2018-06-21 07:35:46');
/*!40000 ALTER TABLE `admin_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `admin_user_permissions`
--

LOCK TABLES `admin_user_permissions` WRITE;
/*!40000 ALTER TABLE `admin_user_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `admin_user_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `admin_users`
--

LOCK TABLES `admin_users` WRITE;
/*!40000 ALTER TABLE `admin_users` DISABLE KEYS */;
INSERT INTO `admin_users` VALUES (1,'admin','$2y$10$DdxA1Og4g2UvY/C0eZcBL.XVhRNCuRvqLIBZjNKdz9BmYxDkdb5fO','Administrator',NULL,'98FglGYug0LPz9nC6TiluVslICnkYxujP7Zzj9tFPymimNzxcKgpqjwoI7GG','2018-06-20 09:12:50','2018-06-20 09:12:50'),(2,'operator_001','$2y$10$1a/zXdW/EE2qzCx/a7R54eApNi57jP1Ikkj/Ur6qGlgSNVaVYbU6K','运营_001',NULL,'JLjn4JClFOa3zBBH7zPqWdZmcyhDDarhWR0HQGkKphXtuBkYlzJc80ElsC0V','2018-06-21 08:37:25','2018-06-21 08:37:25');
/*!40000 ALTER TABLE `admin_users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-07-05  9:58:56
