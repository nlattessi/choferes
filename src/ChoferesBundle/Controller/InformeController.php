<?php

namespace ChoferesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use ChoferesBundle\Form\InformeType;


class InformeController extends Controller
{
    public function informeAction()
    {
        $usuarioService =  $this->get('choferes.servicios.usuario');

        $form = $this->createForm(new InformeType($usuarioService));
        
        return $this->render('ChoferesBundle:Informe:informe.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
