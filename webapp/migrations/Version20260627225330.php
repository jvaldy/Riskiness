<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260627225330 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE risk_reset_password_request (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, requested_at DATETIME NOT NULL, expires_at DATETIME NOT NULL, selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(255) NOT NULL, user_id INTEGER NOT NULL, CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES risk_users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7CE748A9692E25D ON risk_reset_password_request (selector)');
        $this->addSql('CREATE INDEX IDX_7CE748AA76ED395 ON risk_reset_password_request (user_id)');
        $this->addSql('CREATE TABLE risk_users (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(180) NOT NULL, display_name VARCHAR(120) DEFAULT NULL, roles CLOB NOT NULL, password VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_RISK_USERS_EMAIL ON risk_users (email)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE risk_reset_password_request');
        $this->addSql('DROP TABLE risk_users');
    }
}
