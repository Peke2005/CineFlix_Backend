<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\RespuestaReaccionesRepository;

#[ORM\Entity(repositoryClass: RespuestaReaccionesRepository::class)]
#[ORM\Table(name: 'respuesta_reacciones')]
class RespuestaReacciones
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Respuestas::class)]
    #[ORM\JoinColumn(name: 'respuesta_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Respuestas $respuesta = null;

    #[ORM\ManyToOne(targetEntity: Usuarios::class)]
    #[ORM\JoinColumn(name: 'usuario_id', referencedColumnName: 'id_usuario', nullable: false, onDelete: 'CASCADE')]
    private ?Usuarios $usuario = null;

    #[ORM\Column(type: 'string', columnDefinition: "ENUM('like', 'dislike')")]
    private ?string $tipo = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRespuesta(): ?Respuestas
    {
        return $this->respuesta;
    }

    public function setRespuesta(?Respuestas $respuesta): self
    {
        $this->respuesta = $respuesta;
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
