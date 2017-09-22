<?php
namespace ChoferesBundle\Controller;

use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use ChoferesBundle\Entity\Chofer;
use ChoferesBundle\Entity\ChoferCurso;
use ChoferesBundle\Entity\Curso;
use ChoferesBundle\Entity\TipoCurso;
use ChoferesBundle\Form\CursoType;
use ChoferesBundle\Form\CursoFilterType;
use ChoferesBundle\Resources\views\TwitterBootstrapViewCustom;

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
        list($filterForm, $queryBuilder) = $this->filter();

        list($entities, $pagerHtml) = $this->paginator($queryBuilder);

        return $this->render('ChoferesBundle:Curso:index.html.twig', array(
            'entities' => $entities,
            'validar' => true,
            'pagerHtml' => $pagerHtml,
            'filterForm' => $filterForm->createView(),
            'css_active' => 'curso_todos',
        ));
    }

    public function indexCursosAnterioresAction()
    {
        $this->resetearFiltro();

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
        $this->resetearFiltro();
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

        $this->resetearFiltro();

        list($filterForm, $queryBuilder) = $this->filter();
        $em = $this->getDoctrine()->getManager();
        $queryBuilder
            ->andWhere('d.fechaInicio > :fechaHoy')
            ->andWhere('d.estado = :estado')
            ->setParameter('fechaHoy', new \DateTime(''), \Doctrine\DBAL\Types\Type::DATETIME)
            ->setParameter('estado', $em->getRepository('ChoferesBundle:EstadoCurso')->find(self::ESTADO_CURSO_DEFAULT));

        list($entities, $pagerHtml) = $this->paginator($queryBuilder, 'curso_paraconfirmar');

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

        return $this->indexCursosConfirmarAction();
    }

    public function realizarCursoAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $choferService = $this->get('choferes.servicios.chofer');

        $entity = $em->getRepository('ChoferesBundle:Curso')->find($id);
        $choferesCurso= $em->getRepository('ChoferesBundle:ChoferCurso')->findBy(array(
            'curso' => $entity
        ));

        $choferesCurso = array_filter($choferesCurso, function ($choferCurso) {
            return ($choferCurso->getChofer()->getEstaActivo() === TRUE);
        });

        if ($request->getMethod() == 'POST') {

            if ($entity->getEstado()->getId() == self::ESTADO_CURSO_VALIDADO) {
                foreach($choferesCurso as $choferCurso){
                    $choferCurso->setPagado(strlen($entity->getComprobante()) > 0);
                    $choferCurso->setIsAprobado("SI" == $request->get($choferCurso->getId()));
                    $em->persist($choferCurso);

                    $choferService->updateTieneCursoBasico(
                        $choferCurso->getChofer(),
                        $choferCurso->getCurso(),
                        $choferCurso
                    );
                }
                $em->flush();
                $this->get('session')->getFlashBag()->add('success', 'Se actualizaron las notas.');
                return $this->redirect($this->generateUrl('curso_validados', []));
            }

            $entity->setEstado($em->getRepository('ChoferesBundle:EstadoCurso')->find(self::ESTADO_CURSO_PORVALIDAR));
            $em->persist($entity);

            foreach($choferesCurso as $choferCurso){

                $choferCurso->setPagado(strlen($entity->getComprobante()) > 0);
                $choferCurso->setIsAprobado("SI" == $request->get($choferCurso->getId()));
                $em->persist($choferCurso);

                $choferService->updateTieneCursoBasico(
                    $choferCurso->getChofer(),
                    $choferCurso->getCurso(),
                    $choferCurso
                );
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
        $choferService = $this->get('choferes.servicios.chofer');

        $entity = $em->getRepository('ChoferesBundle:Curso')->find($id);

        $choferesCurso = $em->createQueryBuilder()
            ->select(
                'chofer.nombre', 'chofer.apellido', 'chofer.dni',
                'choferCurso.id', 'choferCurso.documentacion'
            )
            ->from('ChoferesBundle:ChoferCurso', 'choferCurso')
            ->leftJoin(
                'ChoferesBundle:Chofer', 'chofer',
                \Doctrine\ORM\Query\Expr\Join::WITH, 'chofer.id = choferCurso.chofer'
            )
            ->where('choferCurso.curso = :curso')
            ->andWhere('chofer.estaActivo = TRUE')
            ->orderBy('chofer.nombre', 'ASC')
            ->setParameter('curso', $entity)
            ->getQuery()
            ->getResult();

        if ($request->getMethod() == 'POST') {

            $entity->setEstado($em->getRepository('ChoferesBundle:EstadoCurso')->find(self::ESTADO_CURSO_VALIDADO));
            //Se agrega la fecha de validación
            $entity->setFechaValidacion( new \DateTime(''));
            $em->persist($entity);

            foreach ($choferesCurso as $choferCursoData) {
                $choferCurso= $em->getRepository('ChoferesBundle:ChoferCurso')
                    ->find($choferCursoData['id']);

                $choferCurso->setPagado(strlen($entity->getComprobante()) > 0);
                $choferCurso->setDocumentacion("SI" == $request->get($choferCurso->getId()));
                $em->persist($choferCurso);

                $choferService->updateTieneCursoBasico(
                    $choferCurso->getChofer(),
                    $choferCurso->getCurso(),
                    $choferCurso
                );
            }

            $em->flush();

            if ($entity->getEstado()->getId() == self::ESTADO_CURSO_VALIDADO) {
                $this->get('session')->getFlashBag()->add('success', 'Se actualizó el estado de la documentación.');
                return $this->redirect($this->generateUrl('curso_validados', []));
            }

            return $this->render('ChoferesBundle:Curso:confirmacion.html.twig', [
                'titulo' => "Se registró el estado de la documentación",
                'mensaje' => "El curso está ahora en estado VALIDADO",
                'css_active' => 'curso',
            ]);
        }

        return $this->render('ChoferesBundle:Curso:validardocumentacion.html.twig', [
            'curso'=> $entity,
            'entities' => $choferesCurso,
            'css_active' => 'curso',
        ]);
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
        $this->resetearFiltro();
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
        $choferService = $this->get('choferes.servicios.chofer');

        if ($request->getMethod() == 'POST') {
            $id =  $request->get('idCurso');
            $curso =  $em->getRepository('ChoferesBundle:Curso')->findOneBy(array('id' => $id));
            $choferes = $choferService->obtenerChoferesPorCurso($curso);
            $choferesIds = $request->get('chofer');
            foreach ($choferesIds as $idChofer) {
                $chofer = $em->getRepository('ChoferesBundle:Chofer')->find($idChofer);
                if (! in_array($chofer, $choferes)) {
                  $choferCurso = new ChoferCurso();
                  $choferCurso->setChofer($chofer);
                  $choferCurso->setCurso($curso);
                  //Si el curso tiene comprobante de pago marco el choferCurso como pagado
                  $choferCurso->setPagado(strlen($curso->getComprobante()) > 0);

                  $em->persist($choferCurso);
                } else {
                    $this->get('session')->getFlashBag()->add('warning', 'El chofer fue previamente agregado.');
                }
            }
            $em->flush();
            $choferes = $choferService->obtenerChoferesPorCurso($curso);
        } else {
            $id =  $request->query->get('idCurso');

            $curso =  $em->getRepository('ChoferesBundle:Curso')->findOneBy(['id' => $id]);
            $choferes = $choferService->obtenerChoferesPorCurso($curso);
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

    protected function resetearFiltro(){
        $request = $this->getRequest();
        $session = $request->getSession();

        $session->remove('CursoControllerFilter');

    }

    protected function filter()
    {
        $request = $this->getRequest();
        $session = $request->getSession();

        /*Inicio filtro por prestador*/
        $usuario = $this->getUser();
        $usuarioService =  $this->get('choferes.servicios.usuario');

        $filterForm = $this->createForm(new CursoFilterType($usuarioService), null, ['user' => $usuario]);
        $em = $this->getDoctrine()->getManager();

        if ($usuario->getRol() == 'ROLE_PRESTADOR') {
            //filtro solo lo que es de este usuario
            $prestador = $usuarioService->obtenerPrestadorPorUsuario($usuario);
            $queryBuilder = $em->getRepository('ChoferesBundle:Curso')->createQueryBuilder('d')
                ->where('d.prestador = ?1')
                ->setParameter(1, $prestador->getId());
        } else {
            $queryBuilder = $em->getRepository('ChoferesBundle:Curso')->createQueryBuilder('d');
        }
        /*Fin filtro por prestador*/

        // Reset filter
        if ($request->get('filter_action') == 'reset') {
            $this->resetearFiltro();
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

                if (array_key_exists('docente',$filterData)) {
                    $entity = $filterData['docente'];
                    if($entity) {
                        $entity = $this->getDoctrine()->getEntityManager()->merge($entity);
                        $filterData['docente'] = $entity;
                    }
                }

                if (array_key_exists('prestador',$filterData)) {
                    $entity = $filterData['prestador'];
                    if($entity) {
                        $entity = $this->getDoctrine()->getEntityManager()->merge($entity);
                        $filterData['prestador'] = $entity;
                    }
                }

                if (array_key_exists('sede',$filterData)) {
                    $entity = $filterData['sede'];
                    if($entity) {
                        $entity = $this->getDoctrine()->getEntityManager()->merge($entity);
                        $filterData['sede'] = $entity;
                    }
                }

                if (array_key_exists('tipocurso',$filterData)) {
                    $entity = $filterData['tipocurso'];
                    if($entity){
                        $entity = $this->getDoctrine()->getEntityManager()->merge($entity);
                        $filterData['tipocurso'] = $entity;
                    }
                }

                if (array_key_exists('estado',$filterData)) {
                    $entity = $filterData['estado'];
                    if($entity) {
                        $entity = $this->getDoctrine()->getEntityManager()->merge($entity);
                        $filterData['estado'] = $entity;
                    }
                }

                $filterForm = $this->createForm(new CursoFilterType($usuarioService), $filterData, ['user' => $usuario]);
                $this->get('lexik_form_filter.query_builder_updater')->addFilterConditions($filterForm, $queryBuilder);
            }
        }

        return array($filterForm, $queryBuilder);
    }

    /**
    * Get results from paginator and get paginator view.
    *
    */
    protected function paginator($queryBuilder, $route = null)
    {
        // Paginator
        $adapter = new DoctrineORMAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);
        $currentPage = $this->getRequest()->get('page', 1);
        $pagerfanta->setCurrentPage($currentPage);
        $entities = $pagerfanta->getCurrentPageResults();

        // Paginator - route generator
        $me = $this;
        $routeGenerator = function($page) use ($me, $route )
        {
            $route =  $route == null ? $me->getRequest()->get('_route') : $route;
            return $me->generateUrl($route, array('page' => $page));
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

            $docentes = $em->getRepository('ChoferesBundle:Docente')
                ->findBy(
                    ['prestador' => $prestador, 'activo' => true],
                    ['apellido' => 'ASC', 'nombre' => 'ASC']
                );

            $sedes = $em->getRepository('ChoferesBundle:Sede')->findBy(array(
                'prestador' => $prestador,
                'activo' => true
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

            if ($this->isCursoFormValid($form)) {

                $dtInicio = $form->get('fechaInicio')->getData() . ' ' . $form->get('horaInicio')->getData();
                $fechaInicio = \DateTime::createFromFormat('d/m/Y H:i', $dtInicio);

                $dtFin = $form->get('fechaFin')->getData() . ' ' . $form->get('horaFin')->getData();
                $fechaFin = \DateTime::createFromFormat('d/m/Y H:i', $dtFin);

                if ($this->getUser()->getRol() == 'ROLE_PRESTADOR') {
                    $prestador = $usuarioService->obtenerPrestadorPorUsuario($this->getUser());
                    $entity->setPrestador($prestador);
                }

                $entity->setEstado($em->getRepository('ChoferesBundle:EstadoCurso')->find(self::ESTADO_CURSO_DEFAULT));

                $entity->setFechaInicio($fechaInicio);
                $entity->setFechaFin($fechaFin);

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

            $docentes = $em->getRepository('ChoferesBundle:Docente')
                ->findBy(
                    ['prestador' => $prestador, 'activo' => true],
                    ['apellido' => 'ASC', 'nombre' => 'ASC']
                );

            $sedes = $em->getRepository('ChoferesBundle:Sede')->findBy(array(
                'prestador' => $prestador,
                'activo' => true
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

            $choferesCurso = array_filter($choferesCurso, function ($choferCurso) {
                return ($choferCurso->getChofer()->getEstaActivo() === TRUE);
            });
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
                'prestador' => $prestador,
                'activo' => true
            ));

            $sedes = $em->getRepository('ChoferesBundle:Sede')->findBy(array(
                'prestador' => $prestador,
                'activo' => true
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
        $choferService = $this->get('choferes.servicios.chofer');

        $em = $this->getDoctrine()->getManager();

        $curso = $em->getRepository('ChoferesBundle:Curso')->find($id);

        if (! $curso) {
            throw $this->createNotFoundException('Unable to find Curso entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        if ($this->getUser()->getRol() == 'ROLE_PRESTADOR') {
            $prestador = $usuarioService->obtenerPrestadorPorUsuario($this->getUser());

            $docentes = $em->getRepository('ChoferesBundle:Docente')->findBy([
                'prestador' => $prestador,
                'activo' => true
            ]);

            $sedes = $em->getRepository('ChoferesBundle:Sede')->findBy([
                'prestador' => $prestador,
                'activo' => true
            ]);
        } else {
            $docentes = null;
            $sedes = null;
        }

        $editForm = $this->createForm(new CursoType($usuarioService), $curso, [
            'user' => $this->getUser(),
            'docentes' => $docentes,
            'sedes' => $sedes
        ]);

        $editForm->bind($request);

        if ($editForm->isValid()) {
            $dtInicio = $editForm->get('fechaInicio')->getData() . ' ' . $editForm->get('horaInicio')->getData();
            $fechaInicio = \DateTime::createFromFormat('d/m/Y H:i', $dtInicio);

            $dtFin = $editForm->get('fechaFin')->getData() . ' ' . $editForm->get('horaFin')->getData();
            $fechaFin = \DateTime::createFromFormat('d/m/Y H:i', $dtFin);

            if ($fechaInicio > $fechaFin) {
                $this->get('session')->getFlashBag()
                    ->add('error', 'ERROR! Fecha de inicio posterior a fecha fin. Por favor corregir.');
            } else {
                $curso->setFechaInicio($fechaInicio);
                $curso->setFechaFin($fechaFin);

                if ($editForm->has('fechaPago') && $editForm->get('fechaPago')->getData() !== null) {
                    $fechaPago = $editForm->get('fechaPago')->getData();
                    $dtFechaPago = \DateTime::createFromFormat('d/m/Y', $fechaPago);
                    $curso->setFechaPago($dtFechaPago);
                }

                if ($editForm->has('fechaValidacion') && $editForm->get('fechaValidacion')->getData() !== null) {
                    $fechaValidacion = $editForm->get('fechaValidacion')->getData();
                    $dtFechaValidacion = \DateTime::createFromFormat('d/m/Y', $fechaValidacion);
                    $curso->setFechaValidacion($dtFechaValidacion);
                }

                $em->persist($curso);
                $em->flush();
                if (strlen($curso->getComprobante()) > 0) {
                    //comprobante seteado, hay que marcar como pagado todos los ChoferCurso
                    $choferService->actualizarCursoChofer($curso);
                }

                $this->get('session')->getFlashBag()->add('success', 'flash.update.success');
                return $this->redirect($this->generateUrl('curso_edit', ['id' => $id]));
            }
        } else {
            $this->get('session')->getFlashBag()->add('error', 'flash.update.error');

            $errors = $this->get('validator')->validate( $curso );

            foreach( $errors as $error ) {
                $this->get('session')->getFlashBag()->add('error', $error->getMessage());
            }
        }

        return $this->render('ChoferesBundle:Curso:edit.html.twig', [
            'entity'      => $curso,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'css_active' => 'curso',
        ]);
    }

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
    * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_CNTSV')")
    */
    public function cargaMasivaTriAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $cursoService = $this->get('choferes.servicios.curso');

            $ids = $request->request->get('id');
            $tris = $request->request->get('tri');

            $cursos = $cursoService->cargaMasivaTri($ids, $tris);

            $this->get('session')->getFlashBag()->add('success', 'Se cargaron ' . count($cursos) . ' cursos');
            return $this->redirect($this->generateUrl('curso_carga_masiva_tri'));
        }

        return $this->render('ChoferesBundle:Curso:carga_masiva_tri.html.twig', []);
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

    private function isCursoFormValid($form)
    {
        $fechaInicio = Carbon::createFromFormat(
            'd/m/Y H:i',
            $form->get('fechaInicio')->getData() . ' ' . $form->get('horaInicio')->getData()
        );

        $fechaFin = Carbon::createFromFormat(
            'd/m/Y H:i',
            $form->get('fechaFin')->getData() . ' ' . $form->get('horaFin')->getData()
        );

        if ($fechaInicio > $fechaFin)
        {
            $this->get('session')->getFlashBag()->add('error', 'ERROR! Fecha de inicio posterior a fecha fin. Por favor corregir.');
            return false;
        }

        // TODO: FIX VALIDATION SEGUN https://app.asana.com/0/63730808999960/422170563404553/f

        //$diffDaysFechasInicioFin = $fechaInicio->diffInDays($fechaFin);

        //$tipoCursoId = $form->get('tipocurso')->getData()->getId();

        // if ($tipoCursoId === TipoCurso::ID_BASICO)
        // {
        //     if ($diffDaysFechasInicioFin < 2) {
        //         $this->get('session')->getFlashBag()->add('error', 'ERROR! Para un curso básico la duración no puede ser menor a 48 hs. Por favor corregir.');
        //         return false;
        //     }
        // }

        // if ($tipoCursoId === TipoCurso::ID_COMPLEMENTARIO)
        // {
        //     if ($diffDaysFechasInicioFin < 1) {
        //         $this->get('session')->getFlashBag()->add('error', 'ERROR! Para un curso complementario la duración no puede ser menor a 24 hs. Por favor corregir.');
        //         return false;
        //     }
        // }

        // $em = $this->getDoctrine()->getManager();

        // $prestador = $form->get('prestador')->getData();
        // $sede = $form->get('sede')->getData();

        // $cursosMismaFechaInicio = $em->getRepository('ChoferesBundle:Curso')->findBy([
        //     'fechaInicio' => $fechaInicio,
        //     'prestador' => $prestador,
        //     'sede' => $sede,
        // ]);

        // if (count($cursosMismaFechaInicio) >= $sede->getAulas())
        // {
        //     $this->get('session')->getFlashBag()->add('error', 'ERROR! No hay aulas disponibles para dar de alta un curso en este dia y horario para esta sede. Por favor corregir.');
        //     return false;
        // }

        return true;
    }
}
