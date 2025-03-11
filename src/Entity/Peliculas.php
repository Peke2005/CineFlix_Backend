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
    #[ORM\Column]
    private ?int $id_pelicula = null;

    #[ORM\Column(length: 45)]
    private ?string $titulo = null;

    #[ORM\Column(length: 45)]
    private ?string $descripcion = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $año = null;

    #[ORM\Column]
    private ?int $duracion = null;

    #[ORM\Column(length: 255)]
    private ?string $portada = null;

    #[ORM\Column(length: 255)]
    private ?string $trailer = null;

    /**
     * @var Collection<int, Categorias>
     */
    #[ORM\ManyToMany(targetEntity: Categorias::class)]
    private Collection $relationCategorias;

    #[ORM\ManyToOne(inversedBy: 'relacionPeliculas')]
    private ?Paises $paises = null;

    public function __construct()
    {
        $this->relationCategorias = new ArrayCollection();
    }

    public function getIdPelicula(): ?int
    {
        return $this->id_pelicula;
    }

    public function getTitulo(): ?string
    {
        return $this->titulo;
    }

    public function setTitulo(string $titulo): static
    {
        $this->titulo = $titulo;

        return $this;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(string $descripcion): static
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    public function getAño(): ?\DateTimeInterface
    {
        return $this->año;
    }

    public function setAño(\DateTimeInterface $año): static
    {
        $this->año = $año;

        return $this;
    }

    public function getDuracion(): ?int
    {
        return $this->duracion;
    }

    public function setDuracion(int $duracion): static
    {
        $this->duracion = $duracion;

        return $this;
    }

    public function getPortada(): ?string
    {
        return $this->portada;
    }

    public function setPortada(string $portada): static
    {
        $this->portada = $portada;

        return $this;
    }

    public function getTrailer(): ?string
    {
        return $this->trailer;
    }

    public function setTrailer(string $trailer): static
    {
        $this->trailer = $trailer;

        return $this;
    }

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
