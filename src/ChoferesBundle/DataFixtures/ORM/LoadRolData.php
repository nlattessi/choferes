<?php

namespace ChoferesBundle\DataFixtures\ORM;

use \Doctrine\Common\DataFixtures\AbstractFixture;
use \Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use \Doctrine\Common\Persistence\ObjectManager;

use ChoferesBundle\Entity\Rol;

class LoadRolData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $rolAdmin = new Rol();
        $rolAdmin->setNombre('ROLE_ADMIN');
        $this->addReference('rolAdmin', $rolAdmin);

        $rolCNTSV = new Rol();
        $rolCNTSV->setNombre('ROLE_CNTSV');
        $this->addReference('rolCNTSV', $rolCNTSV);

        $rolPrestador = new Rol();
        $rolPrestador->setNombre('ROLE_PRESTADOR');
        $this->addReference('rolPrestador', $rolPrestador);

        $rolCNRT = new Rol();
        $rolCNRT->setNombre('ROLE_CNRT');
        $this->addReference('rolCNRT', $rolCNRT);

        $rolCENT = new Rol();
        $rolCENT->setNombre('ROLE_CENT');
        $this->addReference('rolCENT', $rolCENT);

        $manager->persist($rolAdmin);
        $manager->persist($rolCNTSV);
        $manager->persist($rolPrestador);
        $manager->persist($rolCNRT);
        $manager->persist($rolCENT);

        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 1;
    }
}
