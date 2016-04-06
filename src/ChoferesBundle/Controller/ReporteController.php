<?php

namespace ChoferesBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

use ChoferesBundle\Form\ReporteChoferesVigentesType;


class ReporteController extends Controller
{
    public function choferesVigentesAction(Request $request)
    {
        $form = $this->createForm(new ReporteChoferesVigentesType());
        $form->bind($request);

        if ($form->isValid()) {
            $fechaForm = $form->get('fechaVigencia')->getData();
            $fecha = \DateTime::createFromFormat('d/m/Y', $fechaForm);

            $choferesService =  $this->get('choferes.servicios.chofer');
            $choferesVigentes = $choferesService->getChoferesVigentes($fecha);

            //$normalizer = new GetSetMethodNormalizer();
            //$choferes = [];

            foreach ($choferesVigentes as $chofer) {
                // c2_.fecha_fin + INTERVAL 1 YEAR AS fecha_vigencia
                $fechaFin = $chofer['fechaFin']->format('Y-m-d');
                $fechaVigencia = new \DateTime("+1 year $fechaFin");

                //print_r($fechaFin . '\n');
                //print_r($fechaVigencia->format('Y-m-d'));
                //die();

                $chofer['fechaVigencia'] = $fechaVigencia;
            }

            print_r($choferesVigentes[0]);

            die();
        }

        return $this->render('ChoferesBundle:Reporte:choferes_vigentes.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
