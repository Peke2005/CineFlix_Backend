<?php

namespace App\Entity;

use App\Repository\PeliculasRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PeliculasRepository::class)]
class Peliculas
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id_pelicula = null;

    #[ORM\Column(length: 45)]
    private ?string $titulo = null;

    #[ORM\Column(length: 45)]
    private ?string $descripcion = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $año = null;

    #[ORM\Column(type: 'integer')]
    private ?int $duracion = null;

    #[ORM\Column(length: 255)]
    private ?string $portada = null;

    #[ORM\Column(length: 255)]
    private ?string $trailer = null;

    /**
     * @var Collection<int, Categorias>
     */
    #[ORM\ManyToMany(targetEntity: Categorias::class, inversedBy: 'peliculas')]
    #[ORM\JoinTable(name: 'peliculas_has_categorias')]
    #[ORM\JoinColumn(name: 'id_pelicula', referencedColumnName: 'id_pelicula')]
    #[ORM\InverseJoinColumn(name: 'id_categoria', referencedColumnName: 'id_categoria')]
    private Collection $relationCategorias;

    /**
     * @var Collection<int, Actores>
     */
    #[ORM\ManyToMany(targetEntity: Actores::class, mappedBy: 'relationPeliculas')]
    private Collection $actores;

    #[ORM\ManyToOne(targetEntity: Paises::class, inversedBy: 'relacionPeliculas')]
    #[ORM\JoinColumn(name: 'id_pais', referencedColumnName: 'id_pais')]
    private ?Paises $paises = null;

    public function __construct()
    {
        $this->relationCategorias = new ArrayCollection();
        $this->actores = new ArrayCollection();
    }

    // ... (getters y setters como en tu código original)

    /**
     * @return Collection<int, Categorias>
     */
    public function getRelationCategorias(): Collection
    {
        return $this->relationCategorias;
    }

    public function addRelationCategoria(Categorias $relationCategoria): static
    {
        if (!$this->relationCategorias->contains($relationCategoria)) {
            $this->relationCategorias->add($relationCategoria);
        }
        return $this;
    }

    public function removeRelationCategoria(Categorias $relationCategoria): static
    {
        $this->relationCategorias->removeElement($relationCategoria);
        return $this;
    }

    /**
     * @return Collection<int, Actores>
     */
    public function getActores(): Collection
    {
        return $this->actores;
    }

    public function addActor(Actores $actor): static
    {
        if (!$this->actores->contains($actor)) {
            $this->actores[] = $actor;
            $actor->addRelationPelicula($this);
        }
        return $this;
    }

    public function removeActor(Actores $actor): static
    {
        if ($this->actores->removeElement($actor)) {
            $actor->removeRelationPelicula($this);
        }
        return $this;
    }

    public function getPaises(): ?Paises
    {
        return $this->paises;
    }

    public function setPaises(?Paises $paises): static
    {
        $this->paises = $paises;
        return $this;
    }
}