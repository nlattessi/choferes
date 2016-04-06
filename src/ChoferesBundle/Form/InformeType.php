<?php

namespace ChoferesBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use ChoferesBundle\Servicios\UsuarioService;
use ChoferesBundle\Entity\Prestador;


class InformeType extends AbstractType
{
    private $usuarioService;

    public function __construct(UsuarioService $usuarioService)
    {
        $this->usuarioService = $usuarioService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('fechaInicio', 'text', ['mapped' => false, 'required' => true])
        ->add('horaInicio', 'text', ['mapped' => false, 'required' => true])
        ->add('fechaFin', 'text', ['mapped' => false, 'required' => true])
        ->add('horaFin', 'text', ['mapped' => false, 'required' => true])

        ->add('prestador', 'entity', [
            'class' => 'ChoferesBundle:Prestador',
            //'empty_value' => '',
            'choices' => $this->usuarioService->obtenerPrestadoresActivos(),
            'required' => true
        ])

        ->add('tipocurso', 'filter_entity', [
            'class' => 'ChoferesBundle:TipoCurso',
            'required' => true
        ])

        ->add('reset', 'reset', ['label' => 'Limpiar '])
      ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([]);
    }

    public function getName()
    {
        return 'choferesbundle_informe';
    }
}
