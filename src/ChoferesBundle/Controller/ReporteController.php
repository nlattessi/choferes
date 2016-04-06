<?php

namespace ChoferesBundle\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class ReporteController extends Controller
{
    public function choferesVigentesAction()
    {
        return $this->render('ChoferesBundle:Reporte:choferes_vigentes.html.twig', []);
    }
}
