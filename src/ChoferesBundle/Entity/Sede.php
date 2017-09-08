<?php

namespace ChoferesBundle\Entity;

/**
 * Sede
 */
class Sede
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
    private $direccion;

    /**
     * @var string
     */
    private $provincia;

    /**
     * @var string
     */
    private $ciudad;

    /**
     * @var string
     */
    private $telefono;

    /**
     * @var \ChoferesBundle\Entity\Prestador
     */
    private $prestador;

    /**
     * @var boolean
     */
    private $activo = true;

    private $aulas;


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
     * @return Sede
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
     * Set direccion
     *
     * @param string $direccion
     *
     * @return Sede
     */
    public function setDireccion($direccion)
    {
        $this->direccion = $direccion;

        return $this;
    }

    /**
     * Get direccion
     *
     * @return string
     */
    public function getDireccion()
    {
        return $this->direccion;
    }

    /**
     * Set provincia
     *
     * @param string $provincia
     *
     * @return Sede
     */
    public function setProvincia($provincia)
    {
        $this->provincia = $provincia;

        return $this;
    }

    /**
     * Get provincia
     *
     * @return string
     */
    public function getProvincia()
    {
        return $this->provincia;
    }

    /**
     * Set ciudad
     *
     * @param string $ciudad
     *
     * @return Sede
     */
    public function setCiudad($ciudad)
    {
        $this->ciudad = $ciudad;

        return $this;
    }

    /**
     * Get ciudad
     *
     * @return string
     */
    public function getCiudad()
    {
        return $this->ciudad;
    }

    /**
     * Set telefono
     *
     * @param string $telefono
     *
     * @return Sede
     */
    public function setTelefono($telefono)
    {
        $this->telefono = $telefono;

        return $this;
    }

    /**
     * Get telefono
     *
     * @return string
     */
    public function getTelefono()
    {
        return $this->telefono;
    }

    /**
     * Set prestador
     *
     * @param \ChoferesBundle\Entity\Prestador $prestador
     *
     * @return Sede
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
        return $this->nombre;
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

    public function setAulas($aulas)
    {
        $this->aulas = $aulas;

        return $this;
    }

    public function getAulas()
    {
        return $this->aulas;
    }
}
