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
    private $aprobado = false;

    /**
     * @var boolean
     */
    private $pagado = false;

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
     * Set aprobado
     *
     * @param boolean $aprobado
     *
     * @return ChoferCurso
     */
    public function setAprobado($aprobado)
    {
        $this->aprobado = $aprobado;

        return $this;
    }

    /**
     * Get aprobado
     *
     * @return boolean
     */
    public function getAprobado()
    {
        return $this->aprobado;
    }

    /**
     * Set pagado
     *
     * @param boolean $pagado
     *
     * @return ChoferCurso
     */
    public function setPagado($pagado)
    {
        $this->pagado = $pagado;

        return $this;
    }

    /**
     * Get pagado
     *
     * @return boolean
     */
    public function getPagado()
    {
        return $this->pagado;
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
