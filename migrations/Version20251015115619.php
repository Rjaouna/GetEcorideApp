<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251015115619 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE driver_preferences (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, user_id INT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', smoking_allowed TINYINT(1) DEFAULT NULL, pets_allowed TINYINT(1) DEFAULT NULL, INDEX IDX_E5E3F947B03A8386 (created_by_id), INDEX IDX_E5E3F947896DBBDE (updated_by_id), UNIQUE INDEX UNIQ_E5E3F947A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE driver_preferences ADD CONSTRAINT FK_E5E3F947B03A8386 FOREIGN KEY (created_by_id) REFERENCES `user` (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE driver_preferences ADD CONSTRAINT FK_E5E3F947896DBBDE FOREIGN KEY (updated_by_id) REFERENCES `user` (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE driver_preferences ADD CONSTRAINT FK_E5E3F947A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE driver_preferences DROP FOREIGN KEY FK_E5E3F947B03A8386');
        $this->addSql('ALTER TABLE driver_preferences DROP FOREIGN KEY FK_E5E3F947896DBBDE');
        $this->addSql('ALTER TABLE driver_preferences DROP FOREIGN KEY FK_E5E3F947A76ED395');
        $this->addSql('DROP TABLE driver_preferences');
    }
}
