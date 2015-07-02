<?php

namespace ChoferesBundle\Entity;

/**
 * UsuarioPrestador
 */
class UsuarioPrestador
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \ChoferesBundle\Entity\Prestador
     */
    private $prestador;

    /**
     * @var \ChoferesBundle\Entity\Usuario
     */
    private $usuario;


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
     * Set prestador
     *
     * @param \ChoferesBundle\Entity\Prestador $prestador
     *
     * @return UsuarioPrestador
     */
    public function setPrestador(\ChoferesBundle\Entity\Prestador $prestador = null)
    {
        $this->prestador = $prestador;

        return $this;
    }

    /**
     * Get prestador
     *
     * @return \ChoferesBundle\Entity\Prestador
     */
    public function getPrestador()
    {
        return $this->prestador;
    }

    /**
     * Set usuario
     *
     * @param \ChoferesBundle\Entity\Usuario $usuario
     *
     * @return UsuarioPrestador
     */
    public function setUsuario(\ChoferesBundle\Entity\Usuario $usuario = null)
    {
        $this->usuario = $usuario;

        return $this;
    }

    /**
     * Get usuario
     *
     * @return \ChoferesBundle\Entity\Usuario
     */
    public function getUsuario()
    {
        return $this->usuario;
    }

    public function __toString(){
        return "relacionUsuarioPrestador: " . $this->id;
    }
}

