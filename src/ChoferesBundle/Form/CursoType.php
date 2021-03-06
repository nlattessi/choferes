<?php

namespace ChoferesBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

use ChoferesBundle\Servicios\UsuarioService;
use ChoferesBundle\Entity\Prestador;


class CursoType extends AbstractType
{
    private $usuarioService;

    public function __construct(UsuarioService $usuarioService)
    {
        $this->usuarioService = $usuarioService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $usuario = $options['user'];


        $builder
            ->add('fechaInicio', 'text', array('mapped' => false))
            ->add('horaInicio', 'text', array('mapped' => false))
            ->add('fechaFin', 'text', array('mapped' => false))
            ->add('horaFin', 'text', array('mapped' => false))
            ->add('tipocurso', 'entity', [
                'class' => 'ChoferesBundle:TipoCurso',
                'required' => true,
            ])
            ->add('reset', 'reset', ['label' => 'Limpiar '])
        ;

        if ($usuario->getRol() == 'ROLE_CNTSV')
        {
            $builder
                ->add('prestador', 'entity', [
                    'class' => 'ChoferesBundle:Prestador',
                    'empty_value' => '',
                    'choices' => $this->usuarioService->obtenerPrestadoresActivos()
                ])
                ->add('codigo')
                ->add('comprobante')
                ->add('observaciones')
                // ->add('fechaValidacion', 'text', array('mapped' => false, 'required' => false,))
                ->add('fechaPago', 'text', array(
                    'required' => false,
                    'mapped' => false
                ));

            $formModifier = function(FormInterface $form, Prestador $prestador = null) {
                $docentes = ($prestador === null) ? array() : $this->usuarioService->obtenerDocentesPorPrestador($prestador);
                $sedes = ($prestador === null) ? array() : $this->usuarioService->obtenerSedesPorPrestador($prestador);
                $estados = ($prestador === null) ? array() : $this->usuarioService->obtenerEstados();

                $form->add('docente', 'entity', array(
                    'class' => 'ChoferesBundle:Docente',
                    'empty_value' => '',
                    'required' => false,
                    'choices' => $docentes
                ));

                $form->add('sede', 'entity', array(
                    'class' => 'ChoferesBundle:Sede',
                    'empty_value' => '',
                    'required' => false,
                    'choices' => $sedes
                ));

                $form->add('estado', 'entity', array(
                    'class' => 'ChoferesBundle:EstadoCurso',
                    'empty_value' => '',
                    'required' => false,
                    'choices' => $estados
                ));

            };

            $builder->addEventListener(
                FormEvents::PRE_SET_DATA,
                function (FormEvent $event) use ($formModifier) {
                    $data = $event->getData();

                    $formModifier($event->getForm(), $data->getPrestador());
                }
            );

            $builder->get('prestador')->addEventListener(
                FormEvents::POST_SUBMIT,
                function (FormEvent $event) use ($formModifier) {
                  $prestador = $event->getForm()->getData();
                  $formModifier($event->getForm()->getParent(), $prestador);
                }
            );
        } else {
            if (isset($options['docentes'])) {
                  $builder->add('docente', 'entity', array(
                      'class' => 'ChoferesBundle:Docente',
                      'choices' => $options['docentes'],
                      'required' => false
                  ));
            } else {
                  $builder->add('docente');
            }

            if (isset($options['sedes'])) {
                  $builder->add('sede', 'entity', array(
                      'class' => 'ChoferesBundle:Sede',
                      'choices' => $options['sedes'],
                      'required' => false
                  ));
            } else {
                  $builder->add('sede');
            }

            if (isset($options['estados'])) {
                $builder->add('estado', 'entity', array(
                    'class' => 'ChoferesBundle:EstadoCurso',
                    'choices' => $options['estados'],
                    'required' => false
                ));
            } else {
                $builder->add('estados');
            }
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ChoferesBundle\Entity\Curso',
            'user' => null,
            'docentes' => null,
            'sedes' => null,
            'estados' => null,
        ));
    }

    public function getName()
    {
        return 'choferesbundle_curso';
    }
}
