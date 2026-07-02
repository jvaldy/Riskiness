<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260702110000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create Cycle Care period and event tables.';
    }

    public function up(Schema $schema): void
    {
        if ($this->connection->getDatabasePlatform() instanceof MySQLPlatform) {
            $this->addSql('CREATE TABLE risk_cycle_care_period (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, start_date DATE NOT NULL, end_date DATE NOT NULL, note LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_RISK_CYCLE_CARE_PERIOD_USER_START (user_id, start_date), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
            $this->addSql('CREATE TABLE risk_cycle_care_event (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, event_date DATE NOT NULL, type VARCHAR(40) NOT NULL, note LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, INDEX IDX_RISK_CYCLE_CARE_EVENT_USER_DATE (user_id, event_date), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
            $this->addSql('ALTER TABLE risk_cycle_care_period ADD CONSTRAINT FK_RISK_CYCLE_CARE_PERIOD_USER FOREIGN KEY (user_id) REFERENCES risk_users (id) ON DELETE CASCADE');
            $this->addSql('ALTER TABLE risk_cycle_care_event ADD CONSTRAINT FK_RISK_CYCLE_CARE_EVENT_USER FOREIGN KEY (user_id) REFERENCES risk_users (id) ON DELETE CASCADE');

            return;
        }

        $this->addSql('CREATE TABLE risk_cycle_care_period (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, start_date DATE NOT NULL, end_date DATE NOT NULL, note CLOB DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, CONSTRAINT FK_RISK_CYCLE_CARE_PERIOD_USER FOREIGN KEY (user_id) REFERENCES risk_users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_RISK_CYCLE_CARE_PERIOD_USER_START ON risk_cycle_care_period (user_id, start_date)');
        $this->addSql('CREATE TABLE risk_cycle_care_event (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, event_date DATE NOT NULL, type VARCHAR(40) NOT NULL, note CLOB DEFAULT NULL, created_at DATETIME NOT NULL, CONSTRAINT FK_RISK_CYCLE_CARE_EVENT_USER FOREIGN KEY (user_id) REFERENCES risk_users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_RISK_CYCLE_CARE_EVENT_USER_DATE ON risk_cycle_care_event (user_id, event_date)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE risk_cycle_care_event');
        $this->addSql('DROP TABLE risk_cycle_care_period');
    }
}
