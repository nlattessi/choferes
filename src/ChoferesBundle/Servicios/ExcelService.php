<?php

namespace ChoferesBundle\Servicios;

use Doctrine\ORM\EntityManager;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class ExcelService
{
    private $phpexcel;

    public function __construct($phpexcel)
    {
        $this->phpexcel = $phpexcel;
    }

    public function getCSVExcelResponse($properties, $arrayData, $filename)
    {
        $phpExcelObject = $this->phpexcel->createPHPExcelObject();
        $this->excelSetProperties($phpExcelObject, $properties);
        $sheet = $this->excelGetActiveSheet($phpExcelObject);
        $this->excelSetData($sheet, $arrayData, 'A1');
        
        return $this->excelCreateResponse($phpExcelObject, 'CSV', $filename);
    }

    private function excelSetProperties($phpExcelObject, $properties)
    {
        $phpExcelObject
            ->getProperties()->setCreator($properties['creator'])
            ->setLastModifiedBy($properties['lastModifiedBy'])
            ->setTitle($properties['title'])
            ->setSubject(date('d-m-Y'))
            ->setDescription($properties['description'])
            ->setKeywords("office 2005 openxml php")
            ->setCategory("reporte");
    }

    private function excelSetFont($phpExcelObject)
    {
        $phpExcelObject->getDefaultStyle()->getFont()->setName('Arial');
        $phpExcelObject->getDefaultStyle()->getFont()->setSize(10);
    }

    private function excelGetActiveSheet($phpExcelObject)
    {
        $phpExcelObject->setActiveSheetIndex(0);

        return $phpExcelObject->getActiveSheet();   
    }

    private function excelSetTitle(/*$phpExcelObject*/$sheet, $title)
    {
        //$phpExcelObject->setActiveSheetIndex(0);
        //$phpExcelObject->getActiveSheet()->setTitle($title);
        $sheet->setTitle($title);
    }

    private function excelSetData($sheet, $data, $first)
    {
        $sheet->fromArray(
            $data,
            NULL,
            $first
        );
    }

    private function excelSetHeaderStyle($sheet, $range)
    {
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

        // $phpExcelObject->getActiveSheet()->getStyle($range)
        //     ->applyFromArray($styleArray);
        $sheet->getStyle($range)->applyFromArray($styleArray);
    }

    private function excelSetTextStyle($sheet, $range)
    {
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

        // $phpExcelObject->getActiveSheet()->getStyle($range)
        //     ->applyFromArray($styleArray);
        $sheet->getStyle($range)->applyFromArray($styleArray);
    }

    private function excelCreateResponse($phpExcelObject, $writer, $filename)
    {
         // create the writer
        $writer = $this->phpexcel->createWriter($phpExcelObject, $writer);
        $writer->setPreCalculateFormulas(false);
        
        // create the response
        $response = $this->phpexcel->createStreamedResponse($writer);
        
        // adding headers
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $filename . date('d-m-Y') . '.xls'
        );
        
        $response->headers->set('Set-Cookie', 'fileDownload=true; path=/');
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }
}