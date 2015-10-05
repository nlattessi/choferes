<?php

namespace ChoferesBundle\Servicios;

use Doctrine\ORM\EntityManager;

class UsuarioService
{
    protected $em;
    private $idRolPrestador;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
        $this->idRolPrestador = 3;
    }

    public function obtenerPrestadorPorUsuario($usuario)
    {
        $usuarioPrestador = $this->em->getRepository('ChoferesBundle:UsuarioPrestador')->findOneBy(array('usuario' => $usuario));

        return $usuarioPrestador->getPrestador();
    }

    public function obtenerDocentesPorPrestador($prestador)
    {
        if ($prestador) {
            $docentes = $this->em->getRepository('ChoferesBundle:Docente')->findBy(array('prestador' => $prestador, 'activo' => true));
        } else {
          $docentes = null;
        }
        return $docentes;
    }

    public function obtenerSedesPorPrestador($prestador)
    {
        if ($prestador) {
            $sedes = $this->em->getRepository('ChoferesBundle:Sede')->findBy(array('prestador' => $prestador));
        } else {
          $sedes = null;
        }
        return $sedes;
    }

    public function obtenerUsuariosRolPrestador()
    {
        $queryBuilder = $this->em->getRepository('ChoferesBundle:Usuario')->createQueryBuilder('u')
            ->where('u.rol = ?1')
            ->setParameter(1, $this->idRolPrestador );
       return $queryBuilder->getQuery()->getResult();
    }
}
