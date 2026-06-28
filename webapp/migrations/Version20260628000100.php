<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260628000100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create password vault tables for Riskiness.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE risk_password_vault (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, master_password_hash VARCHAR(255) NOT NULL, master_key_salt VARCHAR(64) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, CONSTRAINT FK_RISK_PASSWORD_VAULT_USER FOREIGN KEY (user_id) REFERENCES risk_users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_RISK_PASSWORD_VAULT_USER ON risk_password_vault (user_id)');
        $this->addSql('CREATE TABLE risk_password_vault_entry (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, vault_id INTEGER NOT NULL, entry_fingerprint VARCHAR(64) NOT NULL, title_encrypted CLOB NOT NULL, username_encrypted CLOB NOT NULL, password_encrypted CLOB NOT NULL, url_encrypted CLOB NOT NULL, notes_encrypted CLOB NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, CONSTRAINT FK_RISK_PASSWORD_VAULT_ENTRY_VAULT FOREIGN KEY (vault_id) REFERENCES risk_password_vault (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_RISK_PASSWORD_VAULT_ENTRY ON risk_password_vault_entry (vault_id, entry_fingerprint)');
        $this->addSql('CREATE INDEX IDX_RISK_PASSWORD_VAULT_ENTRY_VAULT ON risk_password_vault_entry (vault_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE risk_password_vault_entry');
        $this->addSql('DROP TABLE risk_password_vault');
    }
}
