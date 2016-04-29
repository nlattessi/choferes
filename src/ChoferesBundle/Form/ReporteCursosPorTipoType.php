<?php
namespace ChoferesBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ReporteCursosPorTipoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('tipoCurso', 'entity', [
                'class' => 'ChoferesBundle:TipoCurso',
                'property' => 'nombre',
                'mapped' => 'false',
                'required' => true
            ])
            ->add('fechaInicioDesde', 'text', ['mapped' => false, 'required' => true])
            ->add('fechaInicioHasta', 'text', ['mapped' => false, 'required' => true])
            ->add('reset', 'reset', ['label' => 'Limpiar '])
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([]);
    }

    public function getName()
    {
        return 'choferesbundle_reporte_cursos_por_tipo';
    }
}