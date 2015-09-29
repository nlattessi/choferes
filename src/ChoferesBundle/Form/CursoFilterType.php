<?php

namespace ChoferesBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormError;

use ChoferesBundle\Servicios\UsuarioService;
use ChoferesBundle\Entity\Prestador;

class CursoFilterType extends AbstractType
{
    private $usuarioService;

    public function __construct(UsuarioService $usuarioService)
    {
        $this->usuarioService = $usuarioService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $usuario = $options['user'];

        if ($usuario->getRol() == 'ROLE_PRESTADOR') {
            $prestador = $this->usuarioService->obtenerPrestadorPorUsuario($usuario);
            $docentes = $this->usuarioService->obtenerDocentesPorPrestador($prestador);
            $sedes = $this->usuarioService->obtenerSedesPorPrestador($prestador);
        }

        $builder
            ->add('fechaInicio', 'filter_date_range', array(
                'left_date_options' => array(
                    'widget' => 'single_text',
                    'format' => 'dd/M/yyyy'
                ),
                'right_date_options' => array(
                  'widget' => 'single_text',
                  'format' => 'dd/M/yyyy'
                ),
                'label' => 'Fecha inicio',
            ))
            ->add('fechaFin', 'filter_date_range', array(
                'left_date_options' => array(
                    'widget' => 'single_text',
                    'format' => 'dd/M/yyyy'
                ),
                'right_date_options' => array(
                  'widget' => 'single_text',
                  'format' => 'dd/M/yyyy'
                ),
                'label' => 'Fecha fin',
            ))

            ->add('codigo', 'filter_text')
            ->add('comprobante', 'filter_text');

        if ($usuario->getRol() == 'ROLE_PRESTADOR') {
            $builder
                ->add('docente', 'filter_entity', [
                    'class' => 'ChoferesBundle:Docente',
                    'choices' => $docentes
                ])
                ->add('sede', 'filter_entity', [
                    'class' => 'ChoferesBundle:Sede',
                    'choices' => $sedes
                ])
            ;
        } else {
            $builder
                ->add('docente', 'filter_entity', ['class' => 'ChoferesBundle:Docente'])
                ->add('sede', 'filter_entity', ['class' => 'ChoferesBundle:Sede'])
            ;
        }

        $builder
            ->add('tipocurso', 'filter_entity', ['class' => 'ChoferesBundle:TipoCurso'])
            ->add('estado', 'filter_entity', ['class' => 'ChoferesBundle:EstadoCurso'])
        ;

        $listener = function(FormEvent $event)
        {
            // Is data empty?
            foreach ($event->getData() as $data) {
                if(is_array($data)) {
                    foreach ($data as $subData) {
                        if(!empty($subData)) return;
                    }
                }
                else {
                    if(!empty($data)) return;
                }
            }

            $event->getForm()->addError(new FormError('Filter empty'));
        };
        $builder->addEventListener(FormEvents::POST_BIND, $listener);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'user' => null,
        ));
    }

    public function getName()
    {
        return 'choferesbundle_cursofiltertype';
    }
}
