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
                $this->addSql('CREATE TABLE actores (id_actor INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(45) NOT NULL, fecha_nacimiento DATE NOT NULL, nacionalidad VARCHAR(45) NOT NULL, foto VARCHAR(255) NOT NULL,  PRIMARY KEY(id_actor)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
                $this->addSql('CREATE TABLE actores_peliculas (actores_id INT NOT NULL, peliculas_id INT NOT NULL, INDEX IDX_43DBEB6B1A175E98 (actores_id), INDEX IDX_43DBEB6B9EDD74B8 (peliculas_id), PRIMARY KEY(actores_id, peliculas_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
                $this->addSql('CREATE TABLE categorias (id_categoria INT AUTO_INCREMENT NOT NULL, nombre_categoria VARCHAR(45) NOT NULL, PRIMARY KEY(id_categoria)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
                $this->addSql('CREATE TABLE paises (id_pais INT AUTO_INCREMENT NOT NULL, nombre_pais VARCHAR(45) NOT NULL, PRIMARY KEY(id_pais)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
                $this->addSql('CREATE TABLE peliculas (id_pelicula INT AUTO_INCREMENT NOT NULL, paises_id INT DEFAULT NULL, titulo VARCHAR(45) NOT NULL, descripcion VARCHAR(45) NOT NULL, año VARCHAR(4) NOT NULL, duracion INT NOT NULL, portada VARCHAR(255) NOT NULL, trailer VARCHAR(255) NOT NULL, INDEX IDX_9B1814B0282BEA6A (paises_id), PRIMARY KEY(id_pelicula)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
                $this->addSql('CREATE TABLE peliculas_categorias (peliculas_id INT NOT NULL, categorias_id INT NOT NULL, INDEX IDX_29E864CB9EDD74B8 (peliculas_id), INDEX IDX_29E864CB5792B277 (categorias_id), PRIMARY KEY(peliculas_id, categorias_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
                $this->addSql('CREATE TABLE usuarios (id_usuario INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(45) NOT NULL, email VARCHAR(45) NOT NULL, contraseña VARCHAR(60) NOT NULL, rol VARCHAR(45) NOT NULL, fecha_registro DATETIME NOT NULL, foto_perfil LONGBLOB DEFAULT NULL , PRIMARY KEY(id_usuario)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
                $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
                $this->addSql('ALTER TABLE actores_peliculas ADD CONSTRAINT FK_43DBEB6B1A175E98 FOREIGN KEY (actores_id) REFERENCES actores (id_actor) ON DELETE CASCADE');
                $this->addSql('ALTER TABLE actores_peliculas ADD CONSTRAINT FK_43DBEB6B9EDD74B8 FOREIGN KEY (peliculas_id) REFERENCES peliculas (id_pelicula) ON DELETE CASCADE');
                $this->addSql('ALTER TABLE peliculas ADD CONSTRAINT FK_9B1814B0282BEA6A FOREIGN KEY (paises_id) REFERENCES paises (id_pais)');
                $this->addSql('ALTER TABLE peliculas_categorias ADD CONSTRAINT FK_29E864CB9EDD74B8 FOREIGN KEY (peliculas_id) REFERENCES peliculas (id_pelicula) ON DELETE CASCADE');
                $this->addSql('ALTER TABLE peliculas_categorias ADD CONSTRAINT FK_29E864CB5792B277 FOREIGN KEY (categorias_id) REFERENCES categorias (id_categoria) ON DELETE CASCADE');
                $this->addSql('CREATE TABLE comentarios (id INT AUTO_INCREMENT NOT NULL, usuario_id INT NOT NULL, pelicula_id INT NOT NULL, mensaje LONGTEXT NOT NULL, fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, INDEX fkUsuario_idx (usuario_id), INDEX fkPelicula_idx (pelicula_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
                $this->addSql('CREATE TABLE respuestas (id INT AUTO_INCREMENT NOT NULL, usuario_id INT NOT NULL, comentario_id INT NOT NULL, mensaje LONGTEXT NOT NULL, fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, INDEX fkUsuarioRespuesta_idx (usuario_id), INDEX fkComentario_idx (comentario_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
                $this->addSql('ALTER TABLE comentarios ADD CONSTRAINT fkUsuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id_usuario) ON DELETE NO ACTION');
                $this->addSql('ALTER TABLE comentarios ADD CONSTRAINT fkPelicula FOREIGN KEY (pelicula_id) REFERENCES peliculas (id_pelicula) ON DELETE NO ACTION');
                $this->addSql('ALTER TABLE respuestas ADD CONSTRAINT fkUsuarioRespuesta FOREIGN KEY (usuario_id) REFERENCES usuarios (id_usuario) ON DELETE NO ACTION');
                $this->addSql('ALTER TABLE respuestas ADD CONSTRAINT fkComentario FOREIGN KEY (comentario_id) REFERENCES comentarios (id) ON DELETE CASCADE');
                $this->addSql('CREATE TABLE comentario_reacciones (id INT AUTO_INCREMENT NOT NULL, comentario_id INT NOT NULL, usuario_id INT NOT NULL, tipo ENUM("like", "dislike") NOT NULL, UNIQUE INDEX UNIQ_comentario_usuario (comentario_id, usuario_id), INDEX IDX_comentario (comentario_id), INDEX IDX_usuario (usuario_id), PRIMARY KEY(id) ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
                $this->addSql('ALTER TABLE comentario_reacciones ADD CONSTRAINT FK_comentario FOREIGN KEY (comentario_id) REFERENCES comentarios (id) ON DELETE CASCADE');
                $this->addSql('ALTER TABLE comentario_reacciones ADD CONSTRAINT FK_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id_usuario) ON DELETE CASCADE');
                $this->addSql('CREATE TABLE historiales (id INT AUTO_INCREMENT NOT NULL, usuario_id INT NOT NULL, pelicula_id INT NOT NULL, fecha_vista DATETIME NOT NULL, INDEX IDX_HISTORIALES_USUARIO (usuario_id), INDEX IDX_HISTORIALES_PELICULA (pelicula_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
                $this->addSql('ALTER TABLE historiales ADD CONSTRAINT FK_HISTORIALES_USUARIO FOREIGN KEY (usuario_id) REFERENCES usuarios (id_usuario) ON DELETE CASCADE');
                $this->addSql('ALTER TABLE historiales ADD CONSTRAINT FK_HISTORIALES_PELICULA FOREIGN KEY (pelicula_id) REFERENCES peliculas (id_pelicula) ON DELETE CASCADE');
                $this->addSql('CREATE TABLE respuesta_reacciones (id INT AUTO_INCREMENT NOT NULL, respuesta_id INT NOT NULL, usuario_id INT NOT NULL, tipo ENUM("like", "dislike") NOT NULL, UNIQUE INDEX UNIQ_respuesta_usuario (respuesta_id, usuario_id), INDEX IDX_respuesta (respuesta_id), INDEX IDX_usuario_respuesta (usuario_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
                $this->addSql('ALTER TABLE respuesta_reacciones ADD CONSTRAINT FK_respuesta FOREIGN KEY (respuesta_id) REFERENCES respuestas (id) ON DELETE CASCADE');
                $this->addSql('ALTER TABLE respuesta_reacciones ADD CONSTRAINT FK_usuario_respuesta FOREIGN KEY (usuario_id) REFERENCES usuarios (id_usuario) ON DELETE CASCADE');
                $this->addSql('CREATE TABLE valoraciones (id INT AUTO_INCREMENT NOT NULL, usuario_id INT NOT NULL, pelicula_id INT NOT NULL, valor INT NOT NULL, UNIQUE INDEX UNIQ_valoracion_usuario_pelicula (usuario_id, pelicula_id), INDEX IDX_valoracion_usuario (usuario_id), INDEX IDX_valoracion_pelicula (pelicula_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
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
                $this->addSql('DROP TABLE respuestas');
                $this->addSql('ALTER TABLE comentario_reacciones DROP FOREIGN KEY FK_comentario');
                $this->addSql('ALTER TABLE comentario_reacciones DROP FOREIGN KEY FK_usuario');
                $this->addSql('DROP TABLE comentario_reacciones');
                $this->addSql('DROP TABLE comentarios');
                $this->addSql('ALTER TABLE historiales DROP FOREIGN KEY FK_HISTORIALES_USUARIO');
                $this->addSql('ALTER TABLE historiales DROP FOREIGN KEY FK_HISTORIALES_PELICULA');
                $this->addSql('DROP TABLE historiales');
                $this->addSql('ALTER TABLE respuesta_reacciones DROP FOREIGN KEY FK_respuesta');
                $this->addSql('ALTER TABLE respuesta_reacciones DROP FOREIGN KEY FK_usuario_respuesta');
                $this->addSql('DROP TABLE respuesta_reacciones');
                $this->addSql('ALTER TABLE valoraciones ADD CONSTRAINT FK_valoracion_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id_usuario) ON DELETE CASCADE');
                $this->addSql('ALTER TABLE valoraciones ADD CONSTRAINT FK_valoracion_pelicula FOREIGN KEY (pelicula_id) REFERENCES peliculas (id_pelicula) ON DELETE CASCADE');
        }
}
