<?php

namespace ChoferesBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ChoferType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nombre')
            ->add('apellido')
            ->add('dni')
            ->add('precuil')
            ->add('colacuil')
            ->add('cuilEmpresa')
            ->add('matricula', 'text', ['required' => false])
            ->add('tieneCursoBasico', 'checkbox', ['required' => false])
            ->add('reset', 'reset', ['label' => 'Limpiar '])
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ChoferesBundle\Entity\Chofer'
        ));
    }

    public function getName()
    {
        return 'choferesbundle_chofer';
    }
}
