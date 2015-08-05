<?php

namespace ChoferesBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UsuarioType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nombre')
            ->add('mail')
            // ->add('password')
          // ->add('activo', 'checkbox', array('required' => false))
            ->add('rol')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ChoferesBundle\Entity\Usuario'
        ));
    }

    public function getName()
    {
        return 'choferesbundle_usuario';
    }
}
