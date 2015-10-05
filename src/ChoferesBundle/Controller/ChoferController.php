<?php

namespace ChoferesBundle\Controller;

use ChoferesBundle\Resources\views\TwitterBootstrapViewCustom;
use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Form\FormError;

use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;

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
            'css_active' => 'chofer',
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
        $session->remove('ChoferControllerFilter');


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
        $view = new TwitterBootstrapViewCustom();
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
            'css_active' => 'chofer',
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
            'css_active' => 'chofer',
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
            'css_active' => 'chofer',
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
            'css_active' => 'chofer',
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

    public function consultaAction(Request $request, $id = null)
    {
        $status = null;
        $chofer = null;
        $goBack = false;
        $errors = array();

        // Descargo certificado si el usuario pidio hacerlo.
        if ($request->isMethod('POST')) {
            if ($request->request->has('descargar')) {
                $choferService = $this->get('choferes.servicios.chofer');
                return $choferService->descargarCertificado($request->request->get('choferDni'));
            }
        }

        // Saco captcha si el usuario esta logueado al sistema.
        if ($this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')) {
            $form = $this->createForm(new ChoferStatusType(), null, array('use_captcha' => false));
        } else {
            $form = $this->createForm(new ChoferStatusType());
        }

        if ($id) {
            $em = $this->getDoctrine()->getManager();
            $choferService = $this->get('choferes.servicios.chofer');
            $chofer = $em->getRepository('ChoferesBundle:Chofer')->find($id);
            $status = $choferService->getStatusPorDniChofer($chofer->getDni());
            if(!$status)
            {
                if($chofer->getTieneCursoBasico()){
                    $status['message'] = 'No tiene el curso complementario o la vigencia del mismo ya expiro.';
                }else{
                    $status['message'] = 'No tiene el curso basico.';
                }
            }
            $goBack = true;

            return $this->render('ChoferesBundle:Chofer:consulta.html.twig', array(
                'form' => $form->createView(),
                'status' => $status,
                'chofer' => $chofer,
                'goBack' => $goBack,
                'css_active' => 'chofer_consulta',
                'errors' => $errors,
            ));

        }

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $dni = $form->get('dni')->getData();
            $choferService = $this->get('choferes.servicios.chofer');
            $status = $choferService->getStatusPorDniChofer($dni);
            $chofer = $em->getRepository('ChoferesBundle:Chofer')->findOneBy(['dni' => $dni]);
            if(!$chofer){
                $errors[] = new FormError('El DNI ingresado no corresponde a ningún chofer registrado');
                // if ($this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')) {
                //     $form = $this->createForm(new ChoferStatusType(), null, array('use_captcha' => false));
                // } else {
                //     $form = $this->createForm(new ChoferStatusType());
                // }
                $form = $this->createForm(new ChoferStatusType(), null, array('use_captcha' => false));
            }else if(!$status)
                {
                if($chofer->getTieneCursoBasico()){
                     $status['message'] = 'No tiene el curso complementario o la vigencia del mismo ya expiro.';
                    }else{
                     $status['message'] = 'No tiene el curso basico.';
                    }
                }
        } else {
            $errors = array();
            foreach($form->getErrors() as $key => $error) {
                $errors[$key] = $error;
            }
            // if ($this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')) {
            //     $form = $this->createForm(new ChoferStatusType(), null, array('use_captcha' => false));
            // } else {
            //     $form = $this->createForm(new ChoferStatusType());
            // }
            $form = $this->createForm(new ChoferStatusType(), null, array('use_captcha' => false));
        }

        return $this->render('ChoferesBundle:Chofer:consulta.html.twig', array(
            'form' => $form->createView(),
            'status' => $status,
            'chofer' => $chofer,
            'goBack' => $goBack,
            'css_active' => 'chofer_consulta',
            'errors' => $errors,
        ));
    }

    public function descargarCertificadosAction(Request $request, $hash = null)
    {
        $errors = array();

        $dni = $request->request->get('choferDni');
        $form = $this->createForm(new ChoferStatusType());
        $form->handleRequest($request);
        $em = $this->getDoctrine()->getManager();
        $chofer = $em->getRepository('ChoferesBundle:Chofer')->findOneBy(['dni' => $dni]);
        // Descargo certificado si el usuario pidio hacerlo.
        if ($request->isMethod('POST')) {
            if ($request->request->has('descargar')) {
                $choferService = $this->get('choferes.servicios.chofer');
                $resultado = $choferService->descargarCertificado($dni);
                if($resultado){
                    return $resultado;
                }
                if($chofer){
                    $errors[0] =array('message'=>"No hay certificados imprimibles para este chofer");
                }else{
                    $errors[0] =array('message'=>"El DNI ingresado no corresponde a un chofer presente en la base");
                }
                return $this->render('ChoferesBundle:Chofer:descargar-certificados.html.twig', [
                    'form' => $form->createView(),
                    'errors' => $errors,
                ]);
            }
        }
        if ($hash) {
            $hashids = $this->get('hashids');
            $id = $hashids->decode($hash);
            if (! empty($id)) {
                $id = $id[0];

                $chofer = $em->getRepository('ChoferesBundle:Chofer')->findOneBy(['id' => $id]);

                $choferService = $this->get('choferes.servicios.chofer');

                $status = $choferService->getStatusPorDniChofer($chofer->getDni());
                return $this->render('ChoferesBundle:Chofer:descargar-certificados-estatus.html.twig', [
                    'chofer' => $chofer,
                    'status' => $status,
                ]);
            }
        }

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $dni = $form->get('dni')->getData();
            $choferService = $this->get('choferes.servicios.chofer');
            $status = $choferService->getStatusPorDniChofer($dni);

            if ($status) {
                $chofer = $em->getRepository('ChoferesBundle:Chofer')->findOneBy(['dni' => $dni]);
                return $this->render('ChoferesBundle:Chofer:descargar-certificados-estatus.html.twig', [
                    'chofer' => $chofer,
                    'status' => $status,
                ]);
            } else {
                $form->get('dni')->addError(new FormError('No se encuentran resultados.'));
            }
        } else {
            $errors = array();
            foreach($form->getErrors() as $key => $error) {
                $errors[$key] = $error;
            }
            $form = $this->createForm(new ChoferStatusType());
        }

        return $this->render('ChoferesBundle:Chofer:descargar-certificados.html.twig', [
            'form' => $form->createView(),
            'errors' => $errors,
        ]);
    }

    public function reporteCursoAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $rsm = new ResultSetMapping($em);
        $rsm->addScalarResult('nombre','nombre');
        $rsm->addScalarResult('cantidad','cantidad');

        $query = $em->createNativeQuery('select tipo_curso.nombre as nombre, count(chofer_curso.id) as cantidad
                                    from curso , chofer_curso, tipo_curso
                                    where curso.id = chofer_curso.curso_id
                                    and curso.tipoCurso_id = tipo_curso.id
                                    and month(curso.fecha_inicio) = month(now())
                                    group by curso.tipoCurso_id',$rsm);

        $resultado = $query->getResult();

        return $this->render('ChoferesBundle:Reporte:curso-por-tipo.html.twig', [
            'resultado' => $resultado,
            'css_active' => 'tipo',
        ]);
    }

    public function reportePrestadorAction(Request $request){

        $em = $this->getDoctrine()->getManager();
        $rsm = new ResultSetMapping($em);
        $rsm->addScalarResult('nombre','nombre');
        $rsm->addScalarResult('cantidad','cantidad');

        $query = $em->createNativeQuery('select prestador.nombre as nombre, count(chofer_curso.id) as cantidad
                                    from curso , chofer_curso, prestador
                                    where curso.id = chofer_curso.curso_id
                                    and curso.prestador_id = prestador.id
                                    and month(curso.fecha_inicio) = month(now())
                                    group by curso.prestador_id;',$rsm);

        $resultado = $query->getResult();

        return $this->render('ChoferesBundle:Reporte:curso-por-prestador.html.twig', [
            'resultado' => $resultado,
            'css_active' => 'prestador',
        ]);
    }
}
