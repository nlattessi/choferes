<?php

namespace ChoferesBundle\Form;

use ChoferesBundle\Servicios\UsuarioService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormError;

use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;

class SedeFilterType extends AbstractType
{
    public function __construct(UsuarioService $usuarioService)
    {
        $this->usuarioService = $usuarioService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $usuario = $options['user'];
        $builder
            // ->add('id', 'filter_number_range')
            ->add('nombre', 'filter_text', [
                'condition_pattern' => FilterOperands::STRING_BOTH,
            ])
            ->add('direccion', 'filter_text', [
                'condition_pattern' => FilterOperands::STRING_BOTH,
            ])
            ->add('provincia', 'filter_text', [
                'condition_pattern' => FilterOperands::STRING_BOTH,
            ])
            ->add('ciudad', 'filter_text', [
                'condition_pattern' => FilterOperands::STRING_BOTH,
            ])
            ->add('telefono', 'filter_text', [
                'condition_pattern' => FilterOperands::STRING_BOTH,
            ])
        ;
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
                // ->add('docente', 'filter_entity', [
                //     'class' => 'ChoferesBundle:Docente'
                // ])
                // ->add('sede', 'filter_entity', [
                //     'class' => 'ChoferesBundle:Sede'
                // ])
            ;
        }

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

            $event->getForm()->addError(new FormError('Ninguna sede cumple con los parámetros de búsqueda'));
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
        return 'choferesbundle_sedefiltertype';
    }
}
