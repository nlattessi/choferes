<?php

namespace ChoferesBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\View\TwitterBootstrapView;

use ChoferesBundle\Entity\Chofer;
use ChoferesBundle\Form\ChoferType;
use ChoferesBundle\Form\ChoferFilterType;

use ChoferesBundle\Form\ChoferStatusType;

/**
 * Chofer controller.
 *
 */
class ChoferController extends Controller
{
    /**
     * Lists all Chofer entities.
     *
     */
    public function indexAction()
    {
        list($filterForm, $queryBuilder) = $this->filter();

        list($entities, $pagerHtml) = $this->paginator($queryBuilder);

        return $this->render('ChoferesBundle:Chofer:index.html.twig', array(
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
        $filterForm = $this->createForm(new ChoferFilterType());
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em->getRepository('ChoferesBundle:Chofer')->createQueryBuilder('e');

        // Reset filter
        if ($request->get('filter_action') == 'reset') {
            $session->remove('ChoferControllerFilter');
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
                $session->set('ChoferControllerFilter', $filterData);
            }
        } else {
            // Get filter from session
            if ($session->has('ChoferControllerFilter')) {
                $filterData = $session->get('ChoferControllerFilter');
                $filterForm = $this->createForm(new ChoferFilterType(), $filterData);
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
            return $me->generateUrl('chofer', array('page' => $page));
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
     * Creates a new Chofer entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity  = new Chofer();
        $form = $this->createForm(new ChoferType(), $entity);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'flash.create.success');

            return $this->redirect($this->generateUrl('chofer_show', array('id' => $entity->getId())));
        }

        return $this->render('ChoferesBundle:Chofer:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Displays a form to create a new Chofer entity.
     *
     */
    public function newAction()
    {
        $entity = new Chofer();
        $form   = $this->createForm(new ChoferType(), $entity);

        return $this->render('ChoferesBundle:Chofer:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Chofer entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ChoferesBundle:Chofer')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Chofer entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ChoferesBundle:Chofer:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),        ));
    }

    /**
     * Displays a form to edit an existing Chofer entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ChoferesBundle:Chofer')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Chofer entity.');
        }

        $editForm = $this->createForm(new ChoferType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ChoferesBundle:Chofer:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Edits an existing Chofer entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ChoferesBundle:Chofer')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Chofer entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new ChoferType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'flash.update.success');

            return $this->redirect($this->generateUrl('chofer_edit', array('id' => $id)));
        } else {
            $this->get('session')->getFlashBag()->add('error', 'flash.update.error');
        }

        return $this->render('ChoferesBundle:Chofer:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Chofer entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('ChoferesBundle:Chofer')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Chofer entity.');
            }

            $em->remove($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'flash.delete.success');
        } else {
            $this->get('session')->getFlashBag()->add('error', 'flash.delete.error');
        }

        return $this->redirect($this->generateUrl('chofer'));
    }

    /**
     * Creates a form to delete a Chofer entity by id.
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

    public function consultaAction(Request $request)
    {
        $message = '';
        $chofer = null;
        $certificado = false;

        $form = $this->createForm(new ChoferStatusType());
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $dni = $form->get('dni')->getData();

            $query = $em->createQueryBuilder()
                ->select('c.nombre', 'c.apellido', 'c.id', 'c.dni', 'c.tieneCursoBasico', 'cc.id as ccid', 'cc.aprobado', 'cu.id as cuid', 'cu.fechaInicio', 'cc.pagado as pago')
                ->from('ChoferesBundle:Chofer', 'c')
                ->leftJoin(
                    'ChoferesBundle:ChoferCurso', 'cc',
                    \Doctrine\ORM\Query\Expr\Join::WITH, 'cc.chofer = c.id'
                )
                ->leftJoin(
                    'ChoferesBundle:Curso', 'cu',
                    \Doctrine\ORM\Query\Expr\Join::WITH, 'cc.curso = cu.id'
                )
                ->where('c.dni LIKE :dni')
                ->orderBy('cu.fechaInicio', 'DESC')
                ->setParameter('dni', '%' . $dni . '%')
                ->setMaxResults(1)
                ->getQuery();

            $chofer = $query->getOneOrNullResult();

            if ($chofer) {
                if ($chofer['tieneCursoBasico']) {
                    if ($chofer['ccid'] && $chofer['fechaInicio'] > new \DateTime('-1 year')) {
                        if ($chofer['aprobado']) {
                            if ($chofer['pago']) {
                                $message = 'HABILITADO';
                                $certificado = true;
                            } else {
                                $message = 'NO HABILITADO: No figura pago el ultimo curso complementario.';
                            }
                        } else {
                            $message = 'NO HABILITADO: No tiene aprobado el ultimo curso complementario.';
                        }
                    } else {
                        $message = 'NO HABILITADO: No tiene el curso complementario o la vigencia del mismo ya expiro.';
                    }
                } else {
                    $message = 'NO HABILITADO: No tiene el curso basico.';
                }
            } else {
                $message = 'NO HABILITADO: No tiene el curso basico.';
            }
        }

        return $this->render('ChoferesBundle:Chofer:consulta.html.twig', array(
            'form' => $form->createView(),
            'message' => $message,
            'chofer' => $chofer,
            'certificado' => $certificado,
        ));
    }
}
