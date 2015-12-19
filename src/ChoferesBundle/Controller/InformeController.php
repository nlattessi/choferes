<?php

namespace ChoferesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use ChoferesBundle\Form\InformeType;


class InformeController extends Controller
{
    public function informeAction(Request $request)
    {
        $usuarioService =  $this->get('choferes.servicios.usuario');

        $form = $this->createForm(new InformeType($usuarioService));

        $form->bind($request);

        if ($form->isValid()) {
            $dtInicio = $form->get('fechaInicio')->getData() . ' ' . $form->get('horaInicio')->getData();
            $fechaInicio = \DateTime::createFromFormat('d/m/Y H:i', $dtInicio);

            $dtFin = $form->get('fechaFin')->getData() . ' ' . $form->get('horaFin')->getData();
            $fechaFin = \DateTime::createFromFormat('d/m/Y H:i', $dtFin);

            var_dump($fechaInicio);var_dump($fechaFin);die();
        }

        return $this->render('ChoferesBundle:Informe:informe.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
