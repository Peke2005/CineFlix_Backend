<?php

namespace App\Entity;

use App\Repository\PaisesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PaisesRepository::class)]
class Paises
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]  // Añadimos el tipo explícitamente
    private ?int $id_pais = null;

    #[ORM\Column(length: 45)]
    private ?string $nombre_pais = null;

    /**
     * @var Collection<int, Peliculas>
     */
    #[ORM\OneToMany(targetEntity: Peliculas::class, mappedBy: 'paises')]
    private Collection $relacionPeliculas;

    public function __construct()
    {
        $this->relacionPeliculas = new ArrayCollection();
    }

    public function getIdPais(): ?int
    {
        return $this->id_pais;
    }

    public function getNombrePais(): ?string
    {
        return $this->nombre_pais;
    }

    public function setNombrePais(string $nombre_pais): static
    {
        $this->nombre_pais = $nombre_pais;

        return $this;
    }

    /**
     * @return Collection<int, Peliculas>
     */
    public function getRelacionPeliculas(): Collection
    {
        return $this->relacionPeliculas;
    }

    public function addRelacionPelicula(Peliculas $relacionPelicula): static
    {
        if (!$this->relacionPeliculas->contains($relacionPelicula)) {
            $this->relacionPeliculas->add($relacionPelicula);
            $relacionPelicula->setPaises($this);
        }

        return $this;
    }

    public function removeRelacionPelicula(Peliculas $relacionPelicula): static
    {
        if ($this->relacionPeliculas->removeElement($relacionPelicula)) {
            // set the owning side to null (unless already changed)
            if ($relacionPelicula->getPaises() === $this) {
                $relacionPelicula->setPaises(null);
            }
        }

        return $this;
    }
}