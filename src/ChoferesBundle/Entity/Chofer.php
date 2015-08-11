<?php

namespace ChoferesBundle\Entity;

/**
 * Chofer
 */
class Chofer
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
     * @var string
     */
    private $precuil;

    /**
     * @var string
     */
    private $colacuil;

    /**
     * @var string
     */
    private $cuilEmpresa;

    /**
     * @var integer
     */
    private $tieneCursoBasico = false;


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
     * Set id
     *
     * @param integer $id
     *
     * @return Curso
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set nombre
     *
     * @param string $nombre
     *
     * @return Chofer
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
     * @return Chofer
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
     * @return Chofer
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
     * Set precuil
     *
     * @param string $precuil
     *
     * @return Chofer
     */
    public function setPrecuil($precuil)
    {
        $this->precuil = $precuil;

        return $this;
    }

    /**
     * Get precuil
     *
     * @return string
     */
    public function getPrecuil()
    {
        return $this->precuil;
    }

    /**
     * Set colacuil
     *
     * @param string $colacuil
     *
     * @return Chofer
     */
    public function setColacuil($colacuil)
    {
        $this->colacuil = $colacuil;

        return $this;
    }

    /**
     * Get colacuil
     *
     * @return string
     */
    public function getColacuil()
    {
        return $this->colacuil;
    }

    /**
     * Set cuilEmpresa
     *
     * @param string $cuilEmpresa
     *
     * @return Chofer
     */
    public function setCuilEmpresa($cuilEmpresa)
    {
        $this->cuilEmpresa = $cuilEmpresa;

        return $this;
    }

    /**
     * Get cuilEmpresa
     *
     * @return string
     */
    public function getCuilEmpresa()
    {
        return $this->cuilEmpresa;
    }

    /**
     * Set tieneCursoBasico
     *
     * @param boolean $tieneCursoBasico
     *
     * @return Chofer
     */
    public function setTieneCursoBasico($tieneCursoBasico)
    {
        $this->tieneCursoBasico = $tieneCursoBasico;

        return $this;
    }

    /**
     * Get tieneCursoBasico
     *
     * @return boolean
     */
    public function getTieneCursoBasico()
    {
        return $this->tieneCursoBasico;
    }

    public function __toString(){
        return $this->nombre;
    }

}
