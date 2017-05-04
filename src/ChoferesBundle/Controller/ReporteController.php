<?php

namespace ChoferesBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use ChoferesBundle\Form\ReporteChoferesVigentesType;
use ChoferesBundle\Form\ReporteCursosPorTipoType;
use League\Csv\Writer;

class ReporteController extends Controller
{
    public function choferesVigentesAction(Request $request)
    {
        $form = $this->createForm(new ReporteChoferesVigentesType());

        if ($request->isMethod('POST')) {
            $form->bind($request);

            if ($form->isValid()) {
                $fechaDesde = $form->get('fechaDesde')->getData();
                $fechaHasta = $form->get('fechaHasta')->getData();
                $choferesService =  $this->get('choferes.servicios.chofer');
                $choferesVigentes = $choferesService->getChoferesVigentes($fechaDesde, $fechaHasta);

                if (! empty($choferesVigentes)) {
                    return $this->createReporteResponse($choferesVigentes, [
                        'DNI',
                        'Fecha Vencimiento Certificado',
                    ]);
                }
                else {
                    $data =  [
                        'result'  => false,
                        'message' => 'No se encontraron choferes vigentes'
                    ];
                }
            }
            else {
                $data =  [
                    'result'  => false,
                    'message' => 'Se produjo un error... Intente de nuevo'
                ];
            }

            $response = new JsonResponse();
            $response->setData($data);

            return $response;
        }

        return $this->render('ChoferesBundle:Reporte:choferes_vigentes.html.twig', [
            'form' => $form->createView(),
            'css_active' => 'reporte_vigente'
        ]);
    }

    /**
    * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_CNTSV') or has_role('ROLE_CENT')")
    */
    public function cursosPorTipoAction(Request $request)
    {
        $form = $this->createForm(new ReporteCursosPorTipoType());

        if ($request->isMethod('POST')) {
            $form->bind($request);

            if ($form->isValid()) {
                $tipoCurso = $form->get('tipoCurso')->getData();
                $fechaInicioDesde = $form->get('fechaInicioDesde')->getData();
                $fechaInicioHasta = $form->get('fechaInicioHasta')->getData();

                $cursoService =  $this->get('choferes.servicios.curso');
                $cursos = $cursoService->getCursosPorTipoFilterByFechaInicio(
                    $tipoCurso,
                    $fechaInicioDesde,
                    $fechaInicioHasta
                );

                return $this->render('ChoferesBundle:Reporte:cursos_por_tipo_result.html.twig', [
                   'tipoCurso' => $tipoCurso,
                   'fechaInicioDesde' => $fechaInicioDesde,
                   'fechaInicioHasta' => $fechaInicioHasta,
                   'cursos' => $cursos,
                   'css_active' => 'reporte_cursos_por_tipo'
                ]);
            }
        }

        return $this->render('ChoferesBundle:Reporte:cursos_por_tipo.html.twig', [
            'form' => $form->createView(),
            'css_active' => 'reporte_cursos_por_tipo'
        ]);
    }

    private function createReporteResponse($choferesVigentes, $headers)
    {
        $response = new StreamedResponse(function () use ($choferesVigentes, $headers) {
            $csv = Writer::createFromFileObject(new \SplTempFileObject());
            $csv->insertOne($headers);
            $csv->insertAll($choferesVigentes);
            $csv->output('Reporte_Choferes_Vigencia_' . date('d-m-Y') . '.csv');
        });

        $response->headers->set('Set-Cookie', 'fileDownload=true; path=/');
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');

        return $response;
    }
}
