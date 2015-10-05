<?php

namespace ChoferesBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormError;

use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;

class PrestadorFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            #->add('id', 'filter_number_range')
            ->add('nombre', 'filter_text', array('label' => 'Nombre/Razon Social', 'condition_pattern' => FilterOperands::STRING_BOTH))
            ->add('cuit', 'filter_text', [
                'condition_pattern' => FilterOperands::STRING_BOTH,
            ])
            ->add('direccion', 'filter_text', [
                'condition_pattern' => FilterOperands::STRING_BOTH,
            ])
            ->add('telefono', 'filter_text', [
                'condition_pattern' => FilterOperands::STRING_BOTH,
            ])
            ->add('mail', 'filter_text', [
                'condition_pattern' => FilterOperands::STRING_BOTH,
            ])
            // ->add('logo', 'filter_text')
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

            $event->getForm()->addError(new FormError('Ningún prestador cumple con los parámetros de búsqueda'));
        };
        $builder->addEventListener(FormEvents::POST_BIND, $listener);
    }

    public function getName()
    {
        return 'choferesbundle_prestadorfiltertype';
    }
}
