<?php

namespace ChoferesBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

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
    private $cuil;

    /**
     * @var string
     */
    private $cuilEmpresa;

    /**
     * @var integer
     */
    private $tieneCursoBasico = false;

    /**
     * @var string
     */
    private $matricula;

    /**
     * @var string
     */
    private $triCode;

    private $choferCursos;


    public function __construct()
    {
        $this->choferCursos = new ArrayCollection();
    }

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
     * Set cuil
     *
     * @param string $cuil
     *
     * @return Chofer
     */
    public function setCuil($cuil)
    {
        $this->cuil = $cuil;

        return $this;
    }

    /**
     * Get cuil
     *
     * @return string
     */
    public function getCuil()
    {
        return $this->cuil;
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

    /**
     * @return string
     */
    public function getMatricula()
    {
        return $this->matricula;
    }

    /**
     * @param string $matricula
     */
    public function setMatricula($matricula)
    {
        $this->matricula = $matricula;
    }

    /**
     * @return string
     */
    public function getTriCode()
    {
        return $this->triCode;
    }

    /**
     * @param string $triCode
     */
    public function setTriCode($triCode)
    {
        $this->triCode = $triCode;
    }

    public function getChoferCursos()
    {
        return $this->choferCursos;
    }
}
