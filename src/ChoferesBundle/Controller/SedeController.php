<?php

namespace ChoferesBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\View\TwitterBootstrapView;

use ChoferesBundle\Entity\Sede;
use ChoferesBundle\Form\SedeType;
use ChoferesBundle\Form\SedeFilterType;

/**
 * Sede controller.
 *
 */
class SedeController extends Controller
{
    /**
     * Lists all Sede entities.
     *
     */
    public function indexAction()
    {
        list($filterForm, $queryBuilder) = $this->filter();

        list($entities, $pagerHtml) = $this->paginator($queryBuilder);

        return $this->render('ChoferesBundle:Sede:index.html.twig', array(
            'entities' => $entities,
            'pagerHtml' => $pagerHtml,
            'filterForm' => $filterForm->createView(),
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
        $filterForm = $this->createForm(new SedeFilterType());
        $em = $this->getDoctrine()->getManager();

        /*Inicio filtro por prestador*/
        $usuario = $this->getUser();
        $usuarioService =  $this->get('choferes.servicios.usuario');

        if ($usuario->getRol() == 'ROLE_PRESTADOR') {
            //filtro solo lo que es de este usuario
            $prestador = $usuarioService->obtenerPrestadorPorUsuario($usuario);
            $queryBuilder = $em->getRepository('ChoferesBundle:Sede')->createQueryBuilder('d')
                ->where('d.prestador = ?1')
                ->setParameter(1, $prestador->getId());
        } else {
            $queryBuilder = $em->getRepository('ChoferesBundle:Docente')->createQueryBuilder('d');
        }
        /*Fin filtro por prestador*/

        // Reset filter
        if ($request->get('filter_action') == 'reset') {
            $session->remove('SedeControllerFilter');
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
                $session->set('SedeControllerFilter', $filterData);
            }
        } else {
            // Get filter from session
            if ($session->has('SedeControllerFilter')) {
                $filterData = $session->get('SedeControllerFilter');
                $filterForm = $this->createForm(new SedeFilterType(), $filterData);
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
            return $me->generateUrl('sede', array('page' => $page));
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
     * Creates a new Sede entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity  = new Sede();
        $form = $this->createForm(new SedeType(), $entity);
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

            return $this->redirect($this->generateUrl('sede_show', array('id' => $entity->getId())));
        }

        return $this->render('ChoferesBundle:Sede:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Displays a form to create a new Sede entity.
     *
     */
    public function newAction()
    {
        $entity = new Sede();
        $form   = $this->createForm(new SedeType(), $entity);

        return $this->render('ChoferesBundle:Sede:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Sede entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ChoferesBundle:Sede')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Sede entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ChoferesBundle:Sede:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),        ));
    }

    /**
     * Displays a form to edit an existing Sede entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ChoferesBundle:Sede')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Sede entity.');
        }

        $editForm = $this->createForm(new SedeType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ChoferesBundle:Sede:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Edits an existing Sede entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ChoferesBundle:Sede')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Sede entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new SedeType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'flash.update.success');

            return $this->redirect($this->generateUrl('sede_edit', array('id' => $id)));
        } else {
            $this->get('session')->getFlashBag()->add('error', 'flash.update.error');
        }

        return $this->render('ChoferesBundle:Sede:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Sede entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('ChoferesBundle:Sede')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Sede entity.');
            }

            $em->remove($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'flash.delete.success');
        } else {
            $this->get('session')->getFlashBag()->add('error', 'flash.delete.error');
        }

        return $this->redirect($this->generateUrl('sede'));
    }

    /**
     * Creates a form to delete a Sede entity by id.
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
