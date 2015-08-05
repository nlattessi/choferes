<?php

namespace ChoferesBundle\DataFixtures\ORM;

use \Doctrine\Common\DataFixtures\AbstractFixture;
use \Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use \Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use ChoferesBundle\Entity\Usuario;

class LoadUserData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $userAdmin = new Usuario();
        $userAdmin->setNombre('admin');
        $userAdmin->setMail('admin@admin.com');
        $encoder = $this->container
           ->get('security.encoder_factory')
           ->getEncoder($userAdmin)
        ;
        $userAdmin->setPassword($encoder->encodePassword('admin', $userAdmin->getSalt()));
        $userAdmin->setActivo(true);
        $userAdmin->setRol($this->getReference('rolAdmin'));
        $this->addReference('userAdmin', $userAdmin);

        $userCNTSV = new Usuario();
        $userCNTSV->setNombre('cntsv');
        $userCNTSV->setMail('cntsv@cntsv.com');
        $encoder = $this->container
           ->get('security.encoder_factory')
           ->getEncoder($userCNTSV)
        ;
        $userCNTSV->setPassword($encoder->encodePassword('cntsv', $userCNTSV->getSalt()));
        $userCNTSV->setActivo(true);
        $userCNTSV->setRol($this->getReference('rolCNTSV'));
        $this->addReference('userCNTSV', $userCNTSV);

        $userPrestador = new Usuario();
        $userPrestador->setNombre('prestador');
        $userPrestador->setMail('prestador@prestador.com');
        $encoder = $this->container
           ->get('security.encoder_factory')
           ->getEncoder($userPrestador)
        ;
        $userPrestador->setPassword($encoder->encodePassword('prestador', $userPrestador->getSalt()));
        $userPrestador->setActivo(true);
        $userPrestador->setRol($this->getReference('rolPrestador'));
        $this->addReference('userPrestador', $userPrestador);

        $userCNRT = new Usuario();
        $userCNRT->setNombre('cnrt');
        $userCNRT->setMail('cnrt@cnrt.com');
        $encoder = $this->container
           ->get('security.encoder_factory')
           ->getEncoder($userCNRT)
        ;
        $userCNRT->setPassword($encoder->encodePassword('cnrt', $userCNRT->getSalt()));
        $userCNRT->setActivo(true);
        $userCNRT->setRol($this->getReference('rolCNRT'));
        $this->addReference('userCNRT', $userCNRT);

        $userCENT = new Usuario();
        $userCENT->setNombre('cent');
        $userCENT->setMail('cent@cent.com');
        $encoder = $this->container
           ->get('security.encoder_factory')
           ->getEncoder($userCENT)
        ;
        $userCENT->setPassword($encoder->encodePassword('cent', $userCENT->getSalt()));
        $userCENT->setActivo(true);
        $userCENT->setRol($this->getReference('rolCENT'));
        $this->addReference('userCENT', $userCENT);


        $manager->persist($userAdmin);
        $manager->persist($userCNTSV);
        $manager->persist($userPrestador);
        $manager->persist($userCNRT);
        $manager->persist($userCENT);

        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 2;
    }
}
