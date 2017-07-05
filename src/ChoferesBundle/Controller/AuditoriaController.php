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
        $repo = $this->getDoctrine()->getRepository('ChoferesBundle:Prestador');
        $query = $repo->createQueryBuilder('p')
            ->where('p.activo = true')
            ->getQuery();
        $prestadores = collect($query->getResult());

        $repo = $this->getDoctrine()->getRepository('ChoferesBundle:Curso');
        $query = $repo->createQueryBuilder('c')
                ->where('c.estado = :estadoConfirmado')
                ->setParameter('estadoConfirmado', EstadoCurso::ID_CONFIRMADO)
                ->getQuery();
        $cursos = collect($query->getResult());

        $minimoCursoPorPrestador = $prestadores->map(function ($prestador) use ($cursos) {
           return $cursos->filter(function ($curso) use ($prestador) {
                return $curso->getPrestador() === $prestador;
            })->shuffle()->first();
        })->filter();

        $cursosRandom = $cursos->reject(function ($curso) use ($minimoCursoPorPrestador) {
            return $minimoCursoPorPrestador->contains(function ($cursoElegido) use ($curso) {
                return $curso === $cursoElegido;
            });
        })->shuffle();

        $max = (int)(($cursosRandom->count() - $minimoCursoPorPrestador->count()) * 0.2); // 20%

        $cursos = $cursosRandom->take($max)
            ->sortBy(function ($curso) {
                return $curso->getPrestador()->getNombre();
            });

        return $this->render('ChoferesBundle:Auditoria:new.html.twig', [
            'cursos' => $cursos,
            'css_active'  => 'auditoria_new',
        ]);
    }

    public function createAction()
    {

    }
}
