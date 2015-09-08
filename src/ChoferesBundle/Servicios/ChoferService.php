<?php

namespace ChoferesBundle\Servicios;

use Doctrine\ORM\EntityManager;

class ChoferService
{
    protected $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    public function getStatusPorDniChofer($dni)
    {
        $query = $this->em->createQueryBuilder()
            ->select(
                'chofer.id as choferId', 'chofer.nombre', 'chofer.apellido', 'chofer.dni', 'chofer.tieneCursoBasico',
                'choferCurso.id as choferCursoId', 'choferCurso.aprobado','choferCurso.pagado', 'choferCurso.documentacion',
                'curso.id as cursoId', 'curso.fechaFin'
            )
            ->from('ChoferesBundle:Chofer', 'chofer')
            ->leftJoin(
                'ChoferesBundle:ChoferCurso', 'choferCurso',
                \Doctrine\ORM\Query\Expr\Join::WITH, 'choferCurso.chofer = chofer.id'
            )
            ->leftJoin(
                'ChoferesBundle:Curso', 'curso',
                \Doctrine\ORM\Query\Expr\Join::WITH, 'choferCurso.curso = curso.id'
            )
            ->where('chofer.dni = :dni')
            ->orderBy('curso.fechaFin', 'DESC')
            ->setParameter('dni', $dni)
            ->getQuery();

        $status = $query->getOneOrNullResult();

        $result = array();

        if ($status) {
            $result['certificado'] = false;
            if ($status['tieneCursoBasico']) {
                if ($status['choferCursoId'] && $status['fechaFin'] > new \DateTime('-1 year')) {
                    if ($status['aprobado']) {
                        if ($status['pagado']) {
                            if ($status['documentacion']) {
                                $result['fechaFin'] = $status['fechaFin'];
                                $result['certificado'] = true;
                            } else {
                                $result['message'] = "No se cargo en sistema la documentacion correspondiente.";
                            }
                        } else {
                            $result['message'] = 'No figura pago el ultimo curso complementario.';
                        }
                    } else {
                        $result['message'] = 'No tiene aprobado el ultimo curso complementario.';
                    }
                } else {
                    $result['message'] = 'No tiene el curso complementario o la vigencia del mismo ya expiro.';
                }
            } else {
                $result['message'] = 'No tiene el curso basico.';
            }
        }

        return $result;
    }
}
