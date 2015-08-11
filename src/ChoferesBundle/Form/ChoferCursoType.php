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
            ->add('aprobado', 'checkbox', array('required' => false))
            ->add('pagado', 'checkbox', array('required' => false))
            ->add('chofer')
            ->add('curso', 'entity', array(
                'class' => 'ChoferesBundle\Entity\Curso',
                'query_builder' => function (\Doctrine\ORM\EntityRepository $er) use ($options)
                {
                    if (isset($options['attr']['prestadorId']) && ($options['attr']['prestadorId'] != NULL))
                    {
                        return $er->createQueryBuilder('Curso')
                            ->where('Curso.prestador = :param')
                            ->setParameter('param', $options['attr']['prestadorId']);
                    }

                    return $er->createQueryBuilder('Curso');
                },
            ))
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
