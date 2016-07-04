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

        $fechaModuloPago = \DateTimeUtils::createDateTime(
            $this->container->getParameter('fecha_modulo_pago')
        );

        if ($request->isMethod('POST')) {
            $form->bind($request);

            if ($form->isValid()) {
                $cursoService =  $this->get('choferes.servicios.curso');

                $fechaInicioDesde = $form->get('fechaInicioDesde')->getData();
                $fechaInicioHasta = $form->get('fechaInicioHasta')->getData();

                $cursos = $cursoService->getCursosFilterByFechaInicio(
                    $fechaInicioDesde,
                    $fechaInicioHasta
                );

                // Por tipo de curso
                $tiposCurso = $cursoService->getTiposCurso();
                $dataPorTipoCurso = [];
                foreach ($tiposCurso as $tipoCurso) {
                    $cursosPorTipo = $cursoService->getCursosPorTipo($cursos, $tipoCurso);
                    $data = [
                        'tipo' => $tipoCurso->getNombre(),
                        'cantCursos' => count($cursosPorTipo),
                        'cantAlumnos' => $cursoService->getTotalAlumnos($cursosPorTipo),
                        'montoTotal' => $cursoService->getMontoTotalCursos($cursosPorTipo),
                        'montoRecaudado' => $cursoService->getMontoRecaudadoCursos($cursosPorTipo),
                    ];

                    $dataPorTipoCurso[] = $data;
                }

                // Total de cursos
                $totalAlumnos = $this->getTotalFromArray($dataPorTipoCurso, 'cantAlumnos');
                $montoTotal = $this->getTotalFromArray($dataPorTipoCurso, 'montoTotal');
                $montoRecaudado = $this->getTotalFromArray($dataPorTipoCurso, 'montoRecaudado');

                // Mostrar montos de acuerdo a la 'fecha creacion' de los cursos
                $mostrarMontos = $cursoService->areCursosFechaCreacionGreaterThan($cursos, $fechaModuloPago);

                return $this->render('ChoferesBundle:Reporte:cursos_por_tipo_result.html.twig', [
                   'fechaInicioDesde' => $fechaInicioDesde,
                   'fechaInicioHasta' => $fechaInicioHasta,

                   'cursos' => $cursos,
                   'totalAlumnos' => $totalAlumnos,

                   'montoTotal' => $montoTotal,
                   'montoRecaudado' => $montoRecaudado,

                   'dataPorTipoCurso' => $dataPorTipoCurso,

                   'mostrarMontos' => $mostrarMontos,

                   'css_active' => 'reporte_cursos_por_tipo',
                ]);
            }
        }

        return $this->render('ChoferesBundle:Reporte:cursos_por_tipo.html.twig', [
            'form' => $form->createView(),
            'fechaModuloPago' => $fechaModuloPago->format('d/m/Y'),
            'css_active' => 'reporte_cursos_por_tipo',
        ]);
    }

    /**
    * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_CNTSV') or has_role('ROLE_CENT')")
    */
    public function cursosPorTipoExcelAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        if ($request->isMethod('POST')) {
            $cursoService =  $this->get('choferes.servicios.curso');

            $to = $request->request->get('to');
            $from = $request->request->get('from');

            $cursos = $cursoService->getCursosFilterByFechaInicio(
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
                        'Tipo de curso',
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
                        $curso->getTipoCurso()->getNombre(),
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

    private function getTotalFromArray($array, $field)
    {
        return array_reduce($array, function($count, $item) use ($field) {
            return $count + $item[$field];
        }, 0.0);
    }
}
