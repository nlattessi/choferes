<?php

namespace ChoferesBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

use ChoferesBundle\Form\ReporteChoferesVigentesType;


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

                if (!empty($choferesVigentes)) {
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
                            ->setCellValue('F'.$row, $chofer['fechaFin']->format('d-m-Y H:i:s'))
                            ->setCellValue('G'.$row, $chofer['fechaVigencia']->format('d-m-Y H:i:s'));

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
                    ob_start();
                    $writer->save("php://output");
                    $xlsData = ob_get_contents();
                    ob_end_clean();
                    $response =  [
                        'result' => true,
                        'name' => 'Choferes_Vigencia_' . date('d-m-Y') . '.xls',
                        'file' => "data:application/vnd.ms-excel;base64,".base64_encode($xlsData)
                    ];

                } else {
                    $response =  [
                        'result' => false,
                        'message' => 'No se encontraron choferes vigentes'
                    ];
                }
            } else {
                $response =  [
                    'result' => false,
                    'message' => 'Se produjo un error... Intente de nuevo'
                ];
            }

            return new JsonResponse($response);
        }

        return $this->render('ChoferesBundle:Reporte:choferes_vigentes.html.twig', [
            'form' => $form->createView(),
            'css_active' => 'reporte_vigente'
        ]);
    }
}
