<?php

namespace ChoferesBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CursoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $usuario = $options['user'];

        $builder
            ->add('fechaInicio', 'datetime', array(
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
            ))
            ->add('fechaFin', 'datetime', array(
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
            ))
            ->add('codigo')
            ->add('docente')
            ->add('sede')
            ->add('tipocurso', 'entity', array(
                'class' => 'ChoferesBundle:TipoCurso',
                'required' => true
            ))
        ;

        if ($usuario->getRol() == 'ROLE_ADMIN' || $usuario->getRol() == 'ROLE_CNTSV') {
            $builder->add('estado');
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ChoferesBundle\Entity\Curso',
            'user' => null
        ));
    }

    public function getName()
    {
        return 'choferesbundle_curso';
    }
}
