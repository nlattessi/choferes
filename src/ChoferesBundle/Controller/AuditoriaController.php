<?php

namespace ChoferesBundle\Controller;

use Doctrine\ORM\Query\Expr\Join;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\View\TwitterBootstrapView;

use ChoferesBundle\Entity\Auditoria;
use ChoferesBundle\Entity\EstadoAuditoria;
use ChoferesBundle\Entity\EstadoCurso;


/**
 * Auditoria controller.
 *
 */
class AuditoriaController extends Controller
{
    /**
     * Cursos para auditar.
     *
     */
    public function indexAction()
    {
        $auditorias = $this->getDoctrine()->getRepository('ChoferesBundle:Auditoria')->findAll();

        return $this->render('ChoferesBundle:Auditoria:index.html.twig', [
            'auditorias' => $auditorias,
            'pagetitle' => 'Auditorias',
            'css_active' => 'auditorias',
        ]);
    }

    public function indexAuditoriasActivasAction()
    {
        $auditorias = $this->getDoctrine()->getRepository('ChoferesBundle:Auditoria')
            ->findByEstado(EstadoAuditoria::ID_ACTIVA);

        return $this->render('ChoferesBundle:Auditoria:index.html.twig', [
            'auditorias' => $auditorias,
            'pagetitle' => 'Auditorias Activas',
            'css_active' => 'auditoria_activas',
        ]);
    }

    public function indexAuditoriasIntermedioAction()
    {
        $auditorias = $this->getDoctrine()->getRepository('ChoferesBundle:Auditoria')
            ->findByEstado(EstadoAuditoria::ID_INTERMEDIO);

        return $this->render('ChoferesBundle:Auditoria:index.html.twig', [
            'auditorias' => $auditorias,
            'pagetitle' => 'Auditorias en estado Intermedio',
            'css_active' => 'auditoria_intermedio',
        ]);
    }

    public function indexAuditoriasTerminadasAction()
    {
        $auditorias = $this->getDoctrine()->getRepository('ChoferesBundle:Auditoria')
            ->findByEstado(EstadoAuditoria::ID_TERMINADA);

        return $this->render('ChoferesBundle:Auditoria:index.html.twig', [
            'auditorias' => $auditorias,
            'pagetitle' => 'Auditorias Terminadas',
            'css_active' => 'auditoria_terminadas',
        ]);
    }

    public function showAction($id)
    {
        $auditoria = $this->getDoctrine()->getRepository('ChoferesBundle:Auditoria')->find($id);

        if (!$auditoria) {
            throw $this->createNotFoundException('Unable to find Auditoria entity.');
        }

        return $this->render('ChoferesBundle:Auditoria:show.html.twig', [
            'auditoria'  => $auditoria,
            'css_active' => 'auditoria',
        ]);
    }

    public function newAction()
    {
        $prestadoresCursos = [];

        $repository = $this->getDoctrine()->getRepository('ChoferesBundle:Curso');

        $prestadoresEntities = $this->getDoctrine()->getRepository('ChoferesBundle:Prestador')->findAll();
        foreach ($prestadoresEntities as $prestador) {

            $yaAuditados = $repository->createQueryBuilder('c')
                ->innerJoin('ChoferesBundle:CursoAuditoria', 'ca', Join::WITH, 'ca.curso = c.id')
                ->where('c.estado = :estadoConfirmado')
                ->andWhere('c.prestador = :prestador')
                ->andWhere('c.prestador = :prestador')
                ->setParameter('estadoConfirmado', EstadoCurso::ID_CONFIRMADO)
                ->setParameter('prestador', $prestador->getId())
                ->getQuery()
                ->getResult();

            print_r($yaAuditados[0]->getId());die;

            $total = $repository->createQueryBuilder('c')
                ->select('COUNT(c.id)')
                ->where('c.estado = :estadoConfirmado')
                ->andWhere('c.prestador = :prestador')
                ->setParameter('estadoConfirmado', EstadoCurso::ID_CONFIRMADO)
                ->setParameter('prestador', $prestador->getId())
                ->getQuery()
                ->getSingleScalarResult();

            $max = (int)($total * 0.2); // 20%

            $query = $repository->createQueryBuilder('c')
                ->where('c.estado = :estadoConfirmado')
                ->andWhere('c.prestador = :prestador')
                ->orderBy('RAND()')
                ->setParameter('estadoConfirmado', EstadoCurso::ID_CONFIRMADO)
                ->setParameter('prestador', $prestador->getId())
                ->setMaxResults($max)
                ->getQuery();

            $prestadoresCursos[] = [
                'prestador' => $prestador,
                'cursos' => $query->getResult(),
            ];
        }

        // $query = $repository->createQueryBuilder('c')
        //     ->where('c.estado = :estadoConfirmado')
        //     ->orderBy('RAND()')
        //     ->setParameter('estadoConfirmado', EstadoCurso::ID_CONFIRMADO)
        //     ->setMaxResults(10)
        //     ->getQuery();

        // $cursos = $query->getResult();

        return $this->render('ChoferesBundle:Auditoria:new.html.twig', [
            'prestadoresCursos' => $prestadoresCursos,
            'css_active'  => 'auditoria_new',
        ]);
    }

    public function createAction()
    {

    }
}
