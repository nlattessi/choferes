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
