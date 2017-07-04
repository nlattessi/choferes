<?php

namespace ChoferesBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\View\TwitterBootstrapView;

use ChoferesBundle\Entity\Auditoria;


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

        return $this->render('ChoferesBundle:Auditoria:index.html.twig', array(
        ));
    }
}
