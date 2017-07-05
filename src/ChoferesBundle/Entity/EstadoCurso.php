<?php

namespace ChoferesBundle\Entity;

/**
 * EstadoCurso
 */
class EstadoCurso
{
    const ID_CARGADO = 1;
    const ID_CONFIRMADO = 2;
    const ID_POR_VALIDAR = 3;
    const ID_CANCELADO = 4;
    const ID_VALIDADO = 5;
    const ID_FALLA_VALIDACION = 6;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $nombre;


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
     * @return EstadoCurso
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

