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
-- Current Database: `choferes`
--

/*!40000 DROP DATABASE IF EXISTS `choferes`*/;

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `choferes` /*!40100 DEFAULT CHARACTER SET latin1 */;

USE `choferes`;

--
-- Table structure for table `chofer`
--

DROP TABLE IF EXISTS `chofer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chofer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `apellido` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `dni` varchar(8) COLLATE utf8_unicode_ci NOT NULL,
  `precuil` varchar(2) COLLATE utf8_unicode_ci DEFAULT NULL,
  `colacuil` varchar(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cuil_empresa` varchar(11) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tiene_curso_basico` tinyint(1) NOT NULL DEFAULT '0',
  `matricula` varchar(18) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chofer`
--

LOCK TABLES `chofer` WRITE;
/*!40000 ALTER TABLE `chofer` DISABLE KEYS */;
/*!40000 ALTER TABLE `chofer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chofer_curso`
--

DROP TABLE IF EXISTS `chofer_curso`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chofer_curso` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `chofer_id` int(11) DEFAULT NULL,
  `curso_id` int(11) DEFAULT NULL,
  `is_aprobado` tinyint(1) NOT NULL DEFAULT '0',
  `pagado` tinyint(1) NOT NULL DEFAULT '0',
  `documentacion` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `chofer_curso_idx` (`chofer_id`),
  KEY `curso_chofer_idx` (`curso_id`),
  CONSTRAINT `FK_D3A6CA3787CB4A1F` FOREIGN KEY (`curso_id`) REFERENCES `curso` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_D3A6CA372A5E4E82` FOREIGN KEY (`chofer_id`) REFERENCES `chofer` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chofer_curso`
--

LOCK TABLES `chofer_curso` WRITE;
/*!40000 ALTER TABLE `chofer_curso` DISABLE KEYS */;
/*!40000 ALTER TABLE `chofer_curso` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `curso`
--

DROP TABLE IF EXISTS `curso`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `curso` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `docente_id` int(11) DEFAULT NULL,
  `estado_id` int(11) DEFAULT NULL,
  `prestador_id` int(11) DEFAULT NULL,
  `sede_id` int(11) DEFAULT NULL,
  `fecha_inicio` datetime NOT NULL,
  `fecha_fin` datetime NOT NULL,
  `fecha_creacion` datetime NOT NULL,
  `codigo` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `anio` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `comprobante` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fecha_pago` date DEFAULT NULL,
  `observaciones` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tipoCurso_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `prestador_curso_idx` (`prestador_id`),
  KEY `docente_curso_idx` (`docente_id`),
  KEY `tipoCurso_curso_idx` (`tipoCurso_id`),
  KEY `sede_curso_idx` (`sede_id`),
  KEY `estado_curso_idx` (`estado_id`),
  CONSTRAINT `FK_CA3B40EC190CE894` FOREIGN KEY (`tipoCurso_id`) REFERENCES `tipo_curso` (`id`),
  CONSTRAINT `FK_CA3B40EC94E27525` FOREIGN KEY (`docente_id`) REFERENCES `docente` (`id`),
  CONSTRAINT `FK_CA3B40EC9F5A440B` FOREIGN KEY (`estado_id`) REFERENCES `estado_curso` (`id`),
  CONSTRAINT `FK_CA3B40ECD2DB9D53` FOREIGN KEY (`prestador_id`) REFERENCES `prestador` (`id`),
  CONSTRAINT `FK_CA3B40ECE19F41BF` FOREIGN KEY (`sede_id`) REFERENCES `sede` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `curso`
--

LOCK TABLES `curso` WRITE;
/*!40000 ALTER TABLE `curso` DISABLE KEYS */;
/*!40000 ALTER TABLE `curso` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `docente`
--

DROP TABLE IF EXISTS `docente`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `docente` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prestador_id` int(11) DEFAULT NULL,
  `nombre` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `apellido` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `dni` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `activo` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `prestador_docente_idx` (`prestador_id`),
  CONSTRAINT `FK_FD9FCFA4D2DB9D53` FOREIGN KEY (`prestador_id`) REFERENCES `prestador` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `docente`
--

LOCK TABLES `docente` WRITE;
/*!40000 ALTER TABLE `docente` DISABLE KEYS */;
/*!40000 ALTER TABLE `docente` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `estado_curso`
--

DROP TABLE IF EXISTS `estado_curso`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `estado_curso` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `estado_curso`
--

LOCK TABLES `estado_curso` WRITE;
/*!40000 ALTER TABLE `estado_curso` DISABLE KEYS */;
INSERT INTO `estado_curso` VALUES (1,'Cargado'),(2,'Confirmado'),(3,'Por Validar'),(4,'Cancelado'),(5,'Validado');
/*!40000 ALTER TABLE `estado_curso` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `prestador`
--

DROP TABLE IF EXISTS `prestador`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `prestador` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `cuit` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `direccion` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `telefono` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mail` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `logo` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `activo` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `prestador`
--

LOCK TABLES `prestador` WRITE;
/*!40000 ALTER TABLE `prestador` DISABLE KEYS */;
/*!40000 ALTER TABLE `prestador` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rol`
--

DROP TABLE IF EXISTS `rol`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rol` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rol`
--

LOCK TABLES `rol` WRITE;
/*!40000 ALTER TABLE `rol` DISABLE KEYS */;
INSERT INTO `rol` VALUES (1,'ROLE_ADMIN'),(2,'ROLE_CNTSV'),(3,'ROLE_PRESTADOR'),(4,'ROLE_CNRT'),(5,'ROLE_CENT');
/*!40000 ALTER TABLE `rol` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sede`
--

DROP TABLE IF EXISTS `sede`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sede` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prestador_id` int(11) DEFAULT NULL,
  `nombre` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `direccion` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `provincia` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ciudad` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `telefono` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `activo` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `prestador_sede_idx` (`prestador_id`),
  CONSTRAINT `FK_2A9BE2D1D2DB9D53` FOREIGN KEY (`prestador_id`) REFERENCES `prestador` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sede`
--

LOCK TABLES `sede` WRITE;
/*!40000 ALTER TABLE `sede` DISABLE KEYS */;
/*!40000 ALTER TABLE `sede` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tipo_curso`
--

DROP TABLE IF EXISTS `tipo_curso`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tipo_curso` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tipo_curso`
--

LOCK TABLES `tipo_curso` WRITE;
/*!40000 ALTER TABLE `tipo_curso` DISABLE KEYS */;
INSERT INTO `tipo_curso` VALUES (1,'basico'),(2,'complementario');
/*!40000 ALTER TABLE `tipo_curso` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuario`
--

DROP TABLE IF EXISTS `usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuario` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rol_id` int(11) DEFAULT NULL,
  `nombre` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `mail` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `activo` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `rol_usuario_idx` (`rol_id`),
  CONSTRAINT `FK_2265B05D4BAB96C` FOREIGN KEY (`rol_id`) REFERENCES `rol` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuario`
--

LOCK TABLES `usuario` WRITE;
/*!40000 ALTER TABLE `usuario` DISABLE KEYS */;
INSERT INTO `usuario` VALUES (1,1,'admin','admin@admin.com','$2a$12$7hFGMobKDxo5tthJcgxjDei1dxBdR5nJJ7HwghnTSn3p7yXE9sUxq',1);
/*!40000 ALTER TABLE `usuario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuario_prestador`
--

DROP TABLE IF EXISTS `usuario_prestador`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuario_prestador` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prestador_id` int(11) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_prestador_idx` (`usuario_id`),
  KEY `prestador_usuario_idx` (`prestador_id`),
  CONSTRAINT `FK_B9A46544DB38439E` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`),
  CONSTRAINT `FK_B9A46544D2DB9D53` FOREIGN KEY (`prestador_id`) REFERENCES `prestador` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuario_prestador`
--

LOCK TABLES `usuario_prestador` WRITE;
/*!40000 ALTER TABLE `usuario_prestador` DISABLE KEYS */;
/*!40000 ALTER TABLE `usuario_prestador` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
