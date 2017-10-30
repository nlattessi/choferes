<?php

namespace ChoferesBundle\Servicios;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Carbon\Carbon;

class ChoferService
{
    const ESTADO_CURSO_VALIDADO  = 'Validado';
    const ESTADO_CURSO_CANCELADO = 'Cancelado';

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
            ->andWhere('chofer.estaActivo = TRUE')
            ->orderBy('curso.fechaFin', 'DESC')
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
        $chofer = $this->em->getRepository('ChoferesBundle:Chofer')->findOneBy(['dni' => $dni, 'estaActivo' => TRUE]);

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

    public function getChoferesVigentesPorFechaCurso($fechaDesdeForm, $fechaHastaForm)
    {
        $fechaDesde = \DateTime::createFromFormat('d/m/Y', $fechaDesdeForm);
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
            ->andWhere('curso.fechaInicio >= :fechaDesde')
            ->andWhere('curso.fechaFin <= :fechaHasta')
            ->andWhere('chofer.estaActivo = TRUE')
            ->orderBy('curso.fechaCreacion', 'DESC')
            ->setParameter('fechaDesde', $fechaDesde->format('Y-m-d'))
            ->setParameter('fechaHasta', $fechaHasta->format('Y-m-d'))
            ->getQuery();

        $result = $query->getResult();

        if (isset($result)) {
            foreach ($result as & $chofer) {
                $fechaFin = $chofer['fechaFin']->format('d-m-Y H:i:s');
                unset($chofer['fechaFin']);
                $fechaVencimientoCertificado = new \DateTime("+1 year $fechaFin");
                $chofer['fechaVencimientoCertificado'] = $fechaVencimientoCertificado->format('d-m-Y H:i:s');
            }
        }

        return $result;
    }

    public function getTotalChoferesVigentesPorFechaCurso($fechaDesde, $fechaHasta)
    {
        $fechaDesde = \DateTime::createFromFormat('d/m/Y', $fechaDesde);
        $fechaHasta = \DateTime::createFromFormat('d/m/Y', $fechaHasta);

        $query = $this->em->createQueryBuilder()
            ->select('COUNT(chofer.id)')
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
            ->andWhere('curso.fechaInicio >= :fechaDesde')
            ->andWhere('curso.fechaFin <= :fechaHasta')
            ->andWhere('chofer.estaActivo = TRUE')
            ->orderBy('curso.fechaCreacion', 'DESC')
            ->setParameter('fechaDesde', $fechaDesde->format('Y-m-d'))
            ->setParameter('fechaHasta', $fechaHasta->format('Y-m-d'))
            ->getQuery();

        return $query->getSingleScalarResult();
    }

    public function getChoferesVigentesPorFechaValidacion($fechaDesdeForm, $fechaHastaForm)
    {
        $fechaDesde = \DateTime::createFromFormat('d/m/Y', $fechaDesdeForm);
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
            ->andWhere('curso.fechaValidacion >= :fechaDesde')
            ->andWhere('curso.fechaValidacion <= :fechaHasta')
            ->andWhere('chofer.estaActivo = TRUE')
            ->orderBy('curso.fechaValidacion', 'DESC')
            ->setParameter('fechaDesde', $fechaDesde->format('Y-m-d'))
            ->setParameter('fechaHasta', $fechaHasta->format('Y-m-d'))
            ->getQuery();

        $result = $query->getResult();

        if (isset($result)) {
            foreach ($result as & $chofer) {
                $fechaFin = $chofer['fechaFin']->format('d-m-Y H:i:s');
                unset($chofer['fechaFin']);
                $fechaVencimientoCertificado = new \DateTime("+1 year $fechaFin");
                $chofer['fechaVencimientoCertificado'] = $fechaVencimientoCertificado->format('d-m-Y H:i:s');
            }
        }

        return $result;
    }

    public function getTotalChoferesVigentesPorFechaValidacion($fechaDesde, $fechaHasta)
    {
        $fechaDesde = \DateTime::createFromFormat('d/m/Y', $fechaDesde);
        $fechaHasta = \DateTime::createFromFormat('d/m/Y', $fechaHasta);

        $query = $this->em->createQueryBuilder()
            ->select('COUNT(chofer.id)')
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
            ->andWhere('curso.fechaValidacion >= :fechaDesde')
            ->andWhere('curso.fechaValidacion <= :fechaHasta')
            ->andWhere('chofer.estaActivo = TRUE')
            ->orderBy('curso.fechaValidacion', 'DESC')
            ->setParameter('fechaDesde', $fechaDesde->format('Y-m-d'))
            ->setParameter('fechaHasta', $fechaHasta->format('Y-m-d'))
            ->getQuery();

        return $query->getSingleScalarResult();
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

    public function getCursosAuditoriaPorFechaCurso($fechaDesdeForm, $fechaHastaForm)
    {
        $fechaDesde = Carbon::createFromFormat('d/m/Y', $fechaDesdeForm);
        $fechaHasta = Carbon::createFromFormat('d/m/Y', $fechaHastaForm)->addDay();

        $query = $this->em->createQueryBuilder()
            ->select(
                'P.nombre as prestador',
                'TC.nombre as curso_tipo',
                'C.id as curso_numero',
                'C.fechaInicio as fecha_curso',
                'S.direccion as lugar_direccion',
                'S.provincia AS lugar_provincia',
                'S.ciudad AS lugar_ciudad',
                'D.nombre AS docente_nombre',
                'D.apellido as docente_apellido'
            )
            ->from('ChoferesBundle:Curso', 'C')
            ->innerJoin(
                'ChoferesBundle:Prestador', 'P',
                Join::WITH, 'C.prestador = P.id'
            )
            ->innerJoin(
                'ChoferesBundle:Docente', 'D',
                Join::WITH, 'C.docente = D.id'
            )
            ->innerJoin(
                'ChoferesBundle:Sede', 'S',
                Join::WITH, 'C.sede = S.id'
            )
            ->innerJoin(
                'ChoferesBundle:TipoCurso', 'TC',
                Join::WITH, 'C.tipocurso = TC.id'
            )
            ->where('C.fechaInicio >= :fechaDesde')
            ->andWhere('C.fechaInicio < :fechaHasta')
            ->orderBy('C.id', 'ASC')
            ->setParameter('fechaDesde', $fechaDesde->format('Y-m-d'))
            ->setParameter('fechaHasta', $fechaHasta->format('Y-m-d'))
            ->getQuery();

        $result = $query->getResult();

        if (isset($result)) {
            return collect($result)->map(function ($curso) {
                $curso['fecha_curso'] = $curso['fecha_curso']->format('d-m-Y H:i:s');
                return $curso;
            })->all();
        }

        return $result;
    }

    public function getCursosYaRealizados()
    {
        $estadoCancelado = $this->em->getRepository('ChoferesBundle:EstadoCurso')->findByNombre(self::ESTADO_CURSO_CANCELADO);

        $fechaHoy = Carbon::now();

        $query = $this->em->createQueryBuilder()
            ->select('C')
            ->from('ChoferesBundle:Curso', 'C')
            ->where('C.estado != :estadoCancelado')
            ->andWhere('C.fechaFin < :fechaHoy')
            ->orderBy('C.id', 'ASC')
            ->setParameter('estadoCancelado', $estadoCancelado)
            ->setParameter('fechaHoy', $fechaHoy->format('Y-m-d'))
            ->getQuery();

        $result = $query->getResult();

        if (isset($result)) {
            return collect($result)->map(function ($curso) {
                $fechaInicio = $curso->getFechaInicio();
                $fechaFin = $curso->getFechaFin();
                $fechaValidacion = $curso->getFechaValidacion();
                $fechaPago = $curso->getFechaPago();

                return [
                    'id' => $curso->getId(),
                    'fecha_inicio' => isset($fechaInicio) ? $fechaInicio->format('d-m-Y H:i:s') : "",
                    'fecha_fin' => isset($fechaFin) ? $fechaFin->format('d-m-Y H:i:s') : "",
                    'fecha_validacion' => isset($fechaValidacion) ? $fechaValidacion->format('d-m-Y H:i:s') : "",
                    'codigo' => $curso->getCodigo(),
                    'año' => $curso->getAnio(),
                    'comprobante' => $curso->getComprobante(),
                    'docente' => (string)$curso->getDocente(),
                    'estado' => (string)$curso->getEstado(),
                    'prestador' => (string)$curso->getPrestador(),
                    'sede' => (string)$curso->getSede(),
                    'tipo' => (string)$curso->getTipoCurso(),
                    'fecha_pago' => isset($fechaPago) ? $fechaPago->format('d-m-Y H:i:s') : "",
                    'observaciones' => (string)$curso->getObservaciones(),
                ];
            })->all();
        }

        return $result;
    }

    public function getCursosPorRealizar()
    {
        $estadoCancelado = $this->em->getRepository('ChoferesBundle:EstadoCurso')->findByNombre(self::ESTADO_CURSO_CANCELADO);

        $fechaHoy = Carbon::now();

        $query = $this->em->createQueryBuilder()
            ->select('C')
            ->from('ChoferesBundle:Curso', 'C')
            ->where('C.estado != :estadoCancelado')
            ->andWhere('C.fechaFin >= :fechaHoy')
            ->orderBy('C.id', 'ASC')
            ->setParameter('estadoCancelado', $estadoCancelado)
            ->setParameter('fechaHoy', $fechaHoy->format('Y-m-d'))
            ->getQuery();

        $result = $query->getResult();

        if (isset($result)) {
            return collect($result)->map(function ($curso) {
                $fechaInicio = $curso->getFechaInicio();
                $fechaFin = $curso->getFechaFin();
                $fechaValidacion = $curso->getFechaValidacion();
                $fechaPago = $curso->getFechaPago();

                return [
                    'id' => $curso->getId(),
                    'fecha_inicio' => isset($fechaInicio) ? $fechaInicio->format('d-m-Y H:i:s') : "",
                    'fecha_fin' => isset($fechaFin) ? $fechaFin->format('d-m-Y H:i:s') : "",
                    'fecha_validacion' => isset($fechaValidacion) ? $fechaValidacion->format('d-m-Y H:i:s') : "",
                    'codigo' => $curso->getCodigo(),
                    'año' => $curso->getAnio(),
                    'comprobante' => $curso->getComprobante(),
                    'docente' => (string)$curso->getDocente(),
                    'estado' => (string)$curso->getEstado(),
                    'prestador' => (string)$curso->getPrestador(),
                    'sede' => (string)$curso->getSede(),
                    'tipo' => (string)$curso->getTipoCurso(),
                    'fecha_pago' => isset($fechaPago) ? $fechaPago->format('d-m-Y H:i:s') : "",
                    'observaciones' => (string)$curso->getObservaciones(),
                ];
            })->all();
        }

        return $result;
    }

    private function adaptDates(&$choferes)
    {
        foreach ($choferes as &$chofer) {
            $fechaFin = $chofer['fechaFin']->format('d-m-Y H:i:s');
            $chofer['fechaFin'] = $fechaFin;

            $fechaVigencia = new \DateTime("+1 year $fechaFin");
            $chofer['fechaVigencia'] = $fechaVigencia->format('d-m-Y H:i:s');
        }
    }
}
