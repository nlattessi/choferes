SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

LOCK TABLES `choferes`.`rol` WRITE;
/*!40000 ALTER TABLE `choferes`.`rol` DISABLE KEYS */;
INSERT INTO `choferes`.`rol` (id, nombre) VALUES (1,'ROLE_ADMIN');
INSERT INTO `choferes`.`rol` (id, nombre) VALUES (2,'ROLE_CNTSV');
INSERT INTO `choferes`.`rol` (id, nombre) VALUES (3,'ROLE_PRESTADOR');
INSERT INTO `choferes`.`rol` (id, nombre) VALUES (4,'ROLE_CNRT');
INSERT INTO `choferes`.`rol` (id, nombre) VALUES (5,'ROLE_CENT');
/*!40000 ALTER TABLE `choferes`.`rol` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `choferes`.`usuario` WRITE;
/*!40000 ALTER TABLE `choferes`.`usuario` DISABLE KEYS */;
INSERT INTO `choferes`.`usuario` (id, nombre, mail, password, rol_id, activo) VALUES (1,'admin','admin@admin.com','$2a$12$7hFGMobKDxo5tthJcgxjDei1dxBdR5nJJ7HwghnTSn3p7yXE9sUxq',1,TRUE);
/*!40000 ALTER TABLE `choferes`.`usuario` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `choferes`.`tipo_curso` WRITE;
/*!40000 ALTER TABLE `choferes`.`tipo_curso` DISABLE KEYS */;
INSERT INTO `choferes`.`tipo_curso` (id, nombre) VALUES (1,'basico');
INSERT INTO `choferes`.`tipo_curso` (id, nombre) VALUES (2,'complementario');
/*!40000 ALTER TABLE `choferes`.`tipo_curso` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `choferes`.`estado_curso` WRITE;
/*!40000 ALTER TABLE `choferes`.`estado_curso` DISABLE KEYS */;
INSERT INTO `choferes`.`estado_curso` (id, nombre) VALUES (1,'Cargado');
INSERT INTO `choferes`.`estado_curso` (id, nombre) VALUES (2,'Confirmado');
INSERT INTO `choferes`.`estado_curso` (id, nombre) VALUES (3,'Por Validar');
INSERT INTO `choferes`.`estado_curso` (id, nombre) VALUES (4,'Cancelado');
INSERT INTO `choferes`.`estado_curso` (id, nombre) VALUES (5,'Validado');
/*!40000 ALTER TABLE `choferes`.`estado_curso` ENABLE KEYS */;
UNLOCK TABLES;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
