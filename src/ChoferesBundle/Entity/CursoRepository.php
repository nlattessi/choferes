<?php
namespace ChoferesBundle\Entity;

use Doctrine\ORM\EntityRepository;

class CursoRepository extends EntityRepository
{
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
            AND c.fechaInicio < :to";

        $query = $em->createQuery($dql);
        $query->setParameter('from', $from);
        $query->setParameter('to', $to);

        $cursos = $query->getResult();

        return $cursos;
    }
}
