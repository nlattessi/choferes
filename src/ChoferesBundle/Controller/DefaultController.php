<?php

namespace ChoferesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->redirect($this->generateUrl('home'));
    }

    public function unauthorizedAction()
    {
        return $this->render('ChoferesBundle:Default:unauthorized.html.twig');
    }

    public function homeAction()
    {
        return $this->render('ChoferesBundle:Default:home.html.twig', ['css_active' => 'home']);
    }

    public function enConstruccionAction()
    {
        return $this->render('ChoferesBundle:Default:en_construccion.html.twig');
    }
}
