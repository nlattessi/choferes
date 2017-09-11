<?php

namespace ChoferesBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ChoferType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $usuario = $options['user'];

        $builder
            ->add('nombre')
            ->add('apellido')
            ->add('dni')
            // ->add('cuil')
            ->add('triCode')
            // ->add('cuilEmpresa')
            // ->add('tieneCursoBasico', 'checkbox', ['required' => false])
            ->add('reset', 'reset', ['label' => 'Limpiar '])
        ;

        if ($usuario->getRol() != 'ROLE_PRESTADOR')
        {
            $builder->add('tieneCursoBasico', 'checkbox', ['required' => false]);
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ChoferesBundle\Entity\Chofer',
            'user' => null,
        ));
    }

    public function getName()
    {
        return 'choferesbundle_chofer';
    }
}
