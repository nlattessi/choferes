<?php

namespace ChoferesBundle\Servicios;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class ChoferService
{
    const ESTADO_CURSO_VALIDADO = 'Validado';

    private $em;
    private $kernelCacheDir;
    private $hashids;
    private $router;
    private $usuarioService;

    public function __construct(EntityManager $entityManager, $kernelCacheDir, $hashids, $router, $usuarioService)
    {
        $this->em = $entityManager;
        $this->kernelCacheDir = $kernelCacheDir;
        $this->hashids = $hashids;
        $this->router = $router;
        $this->usuarioService = $usuarioService;
    }

    public function getStatusPorDniChofer($dni)
    {
        $estadoValidado = $this->em->getRepository('ChoferesBundle:EstadoCurso')->findByNombre(self::ESTADO_CURSO_VALIDADO);

        $query = $this->em->createQueryBuilder()
            ->select(
                'chofer.id as choferId', 'chofer.nombre', 'chofer.apellido', 'chofer.dni', 'chofer.tieneCursoBasico as tieneCursoBasico',
                'choferCurso.id as choferCursoId', 'choferCurso.isAprobado as aprobado','choferCurso.pagado', 'choferCurso.documentacion',
                'curso.id as cursoId', 'curso.fechaFin as fechaFin'
            )
            ->from('ChoferesBundle:Chofer', 'chofer')
            ->leftJoin(
                'ChoferesBundle:ChoferCurso', 'choferCurso',
                Join::WITH, 'choferCurso.chofer = chofer.id'
            )
            ->leftJoin(
                'ChoferesBundle:Curso', 'curso',
                Join::WITH, 'choferCurso.curso = curso.id'
            )
            ->where('chofer.dni = :dni')
            ->andWhere('curso.estado = :estadoValidado')
            ->orderBy('curso.fechaCreacion', 'DESC')
            ->setParameter('dni', $dni)
            ->setParameter('estadoValidado', $estadoValidado)
            ->setMaxResults(1)
            ->getQuery();

        $result = $query->getResult();

        if ($result) {
            $result = $result[0];
            $result['certificado'] = false;
            if ($result['tieneCursoBasico']) {
                if ($result['choferCursoId'] && $result['fechaFin'] > new \DateTime('-1 year')) {
                    if ($result['aprobado']) {
                        if ($result['pagado']) {
                            if ($result['documentacion']) {
                                $result['certificado'] = true;
                                $result['message'] = "Puede descargar el certificado para este chofer.";
                            } else {
                                $result['message'] = "No se cargo en sistema la documentación correspondiente.";
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

        if(!$chofer){
            //no Existe el chofer
            return null;
        }

        $status = $this->getStatusPorDniChofer($chofer->getDni());

        if(count($status) < 1){
            //No existen certificados
            return null;
        }
        if(!$status['certificado']){
            //Por algún motivo no está listo para ser impreso, falta pago, falta documentación
            return null;
        }
        $curso = $this->em->getRepository('ChoferesBundle:Curso')->find($status['cursoId']);

        $url = $this->router->generate('choferes_descargar_certificados_hash', [
            'hash' => $this->hashids->encode($chofer->getId())
        ]);
        $url = "http://$_SERVER[HTTP_HOST]" . $url;

        $data = [
            'id' => $chofer->getId(),
            'prestador' => $curso->getPrestador()->getNombre(),
            'chofer' => $chofer->getNombre() . ' ' . $chofer->getApellido(),
            'dni' => $chofer->getDni(),
            'curso' => $curso->getTipocurso(),
            'sede' => $curso->getSede(),
            'fecha_curso' => $status['fechaFin']->format('d/m/Y'),
            'transaccion' => $curso->getComprobante(),
            'fecha_transaccion' => $curso->getFechaPago()->format('d/m/Y'),
            'url' => $url
        ];

        $pdfHtml  = new \PdfHtml();
        $pdf = $pdfHtml->crear_certificado($data, $this->kernelCacheDir);

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

    public function getChoferesVigentes($fechaForm)
    {
        $fecha = \DateTime::createFromFormat('d/m/Y', $fechaForm);
        $fechaVigente = $fecha->sub(new \DateInterval('P1Y'));

        $query = $this->em->createQueryBuilder()
            ->select(
                'chofer.id as choferId', 'chofer.nombre', 'chofer.apellido', 'chofer.dni',
                'curso.id as cursoId', 'curso.fechaFin as fechaFin'
            )
            ->from('ChoferesBundle:Chofer', 'chofer')
            ->innerJoin(
                'ChoferesBundle:ChoferCurso', 'choferCurso',
                Join::WITH, 'choferCurso.chofer = chofer.id'
            )
            ->innerJoin(
                'ChoferesBundle:Curso', 'curso',
                Join::WITH, 'choferCurso.curso = curso.id'
            )
            ->where('chofer.tieneCursoBasico = TRUE')
            ->andWhere('choferCurso.isAprobado = TRUE')
            ->andWhere('choferCurso.pagado = TRUE')
            ->andWhere('choferCurso.documentacion = TRUE')
            ->andWhere('curso.fechaFin > :fechaVigencia')
            ->orderBy('curso.fechaCreacion', 'DESC')
            ->setParameter('fechaVigencia', $fechaVigente)
            ->setMaxResults(5)
            ->getQuery();

        $result = $query->getResult();

        if (isset($result)) {
            foreach ($result as &$chofer) {
                $fechaFin = $chofer['fechaFin']->format('d-m-Y H:i:s');
                $chofer['fechaFin'] = $fechaFin;

                $fechaVigencia = new \DateTime("+1 year $fechaFin");
                $chofer['fechaVigencia'] = $fechaVigencia->format('d-m-Y H:i:s');
            }
        }

        return $result;
    }

    public function getChoferesVigentesCNRT($fechaDesdeForm, $fechaHastaForm)
    {
        $fechaDesde = \DateTime::createFromFormat('d/m/Y', $fechaDesdeForm);
        $fechaVigente = $fechaDesde->sub(new \DateInterval('P1Y'));

        $fechaHasta = \DateTime::createFromFormat('d/m/Y', $fechaHastaForm);

        $query = $this->em->createQueryBuilder()
            ->select('chofer.dni', 'curso.fechaFin as fechaFin')
            ->from('ChoferesBundle:Chofer', 'chofer')
            ->innerJoin(
                'ChoferesBundle:ChoferCurso', 'choferCurso',
                Join::WITH, 'choferCurso.chofer = chofer.id'
            )
            ->innerJoin(
                'ChoferesBundle:Curso', 'curso',
                Join::WITH, 'choferCurso.curso = curso.id'
            )
            ->where('chofer.tieneCursoBasico = TRUE')
            ->andWhere('choferCurso.isAprobado = TRUE')
            ->andWhere('choferCurso.pagado = TRUE')
            ->andWhere('choferCurso.documentacion = TRUE')
            ->andWhere('curso.fechaFin > :fechaVigencia')
            ->orderBy('curso.fechaCreacion', 'DESC')
            ->setParameter('fechaVigencia', $fechaVigente)
            ->setMaxResults(5)
            ->getQuery();

        $result = $query->getResult();

        if (isset($result)) {
            foreach ($result as &$chofer) {
                $fechaFin = $chofer['fechaFin']->format('d-m-Y H:i:s');
                unset($chofer['fechaFin']);

                $fechaVigencia = new \DateTime("+1 year $fechaFin");
                $chofer['fechaVigencia'] = $fechaVigencia->format('d-m-Y H:i:s');
            }
        }

        return $result;
    }

    public function isChoferFromPrestador($chofer, $userPrestador, $cursoId)
    {
        $curso = $this->em->getRepository('ChoferesBundle:Curso')->find($cursoId);

        if (isset($curso)) {
            $prestador = $this->usuarioService->obtenerPrestadorPorUsuario($userPrestador);

            if (isset($prestador)) {
                if ($curso->getPrestador() === $prestador) {
                    return true;
                }
            }
        }

        return false;
    }

    public function updateTieneCursoBasico($chofer, $curso, $choferCurso)
    {
        if ($curso->getTipoCurso()->getId() == 1) {
            if ($choferCurso->isDocumentacion() == true) {
                if ($choferCurso->getPagado() == true) {
                    if ($choferCurso->getIsAprobado() == true) {
                        $chofer->setTieneCursoBasico(true);
                        $this->em->persist($chofer);
                        $this->em->flush();
                    }
                }
            }
        }
    }

    public function obtenerChoferesPorCurso($curso)
    {
        $choferes = [];
        $choferesCursos = $this->em->getRepository('ChoferesBundle:ChoferCurso')->findBy(['curso' => $curso]);
        foreach ($choferesCursos as $choferCurso) {
            $choferes[] = $choferCurso->getChofer();
        }

        return $choferes;
    }

    public function actualizarCursoChofer($curso)
    {
        $choferesCurso = $this->em->getRepository('ChoferesBundle:ChoferCurso')->findBy(['curso' => $curso]);

        foreach ($choferesCurso as $choferCurso) {
            $choferCurso->setPagado(true);
            $this->em->persist($choferCurso);

            $this->updateTieneCursoBasico(
                $choferCurso->getChofer(),
                $choferCurso->getCurso(),
                $choferCurso
            );
        }

        $this->em->flush();
    }
}
