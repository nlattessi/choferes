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

    public function getTiposCurso()
    {
        return $this->em->getRepository('ChoferesBundle:TipoCurso')->findAll();
    }

    public function getCursosPorTipoFilterByFechaInicio($tipoCurso, $fechaInicioDesde, $fechaInicioHasta)
    {
        $fechaDesde = \DateTimeUtils::createDateTime($fechaInicioDesde);
        $fechaHasta = \DateTimeUtils::createDateTime($fechaInicioHasta);

        $cursos = $this->em->getRepository('ChoferesBundle:Curso')->findCursosByTipoFilterByFechaInicio(
            $tipoCurso,
            $fechaDesde,
            $fechaHasta
        );

        return $cursos;
    }

    public function getCursosFilterByFechaInicio($fechaInicioDesde, $fechaInicioHasta)
    {
        $fechaDesde = \DateTimeUtils::createDateTime($fechaInicioDesde);
        $fechaHasta = \DateTimeUtils::createDateTime($fechaInicioHasta);

        $cursos = $this->em->getRepository('ChoferesBundle:Curso')->findCursosFilterByFechaInicio(
            $fechaDesde,
            $fechaHasta
        );

        return $cursos;
    }

    public function getCursosPorTipo($cursos, $tipo)
    {
        return array_filter($cursos, function($curso) use ($tipo) {
            return $curso->getTipocurso() === $tipo;
        });
    }

     public function getMontoTotalCursos($cursos)
    {
        return array_reduce(
            $cursos,
            function($count, $curso) {
                return $count + $curso->getMontoTotal();
            },
            0.0
        );
    }

    public function getMontoRecaudadoCursos($cursos)
    {
        return array_reduce(
            $cursos,
            function($count, $curso) {
                return $count + $curso->getMontoRecaudado();
            },
            0.0
        );
    }

    public function getTotalAlumnos($cursos)
    {
        return array_reduce(
            $cursos,
            function($count, $curso) {
                return $count + $curso->getChoferCursos()->count();
            },
            0
        );
    }

    /*
     * Podría extenderse para manejar todos los cambios de estado en un solo lugar
     * */
    public function actualizarEstado(Curso $curso) {
        $curso = $this->pagoService->setCursoMontoTotal($curso);
        $curso = $this->pagoService->setCursoMontoRecaudado($curso);

        if ($curso->getEstado()->getId() === CursoController::ESTADO_CURSO_CONFIRMADO) {
            return $curso;
        }

        if ($curso->getMontoRecaudado() < $curso->getMontoTotal()) {
            //volver a estado POR_PAGAR
             $curso->setEstado(
                $this->em->getRepository('ChoferesBundle:EstadoCurso')->find(CursoController::ESTADO_CURSO_PORPAGAR)
            );

            //actualizar estado de la relación choferCurso
            foreach ($curso->getChoferCursos() as $choferCurso) {
                $choferCurso->setPagado(false);
                $this->em->persist($choferCurso);
            }
        } else {
            //pasar a por Verificar
            $curso->setEstado(
                $this->em->getRepository('ChoferesBundle:EstadoCurso')->find(CursoController::ESTADO_CURSO_PORVALIDAR)
            );

            //actualizar estado de la relación choferCurso
            foreach ($curso->getChoferCursos() as $choferCurso) {
                $choferCurso->setPagado(true);
                $this->em->persist($choferCurso);
            }
        }

        $this->em->persist($curso);
        $this->em->flush();

        return $curso;
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
}
