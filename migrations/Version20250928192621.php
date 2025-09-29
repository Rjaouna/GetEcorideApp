<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250928192621 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add nullable uuid columns (binary) + unique indexes';
    }

    public function up(Schema $schema): void
    {
        // User
        // up()
        $this->addSql("ALTER TABLE `user` MODIFY `uuid` CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'");
        $this->addSql("ALTER TABLE `vehicle` MODIFY `uuid` CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'");

        // down()
        $this->addSql("ALTER TABLE `user` MODIFY `uuid` CHAR(36) DEFAULT NULL COMMENT '(DC2Type:uuid)'");
        $this->addSql("ALTER TABLE `vehicle` MODIFY `uuid` CHAR(36) DEFAULT NULL COMMENT '(DC2Type:uuid)'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DROP INDEX UNIQ_USER_UUID ON `user`");
        $this->addSql("ALTER TABLE `user` DROP uuid");

        $this->addSql("DROP INDEX UNIQ_VEHICLE_UUID ON `vehicle`");
        $this->addSql("ALTER TABLE `vehicle` DROP uuid");
    }
}
