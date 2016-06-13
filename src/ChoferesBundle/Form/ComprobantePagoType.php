<?php

namespace ChoferesBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ComprobantePagoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('monto', 'text', [
                'constraints' => [
                    new Assert\Regex(['pattern' => '/^[0-9]+(\.[0-9]{1,2})?$/', 'message' => 'Ingresar el monto sólo con caracteres numéricos y centavos separados con el cáracter "."']),
                ],
                'error_bubbling' => true,
                'invalid_message' => 'En Monto sólo debe ingresar numeros',
                'required' => true,
            ])
            ->add('codigo', 'text', [
                'required' => true,
            ])
            ->add('reset', 'reset', ['label' => 'Limpiar '])
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ChoferesBundle\Entity\ComprobantePago'
        ));
    }

    public function getName()
    {
        return 'choferesbundle_comprobantepago';
    }
}
