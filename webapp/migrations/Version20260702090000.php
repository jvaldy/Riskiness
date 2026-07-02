<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260702090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create Doc Sentinel documents table.';
    }

    public function up(Schema $schema): void
    {
        if ($this->connection->getDatabasePlatform() instanceof MySQLPlatform) {
            $this->addSql('CREATE TABLE risk_doc_sentinel_document (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, name VARCHAR(180) NOT NULL, category VARCHAR(60) NOT NULL, expiration_date DATE NOT NULL, reminder_date DATE DEFAULT NULL, note LONGTEXT DEFAULT NULL, archived TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_RISK_DOC_SENTINEL_USER_EXPIRATION (user_id, expiration_date), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
            $this->addSql('ALTER TABLE risk_doc_sentinel_document ADD CONSTRAINT FK_RISK_DOC_SENTINEL_USER FOREIGN KEY (user_id) REFERENCES risk_users (id) ON DELETE CASCADE');

            return;
        }

        $this->addSql('CREATE TABLE risk_doc_sentinel_document (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, name VARCHAR(180) NOT NULL, category VARCHAR(60) NOT NULL, expiration_date DATE NOT NULL, reminder_date DATE DEFAULT NULL, note CLOB DEFAULT NULL, archived BOOLEAN NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, CONSTRAINT FK_RISK_DOC_SENTINEL_USER FOREIGN KEY (user_id) REFERENCES risk_users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_RISK_DOC_SENTINEL_USER_EXPIRATION ON risk_doc_sentinel_document (user_id, expiration_date)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE risk_doc_sentinel_document');
    }
}
