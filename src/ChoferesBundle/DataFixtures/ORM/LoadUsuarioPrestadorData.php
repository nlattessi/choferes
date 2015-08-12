<?php

namespace ChoferesBundle\DataFixtures\ORM;

use \Doctrine\Common\DataFixtures\AbstractFixture;
use \Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use \Doctrine\Common\Persistence\ObjectManager;

use ChoferesBundle\Entity\UsuarioPrestador;

class LoadUsuarioPrestadorData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $usuarioPrestador1 = new UsuarioPrestador();
        $usuarioPrestador1->setPrestador($this->getReference('prestador1'));
        $usuarioPrestador1->setUsuario($this->getReference('userPrestador'));
        $this->addReference('usuarioPrestador1', $usuarioPrestador1);

        $manager->persist($usuarioPrestador1);

        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 6;
    }
}
