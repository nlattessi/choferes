<?php

namespace ChoferesBundle\Entity;

/**
 * TipoCurso
 */
class TipoCurso
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
    * @var \ChoferesBundle\Entity\Canon
    */
    private $canon;

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
     * @return TipoCurso
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
    * Set Canon
    *
    * @param Canon $canon
    *
    * @return TipoCurso
    */
    public function setCanon(Canon $canon = null)
    {
        $this->canon = $canon;

        return $this;
    }

    /**
    * Get Canon
    *
    * @return Canon
    */
    public function getCanon()
    {
        return $this->canon;
    }

    public function __toString(){
        return $this->nombre;
    }
}
