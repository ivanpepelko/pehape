<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210217201259 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial migration';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'CREATE TABLE article (id INT AUTO_INCREMENT NOT NULL, owner_id INT NOT NULL, gallery_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(500) DEFAULT NULL, body LONGTEXT NOT NULL, release_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', draft TINYINT(1) NOT NULL, INDEX IDX_23A0E667E3C61F9 (owner_id), INDEX IDX_23A0E664E7AF8F (gallery_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );
        $this->addSql(
            'CREATE TABLE gallery (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(500) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );
        $this->addSql(
            'CREATE TABLE gallery_image (gallery_id INT NOT NULL, image_id INT NOT NULL, INDEX IDX_21A0D47C4E7AF8F (gallery_id), INDEX IDX_21A0D47C3DA5256D (image_id), PRIMARY KEY(gallery_id, image_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );
        $this->addSql(
            'CREATE TABLE image (id INT AUTO_INCREMENT NOT NULL, path LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );
        $this->addSql(
            'CREATE TABLE question (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, subject VARCHAR(500) NOT NULL, body LONGTEXT NOT NULL, INDEX IDX_B6F7494EA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );
        $this->addSql(
            'CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E667E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E664E7AF8F FOREIGN KEY (gallery_id) REFERENCES gallery (id)');
        $this->addSql('ALTER TABLE gallery_image ADD CONSTRAINT FK_21A0D47C4E7AF8F FOREIGN KEY (gallery_id) REFERENCES gallery (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE gallery_image ADD CONSTRAINT FK_21A0D47C3DA5256D FOREIGN KEY (image_id) REFERENCES image (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE question ADD CONSTRAINT FK_B6F7494EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E664E7AF8F');
        $this->addSql('ALTER TABLE gallery_image DROP FOREIGN KEY FK_21A0D47C4E7AF8F');
        $this->addSql('ALTER TABLE gallery_image DROP FOREIGN KEY FK_21A0D47C3DA5256D');
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E667E3C61F9');
        $this->addSql('ALTER TABLE question DROP FOREIGN KEY FK_B6F7494EA76ED395');
        $this->addSql('DROP TABLE article');
        $this->addSql('DROP TABLE gallery');
        $this->addSql('DROP TABLE gallery_image');
        $this->addSql('DROP TABLE image');
        $this->addSql('DROP TABLE question');
        $this->addSql('DROP TABLE user');
    }
}
