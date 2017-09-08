<?php

namespace ChoferesBundle\Form;

use ChoferesBundle\Servicios\UsuarioService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SedeType extends AbstractType
{

    public function __construct(UsuarioService $usuarioService)
    {
        $this->usuarioService = $usuarioService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $usuario = $options['user'];
        $builder
            ->add('nombre')
            ->add('direccion')
            ->add('provincia')
            ->add('ciudad')
            ->add('telefono')
            ->add('prestador')
            ->add('aulas', 'integer')
            ->add('reset', 'reset', ['label' => 'Limpiar '])
        ;

        if ($usuario->getRol() == 'ROLE_CNTSV')
        {
            $builder
                ->add('prestador', 'entity', [
                    'class' => 'ChoferesBundle:Prestador',
                    'empty_value' => '',
                    'choices' => $this->usuarioService->obtenerPrestadoresActivos()
                ]);

        }

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ChoferesBundle\Entity\Sede',
            'user' => null
        ));
    }

    public function getName()
    {
        return 'choferesbundle_sede';
    }
}
