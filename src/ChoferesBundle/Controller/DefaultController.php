<?php

namespace ChoferesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('ChoferesBundle:Default:index.html.twig', array('name' => $name));
    }

    public function unauthorizedAction()
    {
        return $this->render('ChoferesBundle:Default:unauthorized.html.twig');
    }
}
