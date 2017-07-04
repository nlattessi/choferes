<?php

namespace ChoferesBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\View\TwitterBootstrapView;

use ChoferesBundle\Entity\Auditoria;
use ChoferesBundle\Entity\EstadoAuditoria;


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
}
