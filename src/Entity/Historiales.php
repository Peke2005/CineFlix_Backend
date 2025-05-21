<?php

namespace App\Entity;

use App\Repository\HistorialesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HistorialesRepository::class)]
class Historiales
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Usuarios::class, inversedBy: 'historiales')]
    #[ORM\JoinColumn(name: 'usuario_id', referencedColumnName: 'id_usuario', nullable: false)]
    private ?Usuarios $usuario = null;

    #[ORM\ManyToOne(targetEntity: Peliculas::class, inversedBy: 'historiales')]
    #[ORM\JoinColumn(name: 'pelicula_id', referencedColumnName: 'id_pelicula', nullable: false)]
    private ?Peliculas $pelicula = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $fechaVista = null;
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsuario(): ?Usuarios
    {
        return $this->usuario;
    }

    public function setUsuario(?Usuarios $usuario): static
    {
        $this->usuario = $usuario;
        return $this;
    }

    public function getPelicula(): ?Peliculas
    {
        return $this->pelicula;
    }

    public function setPelicula(?Peliculas $pelicula): static
    {
        $this->pelicula = $pelicula;
        return $this;
    }

    public function getFechaVista(): ?\DateTimeInterface
    {
        return $this->fechaVista;
    }

    public function setFechaVista(\DateTimeInterface $fechaVista): static
    {
        $this->fechaVista = $fechaVista;
        return $this;
    }
}