<?php
namespace ChoferesBundle\Entity;

use Doctrine\ORM\EntityRepository;

class CursoRepository extends EntityRepository
{
    const ESTADO_CURSO_DEFAULT = 1;
    const ESTADO_CURSO_CONFIRMADO = 2;
    const ESTADO_CURSO_PORVALIDAR = 3;
    const ESTADO_CURSO_CANCELADO = 4;
    const ESTADO_CURSO_VALIDADO = 5;
    const ESTADO_CURSO_FALLAVALIDACION = 6;

    public function findCursosByTipoFilterByFechaInicio($tipo, $from, $to)
    {
        $em = $this->getEntityManager();

        $dql = "SELECT c
            FROM ChoferesBundle:Curso c
            WHERE c.tipocurso = :tipoCurso
            AND c.fechaInicio >= :from
            AND c.fechaInicio < :to";

        $query = $em->createQuery($dql);
        $query->setParameter('tipoCurso', $tipo);
        $query->setParameter('from', $from);
        $query->setParameter('to', $to);

        $cursos = $query->getResult();

        return $cursos;
    }

    public function findCursosFilterByFechaInicio($from, $to)
    {
        $em = $this->getEntityManager();

        $dql = "SELECT c
            FROM ChoferesBundle:Curso c
            WHERE c.fechaInicio >= :from
            AND c.fechaInicio < :to
            AND c.tipocurso IS NOT NULL";
            // AND c.estado != :estadoCancelado";

        $query = $em->createQuery($dql);
        $query->setParameter('from', $from);
        $query->setParameter('to', $to);
        // $query->setParameter('estadoCancelado', self::ESTADO_CURSO_CANCELADO);

        $cursos = $query->getResult();

        return $cursos;
    }
}
