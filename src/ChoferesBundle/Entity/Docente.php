<?php

namespace ChoferesBundle\Entity;

/**
 * Docente
 */
class Docente
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
    private $apellido;

    /**
     * @var string
     */
    private $dni;

    /**
     * @var \ChoferesBundle\Entity\Prestador
     */
    private $prestador;

    /**
     * @var boolean
     */
    private $activo = true;


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
     * @return Docente
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
     * Set apellido
     *
     * @param string $apellido
     *
     * @return Docente
     */
    public function setApellido($apellido)
    {
        $this->apellido = $apellido;

        return $this;
    }

    /**
     * Get apellido
     *
     * @return string
     */
    public function getApellido()
    {
        return $this->apellido;
    }

    /**
     * Set dni
     *
     * @param string $dni
     *
     * @return Docente
     */
    public function setDni($dni)
    {
        $this->dni = $dni;

        return $this;
    }

    /**
     * Get dni
     *
     * @return string
     */
    public function getDni()
    {
        return $this->dni;
    }

    /**
     * Set prestador
     *
     * @param \ChoferesBundle\Entity\Prestador $prestador
     *
     * @return Docente
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

    public function __toString(){
        return $this->nombre . ' ' . $this->apellido;
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
}
