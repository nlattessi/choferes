<?php

namespace ChoferesBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ChoferCursoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('estado', 'checkbox', array('required' => false))
            ->add('apagado', 'checkbox', array('required' => false))
            ->add('chofer')
            ->add('curso')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ChoferesBundle\Entity\ChoferCurso'
        ));
    }

    public function getName()
    {
        return 'choferesbundle_chofercurso';
    }
}
