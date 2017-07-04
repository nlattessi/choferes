<?php

namespace ChoferesBundle\Entity;

/**
 * CursoAuditoria
 */
class CursoAuditoria
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
     * @var \ChoferesBundle\Entity\Auditoria
     */
    // private $auditoria;


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
     * Set curso
     *
     * @param \ChoferesBundle\Entity\Curso $curso
     *
     * @return CursoAuditoria
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

    /**
     * Set auditoria
     *
     * @param \ChoferesBundle\Entity\Auditoria $auditoria
     *
     * @return CursoAuditoria
     */
    // public function setAuditoria(\ChoferesBundle\Entity\Auditoria $auditoria = null)
    // {
    //     $this->auditoria = $auditoria;

    //     return $this;
    // }

    /**
     * Get auditoria
     *
     * @return \ChoferesBundle\Entity\Auditoria
     */
    // public function getAuditoria()
    // {
    //     return $this->auditoria;
    // }
}
