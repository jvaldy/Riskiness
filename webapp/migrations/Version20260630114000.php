<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260630114000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create movie tracker entries table for Riskiness.';
    }

    public function up(Schema $schema): void
    {
        if ($this->connection->getDatabasePlatform() instanceof MySQLPlatform) {
            $this->addSql('CREATE TABLE risk_movie_tracker_entry (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, type VARCHAR(20) NOT NULL, title VARCHAR(180) NOT NULL, state VARCHAR(20) NOT NULL, waiting_date DATE DEFAULT NULL, anime_format VARCHAR(20) DEFAULT NULL, season INT DEFAULT NULL, episode INT DEFAULT NULL, time_code VARCHAR(16) DEFAULT NULL, book_point VARCHAR(120) DEFAULT NULL, notes LONGTEXT DEFAULT NULL, tags VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_RISK_MOVIE_TRACKER_ENTRY_USER (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
            $this->addSql('ALTER TABLE risk_movie_tracker_entry ADD CONSTRAINT FK_RISK_MOVIE_TRACKER_ENTRY_USER FOREIGN KEY (user_id) REFERENCES risk_users (id) ON DELETE CASCADE');

            return;
        }

        $this->addSql('CREATE TABLE risk_movie_tracker_entry (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, type VARCHAR(20) NOT NULL, title VARCHAR(180) NOT NULL, state VARCHAR(20) NOT NULL, waiting_date DATE DEFAULT NULL, anime_format VARCHAR(20) DEFAULT NULL, season INTEGER DEFAULT NULL, episode INTEGER DEFAULT NULL, time_code VARCHAR(16) DEFAULT NULL, book_point VARCHAR(120) DEFAULT NULL, notes CLOB DEFAULT NULL, tags VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, CONSTRAINT FK_RISK_MOVIE_TRACKER_ENTRY_USER FOREIGN KEY (user_id) REFERENCES risk_users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_RISK_MOVIE_TRACKER_ENTRY_USER ON risk_movie_tracker_entry (user_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE risk_movie_tracker_entry');
    }
}
