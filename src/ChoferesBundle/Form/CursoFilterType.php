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

use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;

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

        $builder
            ->add('id', 'integer', ['required' => false])
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
      ;

        if ($usuario->getRol() == 'ROLE_PRESTADOR') {
            $prestador = $this->usuarioService->obtenerPrestadorPorUsuario($usuario);
            $docentes = $this->usuarioService->obtenerDocentesPorPrestador($prestador);
            $sedes = $this->usuarioService->obtenerSedesPorPrestador($prestador);

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
        }

        if ($usuario->getRol() == 'ROLE_CNTSV') {
            $builder
                ->add('codigo', 'filter_text', [
                    'condition_pattern' => FilterOperands::STRING_BOTH,
                ])
                // ->add('comprobante', 'filter_text')
                ->add('prestador', 'filter_entity', [
                    'class' => 'ChoferesBundle:Prestador',
                    'choices' => $this->usuarioService->obtenerPrestadoresActivos()
                ])
                ->add('fechaValidacion', 'filter_date_range', array(
                    'left_date_options' => array(
                        'widget' => 'single_text',
                        'format' => 'dd/M/yyyy'
                    ),
                    'right_date_options' => array(
                        'widget' => 'single_text',
                        'format' => 'dd/M/yyyy'
                    ),
                    'label' => 'Fecha de Validación',
                ))
                // ->add('docente', 'filter_entity', [
                //     'class' => 'ChoferesBundle:Docente'
                // ])
                // ->add('sede', 'filter_entity', [
                //     'class' => 'ChoferesBundle:Sede'
                // ])
            ;
        }

        $builder
            ->add('tipocurso', 'filter_entity', [
                'class' => 'ChoferesBundle:TipoCurso'
            ])
            ->add('estado', 'filter_entity', [
                'class' => 'ChoferesBundle:EstadoCurso'
            ])
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

            $event->getForm()->addError(new FormError('Ningún curso cumple con los parámetros de búsqueda'));
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
