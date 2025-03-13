<?php

namespace App\Entity;

use App\Repository\ActoresRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ActoresRepository::class)]
class Actores
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]  // Añadimos el tipo explícitamente
    private ?int $id_actor = null;

    #[ORM\Column(length: 45)]
    private ?string $nombre = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $fecha_nacimiento = null;

    #[ORM\Column(length: 45)]
    private ?string $nacionalidad = null;

    /**
     * @var Collection<int, Peliculas>
     */
    #[ORM\ManyToMany(targetEntity: Peliculas::class, inversedBy: 'actores')]
    #[ORM\JoinTable(name: 'peliculas_has_actores')]
    #[ORM\JoinColumn(name: 'id_actor', referencedColumnName: 'id_actor')]
    #[ORM\InverseJoinColumn(name: 'id_pelicula', referencedColumnName: 'id_pelicula')]
    private Collection $relationPeliculas;

    public function __construct()
    {
        $this->relationPeliculas = new ArrayCollection();
    }

    public function getIdActor(): ?int
    {
        return $this->id_actor;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;
        return $this;
    }

    public function getFechaNacimiento(): ?\DateTimeInterface
    {
        return $this->fecha_nacimiento;
    }

    public function setFechaNacimiento(\DateTimeInterface $fecha_nacimiento): static
    {
        $this->fecha_nacimiento = $fecha_nacimiento;
        return $this;
    }

    public function getNacionalidad(): ?string
    {
        return $this->nacionalidad;
    }

    public function setNacionalidad(string $nacionalidad): static
    {
        $this->nacionalidad = $nacionalidad;
        return $this;
    }

    /**
     * @return Collection<int, Peliculas>
     */
    public function getRelationPeliculas(): Collection
    {
        return $this->relationPeliculas;
    }

    public function addRelationPelicula(Peliculas $relationPelicula): static
    {
        if (!$this->relationPeliculas->contains($relationPelicula)) {
            $this->relationPeliculas->add($relationPelicula);
        }
        return $this;
    }

    public function removeRelationPelicula(Peliculas $relationPelicula): static
    {
        $this->relationPeliculas->removeElement($relationPelicula);
        return $this;
    }
}