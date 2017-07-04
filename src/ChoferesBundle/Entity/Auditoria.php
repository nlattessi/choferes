<?php

namespace ChoferesBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

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
     * @var \ChoferesBundle\Entity\CursoAuditoria
     */
    private $cursos;

    /**
     * @var \ChoferesBundle\Entity\EstadoAuditoria
     */
    private $estado;

    public function __construct()
    {
        $this->cursos = new ArrayCollection();
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
     * @return \ChoferesBundle\Entity\CursoAuditoria
     */
    public function getCursos()
    {
        return $this->cursos;
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

