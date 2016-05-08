<?php

namespace ChoferesBundle\Entity;


/**
 * ComprobantePago
 */
class ComprobantePago
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $monto;

    /**
     * @var string
     */
    private $codigo;

    /**
     * @var \ChoferesBundle\Entity\Curso
     */
    private $curso;

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
     * Set monto
     *
     * @param string $monto
     *
     * @return ComprobantePago
     */
    public function setMonto($monto)
    {
        $this->monto = $monto;

        return $this;
    }

    /**
     * Get monto
     *
     * @return string
     */
    public function getMonto()
    {
        return $this->monto;
    }

    /**
     * Set codigo
     *
     * @param string $codigo
     *
     * @return ComprobantePago
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
     * Set curso
     *
     * @param \ChoferesBundle\Entity\Curso $curso
     *
     * @return ComprobantePago
     */
    public function setCurso(Curso $curso = null)
    {
        $this->curso = $curso;

        return $this;
    }

    /**
     * Get curso
     *
     * @return \ChoferesBundle\Entity\Curso
     */
    public function getCurso()
    {
        return $this->curso;
    }
}
