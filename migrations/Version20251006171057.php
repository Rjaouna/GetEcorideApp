<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251006171057 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE carpooling (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, driver_id INT NOT NULL, vehicle_id INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', deparature_city VARCHAR(50) NOT NULL, arrival_city VARCHAR(50) NOT NULL, deparature_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', arrival_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', seats_total SMALLINT NOT NULL, seats_avaible SMALLINT DEFAULT NULL, price DOUBLE PRECISION NOT NULL, status VARCHAR(50) NOT NULL, eco_tag TINYINT(1) NOT NULL, INDEX IDX_6CC153F1B03A8386 (created_by_id), INDEX IDX_6CC153F1896DBBDE (updated_by_id), INDEX IDX_6CC153F1C3423909 (driver_id), INDEX IDX_6CC153F1545317D1 (vehicle_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE carpooling ADD CONSTRAINT FK_6CC153F1B03A8386 FOREIGN KEY (created_by_id) REFERENCES `user` (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE carpooling ADD CONSTRAINT FK_6CC153F1896DBBDE FOREIGN KEY (updated_by_id) REFERENCES `user` (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE carpooling ADD CONSTRAINT FK_6CC153F1C3423909 FOREIGN KEY (driver_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE carpooling ADD CONSTRAINT FK_6CC153F1545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle (id)');
        $this->addSql('ALTER TABLE carpoling DROP FOREIGN KEY FK_EA4F2F73545317D1');
        $this->addSql('ALTER TABLE carpoling DROP FOREIGN KEY FK_EA4F2F73896DBBDE');
        $this->addSql('ALTER TABLE carpoling DROP FOREIGN KEY FK_EA4F2F73B03A8386');
        $this->addSql('ALTER TABLE carpoling DROP FOREIGN KEY FK_EA4F2F73C3423909');
        $this->addSql('DROP TABLE carpoling');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE carpoling (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, driver_id INT NOT NULL, vehicle_id INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', deparature_city VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, arrival_city VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, deparature_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', arrival_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', seats_total SMALLINT NOT NULL, seats_avaible SMALLINT DEFAULT NULL, price DOUBLE PRECISION NOT NULL, status VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, eco_tag TINYINT(1) NOT NULL, INDEX IDX_EA4F2F73545317D1 (vehicle_id), INDEX IDX_EA4F2F73896DBBDE (updated_by_id), INDEX IDX_EA4F2F73B03A8386 (created_by_id), INDEX IDX_EA4F2F73C3423909 (driver_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE carpoling ADD CONSTRAINT FK_EA4F2F73545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE carpoling ADD CONSTRAINT FK_EA4F2F73896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE SET NULL');
        $this->addSql('ALTER TABLE carpoling ADD CONSTRAINT FK_EA4F2F73B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE SET NULL');
        $this->addSql('ALTER TABLE carpoling ADD CONSTRAINT FK_EA4F2F73C3423909 FOREIGN KEY (driver_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE carpooling DROP FOREIGN KEY FK_6CC153F1B03A8386');
        $this->addSql('ALTER TABLE carpooling DROP FOREIGN KEY FK_6CC153F1896DBBDE');
        $this->addSql('ALTER TABLE carpooling DROP FOREIGN KEY FK_6CC153F1C3423909');
        $this->addSql('ALTER TABLE carpooling DROP FOREIGN KEY FK_6CC153F1545317D1');
        $this->addSql('DROP TABLE carpooling');
    }
}
