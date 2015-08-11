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
            ->add('fechaInicio', 'datetime', array(
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'format' => 'dd-MM-yyyy HH:mm:ss',
                'days' => range(date('d') + 5, 31),
                'months' => range(date('m'), 12),
                'years' => range(date('Y'), date('Y') + 5),
                'empty_value' => array(
                    'year'  => 'Año',
                    'month' => 'Mes',
                    'day'   => 'Dia',
                    'hour' => 'Hora',
                    'minute' => 'Minutos')
            ))
            ->add('fechaFin', 'datetime', array(
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'format' => 'dd-MM-yyyy HH:mm:ss',
                'days' => range(date('d') + 5, 31),
                'months' => range(date('m'), 12),
                'years' => range(date('Y'), date('Y') + 5),
                'empty_value' => array(
                    'year'  => 'Año',
                    'month' => 'Mes',
                    'day'   => 'Dia',
                    'hour' => 'Hora',
                    'minute' => 'Minutos')
            ))
            ->add('codigo')
            ->add('docente')
            ->add('estado')
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
