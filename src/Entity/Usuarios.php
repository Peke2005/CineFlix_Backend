<?php

namespace App\Entity;

use App\Repository\UsuariosRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UsuariosRepository::class)]
class Usuarios implements PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id_usuario = null;

    #[ORM\Column(length: 45)]
    private ?string $nombre = null;

    #[ORM\Column(length: 45)]
    private ?string $email = null;

    #[ORM\Column(length: 60)]
    private ?string $contraseña = null;

    #[ORM\Column(length: 45)]
    private ?string $rol = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $fecha_registro = null;

    #[ORM\Column(type: Types::BLOB, nullable: true)]
    private $foto_perfil = null;

    private ?string $fotoPerfilContenido = null;

    #[ORM\OneToMany(mappedBy: 'usuario', targetEntity: Historiales::class, cascade: ['persist', 'remove'])]
    private Collection $historiales;

    public function __construct()
    {
        $this->historiales = new ArrayCollection();
    }

    public function getHistoriales(): Collection
    {
        return $this->historiales;
    }

    public function addHistorial(Historiales $historial): static
    {
        if (!$this->historiales->contains($historial)) {
            $this->historiales[] = $historial;
            $historial->setUsuario($this);
        }

        return $this;
    }


    public function getIdUsuario(): ?int
    {
        return $this->id_usuario;
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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getContraseña(): ?string
    {
        return $this->contraseña;
    }

    public function setContraseña(string $contraseña): static
    {
        $this->contraseña = $contraseña;

        return $this;
    }

    public function getRol(): ?string
    {
        return $this->rol;
    }

    public function setRol(string $rol): static
    {
        $this->rol = $rol;

        return $this;
    }

    public function getFechaRegistro(): ?\DateTimeInterface
    {
        return $this->fecha_registro;
    }

    public function setFechaRegistro(\DateTimeInterface $fecha_registro): static
    {
        $this->fecha_registro = $fecha_registro;

        return $this;
    }

    public function getFotoPerfil()
    {
        return $this->foto_perfil;
    }

    public function setFotoPerfil($foto_perfil): static
    {
        $this->foto_perfil = $foto_perfil;
        // Reiniciar el contenido cacheado al cambiar la foto
        $this->fotoPerfilContenido = null;
        return $this;
    }

    public function getFotoPerfilBase64(): ?string
    {
        // Retorna el base64 si ya está almacenado en la propiedad
        if ($this->fotoPerfilContenido !== null) {
            return base64_encode($this->fotoPerfilContenido);
        }

        $foto = $this->getFotoPerfil();

        if ($foto === null) {
            error_log('foto_perfil es null');
            return null;
        }

        if (is_resource($foto)) {
            rewind($foto);
            $contenido = stream_get_contents($foto);
            fclose($foto);
        } elseif (is_string($foto)) {
            $contenido = $foto;
        } else {
            error_log('Tipo inesperado en foto_perfil: ' . gettype($foto));
            return null;
        }

        if ($contenido === false) {
            error_log('Error al leer contenido de foto_perfil');
            return null;
        }

        // Guardamos el contenido para futuras llamadas
        $this->fotoPerfilContenido = $contenido;

        return base64_encode($contenido);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getIdUsuario(),
            'nombre' => $this->getNombre(),
            'email' => $this->getEmail(),
            'rol' => $this->getRol(),
            'fecha_registro' => $this->getFechaRegistro()?->format('Y-m-d H:i:s'),
            'foto_perfil' => $this->getFotoPerfilBase64(),
        ];
    }

    public function getPassword(): ?string
    {
        // Implementa según tu necesidad de seguridad
        return $this->contraseña;
    }
}
