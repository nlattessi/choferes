<?php

namespace ChoferesBundle\DataFixtures\ORM;

use \Doctrine\Common\DataFixtures\AbstractFixture;
use \Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use \Doctrine\Common\Persistence\ObjectManager;

use ChoferesBundle\Entity\TipoCurso;

class LoadTipoCursoData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $tipoCursoBasico = new TipoCurso();
        $tipoCursoBasico->setNombre('basico');
        $this->addReference('tipoCursoBasico', $tipoCursoBasico);

        $tipoCursoAnual = new TipoCurso();
        $tipoCursoAnual->setNombre('complementario');
        $this->addReference('tipoCursoAnual', $tipoCursoAnual);

        $manager->persist($tipoCursoBasico);
        $manager->persist($tipoCursoAnual);

        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 3;
    }
}
