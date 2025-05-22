<?php

namespace App\Entity;

use App\Repository\ValoracionesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ValoracionesRepository::class)]
#[ORM\Table(name: 'valoraciones')]
class Valoraciones
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Usuarios::class)]
    #[ORM\JoinColumn(name: "usuario_id", referencedColumnName: "id_usuario", nullable: false, onDelete: "CASCADE")]
    private ?Usuarios $usuario = null;

    #[ORM\ManyToOne(targetEntity: Peliculas::class)]
    #[ORM\JoinColumn(name: "pelicula_id", referencedColumnName: "id_pelicula", nullable: false, onDelete: "CASCADE")]
    private ?Peliculas $pelicula = null;

    #[ORM\Column(type: 'integer')]
    private ?int $valor = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsuario(): ?Usuarios
    {
        return $this->usuario;
    }

    public function setUsuario(?Usuarios $usuario): self
    {
        $this->usuario = $usuario;
        return $this;
    }

    public function getPelicula(): ?Peliculas
    {
        return $this->pelicula;
    }

    public function setPelicula(?Peliculas $pelicula): self
    {
        $this->pelicula = $pelicula;
        return $this;
    }

    public function getValor(): int
    {
        return $this->valor;
    }

    public function setValor(int $valor): self
    {
        $this->valor = $valor;
        return $this;
    }
}
