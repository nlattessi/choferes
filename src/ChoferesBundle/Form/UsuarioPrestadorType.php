<?php

namespace ChoferesBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use ChoferesBundle\Servicios\UsuarioService;

class UsuarioPrestadorType extends AbstractType
{
    private $usuarioService;

    public function __construct(UsuarioService $usuarioService)
    {
        $this->usuarioService = $usuarioService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('prestador')
            ->add('usuario')
        ;
        $builder->add('usuario', 'entity', array(
            'class' => 'ChoferesBundle:Usuario',
            'empty_value' => '',
            'required' => true,
            'choices' =>$this->usuarioService->obtenerUsuariosRolPrestador()
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ChoferesBundle\Entity\UsuarioPrestador'
        ));
    }

    public function getName()
    {
        return 'choferesbundle_usuarioprestador';
    }
}
