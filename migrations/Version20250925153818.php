<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250925153818 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE vehicle ADD created_by_id INT DEFAULT NULL, ADD updated_by_id INT DEFAULT NULL, ADD created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE vehicle ADD CONSTRAINT FK_1B80E486B03A8386 FOREIGN KEY (created_by_id) REFERENCES `user` (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE vehicle ADD CONSTRAINT FK_1B80E486896DBBDE FOREIGN KEY (updated_by_id) REFERENCES `user` (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_1B80E486B03A8386 ON vehicle (created_by_id)');
        $this->addSql('CREATE INDEX IDX_1B80E486896DBBDE ON vehicle (updated_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE vehicle DROP FOREIGN KEY FK_1B80E486B03A8386');
        $this->addSql('ALTER TABLE vehicle DROP FOREIGN KEY FK_1B80E486896DBBDE');
        $this->addSql('DROP INDEX IDX_1B80E486B03A8386 ON vehicle');
        $this->addSql('DROP INDEX IDX_1B80E486896DBBDE ON vehicle');
        $this->addSql('ALTER TABLE vehicle DROP created_by_id, DROP updated_by_id, DROP created_at, DROP updated_at');
    }
}
