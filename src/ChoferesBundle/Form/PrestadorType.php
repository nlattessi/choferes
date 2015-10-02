<?php

namespace ChoferesBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PrestadorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nombre', 'text', array('label' => 'Nombre/Razon Social'))
            ->add('cuit')
            ->add('direccion')
            ->add('telefono')
            ->add('mail')
            ->add('reset', 'reset', ['label' => 'Limpiar'])
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ChoferesBundle\Entity\Prestador'
        ));
    }

    public function getName()
    {
        return 'choferesbundle_prestador';
    }
}
