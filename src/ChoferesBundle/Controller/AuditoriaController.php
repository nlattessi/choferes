<?php

namespace ChoferesBundle\Controller;

use Doctrine\ORM\Query\Expr\Join;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\View\TwitterBootstrapView;

use ChoferesBundle\Entity\Auditoria;
use ChoferesBundle\Entity\EstadoAuditoria;
use ChoferesBundle\Entity\EstadoCurso;


/**
 * Auditoria controller.
 *
 */
class AuditoriaController extends Controller
{
    /**
     * Cursos para auditar.
     *
     */
    public function indexAction()
    {
        $auditorias = $this->getDoctrine()->getRepository('ChoferesBundle:Auditoria')->findAll();

        return $this->render('ChoferesBundle:Auditoria:index.html.twig', [
            'auditorias' => $auditorias,
            'pagetitle' => 'Auditorias',
            'css_active' => 'auditorias',
        ]);
    }

    public function indexAuditoriasActivasAction()
    {
        $auditorias = $this->getDoctrine()->getRepository('ChoferesBundle:Auditoria')
            ->findByEstado(EstadoAuditoria::ID_ACTIVA);

        return $this->render('ChoferesBundle:Auditoria:index.html.twig', [
            'auditorias' => $auditorias,
            'pagetitle' => 'Auditorias Activas',
            'css_active' => 'auditoria_activas',
        ]);
    }

    public function indexAuditoriasIntermedioAction()
    {
        $auditorias = $this->getDoctrine()->getRepository('ChoferesBundle:Auditoria')
            ->findByEstado(EstadoAuditoria::ID_INTERMEDIO);

        return $this->render('ChoferesBundle:Auditoria:index.html.twig', [
            'auditorias' => $auditorias,
            'pagetitle' => 'Auditorias en estado Intermedio',
            'css_active' => 'auditoria_intermedio',
        ]);
    }

    public function indexAuditoriasTerminadasAction()
    {
        $auditorias = $this->getDoctrine()->getRepository('ChoferesBundle:Auditoria')
            ->findByEstado(EstadoAuditoria::ID_TERMINADA);

        return $this->render('ChoferesBundle:Auditoria:index.html.twig', [
            'auditorias' => $auditorias,
            'pagetitle' => 'Auditorias Terminadas',
            'css_active' => 'auditoria_terminadas',
        ]);
    }

    public function showAction($id)
    {
        $auditoria = $this->getDoctrine()->getRepository('ChoferesBundle:Auditoria')->find($id);

        if (!$auditoria) {
            throw $this->createNotFoundException('Unable to find Auditoria entity.');
        }

        return $this->render('ChoferesBundle:Auditoria:show.html.twig', [
            'auditoria'  => $auditoria,
            'css_active' => 'auditoria',
        ]);
    }

    public function newAction()
    {
        $repo = $this->getDoctrine()->getRepository('ChoferesBundle:Prestador');
        $query = $repo->createQueryBuilder('p')
            ->where('p.activo = true')
            ->getQuery();
        $prestadores = collect($query->getResult());

        $repo = $this->getDoctrine()->getRepository('ChoferesBundle:Curso');
        $query = $repo->createQueryBuilder('c')
                ->where('c.estado = :estadoConfirmado')
                ->andWhere('c.auditoria is NULL')
                ->setParameter('estadoConfirmado', EstadoCurso::ID_CONFIRMADO)
                ->getQuery();
        $cursos = collect($query->getResult());

        $minimoCursoPorPrestador = $prestadores->map(function ($prestador) use ($cursos) {
           return $cursos->filter(function ($curso) use ($prestador) {
                return $curso->getPrestador() === $prestador;
            })->shuffle()->first();
        })->filter();

        $cursosRandom = $cursos->reject(function ($curso) use ($minimoCursoPorPrestador) {
            return $minimoCursoPorPrestador->contains(function ($cursoElegido) use ($curso) {
                return $curso === $cursoElegido;
            });
        })->shuffle();

        $max = (int)(($cursosRandom->count() - $minimoCursoPorPrestador->count()) * 0.2); // 20%

        $cursos = $cursosRandom->take($max)
            ->sortBy(function ($curso) {
                return $curso->getPrestador()->getNombre();
            });

        return $this->render('ChoferesBundle:Auditoria:new.html.twig', [
            'cursos' => $cursos,
            'css_active'  => 'auditoria_new',
        ]);
    }

    public function createAction()
    {

    }

    public function crearAction()
    {
        $cursos = $this->getRequest()->get("cursos");
        $em = $this->getDoctrine()->getManager();

        foreach ($cursos as $curso) {
            $auditoria = new Auditoria();
            $auditoriaEstado = $em->getRepository('ChoferesBundle:EstadoAuditoria')->find(EstadoAuditoria::ID_BORRADOR);
            $auditoria->setEstado($auditoriaEstado);
            $cursoEntity = $em->getRepository('ChoferesBundle:Curso')->find($curso);
            $auditoria->setCurso($cursoEntity);
            $em->persist($auditoria);
            $em->flush();
        }

        return $this->redirect('confirmar');

    }

    public function confirmarCursosAction()
    {
        $repo = $this->getDoctrine()->getRepository('ChoferesBundle:Curso');
        $query = $repo->createQueryBuilder('c')
            ->leftJoin(
            'ChoferesBundle:Auditoria', 'auditoria',
            \Doctrine\ORM\Query\Expr\Join::WITH, 'auditoria.curso = c.id')
            ->leftJoin(
                'ChoferesBundle:EstadoAuditoria', 'ea',
                \Doctrine\ORM\Query\Expr\Join::WITH, 'ea.id = auditoria.estado')
            ->where('ea.id = :estado')
            ->setParameter('estado', EstadoAuditoria::ID_BORRADOR)
            ->getQuery();

        $cursos = collect($query->getResult());
        return $this->render('ChoferesBundle:Auditoria:confirmar.html.twig', [
            'cursos' => $cursos,
            'css_active'  => 'auditoria_confirmar',
        ]);
    }

    public function quitarAction()
    {
        $em = $this->getDoctrine()->getManager();
        $cursoId = $this->getRequest()->get("id");

        $repository = $this->getDoctrine()->getRepository('ChoferesBundle:Auditoria');

        $query = $repository->createQueryBuilder('a')
            ->where('a.curso = :curso')
            ->setParameter('curso', $cursoId)
            ->getQuery();

        $results = $query->getResult();
        foreach ($results as $result) {
            if($result instanceof Auditoria) {

                $em->remove($result);
                $em->flush();
            }
        }

        return $this->redirect('confirmar');
    }
}
