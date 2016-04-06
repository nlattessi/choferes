<?php

namespace ChoferesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use ChoferesBundle\Form\InformeType;
use \Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;


class InformeController extends Controller
{
    public function informeAction(Request $request)
    {
        $usuarioService =  $this->get('choferes.servicios.usuario');

        if ($request->isMethod('POST')) {
            if ($request->request->has('exportar_excel')) {
                $session = $request->getSession();
                $choferesEntities = $session->get('choferes_excel');
                $normalizers = new \Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer();
                $choferes = [];
                foreach ($choferesEntities as $choferEntity) {
                    $choferes[] = $normalizers->normalize($choferEntity);
                }

                $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

                $phpExcelObject->getProperties()->setCreator("CNTSV")
                  ->setLastModifiedBy("CNTSV")
                  ->setTitle("Informe CNRT")
                  ->setSubject("Informe CNRT")
                  ->setDescription("Informe para CNRT.")
                  ->setKeywords("office 2005 openxml php")
                  ->setCategory("Informe");

                $em = $this->getDoctrine()->getManager();
                $fields = $em->getClassMetadata('ChoferesBundle:Chofer')->getFieldNames();
                $fieldsCount = count($fields);
                $nColumns = 'A';
                $excelMap = [];
                for ($i=0; $i < $fieldsCount; $i++) {
                    $excelMap[$nColumns] = $fields[$i];
                    $nColumns++;
                }
                for($j = 'A'; $j < $nColumns; $j++){
                    $phpExcelObject->getActiveSheet()->setCellValue($j.'1', $excelMap[$j]);
                }
                $nRows = sizeof($choferes);
                for ($i=2; $i < $nRows; $i++) {
                    for ($j='A'; $j < $nColumns; $j++) {
                        $phpExcelObject->getActiveSheet()->setCellValue($j.$i, $choferes[$i-2][$excelMap[$j]]);
                    }
                }

                // $phpExcelObject->getActiveSheet()->setTitle('Simple');
                // Set active sheet index to the first sheet, so Excel opens this as the first sheet
                $phpExcelObject->setActiveSheetIndex(0);

                // create the writer
                $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
                // create the response
                $response = $this->get('phpexcel')->createStreamedResponse($writer);
                // adding headers
                $dispositionHeader = $response->headers->makeDisposition(
                    ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                    'informe_' . date('d-m-Y_H-i') . '.xls'
                );
                $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
                $response->headers->set('Pragma', 'public');
                $response->headers->set('Cache-Control', 'maxage=1');
                $response->headers->set('Content-Disposition', $dispositionHeader);

                return $response;
            }
        }

        $form = $this->createForm(new InformeType($usuarioService));

        $form->bind($request);

        if ($form->isValid()) {
            $dtInicio = $form->get('fechaInicio')->getData() . ' ' . $form->get('horaInicio')->getData();
            $fechaInicio = \DateTime::createFromFormat('d/m/Y H:i', $dtInicio);

            $dtFin = $form->get('fechaFin')->getData() . ' ' . $form->get('horaFin')->getData();
            $fechaFin = \DateTime::createFromFormat('d/m/Y H:i', $dtFin);

            $prestador = $form->get('prestador')->getData();
            $tipocurso = $form->get('tipocurso')->getData();

            $em = $this->getDoctrine()->getManager();
            $query = $em->createQueryBuilder()
                ->select('curso')
                ->from('ChoferesBundle:Curso', 'curso')
                ->where('curso.prestador = :prestador')
                ->andWhere('curso.tipocurso = :tipocurso')
                ->andWhere('curso.fechaInicio >= :fechaInicio')
                ->andWhere('curso.fechaFin <= :fechaFin')
                ->setParameter('prestador', $prestador)
                ->setParameter('tipocurso', $tipocurso)
                ->setParameter('fechaInicio', $fechaInicio)
                ->setParameter('fechaFin', $fechaFin)
                ->getQuery();

            $cursos = $query->getResult();

            $choferService =  $this->get('choferes.servicios.chofer');
            $choferes = $choferService->getChoferesPorCursos($cursos);

            $session = $request->getSession();
            if ($session->has('choferes_excel')) {
                $session->remove('choferes_excel');
            }
            $session->set('choferes_excel', $choferes);

            return $this->render('ChoferesBundle:Informe:informe.html.twig', [
              'form' => $form->createView(),
              'choferes' => $choferes
            ]);
        }

        return $this->render('ChoferesBundle:Informe:informe.html.twig', [
            'form' => $form->createView(),
            'choferes' => []
        ]);
    }
}
