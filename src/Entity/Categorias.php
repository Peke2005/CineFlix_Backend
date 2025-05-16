<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "categorias")]
class Categorias
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id_categoria = null;

    #[ORM\Column(length: 45)]
    private ?string $nombre_categoria = null;

    /**
     * @var Collection<int, Peliculas>
     */
    #[ORM\ManyToMany(targetEntity: Peliculas::class, mappedBy: 'relationCategorias')]
    private Collection $peliculas;

    public function __construct()
    {
        $this->peliculas = new ArrayCollection();
    }

    public function getIdCategoria(): ?int
    {
        return $this->id_categoria;
    }

    public function getNombreCategoria(): ?string
    {
        return $this->nombre_categoria;
    }

    public function setNombreCategoria(string $nombre_categoria): self
    {
        $this->nombre_categoria = $nombre_categoria;
        return $this;
    }

    /**
     * @return Collection<int, Peliculas>
     */
    public function getPeliculas(): Collection
    {
        return $this->peliculas;
    }

    public function addPelicula(Peliculas $pelicula): self
    {
        if (!$this->peliculas->contains($pelicula)) {
            $this->peliculas[] = $pelicula;
        }
        return $this;
    }

    public function removePelicula(Peliculas $pelicula): self
    {
        $this->peliculas->removeElement($pelicula);
        return $this;
    }
}
