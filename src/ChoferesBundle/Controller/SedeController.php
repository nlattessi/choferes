<?php

namespace ChoferesBundle\Controller;

use ChoferesBundle\Resources\views\TwitterBootstrapViewCustom;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;

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
            'css_active' => 'sede',
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
        /*Inicio filtro por prestador*/
        $usuario = $this->getUser();
        $usuarioService =  $this->get('choferes.servicios.usuario');
        $filterForm = $this->createForm(new SedeFilterType($usuarioService), null, ['user' => $usuario]);
        $em = $this->getDoctrine()->getManager();



        if ($usuario->getRol() == 'ROLE_PRESTADOR') {
            //filtro solo lo que es de este usuario
            $prestador = $usuarioService->obtenerPrestadorPorUsuario($usuario);
            $queryBuilder = $em->getRepository('ChoferesBundle:Sede')->createQueryBuilder('d')
                ->where('d.prestador = ?1')
                ->andWhere('d.activo = ?2')
                ->setParameter(1, $prestador->getId())
                ->setParameter(2, true);;
        } else {
            $queryBuilder = $em->getRepository('ChoferesBundle:Sede')->createQueryBuilder('d')
                ->andWhere('d.activo = ?1')
                ->setParameter(1, true);
        }
        /*Fin filtro por prestador*/

        $session->remove('SedeControllerFilter');


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
                $filterForm = $this->createForm(new SedeFilterType($usuarioService), null, ['user' => $usuario]);
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
        $view = new TwitterBootstrapViewCustom();
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
        $usuario = $this->getUser();
        $usuarioService =  $this->get('choferes.servicios.usuario');

        $form = $this->createForm(new SedeType($usuarioService),$entity, array(
            'user' => $this->getUser()
        ));
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();


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
            'css_active' => 'sede',
        ));
    }

    /**
     * Displays a form to create a new Sede entity.
     *
     */
    public function newAction()
    {
        $entity = new Sede();
        $usuarioService =  $this->get('choferes.servicios.usuario');
        $form   = $this->createForm(new SedeType($usuarioService),$entity, array(
            'user' => $this->getUser()
        ));

        return $this->render('ChoferesBundle:Sede:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'css_active' => 'sede',
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
            'delete_form' => $deleteForm->createView(),
            'css_active' => 'sede',
        ));
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
        $usuarioService =  $this->get('choferes.servicios.usuario');
        $editForm = $this->createForm(new SedeType($usuarioService),$entity, array(
            'user' => $this->getUser()
        ));
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ChoferesBundle:Sede:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'css_active' => 'sede',
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
        $usuarioService =  $this->get('choferes.servicios.usuario');
        $editForm = $this->createForm(new SedeType($usuarioService),$entity, array(
            'user' => $this->getUser()
        ));
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
            'css_active' => 'sede',
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

    public function darDeBajaAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ChoferesBundle:Sede')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Sede entity.');
        }

        $bajaAdministrativaService = $this->get('choferes.servicios.bajaAdministrativa');
        $bajaAdministrativaService->darDeBaja($entity);

        $this->get('session')->getFlashBag()->add('success', 'Se realizó la baja administrativa.');

        return $this->redirect($this->generateUrl('sede'));
    }
}
