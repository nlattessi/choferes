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
    const ESTADO_CURSO_DEFAULT = 1;
    const ESTADO_CURSO_CONFIRMADO = 2;
    const ESTADO_CURSO_PORVALIDAR = 3;
    const ESTADO_CURSO_CANCELADO = 4;
    const ESTADO_CURSO_VALIDADO = 5;
    const ESTADO_CURSO_FALLAVALIDACION = 6;

    /**
     * Lists all Curso entities.
     *
     */
    public function indexAction()
    {
        // if ($this->getUser()->getRol() == 'ROLE_PRESTADOR') {
        //     return $this->redirect($this->generateUrl('curso_precargados', array()));
        // }

        list($filterForm, $queryBuilder) = $this->filter();

        list($entities, $pagerHtml) = $this->paginator($queryBuilder);

        return $this->render('ChoferesBundle:Curso:index.html.twig', array(
            'entities' => $entities,
            'validar' => true,
            'pagerHtml' => $pagerHtml,
            'filterForm' => $filterForm->createView(),
            'css_active' => 'curso',
        ));
    }

    public function indexCursosAnterioresAction()
    {
        list($filterForm, $queryBuilder) = $this->filter();

        $queryBuilder
          ->andWhere('d.fechaInicio < :fechaHoy')
          ->setParameter('fechaHoy', new \DateTime(''), \Doctrine\DBAL\Types\Type::DATETIME);

        list($entities, $pagerHtml) = $this->paginator($queryBuilder);

        return $this->render('ChoferesBundle:Curso:index.html.twig', array(
            'entities' => $entities,
            'pagerHtml' => $pagerHtml,
            'filterForm' => $filterForm->createView(),
            'validar' => true,
            'css_active' => 'curso_anteriores',
        ));
    }

    public function indexCursosPrecargadosAction()
    {
        list($filterForm, $queryBuilder) = $this->filter();
        $em = $this->getDoctrine()->getManager();

        $queryBuilder
          ->andWhere('d.fechaInicio > :fechaHoy')

          ->setParameter('fechaHoy', new \DateTime(''), \Doctrine\DBAL\Types\Type::DATETIME);

        list($entities, $pagerHtml) = $this->paginator($queryBuilder);

        return $this->render('ChoferesBundle:Curso:index.html.twig', array(
            'entities' => $entities,
            'pagerHtml' => $pagerHtml,
            'filterForm' => $filterForm->createView(),
            'validar' => true,
            'css_active' => 'curso_precargados',
        ));
    }

    public function indexCursosConfirmarAction()
    {
        list($filterForm, $queryBuilder) = $this->filter();
        $em = $this->getDoctrine()->getManager();
        $queryBuilder
            ->andWhere('d.fechaInicio > :fechaHoy')
            ->andWhere('d.estado = :estado')
            ->setParameter('fechaHoy', new \DateTime(''), \Doctrine\DBAL\Types\Type::DATETIME)
            ->setParameter('estado', $em->getRepository('ChoferesBundle:EstadoCurso')->find(self::ESTADO_CURSO_DEFAULT));

        list($entities, $pagerHtml) = $this->paginator($queryBuilder);

        return $this->render('ChoferesBundle:Curso:index.html.twig', array(
            'entities' => $entities,
            'pagerHtml' => $pagerHtml,
            'filterForm' => $filterForm->createView(),
            'validar' => false,
            'css_active' => 'curso_paraconfirmar',
        ));
    }

    public function confirmarCursoAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ChoferesBundle:Curso')->find($id);
        $entity->setEstado($em->getRepository('ChoferesBundle:EstadoCurso')->find(self::ESTADO_CURSO_CONFIRMADO) );

        $em->persist($entity);
        $em->flush();

        $this->get('session')->getFlashBag()->add('success', 'Curso confirmado');

        return $this->indexCursosPrecargadosAction();
    }

    public function realizarCursoAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ChoferesBundle:Curso')->find($id);
        $choferesCurso= $em->getRepository('ChoferesBundle:ChoferCurso')->findBy(array(
            'curso' => $entity
        ));

        if ($request->getMethod() == 'POST') {
            $entity->setEstado($em->getRepository('ChoferesBundle:EstadoCurso')->find(self::ESTADO_CURSO_PORVALIDAR));
            $em->persist($entity);

            foreach($choferesCurso as $choferCurso){

                $choferCurso->setPagado(strlen($entity->getComprobante()) > 0);
                $choferCurso->setAprobado($request->get("" . $choferCurso->getId()));
                $em->persist($choferCurso);
            }

            $em->flush();

            return $this->render('ChoferesBundle:Curso:confirmacion.html.twig', array(
                'titulo' => "Las notas fueron cargadas con éxito",
                'mensaje' => "El curso está ahora pendiente de validación por la CNSVT",
                'css_active' => 'curso'
            ));
        }


        return $this->render('ChoferesBundle:Curso:cargarnotas.html.twig', array(
            'curso'=> $entity,
            'entities' => $choferesCurso,
            'css_active' => 'curso',
        ));
    }

    public function revisarDocumentacionAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ChoferesBundle:Curso')->find($id);
        $choferesCurso= $em->getRepository('ChoferesBundle:ChoferCurso')->findBy(array(
            'curso' => $entity
        ));


        if ($request->getMethod() == 'POST') {

            $entity->setEstado($em->getRepository('ChoferesBundle:EstadoCurso')->find(self::ESTADO_CURSO_VALIDADO));
            $em->persist($entity);

            foreach($choferesCurso as $choferCurso){

                $choferCurso->setPagado(strlen($entity->getComprobante()) > 0);
                $choferCurso->setDocumentacion("SI" == $request->get($choferCurso->getId()));
                $em->persist($choferCurso);
            }

            $em->flush();

            return $this->render('ChoferesBundle:Curso:confirmacion.html.twig', array(
                'titulo' => "Se registró el estado de la documentación",
                'mensaje' => "El curso está ahora en estado VALIDADO",
                'css_active' => 'curso',
            ));
        }


        return $this->render('ChoferesBundle:Curso:validardocumentacion.html.twig', array(
            'curso'=> $entity,
            'entities' => $choferesCurso,
            'css_active' => 'curso',
        ));
    }

    public function cancelarCursoAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ChoferesBundle:Curso')->find($id);
        $entity->setEstado($em->getRepository('ChoferesBundle:EstadoCurso')->find(self::ESTADO_CURSO_CANCELADO) );

        $em->persist($entity);
        $em->flush();

        $this->get('session')->getFlashBag()->add('error', 'Curso cancelado. Por favor editar el curso y agregar una Observacion.');

        return $this->indexCursosPrecargadosAction();
    }

    public function validarCursoChoferAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ChoferesBundle:Curso')->find($id);
        $entity->setEstado($em->getRepository('ChoferesBundle:EstadoCurso')->find(self::ESTADO_CURSO_VALIDADO) );

        $em->persist($entity);
        $em->flush();
        return $this->indexCursosConfirmarAction();
    }

    public function invalidarCursoChoferAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ChoferesBundle:Curso')->find($id);
        $entity->setEstado($em->getRepository('ChoferesBundle:EstadoCurso')->find(self::ESTADO_CURSO_FALLAVALIDACION) );

        $em->persist($entity);
        $em->flush();
        return $this->indexCursosConfirmarAction();
    }

    private function getCursosPorEstado($estado)
    {
        list($filterForm, $queryBuilder) = $this->filter();
        $em = $this->getDoctrine()->getManager();
        $queryBuilder
            ->andWhere('d.estado = :estado')
            ->setParameter('estado', $em->getRepository('ChoferesBundle:EstadoCurso')->find($estado));

        list($entities, $pagerHtml) = $this->paginator($queryBuilder);

        return [$entities, $pagerHtml, $filterForm];
    }

    public function indexCursosConfirmadosAction()
    {
        list($entities, $pagerHtml, $filterForm) = $this->getCursosPorEstado(self::ESTADO_CURSO_CONFIRMADO);

        return $this->render('ChoferesBundle:Curso:index.html.twig', array(
            'entities' => $entities,
            'pagerHtml' => $pagerHtml,
            'filterForm' => $filterForm->createView(),
            'validar' => false,
            'css_active' => 'curso_confirmados',
        ));
    }

    public function indexCursosPorValidarAction()
    {
        list($entities, $pagerHtml, $filterForm) = $this->getCursosPorEstado(self::ESTADO_CURSO_PORVALIDAR);

        return $this->render('ChoferesBundle:Curso:index.html.twig', array(
            'entities' => $entities,
            'pagerHtml' => $pagerHtml,
            'filterForm' => $filterForm->createView(),
            'validar' => true,
            'css_active' => 'curso_porvalidar',
        ));
    }

    public function indexCursosValidadosAction()
    {
        list($entities, $pagerHtml, $filterForm) = $this->getCursosPorEstado(self::ESTADO_CURSO_VALIDADO);

        return $this->render('ChoferesBundle:Curso:index.html.twig', array(
            'entities' => $entities,
            'pagerHtml' => $pagerHtml,
            'filterForm' => $filterForm->createView(),
            'validar' => true,
            'css_active' => 'curso_validados',
        ));
    }

    public function indexCursosCanceladosAction()
    {
        list($entities, $pagerHtml, $filterForm) = $this->getCursosPorEstado(self::ESTADO_CURSO_CANCELADO);

        return $this->render('ChoferesBundle:Curso:index.html.twig', array(
            'entities' => $entities,
            'pagerHtml' => $pagerHtml,
            'filterForm' => $filterForm->createView(),
            'validar' => false,
            'css_active' => 'curso_cancelados',
        ));
    }

    public function indexCursosFallaValidacionAction()
    {
        list($entities, $pagerHtml, $filterForm) = $this->getCursosPorEstado(self::ESTADO_CURSO_FALLAVALIDACION);

        return $this->render('ChoferesBundle:Curso:index.html.twig', array(
            'entities' => $entities,
            'pagerHtml' => $pagerHtml,
            'filterForm' => $filterForm->createView(),
            'validar' => false,
            'css_active' => 'curso_fallavalidacion',
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
                //Si el curso tiene comprobante de pago marco el choferCurso como pagado
                $choferCurso->setPagado(strlen($curso->getComprobante()) > 0);

                $em->persist($choferCurso);
            }
            $em->flush();
            $choferes = $this->obtenerChoferesPorCurso($curso);
        }else{
            $id =  $request->query->get('idCurso');

            $curso =  $em->getRepository('ChoferesBundle:Curso')->findOneBy(array('id' => $id));
            $choferes = $this->obtenerChoferesPorCurso($curso);
        }

        return $this->render('ChoferesBundle:Curso:addchofer.html.twig', array(
            'idCurso'=> $id,
            'entities' => $choferes,
            'css_active' => 'curso',
        ));
    }

    public function borrarChoferAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $idCurso =  $request->query->get('idCurso');
        $idChofer =  $request->query->get('idBorrar');

        $curso = $em->getRepository('ChoferesBundle:Curso')->findOneById($idCurso);
        $chofer = $em->getRepository('ChoferesBundle:Chofer')->findOneById($idChofer);
        $entity = $em->getRepository('ChoferesBundle:ChoferCurso')->findOneBy(array(
            'chofer' => $chofer,
            'curso' => $curso
        ));

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ChoferCurso entity.');
        }

        $em->remove($entity);
        $em->flush();

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

        /*Inicio filtro por prestador*/
        $usuario = $this->getUser();
        $usuarioService =  $this->get('choferes.servicios.usuario');

        // $entity = new Curso();
        //
        // $filterForm = $this->createForm(new CursoFilterType($usuarioService), null, array(
        //     'user' => $this->getUser()
        // ));

        $filterForm = $this->createForm(new CursoFilterType($usuarioService), null, ['user' => $usuario]);
        $em = $this->getDoctrine()->getManager();

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

                // var_dump($filterForm);die();

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
                $filterForm = $this->createForm(new CursoFilterType($usuarioService), $filterData, ['user' => $this->getUser()]);
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
        $usuarioService =  $this->get('choferes.servicios.usuario');

        $em = $this->getDoctrine()->getManager();

        $entity  = new Curso();

        if ($this->getUser()->getRol() == 'ROLE_PRESTADOR') {
            $prestador = $usuarioService->obtenerPrestadorPorUsuario($this->getUser());

            $docentes = $em->getRepository('ChoferesBundle:Docente')->findBy(array(
                'prestador' => $prestador
            ));

            $sedes = $em->getRepository('ChoferesBundle:Sede')->findBy(array(
                'prestador' => $prestador
            ));
        } else {
            $docentes = null;
            $sedes = null;
        }

        $form = $this->createForm(new CursoType($usuarioService), $entity, array(
            'user' => $this->getUser(),
            'docentes' => $docentes,
            'sedes' => $sedes
        ));

        $form->bind($request);

        if ($form->isValid()) {
            if ($this->getUser()->getRol() == 'ROLE_PRESTADOR') {
                $prestador = $usuarioService->obtenerPrestadorPorUsuario($this->getUser());
                $entity->setPrestador($prestador);
            }

            $entity->setEstado($em->getRepository('ChoferesBundle:EstadoCurso')->find(self::ESTADO_CURSO_DEFAULT) );

            $fechaInicio = $form->get('fechaInicio')->getData() . ' ' . $form->get('horaInicio')->getData();
            $dtFechaInicio = \DateTime::createFromFormat('d/m/Y H:i', $fechaInicio);
            $entity->setFechaInicio($dtFechaInicio);

            $fechaFin = $form->get('fechaFin')->getData() . ' ' . $form->get('horaFin')->getData();
            $dtFechaFin = \DateTime::createFromFormat('d/m/Y H:i', $fechaFin);
            $entity->setFechaFin($dtFechaFin);

            if ($form->has('fechaPago') && $form->get('fechaPago')->getData() !== null) {
                $fechaPago = $form->get('fechaPago')->getData();
                $dtFechaPago = \DateTime::createFromFormat('d/m/Y', $fechaPago);
                $entity->setFechaPago($dtFechaPago);
            }

            $em->persist($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'flash.create.success');

            return $this->redirect($this->generateUrl('curso_show', array('id' => $entity->getId())));
        }

        return $this->render('ChoferesBundle:Curso:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'css_active' => 'curso_new',
        ));
    }

    /**
     * Displays a form to create a new Curso entity.
     *
     */
    public function newAction()
    {
        $usuarioService =  $this->get('choferes.servicios.usuario');

        $em = $this->getDoctrine()->getManager();

        $entity = new Curso();

        if ($this->getUser()->getRol() == 'ROLE_PRESTADOR') {
            $prestador = $usuarioService->obtenerPrestadorPorUsuario($this->getUser());

            $docentes = $em->getRepository('ChoferesBundle:Docente')->findBy(array(
                'prestador' => $prestador
            ));

            $sedes = $em->getRepository('ChoferesBundle:Sede')->findBy(array(
                'prestador' => $prestador
            ));
        } else {

            $docentes = null;
            $sedes = null;
        }

        $form = $this->createForm(new CursoType($usuarioService), $entity, array(
            'user' => $this->getUser(),
            'docentes' => $docentes,
            'sedes' => $sedes
        ));

        return $this->render('ChoferesBundle:Curso:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'css_active' => 'curso_new',
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

        if ($entity->getEstado()->getId() >= 2) { // Estados: Confirmado, Por validar, Cancelado, Validado y con Falla de validacion
            $choferesCurso= $em->getRepository('ChoferesBundle:ChoferCurso')->findBy(array(
                'curso' => $entity
            ));
        } else {
            $choferesCurso = null;
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ChoferesBundle:Curso:show.html.twig', array(
            'entity'      => $entity,
            'choferesCurso' => $choferesCurso,
            'delete_form' => $deleteForm->createView(),
            'css_active' => 'curso',
        ));
    }

    /**
     * Displays a form to edit an existing Curso entity.
     *
     */
    public function editAction($id)
    {
        $usuarioService =  $this->get('choferes.servicios.usuario');

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ChoferesBundle:Curso')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Curso entity.');
        }

        if ($this->getUser()->getRol() == 'ROLE_PRESTADOR') {
            $prestador = $usuarioService->obtenerPrestadorPorUsuario($this->getUser());

            $docentes = $em->getRepository('ChoferesBundle:Docente')->findBy(array(
                'prestador' => $prestador
            ));

            $sedes = $em->getRepository('ChoferesBundle:Sede')->findBy(array(
                'prestador' => $prestador
            ));
        } else {
            $docentes = null;
            $sedes = null;
        }

        $editForm = $this->createForm(new CursoType($usuarioService), $entity, array(
            'user' => $this->getUser(),
            'docentes' => $docentes,
            'sedes' => $sedes
        ));

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ChoferesBundle:Curso:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'css_active' => 'curso',
        ));
    }

    /**
     * Edits an existing Curso entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $usuarioService =  $this->get('choferes.servicios.usuario');

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ChoferesBundle:Curso')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Curso entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        if ($this->getUser()->getRol() == 'ROLE_PRESTADOR') {
            $prestador = $usuarioService->obtenerPrestadorPorUsuario($this->getUser());

            $docentes = $em->getRepository('ChoferesBundle:Docente')->findBy(array(
                'prestador' => $prestador
            ));

            $sedes = $em->getRepository('ChoferesBundle:Sede')->findBy(array(
                'prestador' => $prestador
            ));
        } else {
            $docentes = null;
            $sedes = null;
        }

        $editForm = $this->createForm(new CursoType($usuarioService), $entity, array(
            'user' => $this->getUser(),
            'docentes' => $docentes,
            'sedes' => $sedes
        ));

        $editForm->bind($request);

        if ($editForm->isValid()) {
            $dtInicio = $editForm->get('fechaInicio')->getData() . ' ' . $editForm->get('horaInicio')->getData();
            $fechaInicio = \DateTime::createFromFormat('d/m/Y H:i', $dtInicio);
            $entity->setFechaInicio($fechaInicio);

            $dtFin = $editForm->get('fechaFin')->getData() . ' ' . $editForm->get('horaFin')->getData();
            $fechaFin = \DateTime::createFromFormat('d/m/Y H:i', $dtFin);
            $entity->setFechaFin($fechaFin);

            if ($editForm->has('fechaPago') && $editForm->get('fechaPago')->getData() !== null) {
                $fechaPago = $editForm->get('fechaPago')->getData();
                $dtFechaPago = \DateTime::createFromFormat('d/m/Y', $fechaPago);
                $entity->setFechaPago($dtFechaPago);
            }

            $em->persist($entity);
            $em->flush();
            if(strlen($entity->getComprobante()) > 0){
                //comprobante seteado, hay que marcar como pagado todos los ChoferCurso
                $this->actualizarCursoChofer($entity);
            }

            $this->get('session')->getFlashBag()->add('success', 'flash.update.success');

            return $this->redirect($this->generateUrl('curso_edit', array('id' => $id)));
        } else {
            $this->get('session')->getFlashBag()->add('error', 'flash.update.error');
        }

        return $this->render('ChoferesBundle:Curso:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'css_active' => 'curso',
        ));
    }

    private function actualizarCursoChofer($curso)
    {
        $em = $this->getDoctrine()->getManager();
        $choferesCurso = $em->getRepository('ChoferesBundle:ChoferCurso')->findBy(array('curso' => $curso));

        foreach($choferesCurso as $choferCurso){
            $choferCurso->setPagado(true);
            $em->persist($choferCurso);
        }
        $em->flush();
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
        $choferesCursos =$em->getRepository('ChoferesBundle:ChoferCurso')->findBy(array('curso' => $curso));
        foreach($choferesCursos as $choferCurso){
            $choferes[]= $choferCurso->getChofer();
        }

        return $choferes;
    }
}
