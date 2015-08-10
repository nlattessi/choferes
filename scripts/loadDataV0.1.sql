LOCK TABLES `choferes`.`usuario` WRITE;
/*!40000 ALTER TABLE `choferes`.`usuario` DISABLE KEYS */;
INSERT INTO `choferes`.`usuario` VALUES (2,'cntsv','cntsv@cntsv.com','$2a$12$T62s8mN630dp7vo1z9Q7x.Yhp1oKRYGGpyqQcrLae0S3UP4uO0./C',2,TRUE);
INSERT INTO `choferes`.`usuario` VALUES (3,'prestador','prestador@prestador.com','$2a$12$wl97GTNjoaa4CB6eWyr6beBw2nsaEgsc3.si1.wI6bYp64itpxCcS',3,TRUE);
INSERT INTO `choferes`.`usuario` VALUES (4,'cnrt','cnrt@cnrt.com','$2a$12$CIrGYbaXxpxbLqL3YMNLYuGIar6gPfKgT19cxQO32hc7Bh93T0Ru2',4,TRUE);
INSERT INTO `choferes`.`usuario` VALUES (5,'cent','cent@cent.com','$2a$12$wlu8mAWum4byLBiBShoqxuT3.DZ4TBzj7JJNzWXvEveSAmajKKsii',5,TRUE);
/*!40000 ALTER TABLE `choferes`.`usuario` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `choferes`.`tipo_curso` WRITE;
/*!40000 ALTER TABLE `choferes`.`tipo_curso` DISABLE KEYS */;
INSERT INTO `choferes`.`tipo_curso` VALUES (1,'basico');
INSERT INTO `choferes`.`tipo_curso` VALUES (2,'anual');
/*!40000 ALTER TABLE `choferes`.`tipo_curso` ENABLE KEYS */;
UNLOCK TABLES;
