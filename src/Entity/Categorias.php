<?php

namespace App\Entity;

use App\Repository\CategoriasRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoriasRepository::class)]
class Categorias
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id_categoria = null;

    #[ORM\Column(length: 45)]
    private ?string $nombre_categoria = null;

    public function getIdCategoria(): ?int
    {
        return $this->id_categoria;
    }

    public function getNombreCategoria(): ?string
    {
        return $this->nombre_categoria;
    }

    public function setNombreCategoria(string $nombre_categoria): static
    {
        $this->nombre_categoria = $nombre_categoria;

        return $this;
    }


}
