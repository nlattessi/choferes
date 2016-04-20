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
    protected $hashids;
    protected $router;
    protected $usuarioService;
    static $idTipoCursoBasico = 1;

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
        $query = $this->em->createQueryBuilder()
            ->select(
                'chofer.id as choferId', 'chofer.nombre', 'chofer.apellido', 'chofer.dni', 'chofer.tieneCursoBasico as tieneCursoBasico',
                'choferCurso.id as choferCursoId', 'choferCurso.isAprobado as aprobado','choferCurso.pagado', 'choferCurso.documentacion',
                'curso.id as cursoId', 'curso.fechaFin as fechaFin'
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
            #->andWhere('curso.tipocurso != :idTipoCursoBasico')
            ->orderBy('curso.fechaFin', 'DESC')
            ->setParameter('dni', $dni)
            #->setParameter('idTipoCursoBasico', ChoferService::$idTipoCursoBasico)
            ->setMaxResults(1)
            ->getQuery();

        $result = $query->getResult();
        /*print json_encode($result);
        exit;*/

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
            'matricula' => $chofer->getMatricula(),
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
        $choferesVigentes = [];

        $fecha = \DateTime::createFromFormat('d/m/Y', $fechaForm);
        $fechaVigente = $fecha->sub(new \DateInterval('P1Y'));

        $query = $this->em->createQueryBuilder()
            ->select(
                'chofer.id as choferId', 'chofer.nombre', 'chofer.apellido', 'chofer.dni',
                'curso.id as cursoId', 'curso.fechaFin as fechaFin',
                'chofer.tieneCursoBasico', 'choferCurso.isAprobado', 'choferCurso.pagado', 'choferCurso.documentacion'
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
            ->where('chofer.tieneCursoBasico = TRUE')
            ->andWhere('choferCurso.isAprobado = TRUE')
            ->andWhere('choferCurso.pagado = TRUE')
            ->andWhere('choferCurso.documentacion = 1')
            ->andWhere('curso.fechaFin > :fechaVigencia')
            ->orderBy('curso.fechaFin', 'DESC')
            ->setParameter('fechaVigencia', $fechaVigente)
            ->getQuery();

        $result = $query->getResult();

        if (isset($result)) {
            foreach ($result as $chofer) {
                $fechaFin = $chofer['fechaFin']->format('Y-m-d H:i:s');
                $fechaVigencia = new \DateTime("+1 year $fechaFin");

                $chofer['fechaVigencia'] = $fechaVigencia;
                $choferesVigentes[] = $chofer;
            }
        }

        return $choferesVigentes;
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
}
