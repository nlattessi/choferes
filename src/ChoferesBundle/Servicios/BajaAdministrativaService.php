<?php

namespace ChoferesBundle\Servicios;

use Doctrine\ORM\EntityManager;

class BajaAdministrativaService
{
    protected $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    public function darDeBaja($entity)
    {
        $entity->setActivo(false);
        $this->em->persist($entity);
        $this->em->flush();
    }

    public function darDeBajaPrestador($prestador)
    {
        $docentes = $this->em->getRepository('ChoferesBundle:Docente')->findBy(array('prestador' => $prestador, 'activo' => true));
        foreach ($docentes as $docente) {
            $this->darDeBaja($docente);
        }

        $sedes = $this->em->getRepository('ChoferesBundle:Sede')->findBy(array('prestador' => $prestador, 'activo' => true));
        foreach ($sedes as $sede) {
            $this->darDeBaja($sede);
        }

        $usuarioPrestador = $this->em->getRepository('ChoferesBundle:UsuarioPrestador')->findOneBy(array('prestador' => $prestador));
        $usuario = $usuarioPrestador->getUsuario();
        $usuario->setActivo(false);
        $this->em->persist($usuario);
        $this->em->flush();

        $this->darDeBaja($prestador);
    }
}
