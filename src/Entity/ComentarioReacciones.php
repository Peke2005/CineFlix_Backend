<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ComentarioReaccionesRepository;

#[ORM\Entity(repositoryClass: ComentarioReaccionesRepository::class)]
#[ORM\Table(name: 'comentario_reacciones')]
class ComentarioReacciones
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Comentarios::class)]
    #[ORM\JoinColumn(name: 'comentario_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Comentarios $comentario = null;

    #[ORM\ManyToOne(targetEntity: Usuarios::class)]
    #[ORM\JoinColumn(name: 'usuario_id', referencedColumnName: 'id_usuario', nullable: false, onDelete: 'CASCADE')]
    private ?Usuarios $usuario = null;

    #[ORM\Column(type: 'string', columnDefinition: "ENUM('like', 'dislike')")]
    private ?string $tipo = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getComentario(): ?Comentarios
    {
        return $this->comentario;
    }

    public function setComentario(?Comentarios $comentario): self
    {
        $this->comentario = $comentario;
        return $this;
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

    public function getTipo(): ?string
    {
        return $this->tipo;
    }

    public function setTipo(string $tipo): self
    {
        $this->tipo = $tipo;
        return $this;
    }
}