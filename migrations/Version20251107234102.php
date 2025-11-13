<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251107234102 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE booking (id INT AUTO_INCREMENT NOT NULL, trip_id INT DEFAULT NULL, passager_id INT DEFAULT NULL, status VARCHAR(50) DEFAULT NULL, cancel_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_E00CEDDEA5BC2E0E (trip_id), INDEX IDX_E00CEDDE71A51189 (passager_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE booking ADD CONSTRAINT FK_E00CEDDEA5BC2E0E FOREIGN KEY (trip_id) REFERENCES carpooling (id)');
        $this->addSql('ALTER TABLE booking ADD CONSTRAINT FK_E00CEDDE71A51189 FOREIGN KEY (passager_id) REFERENCES `user` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE booking DROP FOREIGN KEY FK_E00CEDDEA5BC2E0E');
        $this->addSql('ALTER TABLE booking DROP FOREIGN KEY FK_E00CEDDE71A51189');
        $this->addSql('DROP TABLE booking');
    }
}
