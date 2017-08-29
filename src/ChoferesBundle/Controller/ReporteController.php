<?php

namespace ChoferesBundle\Controller;

use ChoferesBundle\Form\CursoFilterType;
use ChoferesBundle\Form\CursoType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use ChoferesBundle\Form\ReporteChoferesVigentesType;
use ChoferesBundle\Form\ReporteCursosPorTipoType;
use ChoferesBundle\Form\ReporteInformeAuditoriaType;
use League\Csv\Writer;

class ReporteController extends Controller
{
    public function choferesVigentesPorFechaCursoAction(Request $request)
    {
        $form = $this->createForm(new ReporteChoferesVigentesType());

        if ($request->isMethod('POST')) {
            $form->bind($request);

            if ($form->isValid()) {
                $fechaDesde = $form->get('fechaDesde')->getData();
                $fechaHasta = $form->get('fechaHasta')->getData();
                $choferesService =  $this->get('choferes.servicios.chofer');
                $choferesVigentes = $choferesService->getChoferesVigentesPorFechaCurso($fechaDesde, $fechaHasta);

                if (! empty($choferesVigentes)) {
                    return $this->createReporteResponse($choferesVigentes, [
                        'DNI',
                        'Fecha Vencimiento Certificado',
                    ], 'Reporte_Choferes_Vigencia_');
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

        return $this->render('ChoferesBundle:Reporte:choferes_vigentes_por_fecha_curso.html.twig', [
            'form' => $form->createView(),
            'css_active' => 'choferes_vigentes_por_fecha_curso'
        ]);
    }

    public function totalChoferesVigentesPorFechaCursoAction(Request $request)
    {
        $response = new JsonResponse();

        $fechaDesde = $request->query->get('fecha_desde', null);
        $fechaHasta = $request->query->get('fecha_hasta', null);

        if ($fechaDesde === null) {
            $data =  [
                'result'  => false,
                'message' => 'Falta parametro fecha_desde',
            ];

            $response->setData($data);
            return $response;
        }

        if ($fechaHasta === null) {
            $data =  [
                'result'  => false,
                'message' => 'Falta parametro fecha_hasta',
            ];

            $response->setData($data);
            return $response;
        }

        $choferesService =  $this->get('choferes.servicios.chofer');
        $totalChoferesVigentes = $choferesService->getTotalChoferesVigentesPorFechaCurso($fechaDesde, $fechaHasta);

        $data =  [
            'result'  => true,
            'total' => $totalChoferesVigentes,
        ];

        $response->setData($data);
        return $response;
    }

    public function choferesVigentesPorFechaValidacionAction(Request $request)
    {
        $form = $this->createForm(new ReporteChoferesVigentesType());

        if ($request->isMethod('POST')) {
            $form->bind($request);

            if ($form->isValid()) {
                $fechaDesde = $form->get('fechaDesde')->getData();
                $fechaHasta = $form->get('fechaHasta')->getData();
                $choferesService =  $this->get('choferes.servicios.chofer');
                $choferesVigentes = $choferesService->getChoferesVigentesPorFechaValidacion($fechaDesde, $fechaHasta);

                if (! empty($choferesVigentes)) {
                    return $this->createReporteResponse($choferesVigentes, [
                        'DNI',
                        'Fecha Vencimiento Certificado',
                    ], 'Reporte_Choferes_Vigencia_');
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

        return $this->render('ChoferesBundle:Reporte:choferes_vigentes_por_fecha_validacion.html.twig', [
            'form' => $form->createView(),
            'css_active' => 'choferes_vigentes_por_fecha_validacion'
        ]);
    }

    public function totalChoferesVigentesPorFechaValidacionAction(Request $request)
    {
        $response = new JsonResponse();

        $fechaDesde = $request->query->get('fecha_desde', null);
        $fechaHasta = $request->query->get('fecha_hasta', null);

        if ($fechaDesde === null) {
            $data =  [
                'result'  => false,
                'message' => 'Falta parametro fecha_desde',
            ];

            $response->setData($data);
            return $response;
        }

        if ($fechaHasta === null) {
            $data =  [
                'result'  => false,
                'message' => 'Falta parametro fecha_hasta',
            ];

            $response->setData($data);
            return $response;
        }

        $choferesService =  $this->get('choferes.servicios.chofer');
        $totalChoferesVigentes = $choferesService->getTotalChoferesVigentesPorFechaValidacion($fechaDesde, $fechaHasta);

        $data =  [
            'result'  => true,
            'total' => $totalChoferesVigentes,
        ];

        $response->setData($data);
        return $response;
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

    /**
    * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_CNTSV') or has_role('ROLE_CENT')")
    */
    public function cursoFiltroAction(Request $request)
    {
        $usuario = $this->getUser();
        $usuarioService =  $this->get('choferes.servicios.usuario');

        $form = $this->createForm(new CursoFilterType($usuarioService), null, ['user' => $usuario]);

        $form->add('reset', 'reset', ['label' => 'Limpiar ']);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $queryBuilder = $em->createQueryBuilder()
                ->select('c.id', 'c.fechaInicio', 'c.fechaFin', 'c.fechaCreacion', 'c.codigo', 'c.anio','c.comprobante','c.fechaPago','c.observaciones','c.fechaValidacion','t.nombre')
                ->from('ChoferesBundle:Curso', 'c')
                ->join('c.tipocurso', 't');

            $id = $form->get('id')->getData();
            if (isset($id)) {
                $queryBuilder
                    ->andWhere('c.id = :id')
                    ->setParameter('id', $id);
            }

            $fechaInicio = $form->get('fechaInicio')->getData();
            if (isset($fechaInicio['left_date'])) {
                $queryBuilder
                    ->andWhere('c.fechaInicio >= :fechaInicioDesde')
                    ->setParameter('fechaInicioDesde', $fechaInicio['left_date']->format('Y-m-d'));
            }
            if (isset($fechaInicio['right_date'])) {
                $queryBuilder
                    ->andWhere('c.fechaInicio <= :fechaInicioHasta')
                    ->setParameter('fechaInicioHasta', $fechaInicio['right_date']->format('Y-m-d'));
            }

            $fechaFin = $form->get('fechaFin')->getData();
            if (isset($fechaFin['left_date'])) {
                $queryBuilder
                    ->andWhere('c.fechaFin >= :fechaFinDesde')
                    ->setParameter('fechaFinDesde', $fechaFin['left_date']->format('Y-m-d'));
            }
            if (isset($fechaFin['right_date'])) {
                $queryBuilder
                    ->andWhere('c.fechaFin <= :fechaFinHasta')
                    ->setParameter('fechaFinHasta', $fechaFin['right_date']->format('Y-m-d'));
            }

            $fechaValidacion = $form->get('fechaValidacion')->getData();
            if (isset($fechaValidacion['left_date'])) {
                $queryBuilder
                    ->andWhere('c.fechaValidacion >= :fechaValidacionDesde')
                    ->setParameter('fechaValidacionDesde', $fechaValidacion['left_date']->format('Y-m-d'));
            }
            if (isset($fechaValidacion['right_date'])) {
                $queryBuilder
                    ->andWhere('c.fechaValidacion <= :fechaValidacionHasta')
                    ->setParameter('fechaValidacionHasta', $fechaValidacion['right_date']->format('Y-m-d'));
            }

            $prestador = $form->get('prestador')->getData();
            if (isset($prestador)) {
                $queryBuilder
                    ->andWhere('c.prestador = :prestador')
                    ->setParameter('prestador', $prestador);
            }

            $codigo = $form->get('codigo')->getData();
            if (isset($codigo)) {
                $queryBuilder
                    ->andWhere('c.codigo = :codigo')
                    ->setParameter('codigo', $codigo);
            }

            $tipocurso = $form->get('tipocurso')->getData();
            if (isset($tipocurso)) {
                $queryBuilder
                    ->andWhere('c.tipocurso = :tipocurso')
                    ->setParameter('tipocurso', $tipocurso);
            }

            $estado = $form->get('estado')->getData();
            if (isset($estado)) {
                $queryBuilder
                    ->andWhere('c.estado = :estado')
                    ->setParameter('estado', $estado);
            }

            $query = $queryBuilder->getQuery();
            $result = $query->getResult();

            if (isset($result)) {
                $cursos = collect($result)->map(function ($curso) {
                    if (isset($curso['fechaInicio'])) {
                        $fechaInicio = $curso['fechaInicio']->format('d-m-Y H:i:s');
                        $curso['fechaInicio'] = $fechaInicio;
                    }

                    if (isset($curso['fechaFin'])) {
                        $fechaFin = $curso['fechaFin']->format('d-m-Y H:i:s');
                        $curso['fechaFin'] = $fechaFin;
                    }

                    if (isset($curso['fechaValidacion'])) {
                        $fechaValidacion = $curso['fechaValidacion']->format('d-m-Y H:i:s');
                        $curso['fechaValidacion'] = $fechaValidacion;
                    }

                    if (isset($curso['fechaCreacion'])) {
                        $fechaCreacion = $curso['fechaCreacion']->format('d-m-Y H:i:s');
                        $curso['fechaCreacion'] = $fechaCreacion;
                    }

                    if (isset($curso['fechaPago'])) {
                        $fechaPago = $curso['fechaPago']->format('d-m-Y H:i:s');
                        $curso['fechaPago'] = $fechaPago;
                    }

                    return $curso;
                })->all();
            }

            return $this->createReporteResponse($cursos, [
                'id',
                'fecha_inicio',
                'fecha_fin',
                'fecha_creacion',
                'codigo',
                'año',
                'comprobante',
                'fecha_pago',
                'observaciones',
                'fecha_validacion',
                'Tipo'
            ], 'Reporte_Cursos_Por_Filros_');
        }

        return $this->render('ChoferesBundle:Reporte:curso_filtro.html.twig', [
            'form' => $form->createView(),
            'css_active' => 'reporte_curso_filtro'
        ]);
    }

    public function cursosAuditoriaPorFechaCursoAction(Request $request)
    {
        $form = $this->createForm(new ReporteInformeAuditoriaType());

        if ($request->isMethod('POST')) {
            $form->bind($request);

            if ($form->isValid()) {
                $fechaDesde = $form->get('fechaDesde')->getData();
                $fechaHasta = $form->get('fechaHasta')->getData();
                $choferesService = $this->get('choferes.servicios.chofer');
                $cursosAuditoria = $choferesService->getCursosAuditoriaPorFechaCurso($fechaDesde, $fechaHasta);

                if (! empty($cursosAuditoria)) {
                    return $this->createReporteResponse($cursosAuditoria, [
                        'prestador',
                        'curso tipo',
                        'curso numero',
                        'fecha curso',
                        'lugar direccion',
                        'lugar provincia',
                        'lugar ciudad',
                        'docente nombre',
                        'docente apellido',
                    ], 'Reporte_Cursos_Auditoria_');
                }
                else {
                    $data =  [
                        'result'  => false,
                        'message' => 'No se encontraron cursos'
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

        return $this->render('ChoferesBundle:Reporte:cursos_auditoria_por_fecha_curso.html.twig', [
            'form' => $form->createView(),
            'css_active' => 'cursos_auditoria_por_fecha_curso'
        ]);

    }

    private function createReporteResponse($rows, $headers, $fileNameSuffix)
    {
        $response = new StreamedResponse(function () use ($rows, $headers, $fileNameSuffix) {
            $csv = Writer::createFromFileObject(new \SplTempFileObject());
            $csv->insertOne($headers);
            $csv->insertAll($rows);
            $csv->output($fileNameSuffix . date('d-m-Y') . '.csv');
        });

        $response->headers->set('Set-Cookie', 'fileDownload=true; path=/');
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');

        return $response;
    }
}
