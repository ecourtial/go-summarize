<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260226154144 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Drop old indexes no longer compatible.
        $this->addSql('ALTER TABLE api_token DROP FOREIGN KEY FK_7BA2F5EBA76ED395');
        $this->addSql('ALTER TABLE pages DROP FOREIGN KEY FK_2074E57551A5BC03');

        // Add new fields.
        $this->addSql('ALTER TABLE api_token CHANGE id id BIGINT AUTO_INCREMENT NOT NULL, CHANGE user_id user_id BIGINT NOT NULL');
        $this->addSql('ALTER TABLE feeds CHANGE id id BIGINT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE pages CHANGE id id BIGINT AUTO_INCREMENT NOT NULL, CHANGE feed_id feed_id BIGINT DEFAULT NULL, CHANGE published_at published_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE users CHANGE id id BIGINT AUTO_INCREMENT NOT NULL');

        // Add new indexes.
        $this->addSql('ALTER TABLE api_token ADD CONSTRAINT FK_7BA2F5EBA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pages ADD CONSTRAINT FK_2074E57551A5BC03 FOREIGN KEY (feed_id) REFERENCES feeds (id)');
    }

    public function down(Schema $schema): void
    {
        // Remove new indexes.
        $this->addSql('ALTER TABLE api_token DROP FOREIGN KEY FK_7BA2F5EBA76ED395');
        $this->addSql('ALTER TABLE pages DROP FOREIGN KEY FK_2074E57551A5BC03');

        // Remove new fields.
        $this->addSql('ALTER TABLE api_token CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE user_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE feeds CHANGE id id SMALLINT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE pages CHANGE id id SMALLINT AUTO_INCREMENT NOT NULL, CHANGE published_at published_at DATETIME DEFAULT \'2026-02-19 00:00:00\' NOT NULL, CHANGE feed_id feed_id SMALLINT DEFAULT NULL');
        $this->addSql('ALTER TABLE users CHANGE id id INT AUTO_INCREMENT NOT NULL');

        // Recreates the old indexes.
        $this->addSql('ALTER TABLE pages ADD CONSTRAINT FK_2074E57551A5BC03 FOREIGN KEY (feed_id) REFERENCES feeds (id)');
        $this->addSql('ALTER TABLE api_token ADD CONSTRAINT FK_7BA2F5EBA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
    }
}
