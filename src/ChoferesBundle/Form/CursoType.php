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
        ;

        if (isset($options['docentes'])) {
              $builder->add('docente', 'entity', array(
                  'class' => 'ChoferesBundle:Docente',
                  'choices' => $options['docentes'],
                  'required' => false
              ));
        } else {
              $builder->add('docente');
        }

        if (isset($options['sedes'])) {
              $builder->add('sede', 'entity', array(
                  'class' => 'ChoferesBundle:Sede',
                  'choices' => $options['sedes'],
                  'required' => false
              ));
        } else {
              $builder->add('sede');
        }

        $builder->add('tipocurso');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ChoferesBundle\Entity\Curso',
            'user' => null,
            'docentes' => null,
            'sedes' => null
        ));
    }

    public function getName()
    {
        return 'choferesbundle_curso';
    }
}
