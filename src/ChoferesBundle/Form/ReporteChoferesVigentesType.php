<?php

namespace ChoferesBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


class ReporteChoferesVigentesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fechaDesde', 'text', ['mapped' => false, 'required' => true])
            ->add('fechaHasta', 'text', ['mapped' => false, 'required' => true])
            ->add('reset', 'reset', ['label' => 'Limpiar '])
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([]);
    }

    public function getName()
    {
        return 'choferesbundle_reporte_choferes_vigente';
    }
}
