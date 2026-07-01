<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260630133500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove Budget Pulse shared expense groups.';
    }

    public function up(Schema $schema): void
    {
        if ($this->connection->getDatabasePlatform() instanceof MySQLPlatform) {
            $this->addSql('DROP TABLE IF EXISTS risk_budget_split_group');

            return;
        }

        $this->addSql('DROP TABLE IF EXISTS risk_budget_split_group');
    }

    public function down(Schema $schema): void
    {
    }
}
