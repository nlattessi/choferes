<?php

namespace ChoferesBundle\Controller;

use ChoferesBundle\Resources\views\TwitterBootstrapViewCustom;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;

use ChoferesBundle\Entity\UsuarioPrestador;
use ChoferesBundle\Form\UsuarioPrestadorType;
use ChoferesBundle\Form\UsuarioPrestadorFilterType;

/**
 * UsuarioPrestador controller.
 *
 */
class UsuarioPrestadorController extends Controller
{
    /**
     * Lists all UsuarioPrestador entities.
     *
     */
    public function indexAction()
    {
        list($filterForm, $queryBuilder) = $this->filter();

        list($entities, $pagerHtml) = $this->paginator($queryBuilder);

        return $this->render('ChoferesBundle:UsuarioPrestador:index.html.twig', array(
            'entities' => $entities,
            'pagerHtml' => $pagerHtml,
            'filterForm' => $filterForm->createView(),
            'css_active' => 'usuarioprestador',
        ));
    }

    /**
    * Create filter form and process filter request.
    *
    */
    protected function filter()
    {
        $request = $this->getRequest();
        $session = $request->getSession();
        $filterForm = $this->createForm(new UsuarioPrestadorFilterType());
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em->getRepository('ChoferesBundle:UsuarioPrestador')->createQueryBuilder('e');

        $session->remove('UsuarioPrestadorControllerFilter');
        // Filter action
        if ($request->get('filter_action') == 'filter') {
            // Bind values from the request
            $filterForm->bind($request);

            if ($filterForm->isValid()) {
                // Build the query from the given form object
                $this->get('lexik_form_filter.query_builder_updater')->addFilterConditions($filterForm, $queryBuilder);
                // Save filter to session
                $filterData = $filterForm->getData();
                $session->set('UsuarioPrestadorControllerFilter', $filterData);
            }
        } else {
            // Get filter from session
            if ($session->has('UsuarioPrestadorControllerFilter')) {
                $filterData = $session->get('UsuarioPrestadorControllerFilter');
                $filterForm = $this->createForm(new UsuarioPrestadorFilterType(), $filterData);
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
            return $me->generateUrl('usuarioprestador', array('page' => $page));
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
     * Creates a new UsuarioPrestador entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity  = new UsuarioPrestador();
        $usuarioService =  $this->get('choferes.servicios.usuario');
        $form = $this->createForm(new UsuarioPrestadorType($usuarioService), $entity);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'flash.create.success');

            return $this->redirect($this->generateUrl('usuarioprestador_show', array('id' => $entity->getId())));
        }

        return $this->render('ChoferesBundle:UsuarioPrestador:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'css_active' => 'usuarioprestador',
        ));
    }

    /**
     * Displays a form to create a new UsuarioPrestador entity.
     *
     */
    public function newAction()
    {
        $usuarioService =  $this->get('choferes.servicios.usuario');
        $entity = new UsuarioPrestador();
        $form   = $this->createForm(new UsuarioPrestadorType($usuarioService), $entity);

        return $this->render('ChoferesBundle:UsuarioPrestador:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'css_active' => 'usuarioprestador',
        ));
    }

    /**
     * Finds and displays a UsuarioPrestador entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ChoferesBundle:UsuarioPrestador')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find UsuarioPrestador entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ChoferesBundle:UsuarioPrestador:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
            'css_active' => 'usuarioprestador',
        ));
    }

    /**
     * Displays a form to edit an existing UsuarioPrestador entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $usuarioService =  $this->get('choferes.servicios.usuario');

        $entity = $em->getRepository('ChoferesBundle:UsuarioPrestador')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find UsuarioPrestador entity.');
        }

        $editForm = $this->createForm(new UsuarioPrestadorType($usuarioService), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ChoferesBundle:UsuarioPrestador:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'css_active' => 'usuarioprestador',
        ));
    }

    /**
     * Edits an existing UsuarioPrestador entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $usuarioService =  $this->get('choferes.servicios.usuario');
        $entity = $em->getRepository('ChoferesBundle:UsuarioPrestador')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find UsuarioPrestador entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new UsuarioPrestadorType($usuarioService), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'flash.update.success');

            return $this->redirect($this->generateUrl('usuarioprestador_edit', array('id' => $id)));
        } else {
            $this->get('session')->getFlashBag()->add('error', 'flash.update.error');
        }

        return $this->render('ChoferesBundle:UsuarioPrestador:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'css_active' => 'usuarioprestador',
        ));
    }

    /**
     * Deletes a UsuarioPrestador entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('ChoferesBundle:UsuarioPrestador')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find UsuarioPrestador entity.');
            }

            $em->remove($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'flash.delete.success');
        } else {
            $this->get('session')->getFlashBag()->add('error', 'flash.delete.error');
        }

        return $this->redirect($this->generateUrl('usuarioprestador'));
    }

    /**
     * Creates a form to delete a UsuarioPrestador entity by id.
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
