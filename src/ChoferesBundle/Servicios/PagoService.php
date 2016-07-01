<?php

namespace ChoferesBundle\Servicios;

use Doctrine\ORM\EntityManager;


class PagoService
{
    private $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    public function setCursoMontoTotal($curso)
    {
        $choferCursos = $curso->getChoferCursos();
        $cantChoferes = sizeof($choferCursos);

        $montoTipoCurso = $curso->getTipoCurso()->getCanon()->getMonto();
        $montoTotal = $cantChoferes * $montoTipoCurso;

        $curso->setMontoTotal($montoTotal);

        $this->em->persist($curso);
        $this->em->flush();

        return $curso;
    }

    public function setCursoMontoRecaudado($curso){
        $monto = 0;
        foreach($curso->getComprobantesPago() as $comprobante){
            $monto += $comprobante->getMonto();
        }
        $curso->setMontoRecaudado($monto);
        $this->em->persist($curso);
        $this->em->flush();

        return $curso;
    }
}
