<?php
/**
 * Created by PhpStorm.
 * User: fede
 * Date: 04-Jul-15
 * Time: 01:28 PM
 */

class UsuarioService {

    protected $em;

    public function __construct(EntityManager $entityManager){
        $this->em = $entityManager;
    }

    public function obtenerPrestadorPorUsuario($usuario){

        $prestador = $this->$em->getRepository('ChoferesBundle:UsuarioPrestador')->findOneBy(array('usuario' => $usuario));

        return $prestador;

    }
}