<?php
namespace ChoferesBundle\Servicios;

use Doctrine\ORM\EntityManager;

class CursoService
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
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
