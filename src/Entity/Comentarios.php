<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Usuarios;
use App\Entity\Peliculas;
use App\Repository\ComentariosRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\Respuestas;


#[ORM\Entity(repositoryClass: ComentariosRepository::class)]
#[ORM\Table(name: 'comentarios')]
class Comentarios
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Usuarios::class)]
    #[ORM\JoinColumn(name: 'usuario_id', referencedColumnName: 'id_usuario', nullable: false)]
    private ?Usuarios $usuario = null;

    #[ORM\ManyToOne(targetEntity: Peliculas::class)]
    #[ORM\JoinColumn(name: 'pelicula_id', referencedColumnName: 'id_pelicula', nullable: false)]
    private ?Peliculas $pelicula = null;

    #[ORM\Column(type: 'text')]
    private ?string $mensaje = null;

    #[ORM\Column(type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private ?\DateTimeInterface $fechaCreacion = null;

    #[ORM\OneToMany(mappedBy: 'comentario', targetEntity: Respuestas::class, cascade: ['persist', 'remove'])]
    private Collection $relacionRespuestas;

    public function __construct()
    {
        $this->fechaCreacion = new \DateTime();
        $this->relacionRespuestas = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsuario(): ?Usuarios
    {
        return $this->usuario;
    }

    public function getRelacionRespuestas(): Collection
    {
        return $this->relacionRespuestas;
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

    public function toArray(bool $includeRespuestas = true): array
    {
        $respuestas = [];

        if ($includeRespuestas) {
            foreach ($this->getRelacionRespuestas() as $respuesta) {
                $respuestas[] = $respuesta->toArray();
            }
        }

        return [
            'id' => $this->getId(),
            'usuario' => $this->getUsuario()?->toArray(),
            'mensaje' => $this->getMensaje(),
            'fecha_creacion' => $this->getFechaCreacion()?->format('Y-m-d H:i:s'),
            'respuestas' => $respuestas,
        ];
    }
}
