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

    public function getCursosFilterByFechaInicio($fechaInicioDesde, $fechaInicioHasta)
    {
        $fechaDesde = \DateTime::createFromFormat('d/m/Y', $fechaInicioDesde);
        $fechaHasta = \DateTime::createFromFormat('d/m/Y', $fechaInicioHasta);

        $cursos = $this->em->getRepository('ChoferesBundle:Curso')->findCursosFilterByFechaInicio(
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
}
