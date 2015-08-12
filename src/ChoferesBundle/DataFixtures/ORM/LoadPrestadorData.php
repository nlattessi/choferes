<?php

namespace ChoferesBundle\DataFixtures\ORM;

use \Doctrine\Common\DataFixtures\AbstractFixture;
use \Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use \Doctrine\Common\Persistence\ObjectManager;

use ChoferesBundle\Entity\Prestador;

class LoadPrestadorData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $prestador1 = new Prestador();
        $prestador1->setNombre('prestador1');
        $prestador1->setCuit('20111111117');
        $prestador1->setDireccion('direccion 123');
        $prestador1->setTelefono('1234-5678');
        $prestador1->setMail('prestador1@email.com');
        $prestador1->setLogo(NULL);
        $this->addReference('prestador1', $prestador1);

        $manager->persist($prestador1);

        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 5;
    }
}
