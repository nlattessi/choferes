<?php

namespace ChoferesBundle\Entity;

/**
 * ChoferCurso
 */
class ChoferCurso
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var boolean
     */
    private $estado = '0';

    /**
     * @var boolean
     */
    private $apagado = '0';

    /**
     * @var \ChoferesBundle\Entity\Chofer
     */
    private $chofer;

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
     * Set estado
     *
     * @param boolean $estado
     *
     * @return ChoferCurso
     */
    public function setEstado($estado)
    {
        $this->estado = $estado;

        return $this;
    }

    /**
     * Get estado
     *
     * @return boolean
     */
    public function getEstado()
    {
        return $this->estado;
    }

    /**
     * Set apagado
     *
     * @param boolean $apagado
     *
     * @return ChoferCurso
     */
    public function setApagado($apagado)
    {
        $this->apagado = $apagado;

        return $this;
    }

    /**
     * Get apagado
     *
     * @return boolean
     */
    public function getApagado()
    {
        return $this->apagado;
    }

    /**
     * Set chofer
     *
     * @param \ChoferesBundle\Entity\Chofer $chofer
     *
     * @return ChoferCurso
     */
    public function setChofer(\ChoferesBundle\Entity\Chofer $chofer = null)
    {
        $this->chofer = $chofer;

        return $this;
    }

    /**
     * Get chofer
     *
     * @return \ChoferesBundle\Entity\Chofer
     */
    public function getChofer()
    {
        return $this->chofer;
    }

    /**
     * Set curso
     *
     * @param \ChoferesBundle\Entity\Curso $curso
     *
     * @return ChoferCurso
     */
    public function setCurso(\ChoferesBundle\Entity\Curso $curso = null)
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

    public function __toString(){
        return "RelacionChoferCurso: " . $this->id;
    }

}

