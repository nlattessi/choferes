<?php

namespace ChoferesBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * EstadoAuditoria
 */
class EstadoAuditoria
{
    const ID_ACTIVA = 1;
    const ID_INTERMEDIO = 2;
    const ID_TERMINADA = 3;
    const ID_BORRADOR = 4;
    const ID_ENVIADA = 5;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $nombre;

    private $auditorias;

    public function __construct()
    {
        $this->auditorias = new ArrayCollection();
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
     * Set nombre
     *
     * @param string $nombre
     *
     * @return Rol
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

    public function __toString(){
        return $this->nombre;
    }
}

