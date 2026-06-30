<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260630120500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add source field to movie tracker entries.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE risk_movie_tracker_entry ADD source VARCHAR(160) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        if ($this->connection->getDatabasePlatform() instanceof MySQLPlatform) {
            $this->addSql('ALTER TABLE risk_movie_tracker_entry DROP source');

            return;
        }

        $this->addSql('CREATE TEMPORARY TABLE __temp__risk_movie_tracker_entry AS SELECT id, user_id, type, title, state, waiting_date, anime_format, season, episode, time_code, book_point, notes, tags, created_at, updated_at FROM risk_movie_tracker_entry');
        $this->addSql('DROP TABLE risk_movie_tracker_entry');
        $this->addSql('CREATE TABLE risk_movie_tracker_entry (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, type VARCHAR(20) NOT NULL, title VARCHAR(180) NOT NULL, state VARCHAR(20) NOT NULL, waiting_date DATE DEFAULT NULL, anime_format VARCHAR(20) DEFAULT NULL, season INTEGER DEFAULT NULL, episode INTEGER DEFAULT NULL, time_code VARCHAR(16) DEFAULT NULL, book_point VARCHAR(120) DEFAULT NULL, notes CLOB DEFAULT NULL, tags VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, CONSTRAINT FK_RISK_MOVIE_TRACKER_ENTRY_USER FOREIGN KEY (user_id) REFERENCES risk_users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO risk_movie_tracker_entry (id, user_id, type, title, state, waiting_date, anime_format, season, episode, time_code, book_point, notes, tags, created_at, updated_at) SELECT id, user_id, type, title, state, waiting_date, anime_format, season, episode, time_code, book_point, notes, tags, created_at, updated_at FROM __temp__risk_movie_tracker_entry');
        $this->addSql('DROP TABLE __temp__risk_movie_tracker_entry');
        $this->addSql('CREATE INDEX IDX_RISK_MOVIE_TRACKER_ENTRY_USER ON risk_movie_tracker_entry (user_id)');
    }
}
