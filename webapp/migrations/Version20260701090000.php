<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260701090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create Recipe Atelier items table.';
    }

    public function up(Schema $schema): void
    {
        if ($this->connection->getDatabasePlatform() instanceof MySQLPlatform) {
            $this->addSql('CREATE TABLE risk_atelier_item (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, title VARCHAR(180) NOT NULL, type VARCHAR(32) NOT NULL, category VARCHAR(80) NOT NULL, tags VARCHAR(255) DEFAULT NULL, estimated_duration INT DEFAULT NULL, target_quantity VARCHAR(80) DEFAULT NULL, difficulty VARCHAR(20) NOT NULL, status VARCHAR(20) NOT NULL, notes LONGTEXT DEFAULT NULL, is_favorite TINYINT(1) NOT NULL, resources JSON NOT NULL, steps JSON NOT NULL, variants JSON NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_RISK_ATELIER_ITEM_USER_UPDATED (user_id, updated_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
            $this->addSql('ALTER TABLE risk_atelier_item ADD CONSTRAINT FK_RISK_ATELIER_ITEM_USER FOREIGN KEY (user_id) REFERENCES risk_users (id) ON DELETE CASCADE');

            return;
        }

        $this->addSql('CREATE TABLE risk_atelier_item (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, title VARCHAR(180) NOT NULL, type VARCHAR(32) NOT NULL, category VARCHAR(80) NOT NULL, tags VARCHAR(255) DEFAULT NULL, estimated_duration INTEGER DEFAULT NULL, target_quantity VARCHAR(80) DEFAULT NULL, difficulty VARCHAR(20) NOT NULL, status VARCHAR(20) NOT NULL, notes CLOB DEFAULT NULL, is_favorite BOOLEAN NOT NULL, resources CLOB NOT NULL, steps CLOB NOT NULL, variants CLOB NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, CONSTRAINT FK_RISK_ATELIER_ITEM_USER FOREIGN KEY (user_id) REFERENCES risk_users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_RISK_ATELIER_ITEM_USER_UPDATED ON risk_atelier_item (user_id, updated_at)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE risk_atelier_item');
    }
}
