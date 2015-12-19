<?php

namespace ChoferesBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Validator\Constraints as Assert;

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
        // $builder
        //     ->add('fecha_desde') //LISTO
        //     ->add('fecha_hasta') //LISTO
        //     ->add('prestador') // FALTA ESTILOS
        //     ->add('curso_basico o complementario') // FALTA ESTILOS
        $builder
        ->add('fechaInicio', 'text', array('mapped' => false))
        ->add('horaInicio', 'text', array('mapped' => false))
        ->add('fechaFin', 'text', array('mapped' => false))
        ->add('horaFin', 'text', array('mapped' => false))

        ->add('prestador', 'entity', [
            'class' => 'ChoferesBundle:Prestador',
            'empty_value' => '',
            'choices' => $this->usuarioService->obtenerPrestadoresActivos()
        ])

        ->add('tipocurso', 'filter_entity', [
            'class' => 'ChoferesBundle:TipoCurso'
        ])

        ->add('reset', 'reset', ['label' => 'Limpiar '])
      ;

        // $builder->add('submit', 'submit', [
        //   'label' => 'Realizar',
        //   'attr' => ['type' => 'submit', 'class' => 'btn btn-info btn-block']
        // ]);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([

        ]);
    }

    public function getName()
    {
        return 'choferesbundle_informe';
    }
}
