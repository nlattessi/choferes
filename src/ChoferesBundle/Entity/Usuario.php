<?php

namespace ChoferesBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

/**
 * Usuario
 */
class Usuario implements UserInterface, \Serializable, AdvancedUserInterface
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $nombre;

    /**
     * @var string
     */
    private $mail;

    /**
     * @var string
     */
    private $password;

    /**
     * @var boolean
     */
    private $activo = true;

    /**
     * @var \ChoferesBundle\Entity\Rol
     */
    private $rol;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set nombre
     *
     * @param string $nombre
     *
     * @return Usuario
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;

        return $this;
    }

    /**
     * Get nombre
     *
     * @return string
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Set mail
     *
     * @param string $mail
     *
     * @return Usuario
     */
    public function setMail($mail)
    {
        $this->mail = $mail;

        return $this;
    }

    /**
     * Get mail
     *
     * @return string
     */
    public function getMail()
    {
        return $this->mail;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return Usuario
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set activo
     *
     * @param boolean $activo
     *
     * @return Usuario
     */
    public function setActivo($activo)
    {
        $this->activo = $activo;

        return $this;
    }

    /**
     * Get activo
     *
     * @return boolean
     */
    public function getActivo()
    {
        return $this->activo;
    }

    /**
     * Set rol
     *
     * @param \ChoferesBundle\Entity\Rol $rol
     *
     * @return Usuario
     */
    public function setRol(\ChoferesBundle\Entity\Rol $rol = null)
    {
        $this->rol = $rol;

        return $this;
    }

    /**
     * Get rol
     *
     * @return \ChoferesBundle\Entity\Rol
     */
    public function getRol()
    {
        return $this->rol;
    }

    public function __toString(){
        return $this->nombre;
    }

    public function getUsername()
    {
        return $this->getMail();
    }

    public function getSalt()
    {
        return null;
    }

    public function getRoles()
    {
        return array($this->getRol()->getNombre());
    }

    public function eraseCredentials()
    {
    }

    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->mail,
            $this->password,
        ));
    }

    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->mail,
            $this->password,
        ) = unserialize($serialized);
    }

    public function isAccountNonExpired()
    {
        return true;
    }

    public function isAccountNonLocked()
    {
        return true;
    }

    public function isCredentialsNonExpired()
    {
        return true;
    }

    public function isEnabled()
    {
        return $this->activo;
    }
}
