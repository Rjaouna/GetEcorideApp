<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251101170141 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE carpooling_user (carpooling_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_257FA72FAFB2200A (carpooling_id), INDEX IDX_257FA72FA76ED395 (user_id), PRIMARY KEY(carpooling_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE carpooling_user ADD CONSTRAINT FK_257FA72FAFB2200A FOREIGN KEY (carpooling_id) REFERENCES carpooling (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE carpooling_user ADD CONSTRAINT FK_257FA72FA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE carpooling_user DROP FOREIGN KEY FK_257FA72FAFB2200A');
        $this->addSql('ALTER TABLE carpooling_user DROP FOREIGN KEY FK_257FA72FA76ED395');
        $this->addSql('DROP TABLE carpooling_user');
    }
}
