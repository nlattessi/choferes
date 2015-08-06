<?php

namespace ChoferesBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CursoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fechaInicio')
            ->add('fechaFin')
            ->add('fechaCreacion')
            ->add('codigo')
            ->add('docente')
            ->add('estado')
            // ->add('prestador')
            ->add('sede')
            ->add('tipocurso')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ChoferesBundle\Entity\Curso'
        ));
    }

    public function getName()
    {
        return 'choferesbundle_curso';
    }
}
