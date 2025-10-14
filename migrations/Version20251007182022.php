<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251007182022 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add user.is_verified (boolean, default 0) if missing';
    }

    public function up(Schema $schema): void
    {
        $sm = $this->connection->createSchemaManager();

        // Si la table existe et ne possède pas encore la colonne, on l’ajoute
        if ($sm->tablesExist(['user'])) {
            $table = $sm->introspectTable('user');

            if (!$table->hasColumn('is_verified')) {
                // NOT NULL + DEFAULT 0 pour être cohérent avec l’entité (false)
                $this->addSql('ALTER TABLE `user` ADD `is_verified` TINYINT(1) NOT NULL DEFAULT 0');
            }
        }
    }

    public function down(Schema $schema): void
    {
        $sm = $this->connection->createSchemaManager();

        if ($sm->tablesExist(['user'])) {
            $table = $sm->introspectTable('user');

            if ($table->hasColumn('is_verified')) {
                $this->addSql('ALTER TABLE `user` DROP COLUMN `is_verified`');
            }
        }
    }
}
