<?php

namespace ChoferesBundle\Controller;

use ChoferesBundle\Entity\Chofer;
use ChoferesBundle\Entity\ChoferCurso;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;


use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\View\TwitterBootstrapView;

use ChoferesBundle\Entity\Curso;
use ChoferesBundle\Form\CursoType;
use ChoferesBundle\Form\CursoFilterType;

/**
 * Curso controller.
 *
 */
class CursoController extends Controller
{
    /**
     * Lists all Curso entities.
     *
     */
    public function indexAction()
    {
        list($filterForm, $queryBuilder) = $this->filter();

        list($entities, $pagerHtml) = $this->paginator($queryBuilder);

        return $this->render('ChoferesBundle:Curso:index.html.twig', array(
            'entities' => $entities,
            'pagerHtml' => $pagerHtml,
            'filterForm' => $filterForm->createView(),
        ));
    }

    public function indexCursosAnterioresAction()
    {
        list($filterForm, $queryBuilder) = $this->filter();

        $queryBuilder
          ->andWhere('d.fechaInicio < :fechaHoy')
          ->setParameter('fechaHoy', new \DateTime('-1 day'), \Doctrine\DBAL\Types\Type::DATETIME);

        list($entities, $pagerHtml) = $this->paginator($queryBuilder);

        return $this->render('ChoferesBundle:Curso:index.html.twig', array(
            'entities' => $entities,
            'pagerHtml' => $pagerHtml,
            'filterForm' => $filterForm->createView(),
        ));
    }

    public function addchoferAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        if ($request->getMethod() == 'POST') {
            $id =  $request->get('idCurso');
            $curso =  $em->getRepository('ChoferesBundle:Curso')->findOneBy(array('id' => $id));
            $choferesIds = $request->get('chofer');
            foreach ($choferesIds as $idChofer) {
                $choferCurso = new ChoferCurso();
                $chofer = $em->getRepository('ChoferesBundle:Chofer')->find($idChofer);
                $choferCurso->setChofer($chofer);
                $choferCurso->setCurso($curso);
                $em->persist($choferCurso);
            }
            $em->flush();
            $choferes = $this->obtenerChoferesPorCurso($curso);
        }else{
            $id =  $request->query->get('idCurso');

            $curso =  $em->getRepository('ChoferesBundle:Curso')->findOneBy(array('id' => $id));
            $choferes = $this->obtenerChoferesPorCurso($curso);
        }

        //print_r($choferes[0]);exit;

        return $this->render('ChoferesBundle:Curso:addchofer.html.twig', array(
            'idCurso'=> $id,
            'entities' => $choferes
        ));
    }

    public function borrarChoferAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $id =  $request->query->get('idBorrar');

        $qb = $em->createQueryBuilder();
        $qb->delete('ChoferesBundle:ChoferCurso', 'c');
        $q = $em->createQuery('delete from ChoferesBundle:ChoferCurso c where c.chofer = '.$id);
        $qb->andWhere($qb->expr()->eq('c.id', ':id'));
        $qb->setParameter(':project', $id);
       // $qb->getQuery()->execute();
        $q->execute();

        $idCurso =  $request->query->get('idCurso');
        // $curso =  $em->getRepository('ChoferesBundle:Curso')->findOneBy(array('id' => $idCurso));
        // $choferes = $this->obtenerChoferesPorCurso($curso);
        // return $this->render('ChoferesBundle:Curso:addchofer.html.twig', array(
        //     'idCurso'=> $idCurso,
        //     'entities' => $choferes
        // ));
        return $this->redirect($this->generateUrl('curso_addchofer', array('idCurso' => $idCurso)));
    }

        /**
    * Create filter form and process filter request.
    *
    */
    protected function filter()
    {
        $request = $this->getRequest();
        $session = $request->getSession();
        $filterForm = $this->createForm(new CursoFilterType());
        $em = $this->getDoctrine()->getManager();
        /*Inicio filtro por prestador*/
        $usuario = $this->getUser();
        $usuarioService =  $this->get('choferes.servicios.usuario');

        if($usuario->getRol() == 'ROLE_PRESTADOR') {
            //filtro solo lo que es de este usuario
            $prestador = $usuarioService->obtenerPrestadorPorUsuario($usuario);
            $queryBuilder = $em->getRepository('ChoferesBundle:Curso')->createQueryBuilder('d')
                ->where('d.prestador = ?1')
                ->setParameter(1, $prestador->getId());
        }else{
            $queryBuilder = $em->getRepository('ChoferesBundle:Curso')->createQueryBuilder('d');
        }
        /*Fin filtro por prestador*/

        // Reset filter
        if ($request->get('filter_action') == 'reset') {
            $session->remove('CursoControllerFilter');
        }

        // Filter action
        if ($request->get('filter_action') == 'filter') {
            // Bind values from the request
            $filterForm->bind($request);

            if ($filterForm->isValid()) {
                // Build the query from the given form object
                $this->get('lexik_form_filter.query_builder_updater')->addFilterConditions($filterForm, $queryBuilder);
                // Save filter to session
                $filterData = $filterForm->getData();
                $session->set('CursoControllerFilter', $filterData);
            }
        } else {
            // Get filter from session
            if ($session->has('CursoControllerFilter')) {
                $filterData = $session->get('CursoControllerFilter');
                $filterForm = $this->createForm(new CursoFilterType(), $filterData);
                $this->get('lexik_form_filter.query_builder_updater')->addFilterConditions($filterForm, $queryBuilder);
            }
        }

        return array($filterForm, $queryBuilder);
    }

    /**
    * Get results from paginator and get paginator view.
    *
    */
    protected function paginator($queryBuilder)
    {
        // Paginator
        $adapter = new DoctrineORMAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);
        $currentPage = $this->getRequest()->get('page', 1);
        $pagerfanta->setCurrentPage($currentPage);
        $entities = $pagerfanta->getCurrentPageResults();

        // Paginator - route generator
        $me = $this;
        $routeGenerator = function($page) use ($me)
        {
            return $me->generateUrl('curso', array('page' => $page));
        };

        // Paginator - view
        $translator = $this->get('translator');
        $view = new TwitterBootstrapView();
        $pagerHtml = $view->render($pagerfanta, $routeGenerator, array(
            'proximity' => 3,
            'prev_message' => $translator->trans('views.index.pagprev', array(), 'JordiLlonchCrudGeneratorBundle'),
            'next_message' => $translator->trans('views.index.pagnext', array(), 'JordiLlonchCrudGeneratorBundle'),
        ));

        return array($entities, $pagerHtml);
    }

    /**
     * Creates a new Curso entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity  = new Curso();
        $form = $this->createForm(new CursoType(), $entity);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $usuario = $this->getUser();
            $usuarioService =  $this->get('choferes.servicios.usuario');
            if ($usuario->getRol() == 'ROLE_PRESTADOR') {
                $prestador = $usuarioService->obtenerPrestadorPorUsuario($usuario);
                $entity->setPrestador($prestador);
            }

            $em->persist($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'flash.create.success');

            return $this->redirect($this->generateUrl('curso_show', array('id' => $entity->getId())));
        }

        return $this->render('ChoferesBundle:Curso:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Displays a form to create a new Curso entity.
     *
     */
    public function newAction()
    {
        $entity = new Curso();
        $form   = $this->createForm(new CursoType(), $entity);

        return $this->render('ChoferesBundle:Curso:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Curso entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ChoferesBundle:Curso')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Curso entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ChoferesBundle:Curso:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),        ));
    }

    /**
     * Displays a form to edit an existing Curso entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ChoferesBundle:Curso')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Curso entity.');
        }

        $editForm = $this->createForm(new CursoType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ChoferesBundle:Curso:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Edits an existing Curso entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ChoferesBundle:Curso')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Curso entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new CursoType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'flash.update.success');

            return $this->redirect($this->generateUrl('curso_edit', array('id' => $id)));
        } else {
            $this->get('session')->getFlashBag()->add('error', 'flash.update.error');
        }

        return $this->render('ChoferesBundle:Curso:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Curso entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('ChoferesBundle:Curso')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Curso entity.');
            }

            $em->remove($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'flash.delete.success');
        } else {
            $this->get('session')->getFlashBag()->add('error', 'flash.delete.error');
        }

        return $this->redirect($this->generateUrl('curso'));
    }

    /**
     * Creates a form to delete a Curso entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }

    private function obtenerChoferesPorCurso($curso)
    {
        $em = $this->getDoctrine()->getManager();
        $choferes = array();
        //
        $choferesCursos =$em->getRepository('ChoferesBundle:ChoferCurso')->findBy(array('curso' => $curso));
      //  print_r($choferesCursos->getId());exit;
        foreach($choferesCursos as $choferCurso){
            $choferes[]= $choferCurso->getChofer();
        }
        return $choferes;

    }
}
