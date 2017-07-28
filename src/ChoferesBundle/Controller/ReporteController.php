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

    public function cursoFiltroAction(Request $request){

        //hago un tipo nuevo o reuso el del curso
        //$form = $this->createForm(new ReporteFiltroCursoType());
        $usuario = $this->getUser();
        $usuarioService =  $this->get('choferes.servicios.usuario');

        $form = $this->createForm(new CursoFilterType($usuarioService), null, ['user' => $usuario]);
        if ($request->isMethod('POST')) {

            $em = $this->getDoctrine()->getManager();
            $queryBuilder = $em->createQueryBuilder()
                ->select('c.id',	'c.fechaInicio',	'c.fechaFin',
                    'c.fechaCreacion',	'c.codigo',	'c.anio','c.comprobante','c.fechaPago','c.observaciones','c.fechaValidacion','t.nombre')
                ->from('ChoferesBundle:Curso', 'c')
            ->join('c.tipocurso', 't');
            //{"choferesbundle_cursofiltertype":{"id":"","fechaInicio":{"left_date":"20\/07\/2017","right_date":""},
            //"fechaFin":{"left_date":"","right_date":""},"fechaValidacion":{"left_date":"","right_date":""},
            //"prestador":"","codigo":"","tipocurso":"","estado":"","_token":"6CaUf8mkf9p_laADRbxjuT5KfY_yB2_kHt05_3zd2lQ"},"filter_action":"filter"}
        //    print json_encode($_POST['choferesbundle_cursofiltertype']["fechaInicio"]['left_date']);
          //  print $_POST["fechaInicio"];
            $elPost = $_POST['choferesbundle_cursofiltertype'];
            if(!empty($elPost["fechaInicio"]['left_date'])){
                $fecha = \DateTime::createFromFormat('d/m/Y', $elPost["fechaInicio"]['left_date']);
                $queryBuilder->andWhere('c.fechaInicio >= :fechaIniD')
                    ->setParameter('fechaIniD', $fecha);
            }
            if(!empty($elPost["fechaInicio"]['right_date'])){
                $fecha = \DateTime::createFromFormat('d/m/Y', $elPost["fechaInicio"]['right_date']);
                $queryBuilder->andWhere('c.fechaInicio <= :fechaIniH')
                    ->setParameter('fechaIniH', $fecha);
            }
            if(!empty($elPost["fechaFin"]['left_date'])){
                $fecha = \DateTime::createFromFormat('d/m/Y', $elPost["fechaFin"]['left_date']);
                $queryBuilder->andWhere('c.fechaFin >= :fechaFinD')
                    ->setParameter('fechaFinD', $fecha);
            }
            if(!empty($elPost["fechaFin"]['right_date'])){
                $fecha = \DateTime::createFromFormat('d/m/Y', $elPost["fechaFin"]['right_date']);
                $queryBuilder->andWhere('c.fechaFin <= :fechaFinH')
                    ->setParameter('fechaFinH', $fecha);
            }
            if(!empty($elPost["fechaValidacion"]['left_date'])){
                $fecha = \DateTime::createFromFormat('d/m/Y', $elPost["fechaValidacion"]['left_date']);
                $queryBuilder->andWhere('c.fechaValidacion >= :fechaValD')
                    ->setParameter('fechaValD', $fecha);
            }
            if(!empty($elPost["fechaValidacion"]['right_date'])){
                $fecha = \DateTime::createFromFormat('d/m/Y', $elPost["fechaValidacion"]['right_date']);
                $queryBuilder->andWhere('c.fechaValidacion <= :fechaValH')
                    ->setParameter('fechaValH', $fecha);
            }
            if(!empty($elPost["prestador"])){
                $queryBuilder->andWhere('c.prestador = :prestador')
                    ->setParameter('prestador', $elPost["prestador"]);
            }
            if(!empty($elPost["codigo"])){
                $queryBuilder->andWhere('c.codigo = :codigo')
                    ->setParameter('codigo', $elPost["codigo"]);
            }
            if(!empty($elPost["tipocurso"])){
                $queryBuilder->andWhere('c.tipocurso = :tipocurso')
                    ->setParameter('tipocurso', $elPost["tipocurso"]);
            }
            if(!empty($elPost["estado"])){
                $queryBuilder->andWhere('c.estado = :estado')
                    ->setParameter('estado', $elPost["estado"]);
            }


            $query = $queryBuilder->getQuery();
           /* print json_encode($query->getSql());
            print json_encode($query->getParameters());
            exit;*/
            $result = $query->getResult();
            if (isset($result)) {
                foreach ($result as & $curso) {
                    if($curso['fechaInicio']){
                        $fechaInicio = $curso['fechaInicio']->format('d-m-Y H:i:s');
                        $curso['fechaInicio'] = $fechaInicio;
                    }

                    if($curso['fechaFin']){
                        $fechaFin = $curso['fechaFin']->format('d-m-Y H:i:s');
                        $curso['fechaFin'] = $fechaFin;
                    }
                    if($curso['fechaValidacion']){
                        $fechaValidacion = $curso['fechaValidacion']->format('d-m-Y H:i:s');
                        $curso['fechaValidacion'] = $fechaValidacion;
                    }

                    if($curso['fechaCreacion']){
                        $fechaCreacion = $curso['fechaCreacion']->format('d-m-Y H:i:s');
                        $curso['fechaCreacion'] = $fechaCreacion;
                    }

                    if($curso['fechaPago']){
                        $fechaPago = $curso['fechaPago']->format('d-m-Y H:i:s');
                        $curso['fechaPago'] = $fechaPago;
                    }

                }
            }


            /*
            return $this->createReporteResponse($result, [
                'id',	'docente_id' ,	'estado','prestador','sede','fecha_inicio',	'fecha_fin',
            'fecha_creacion',	'codigo',	'año','comprobante','fecha_pago','observaciones','tipoCurso' ,'fecha_validacion'
            ]);*/
            return $this->createReporteResponse($result, [
                'id',	'fecha_inicio',	'fecha_fin',
                'fecha_creacion',	'codigo',	'año','comprobante','fecha_pago','observaciones','fecha_validacion','Tipo'
            ]);
              //  print json_encode($form->getData());


        }

        return $this->render('ChoferesBundle:Reporte:curso_filtro.html.twig', [
            'form' => $form->createView(),
            'css_active' => 'reporte_curso_filtro'
        ]);
    }
}
