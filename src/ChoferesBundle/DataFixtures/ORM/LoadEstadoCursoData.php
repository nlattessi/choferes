<?php

namespace ChoferesBundle\DataFixtures\ORM;

use \Doctrine\Common\DataFixtures\AbstractFixture;
use \Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use \Doctrine\Common\Persistence\ObjectManager;

use ChoferesBundle\Entity\EstadoCurso;

class LoadEstadoCursoData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $estadoCursoCargado = new EstadoCurso();
        $estadoCursoCargado->setNombre('cargado');
        $this->addReference('estadoCursoCargado', $estadoCursoCargado);

        $estadoCursoVerificado = new EstadoCurso();
        $estadoCursoVerificado->setNombre('verificado');
        $this->addReference('estadoCursoVerificado', $estadoCursoVerificado);

        $estadoCursoPagado = new EstadoCurso();
        $estadoCursoPagado->setNombre('pagado');
        $this->addReference('estadoCursoPagado', $estadoCursoPagado);

        $manager->persist($estadoCursoCargado);
        $manager->persist($estadoCursoVerificado);
        $manager->persist($estadoCursoPagado);

        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 4;
    }
}
