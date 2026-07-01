<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260630123500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create budget pulse transactions table.';
    }

    public function up(Schema $schema): void
    {
        if ($this->connection->getDatabasePlatform() instanceof MySQLPlatform) {
            $this->addSql('CREATE TABLE risk_budget_transaction (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, type VARCHAR(12) NOT NULL, amount NUMERIC(12, 2) NOT NULL, category VARCHAR(80) NOT NULL, label VARCHAR(180) NOT NULL, note LONGTEXT DEFAULT NULL, occurred_at DATE NOT NULL, is_recurring TINYINT(1) NOT NULL, recurrence_frequency VARCHAR(20) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_RISK_BUDGET_TRANSACTION_USER_DATE (user_id, occurred_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
            $this->addSql('ALTER TABLE risk_budget_transaction ADD CONSTRAINT FK_RISK_BUDGET_TRANSACTION_USER FOREIGN KEY (user_id) REFERENCES risk_users (id) ON DELETE CASCADE');

            return;
        }

        $this->addSql('CREATE TABLE risk_budget_transaction (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, type VARCHAR(12) NOT NULL, amount NUMERIC(12, 2) NOT NULL, category VARCHAR(80) NOT NULL, label VARCHAR(180) NOT NULL, note CLOB DEFAULT NULL, occurred_at DATE NOT NULL, is_recurring BOOLEAN NOT NULL, recurrence_frequency VARCHAR(20) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, CONSTRAINT FK_RISK_BUDGET_TRANSACTION_USER FOREIGN KEY (user_id) REFERENCES risk_users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_RISK_BUDGET_TRANSACTION_USER_DATE ON risk_budget_transaction (user_id, occurred_at)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE risk_budget_transaction');
    }
}
