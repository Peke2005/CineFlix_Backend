<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\RespuestasRepository;

#[ORM\Entity(repositoryClass: RespuestasRepository::class)]
#[ORM\Table(name: 'respuestas')]
class Respuestas
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Usuarios::class)]
    #[ORM\JoinColumn(name: 'usuario_id', referencedColumnName: 'id_usuario', nullable: false)]
    private ?Usuarios $usuario = null;

    #[ORM\ManyToOne(targetEntity: Comentarios::class)]
    #[ORM\JoinColumn(name: 'comentario_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Comentarios $comentario = null;

    #[ORM\Column(type: 'text')]
    private ?string $mensaje = null;

    #[ORM\Column(type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private ?\DateTimeInterface $fechaCreacion = null;

    public function __construct()
    {
        $this->fechaCreacion = new \DateTime();
    }

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

    public function getComentario(): ?Comentarios
    {
        return $this->comentario;
    }

    public function setComentario(?Comentarios $comentario): self
    {
        $this->comentario = $comentario;
        return $this;
    }

    public function getMensaje(): ?string
    {
        return $this->mensaje;
    }

    public function setMensaje(string $mensaje): self
    {
        $this->mensaje = $mensaje;
        return $this;
    }

    public function getFechaCreacion(): ?\DateTimeInterface
    {
        return $this->fechaCreacion;
    }

    public function setFechaCreacion(\DateTimeInterface $fechaCreacion): self
    {
        $this->fechaCreacion = $fechaCreacion;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'mensaje' => $this->getMensaje(),
            'fecha_creacion' => $this->getFechaCreacion()?->format('Y-m-d H:i:s'),
            'usuario' => $this->getUsuario()?->toArray(),
        ];
    }
}
