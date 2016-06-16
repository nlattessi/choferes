<?php
namespace ChoferesBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use ChoferesBundle\Form\ReporteChoferesVigentesType;
use ChoferesBundle\Form\ReporteCursosPorTipoType;

class ReporteController extends Controller
{
    public function choferesVigentesAction(Request $request)
    {
        $form = $this->createForm(new ReporteChoferesVigentesType());

        if ($request->isMethod('POST')) {
            $form->bind($request);

            if ($form->isValid()) {
                $fechaForm = $form->get('fechaVigencia')->getData();

                $choferesService =  $this->get('choferes.servicios.chofer');
                $choferesVigentes = $choferesService->getChoferesVigentes($fechaForm);

                if (! empty($choferesVigentes)) {
                    $excelService =  $this->get('choferes.servicios.excel');
                    
                    $properties = [
                        'creator' => 'CNTSV',
                        'lastModifiedBy' => 'CNTSV',
                        'title' => 'Choferes_Vigencia',
                        'description' => 'Listado de choferes vigentes.',
                    ];

                    $arrayData = [
                        [
                            'Chofer id',
                            'Nombre',
                            'Apellido',
                            'DNI',
                            'Curso id',
                            'Curso Fecha Fin',
                            'Vigente Hasta',
                        ],
                    ];
                    
                    foreach ($choferesVigentes as $chofer) {
                        $arrayData[] = [
                            $chofer['choferId'],
                            $chofer['nombre'],
                            $chofer['apellido'],
                            $chofer['dni'],
                            $chofer['cursoId'],
                            $chofer['fechaFin']->format('d-m-Y H:i:s'),
                            $chofer['fechaDeVigencia'],
                        ];
                    }

                    return $excelService->getCSVExcelResponse(
                        $properties,
                        $arrayData,
                        'Choferes_Vigencia_'
                    );
                } else {
                    $data =  [
                        'result'  => false,
                        'message' => 'No se encontraron choferes vigentes'
                    ];
                }
            } else {
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

                $montoTotal = $this->getMontoTotalCursos($cursos);
                $montoRecaudado = $this->getMontoRecaudadoCursos($cursos);

                return $this->render('ChoferesBundle:Reporte:cursos_por_tipo_result.html.twig', [
                   'tipoCurso' => $tipoCurso,
                   'fechaInicioDesde' => $fechaInicioDesde,
                   'fechaInicioHasta' => $fechaInicioHasta,
                   'cursos' => $cursos,
                   'montoTotal' => $montoTotal,
                   'montoRecaudado' => $montoRecaudado,
                   'css_active' => 'reporte_cursos_por_tipo',
                ]);
            }
        }

        return $this->render('ChoferesBundle:Reporte:cursos_por_tipo.html.twig', [
            'form' => $form->createView(),
            'css_active' => 'reporte_cursos_por_tipo'
        ]);
    }

    /**
    * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_CNTSV') or has_role('ROLE_CENT')")
    */
    public function cursosPorTipoExcelAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        if ($request->isMethod('POST')) {
            $to = $request->request->get('to');
            $from = $request->request->get('from');
            $type = $request->request->get('type');

            $tipoCurso = $em->getRepository('ChoferesBundle:TipoCurso')->findByNombre($type);
            if (! $tipoCurso) {
                throw $this->createNotFoundException('Unable to find TipoCurso entity.');
            }

            $cursoService =  $this->get('choferes.servicios.curso');
            $cursos = $cursoService->getCursosPorTipoFilterByFechaInicio(
                $tipoCurso,
                $from,
                $to
            );

            if (! empty($cursos)) {
                $excelService =  $this->get('choferes.servicios.excel');

                $properties = [
                    'creator' => 'CNTSV',
                    'lastModifiedBy' => 'CNTSV',
                    'title' => 'Cursos_por_tipo',
                    'description' => 'Cursos por tipo.'
                ];

                $arrayData = [
                    [
                        'Curso id',
                        'Fecha de inicio',
                        'Fecha de fin',
                        'Prestador',
                        'Nro de TRI',
                        'Estado',
                        'Sede',
                        'Monto a recaudar ($)',
                        'Monto recaudado ($)',
                    ],
                ];

                foreach ($cursos as $curso) {
                    $arrayData[] = [
                        $curso->getId(),
                        $curso->getFechaInicio()->format('d-m-Y'),
                        $curso->getFechaFin()->format('d-m-Y'),
                        $curso->getPrestador(),
                        $curso->getCodigo(),
                        $curso->getEstado(),
                        $curso->getSede(),
                        ($curso->getMontoTotal()) ? $curso->getMontoTotal() : '0',
                        ($curso->getMontoRecaudado()) ? $curso->getMontoRecaudado() : '0',
                    ];
                }

                return $excelService->getCSVExcelResponse(
                    $properties,
                    $arrayData,
                    'Cursos_por_tipo_'
                );
            }
        }
        
        $data =  [
            'result'  => false,
            'message' => 'No se encontraron cursos'
        ];

        $response = new JsonResponse();
        $response->setData($data);

        return $response;
    }

    private function getMontoTotalCursos($cursos)
    {
        return array_reduce(
            $cursos,
            function($res, $a) {
                return $res + $a->getMontoTotal();
            },
            0.0
        );
    }

    private function getMontoRecaudadoCursos($cursos)
    {
        return array_reduce(
            $cursos,
            function($res, $a) {
                return $res + $a->getMontoRecaudado();
            },
            0.0
        );
    }
}
