<?php

namespace ChoferesBundle\Servicios;

use Doctrine\ORM\EntityManager;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class ChoferService
{
    protected $em;
    protected $kernelCacheDir;

    public function __construct(EntityManager $entityManager, $kernelCacheDir)
    {
        $this->em = $entityManager;
        $this->kernelCacheDir = $kernelCacheDir;
    }

    public function getStatusPorDniChofer($dni)
    {
        $query = $this->em->createQueryBuilder()
            ->select(
                'chofer.id as choferId', 'chofer.nombre', 'chofer.apellido', 'chofer.dni', 'chofer.tieneCursoBasico',
                'choferCurso.id as choferCursoId', 'choferCurso.aprobado','choferCurso.pagado', 'choferCurso.documentacion',
                'curso.id as cursoId', 'curso.fechaFin'
            )
            ->from('ChoferesBundle:Chofer', 'chofer')
            ->leftJoin(
                'ChoferesBundle:ChoferCurso', 'choferCurso',
                \Doctrine\ORM\Query\Expr\Join::WITH, 'choferCurso.chofer = chofer.id'
            )
            ->leftJoin(
                'ChoferesBundle:Curso', 'curso',
                \Doctrine\ORM\Query\Expr\Join::WITH, 'choferCurso.curso = curso.id'
            )
            ->where('chofer.dni = :dni')
            ->orderBy('curso.fechaFin', 'DESC')
            ->setParameter('dni', $dni)
            ->setMaxResults(1)
            ->getQuery();

        $result = $query->getResult();
        if ($result) {
            $status = $result[0];
        }

        $result = array();

        if (isset($status)) {
            $result['certificado'] = false;
            if ($status['tieneCursoBasico']) {
                if ($status['choferCursoId'] && $status['fechaFin'] > new \DateTime('-1 year')) {
                    if ($status['aprobado']) {
                        if ($status['pagado']) {
                            if ($status['documentacion']) {
                                $result['fechaFin'] = $status['fechaFin'];
                                $result['cursoId'] = $status['cursoId'];
                                $result['certificado'] = true;
                            } else {
                                $result['message'] = "No se cargo en sistema la documentacion correspondiente.";
                            }
                        } else {
                            $result['message'] = 'No figura pago el ultimo curso complementario.';
                        }
                    } else {
                        $result['message'] = 'No tiene aprobado el ultimo curso complementario.';
                    }
                } else {
                    $result['message'] = 'No tiene el curso complementario o la vigencia del mismo ya expiro.';
                }
            } else {
                $result['message'] = 'No tiene el curso basico.';
            }
        }

        return $result;
    }

    public function descargarCertificado($dni)
    {
        $chofer = $this->em->getRepository('ChoferesBundle:Chofer')->findOneBy(['dni' => $dni]);
        $status = $this->getStatusPorDniChofer($chofer->getDni());

        $curso = $this->em->getRepository('ChoferesBundle:Curso')->find($status['cursoId']);

        $data = [
            'prestador' => $curso->getPrestador()->getNombre(),
            'chofer' => $chofer->getNombre() . ' ' . $chofer->getApellido(),
            'matricula' => '', //$chofer->getMatricula()
            'dni' => $chofer->getDni(),
            'curso' => $curso->getTipocurso(),
            'sede' => $curso->getSede(),
            'fecha_curso' => $status['fechaFin']->format('d/m/Y'),
            'transaccion' => $curso->getComprobante(),
            'fecha_transaccion' => '', //$curso->getFechaComprobante()
        ];

        $pdfHtml  = new \PdfHtml();
        $pdf = $pdfHtml->crear_certificado($data);

        $filename = 'certificado' . $dni . '.pdf';
        $filepath =  $this->kernelCacheDir . '/' . $filename;

        $pdfFile = $pdf->Output($filepath, 'F');

        $fs = new FileSystem();
        if (!$fs->exists($filepath)) {
            throw $this->createNotFoundException();
        }

        $response = new \Symfony\Component\HttpFoundation\BinaryFileResponse($filepath);
        $response->headers->set('Content-Type', 'application/pdf');
        $d = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $filename
        );
        $response->headers->set('Content-Disposition', $d);

        return $response;
    }
}
