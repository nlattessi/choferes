LOCK TABLES `choferes`.`rol` WRITE;
/*!40000 ALTER TABLE `choferes`.`rol` DISABLE KEYS */;
INSERT INTO `choferes`.`rol` VALUES (1,'ROLE_ADMIN');
INSERT INTO `choferes`.`rol` VALUES (2,'ROLE_CNTSV');
INSERT INTO `choferes`.`rol` VALUES (3,'ROLE_PRESTADOR');
INSERT INTO `choferes`.`rol` VALUES (4,'ROLE_CNRT');
INSERT INTO `choferes`.`rol` VALUES (5,'ROLE_CENT');
/*!40000 ALTER TABLE `choferes`.`rol` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `choferes`.`usuario` WRITE;
/*!40000 ALTER TABLE `choferes`.`usuario` DISABLE KEYS */;
INSERT INTO `choferes`.`usuario` VALUES (1,'admin','admin@admin.com','$2a$12$7hFGMobKDxo5tthJcgxjDei1dxBdR5nJJ7HwghnTSn3p7yXE9sUxq',1,TRUE);
INSERT INTO `choferes`.`usuario` VALUES (2,'cntsv','cntsv@cntsv.com','$2a$12$T62s8mN630dp7vo1z9Q7x.Yhp1oKRYGGpyqQcrLae0S3UP4uO0./C',2,TRUE);

INSERT INTO `choferes`.`usuario` VALUES (4,'cnrt','cnrt@cnrt.com','$2a$12$CIrGYbaXxpxbLqL3YMNLYuGIar6gPfKgT19cxQO32hc7Bh93T0Ru2',4,TRUE);
INSERT INTO `choferes`.`usuario` VALUES (5,'cent','cent@cent.com','$2a$12$wlu8mAWum4byLBiBShoqxuT3.DZ4TBzj7JJNzWXvEveSAmajKKsii',5,TRUE);
/*!40000 ALTER TABLE `choferes`.`usuario` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `choferes`.`tipo_curso` WRITE;
/*!40000 ALTER TABLE `choferes`.`tipo_curso` DISABLE KEYS */;
INSERT INTO `choferes`.`tipo_curso` VALUES (1,'basico');
INSERT INTO `choferes`.`tipo_curso` VALUES (2,'complementario');
/*!40000 ALTER TABLE `choferes`.`tipo_curso` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `choferes`.`estado_curso` WRITE;
/*!40000 ALTER TABLE `choferes`.`estado_curso` DISABLE KEYS */;
INSERT INTO `choferes`.`estado_curso` VALUES (1,'Cargado');
INSERT INTO `choferes`.`estado_curso` VALUES (2,'Confirmado');
INSERT INTO `choferes`.`estado_curso` VALUES (3,'Por Validar');
INSERT INTO `choferes`.`estado_curso` VALUES (4,'Cancelado');
INSERT INTO `choferes`.`estado_curso` VALUES (5,'Validado');