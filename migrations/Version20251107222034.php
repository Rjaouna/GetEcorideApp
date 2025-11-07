<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251107222034 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE driver_review (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, trip_id INT DEFAULT NULL, rater_id INT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', rating VARCHAR(60) DEFAULT NULL, comment LONGTEXT DEFAULT NULL, INDEX IDX_3C1C5F7EB03A8386 (created_by_id), INDEX IDX_3C1C5F7E896DBBDE (updated_by_id), INDEX IDX_3C1C5F7EA5BC2E0E (trip_id), INDEX IDX_3C1C5F7E3FC1CD0A (rater_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE driver_review ADD CONSTRAINT FK_3C1C5F7EB03A8386 FOREIGN KEY (created_by_id) REFERENCES `user` (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE driver_review ADD CONSTRAINT FK_3C1C5F7E896DBBDE FOREIGN KEY (updated_by_id) REFERENCES `user` (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE driver_review ADD CONSTRAINT FK_3C1C5F7EA5BC2E0E FOREIGN KEY (trip_id) REFERENCES carpooling (id)');
        $this->addSql('ALTER TABLE driver_review ADD CONSTRAINT FK_3C1C5F7E3FC1CD0A FOREIGN KEY (rater_id) REFERENCES `user` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE driver_review DROP FOREIGN KEY FK_3C1C5F7EB03A8386');
        $this->addSql('ALTER TABLE driver_review DROP FOREIGN KEY FK_3C1C5F7E896DBBDE');
        $this->addSql('ALTER TABLE driver_review DROP FOREIGN KEY FK_3C1C5F7EA5BC2E0E');
        $this->addSql('ALTER TABLE driver_review DROP FOREIGN KEY FK_3C1C5F7E3FC1CD0A');
        $this->addSql('DROP TABLE driver_review');
    }
}
