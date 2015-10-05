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

    public function darDeBajaDocente($docente)
    {
        $docente->setActivo(false);
        $this->em->persist($docente);
        $this->em->flush();
    }
}
