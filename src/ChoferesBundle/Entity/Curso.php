<?php

namespace ChoferesBundle\Entity;

/**
 * Curso
 */
class Curso
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $fechaInicio;

    /**
     * @var \DateTime
     */
    private $fechaFin;

    /**
     * @var \DateTime
     */
    private $fechaCreacion;

    /**
     * @var string
     */
    private $codigo;

    /**
     * @var string
     */
    private $anio;

    /**
     * @var string
     */
    private $comprobante;

    /**
     * @var \ChoferesBundle\Entity\Docente
     */
    private $docente;

    /**
     * @var \ChoferesBundle\Entity\EstadoCurso
     */
    private $estado;

    /**
     * @var \ChoferesBundle\Entity\Prestador
     */
    private $prestador;

    /**
     * @var \ChoferesBundle\Entity\Sede
     */
    private $sede;

    /**
     * @var \ChoferesBundle\Entity\TipoCurso
     */
    private $tipocurso;

    /**
     * @var \Date
     */
    private $fechaPago;


    /**
     * @var string
     */
    private $observaciones;

    public function __construct()
    {
        $this->setFechaCreacion(new \DateTime());
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
     * Set fechaInicio
     *
     * @param \DateTime $fechaInicio
     *
     * @return Curso
     */
    public function setFechaInicio($fechaInicio)
    {
        $this->fechaInicio = $fechaInicio;

        return $this;
    }

    /**
     * Get fechaInicio
     *
     * @return \DateTime
     */
    public function getFechaInicio()
    {
        return $this->fechaInicio;
    }

    /**
     * Set fechaFin
     *
     * @param \DateTime $fechaFin
     *
     * @return Curso
     */
    public function setFechaFin($fechaFin)
    {
        $this->fechaFin = $fechaFin;

        return $this;
    }

    /**
     * Get fechaFin
     *
     * @return \DateTime
     */
    public function getFechaFin()
    {
        return $this->fechaFin;
    }

    /**
     * Set fechaCreacion
     *
     * @param \DateTime $fechaCreacion
     *
     * @return Curso
     */
    public function setFechaCreacion($fechaCreacion)
    {
        $this->fechaCreacion = $fechaCreacion;

        return $this;
    }

    /**
     * Get fechaCreacion
     *
     * @return \DateTime
     */
    public function getFechaCreacion()
    {
        return $this->fechaCreacion;
    }

    /**
     * Set codigo
     *
     * @param string $codigo
     *
     * @return Curso
     */
    public function setCodigo($codigo)
    {
        $this->codigo = $codigo;

        return $this;
    }

    /**
     * Get codigo
     *
     * @return string
     */
    public function getCodigo()
    {
        return $this->codigo;
    }

    /**
     * @return string
     */
    public function getComprobante()
    {
        return $this->comprobante;
    }

    /**
     * @param string $comprobante
     */
    public function setComprobante($comprobante)
    {
        $this->comprobante = $comprobante;
    }



    /**
     * Set docente
     *
     * @param \ChoferesBundle\Entity\Docente $docente
     *
     * @return Curso
     */
    public function setDocente(\ChoferesBundle\Entity\Docente $docente = null)
    {
        $this->docente = $docente;

        return $this;
    }

    /**
     * Get docente
     *
     * @return \ChoferesBundle\Entity\Docente
     */
    public function getDocente()
    {
        return $this->docente;
    }

    /**
     * Set estado
     *
     * @param \ChoferesBundle\Entity\EstadoCurso $estado
     *
     * @return Curso
     */
    public function setEstado(\ChoferesBundle\Entity\EstadoCurso $estado = null)
    {
        $this->estado = $estado;

        return $this;
    }

    /**
     * Get estado
     *
     * @return \ChoferesBundle\Entity\EstadoCurso
     */
    public function getEstado()
    {
        return $this->estado;
    }

    /**
     * Set prestador
     *
     * @param \ChoferesBundle\Entity\Prestador $prestador
     *
     * @return Curso
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
     * Set sede
     *
     * @param \ChoferesBundle\Entity\Sede $sede
     *
     * @return Curso
     */
    public function setSede(\ChoferesBundle\Entity\Sede $sede = null)
    {
        $this->sede = $sede;

        return $this;
    }

    /**
     * Get sede
     *
     * @return \ChoferesBundle\Entity\Sede
     */
    public function getSede()
    {
        return $this->sede;
    }

    /**
     * Set tipocurso
     *
     * @param \ChoferesBundle\Entity\TipoCurso $tipocurso
     *
     * @return Curso
     */
    public function setTipocurso(\ChoferesBundle\Entity\TipoCurso $tipocurso = null)
    {
        $this->tipocurso = $tipocurso;

        return $this;
    }

    /**
     * Get tipocurso
     *
     * @return \ChoferesBundle\Entity\TipoCurso
     */
    public function getTipocurso()
    {
        return $this->tipocurso;
    }

    public function __toString(){
        if (is_null($this->prestador)) {
          return (string) $this->codigo;
        }else {
          return $this->prestador->getNombre() . ": " . $this->codigo;
        }
    }

    /**
     * @return \Date
     */
    public function getFechaPago()
    {
        return $this->fechaPago;
    }

    /**
     * @param \Date $fechaPago
     */
    public function setFechaPago($fechaPago)
    {
        $this->fechaPago = $fechaPago;
    }

    /**
     * @return string
     */
    public function getAnio()
    {
        return $this->anio;
    }

    /**
     * @param string $anio
     */
    public function setAnio($anio)
    {
        $this->anio = $anio;
    }

    /**
     * @return string
     */
    public function getObservaciones()
    {
        return $this->observaciones;
    }

    /**
     * @param string $observaciones
     */
    public function setObservaciones($observaciones)
    {
        $this->observaciones = $observaciones;
    }


}
