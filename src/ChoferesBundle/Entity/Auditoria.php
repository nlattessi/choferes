<?php

namespace ChoferesBundle\Entity;

/**
 * Auditoria
 */
class Auditoria
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \ChoferesBundle\Entity\Curso
     */
    private $curso;

    /**
     * @var \ChoferesBundle\Entity\EstadoAuditoria
     */
    private $estado;

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
     * @return \ChoferesBundle\Entity\Curso
     */
    public function getCurso()
    {
        return $this->curso;
    }

    /**
     * @param \ChoferesBundle\Entity\Curso $curso
     */
    public function setCurso($curso)
    {
        $this->curso = $curso;
    }

    /**
     * @return \ChoferesBundle\Entity\EstadoAuditoria
     */
    public function getEstado()
    {
        return $this->estado;
    }

    /**
     * @param \ChoferesBundle\Entity\EstadoAuditoria $estado
     */
    public function setEstado($estado)
    {
        $this->estado = $estado;
    }

}

