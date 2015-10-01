<?php

namespace ChoferesBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Validator\Constraints as Assert;

class ChoferStatusType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('dni', 'text', [
              // 'attr' => ['placeholder' => 'Ingrese el DNI', 'class' => 'span4'],
              'constraints' => [
                new Assert\Length(['min' => 7, 'minMessage' => 'DNI debe tener un minimo de 7 digitos']),
                new Assert\Regex(['pattern' => '/^[0-9]*$/', 'message' => 'DNI solo con caracteres numericos'])
              ],
              'error_bubbling' => true,
              'invalid_message' => 'En DNI solo debe ingresar numeros',
              //'label' => 'DNI'
            ]);

        if ($options['use_captcha']) {
            $builder->add(
                'captcha', 'captcha', [
                'length' => 5,
                //'attr' => ['maxlength' => 10, 'placeholder' => 'Ingrese el codigo de seguridad'],
                'invalid_message' => 'Codigo de seguridad invalido',
                'background_color' => [255, 255, 255],
                'error_bubbling' => true,
                'ignore_all_effects' => true,
                'height' => 40,
                'width' => 130,
           ]);
        }

        // $builder->add('submit', 'submit', [
        //   'label' => 'Consultar',
        //   'attr' => ['type' => 'submit', 'class' => 'btn btn-info btn-block']
        // ]);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'use_captcha' => true,
        ));
    }

    public function getName()
    {
        return 'choferesbundle_choferstatus';
    }
}
