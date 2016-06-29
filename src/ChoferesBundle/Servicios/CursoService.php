<?php
namespace ChoferesBundle\Servicios;

use ChoferesBundle\Controller\CursoController;
use ChoferesBundle\Entity\Curso;
use Doctrine\ORM\EntityManager;

class CursoService
{
    private $em;
    private $pagoService;

    public function __construct(EntityManager $em, $pagoService)
    {
        $this->em = $em;
        $this->pagoService = $pagoService;
    }

    public function cargaMasivaTri($ids, $tris)
    {
        $updatedCursos = [];

        foreach ($ids as $key => $idCurso) {
            if (! empty($idCurso) && ! empty($tris[$key])) {
                $updatedCursos[] = $this->updateCursoTri($idCurso, $tris[$key]);
            }
        }

        return $updatedCursos;
    }

    public function getCursosPorTipoFilterByFechaInicio($tipoCurso, $fechaInicioDesde, $fechaInicioHasta)
    {
        $fechaDesde = \DateTime::createFromFormat('d/m/Y', $fechaInicioDesde);
        $fechaHasta = \DateTime::createFromFormat('d/m/Y', $fechaInicioHasta);

        $cursos = $this->em->getRepository('ChoferesBundle:Curso')->findCursosByTipoFilterByFechaInicio(
            $tipoCurso,
            $fechaDesde,
            $fechaHasta
        );

        return $cursos;

    }

    private function updateCursoTri($cursoId, $tri)
    {
        $curso = $this->em->getRepository('ChoferesBundle:Curso')->find($cursoId);

        if (isset($curso)) {
            $curso->setCodigo($tri);
            $this->em->persist($curso);
        }

        $this->em->flush();

        return $curso;
    }
/*
 * Podría extenderse para manejar todos los cambios de estado en un solo lugar
 * */
    public function actualizarEstado(Curso $curso){

        $curso = $this->pagoService->setCursoMontoTotal($curso);
        $curso = $this->pagoService->setCursoMontoRecaudado($curso);
        if($curso->getMontoRecaudado() < $curso->getMontoTotal()){
            //volver a estado POR_PAGAR

             $curso->setEstado($this->em->getRepository('ChoferesBundle:EstadoCurso')->find( CursoController::ESTADO_CURSO_PORPAGAR));
        }else{
            //pasar a por Verificar
            $curso->setEstado($this->em->getRepository('ChoferesBundle:EstadoCurso')->find( CursoController::ESTADO_CURSO_PORVALIDAR));
        }

        $this->em->persist($curso);
        $this->em->flush();

        return $curso;
    }


}
