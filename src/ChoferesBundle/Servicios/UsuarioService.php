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

    public function obtenerPrestadoresActivos()
    {
        $prestadores = $this->em->getRepository('ChoferesBundle:Prestador')->findBy(array('activo' => true));

        return $prestadores;
    }

    public function obtenerEstados()
    {
        $estados = $this->em->getRepository('ChoferesBundle:EstadoCurso')->findAll();

        return $estados;
    }

    public function obtenerPrestadorPorUsuario($usuario)
    {
        $usuarioPrestador = $this->em->getRepository('ChoferesBundle:UsuarioPrestador')->findOneBy(array('usuario' => $usuario));

        if (isset($usuarioPrestador)) {
            return $usuarioPrestador->getPrestador();
        }

        return $usuarioPrestador;
    }

    public function obtenerDocentesPorPrestador($prestador)
    {
        if ($prestador) {
            $docentes = $this->em->getRepository('ChoferesBundle:Docente')
                ->findBy(
                    ['prestador' => $prestador, 'activo' => true],
                    ['apellido' => 'ASC', 'nombre' => 'ASC']
                );
        }
        else {
            $docentes = null;
        }

        return $docentes;
    }

    public function obtenerSedesPorPrestador($prestador)
    {
        if ($prestador) {
            $sedes = $this->em->getRepository('ChoferesBundle:Sede')->findBy(array('prestador' => $prestador, 'activo' => true));
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
