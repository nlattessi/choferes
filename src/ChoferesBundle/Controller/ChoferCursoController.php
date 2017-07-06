<?php

namespace ChoferesBundle\Controller;

use ChoferesBundle\Resources\views\TwitterBootstrapViewCustom;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;

use Symfony\Component\HttpFoundation\Response;


use ChoferesBundle\Entity\ChoferCurso;
use ChoferesBundle\Form\ChoferCursoType;
use ChoferesBundle\Form\ChoferCursoFilterType;

/**
 * ChoferCurso controller.
 *
 */
class ChoferCursoController extends Controller
{
    /**
     * Lists all ChoferCurso entities.
     *
     */
    public function indexAction()
    {
        list($filterForm, $queryBuilder) = $this->filter();

        list($entities, $pagerHtml) = $this->paginator($queryBuilder);

        return $this->render('ChoferesBundle:ChoferCurso:index.html.twig', array(
            'entities' => $entities,
            'pagerHtml' => $pagerHtml,
            'filterForm' => $filterForm->createView(),
        ));
    }

    public function autocompletarAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQueryBuilder()
            ->select('c.nombre', 'c.apellido', 'c.id', 'c.dni', 'c.tieneCursoBasico')
            ->from('ChoferesBundle:Chofer', 'c')
            ->leftJoin(
                'ChoferesBundle:ChoferCurso',
                'cc',
                \Doctrine\ORM\Query\Expr\Join::WITH,
                'cc.chofer = c.id'
            )
            ->leftJoin(
                'ChoferesBundle:Curso',
                'cu',
                \Doctrine\ORM\Query\Expr\Join::WITH,
                'cc.curso = cu.id'
            )
            ->where('LOWER(c.nombre) LIKE LOWER(:query) OR LOWER(c.apellido) LIKE LOWER(:query) OR c.dni LIKE :query')
            ->andWhere('cu.id <> :idcurso OR cu.id IS NULL')
            ->setParameter('query', '%'.$request->query->get('query').'%')
            ->setParameter('idcurso', $request->query->get('idcurso'))
            ->distinct()
            ->getQuery();

        $entities = $query->getResult();

        return new Response(json_encode($entities));
    }

    /**
    * Create filter form and process filter request.
    *
    */
    protected function filter()
    {
        $request = $this->getRequest();
        $session = $request->getSession();
        $filterForm = $this->createForm(new ChoferCursoFilterType());
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em->getRepository('ChoferesBundle:ChoferCurso')->createQueryBuilder('e');

        // Reset filter
        $session->remove('ChoferCursoControllerFilter');


        // Filter action
        if ($request->get('filter_action') == 'filter') {
            // Bind values from the request
            $filterForm->bind($request);

            if ($filterForm->isValid()) {
                // Build the query from the given form object
                $this->get('lexik_form_filter.query_builder_updater')->addFilterConditions($filterForm, $queryBuilder);
                // Save filter to session
                $filterData = $filterForm->getData();
                $session->set('ChoferCursoControllerFilter', $filterData);
            }
        } else {
            // Get filter from session
            if ($session->has('ChoferCursoControllerFilter')) {
                $filterData = $session->get('ChoferCursoControllerFilter');
                $filterForm = $this->createForm(new ChoferCursoFilterType(), $filterData);
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
            return $me->generateUrl('chofercurso', array('page' => $page));
        };

        // Paginator - view
        $translator = $this->get('translator');
        $view = new TwitterBootstrapViewCustom();
        $pagerHtml = $view->render($pagerfanta, $routeGenerator, array(
            'proximity' => 3,
            'prev_message' => $translator->trans('views.index.pagprev', array(), 'JordiLlonchCrudGeneratorBundle'),
            'next_message' => $translator->trans('views.index.pagnext', array(), 'JordiLlonchCrudGeneratorBundle'),
        ));

        return array($entities, $pagerHtml);
    }

    /**
     * Creates a new ChoferCurso entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity  = new ChoferCurso();

        /*Inicio filtro por prestador*/
        $usuario = $this->getUser();
        $usuarioService =  $this->get('choferes.servicios.usuario');

        if($usuario->getRol() == 'ROLE_PRESTADOR') {
            //filtro solo lo que es de este usuario
            $prestador = $usuarioService->obtenerPrestadorPorUsuario($usuario);
            $form = $this->createForm(new ChoferCursoType(), $entity, array(
                'attr' => array('prestadorId' => $prestador->getId())));
        }else{
            $form = $this->createForm(new ChoferCursoType(), $entity, array(
                'attr' => array('prestadorId' => null)));
        }
        /*Fin filtro por prestador*/


        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'flash.create.success');

            return $this->redirect($this->generateUrl('chofercurso_show', array('id' => $entity->getId())));
        }

        return $this->render('ChoferesBundle:ChoferCurso:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Displays a form to create a new ChoferCurso entity.
     *
     */
    public function newAction()
    {
        $entity = new ChoferCurso();

        /*Inicio filtro por prestador*/
        $usuario = $this->getUser();
        $usuarioService =  $this->get('choferes.servicios.usuario');

        if($usuario->getRol() == 'ROLE_PRESTADOR') {
            //filtro solo lo que es de este usuario
            $prestador = $usuarioService->obtenerPrestadorPorUsuario($usuario);
            $form = $this->createForm(new ChoferCursoType(), $entity, array(
                'attr' => array('prestadorId' => $prestador->getId())));
        }else{
            $form = $this->createForm(new ChoferCursoType(), $entity, array(
                'attr' => array('prestadorId' => null)));
        }
        /*Fin filtro por prestador*/

        return $this->render('ChoferesBundle:ChoferCurso:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a ChoferCurso entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ChoferesBundle:ChoferCurso')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ChoferCurso entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ChoferesBundle:ChoferCurso:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),        ));
    }

    /**
     * Displays a form to edit an existing ChoferCurso entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ChoferesBundle:ChoferCurso')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ChoferCurso entity.');
        }

        $editForm = $this->createForm(new ChoferCursoType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ChoferesBundle:ChoferCurso:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Edits an existing ChoferCurso entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ChoferesBundle:ChoferCurso')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ChoferCurso entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new ChoferCursoType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'flash.update.success');

            return $this->redirect($this->generateUrl('chofercurso_edit', array('id' => $id)));
        } else {
            $this->get('session')->getFlashBag()->add('error', 'flash.update.error');
        }

        return $this->render('ChoferesBundle:ChoferCurso:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a ChoferCurso entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('ChoferesBundle:ChoferCurso')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find ChoferCurso entity.');
            }

            $em->remove($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'flash.delete.success');
        } else {
            $this->get('session')->getFlashBag()->add('error', 'flash.delete.error');
        }

        return $this->redirect($this->generateUrl('chofercurso'));
    }

    /**
     * Creates a form to delete a ChoferCurso entity by id.
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
}
