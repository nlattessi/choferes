<?php

namespace ChoferesBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use ChoferesBundle\Form\ReporteChoferesVigentesType;
use ChoferesBundle\Form\ReporteChoferesVigentesCNRTType;
use ChoferesBundle\Form\ReporteChoferesVigentesCNTSVType;
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
                $fechaForm = $form->get('fechaVigencia')->getData();
                $choferesService =  $this->get('choferes.servicios.chofer');
                $choferesVigentes = $choferesService->getChoferesVigentes($fechaForm);

                if (! empty($choferesVigentes)) {
                    // return $this->createCNTSVReport($choferesVigentes);
                    return $this->createReporteResponse($choferesVigentes, [
                        'Chofer id',
                        'Nombre',
                        'Apellido',
                        'DNI',
                        'Curso id',
                        'Curso Fecha Fin',
                        'Vigente Hasta'
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

    public function choferesVigentesCNTSVAction(Request $request)
    {
        $form = $this->createForm(new ReporteChoferesVigentesCNTSVType());

        if ($request->isMethod('POST')) {
            $form->bind($request);

            if ($form->isValid()) {
                $fechaDesde = $form->get('fechaDesde')->getData();
                $fechaHasta = $form->get('fechaHasta')->getData();
                $choferesService =  $this->get('choferes.servicios.chofer');
                $choferesVigentes = $choferesService->getChoferesVigentesCNTSV($fechaDesde, $fechaHasta);

                if (! empty($choferesVigentes)) {
                    return $this->createReporteResponse($choferesVigentes, [
                        'DNI',
                        'Vigente Desde',
                        'Vigente Hasta',
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

        return $this->render('ChoferesBundle:Reporte:choferes_vigentes_cntsv.html.twig', [
            'form' => $form->createView(),
            'css_active' => 'reporte_vigente'
        ]);
    }

    public function choferesVigentesCNRTAction(Request $request)
    {
        $form = $this->createForm(new ReporteChoferesVigentesCNRTType());

        if ($request->isMethod('POST')) {
            $form->bind($request);

            if ($form->isValid()) {
                $fechaDesde = $form->get('fechaDesde')->getData();
                $fechaHasta = $form->get('fechaHasta')->getData();
                $choferesService =  $this->get('choferes.servicios.chofer');
                // $choferesVigentes = $choferesService->getChoferesVigentesCNRT($fechaDesde, $fechaHasta);
                $choferesVigentes = $choferesService->getChoferesVigentesCNRT2($fechaDesde, $fechaHasta);

                if (! empty($choferesVigentes)) {
                    // return $this->createReporteResponse($choferesVigentes, [
                    //     'DNI',
                    //     'Vigente Desde',
                    //     'Vigente Hasta',
                    // ]);
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

        return $this->render('ChoferesBundle:Reporte:choferes_vigentes_cnrt.html.twig', [
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

    private function createCNTSVReport($choferesVigentes)
    {
        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

        $phpExcelObject->getProperties()->setCreator("CNTSV")
            ->setLastModifiedBy("CNTSV")
            ->setTitle("Choferes_Vigencia")
            ->setSubject(date('d-m-Y'))
            ->setDescription("Listado de choferes vigentes.")
            ->setKeywords("office 2005 openxml php")
            ->setCategory("reporte");

        $phpExcelObject->getDefaultStyle()->getFont()->setName('Arial');
        $phpExcelObject->getDefaultStyle()->getFont()->setSize(10);

        $phpExcelObject->setActiveSheetIndex(0);
        $phpExcelObject->getActiveSheet()->setTitle('Choferes');

        $phpExcelObject->getActiveSheet()
            ->setCellValue('A1', 'Chofer id')
            ->setCellValue('B1', 'Nombre')
            ->setCellValue('C1', 'Apellido')
            ->setCellValue('D1', 'DNI')
            ->setCellValue('E1', 'Curso id')
            ->setCellValue('F1', 'Curso Fecha Fin')
            ->setCellValue('G1', 'Vigente Hasta');

        $styleArray = [
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF']
            ],
            'alignment' => [
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allborders' => [
                    'style' => \PHPExcel_Style_Border::BORDER_THIN,
                    'color' => ['argb' => '00000000']
                ],
            ],
            'fill' => [
                'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                'startcolor' => [
                    'argb' => '00000000'
                ],
            ]
        ];

        $phpExcelObject->getActiveSheet()->getStyle('A1:G1')
            ->applyFromArray($styleArray);

        $phpExcelObject->getActiveSheet()
            ->getColumnDimension('A')
            ->setWidth(10);
        $phpExcelObject->getActiveSheet()
            ->getColumnDimension('B')
            ->setWidth(30);
        $phpExcelObject->getActiveSheet()
            ->getColumnDimension('C')
            ->setWidth(30);
        $phpExcelObject->getActiveSheet()
            ->getColumnDimension('D')
            ->setWidth(10);
        $phpExcelObject->getActiveSheet()
            ->getColumnDimension('E')
            ->setWidth(10);
        $phpExcelObject->getActiveSheet()
            ->getColumnDimension('F')
            ->setWidth(20);
        $phpExcelObject->getActiveSheet()
            ->getColumnDimension('G')
            ->setWidth(20);

        $row = 2;
        foreach ($choferesVigentes as $chofer) {
            $phpExcelObject->getActiveSheet()
                ->setCellValue('A'.$row, $chofer['choferId'])
                ->setCellValue('B'.$row, $chofer['nombre'])
                ->setCellValue('C'.$row, $chofer['apellido'])
                ->setCellValue('D'.$row, $chofer['dni'])
                ->setCellValue('E'.$row, $chofer['cursoId'])
                ->setCellValue('F'.$row, $chofer['fechaFin'])
                ->setCellValue('G'.$row, $chofer['fechaVigencia']);

            $row++;
        }

        $styleArray = [
            'alignment' => [
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allborders' => [
                    'style' => \PHPExcel_Style_Border::BORDER_THIN,
                    'color' => ['argb' => '00000000']
                ],
            ]
        ];

        $phpExcelObject->getActiveSheet()->getStyle('A2:G' . ($row - 1))
            ->applyFromArray($styleArray);

        // create the writer
        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        // create the response
        $response = $this->get('phpexcel')->createStreamedResponse($writer);
        // adding headers
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'Choferes_Vigencia_' . date('d-m-Y') . '.xls'
        );
        $response->headers->set('Set-Cookie', 'fileDownload=true; path=/');
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
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
