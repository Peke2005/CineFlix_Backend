<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250311154722 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE actores (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(45) NOT NULL, fecha_nacimiento DATE NOT NULL, nacionalidad VARCHAR(45) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE actores_peliculas (actores_id INT NOT NULL, peliculas_id INT NOT NULL, INDEX IDX_43DBEB6B1A175E98 (actores_id), INDEX IDX_43DBEB6B9EDD74B8 (peliculas_id), PRIMARY KEY(actores_id, peliculas_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE categorias (id INT AUTO_INCREMENT NOT NULL, nombre_categoria VARCHAR(45) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE paises (id INT AUTO_INCREMENT NOT NULL, nombre_pais VARCHAR(45) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE peliculas (id INT AUTO_INCREMENT NOT NULL, paises_id INT DEFAULT NULL, titulo VARCHAR(45) NOT NULL, descripcion VARCHAR(45) NOT NULL, año DATE NOT NULL, duracion INT NOT NULL, portada VARCHAR(255) NOT NULL, trailer VARCHAR(255) NOT NULL, INDEX IDX_9B1814B0282BEA6A (paises_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE peliculas_categorias (peliculas_id INT NOT NULL, categorias_id INT NOT NULL, INDEX IDX_29E864CB9EDD74B8 (peliculas_id), INDEX IDX_29E864CB5792B277 (categorias_id), PRIMARY KEY(peliculas_id, categorias_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE usuarios (id_usuario INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(45) NOT NULL, email VARCHAR(45) NOT NULL, contraseña VARCHAR(45) NOT NULL, rol VARCHAR(45) NOT NULL, fecha_registro DATETIME NOT NULL, PRIMARY KEY(id_usuario)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE actores_peliculas ADD CONSTRAINT FK_43DBEB6B1A175E98 FOREIGN KEY (actores_id) REFERENCES actores (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE actores_peliculas ADD CONSTRAINT FK_43DBEB6B9EDD74B8 FOREIGN KEY (peliculas_id) REFERENCES peliculas (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE peliculas ADD CONSTRAINT FK_9B1814B0282BEA6A FOREIGN KEY (paises_id) REFERENCES paises (id)');
        $this->addSql('ALTER TABLE peliculas_categorias ADD CONSTRAINT FK_29E864CB9EDD74B8 FOREIGN KEY (peliculas_id) REFERENCES peliculas (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE peliculas_categorias ADD CONSTRAINT FK_29E864CB5792B277 FOREIGN KEY (categorias_id) REFERENCES categorias (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE actores_peliculas DROP FOREIGN KEY FK_43DBEB6B1A175E98');
        $this->addSql('ALTER TABLE actores_peliculas DROP FOREIGN KEY FK_43DBEB6B9EDD74B8');
        $this->addSql('ALTER TABLE peliculas DROP FOREIGN KEY FK_9B1814B0282BEA6A');
        $this->addSql('ALTER TABLE peliculas_categorias DROP FOREIGN KEY FK_29E864CB9EDD74B8');
        $this->addSql('ALTER TABLE peliculas_categorias DROP FOREIGN KEY FK_29E864CB5792B277');
        $this->addSql('DROP TABLE actores');
        $this->addSql('DROP TABLE actores_peliculas');
        $this->addSql('DROP TABLE categorias');
        $this->addSql('DROP TABLE paises');
        $this->addSql('DROP TABLE peliculas');
        $this->addSql('DROP TABLE peliculas_categorias');
        $this->addSql('DROP TABLE usuarios');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
