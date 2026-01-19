<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260211122849 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE feeds (id SMALLINT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, last_fetched_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_5A29F52FF47645AE (url), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE pages (id SMALLINT AUTO_INCREMENT NOT NULL, url VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, status ENUM(\'PENDING\', \'DISCARDED\', \'TO_READ\', \'TO_SUMMARIZE\', \'DONE\') NOT NULL, processed_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, feed_id SMALLINT DEFAULT NULL, UNIQUE INDEX UNIQ_2074E575F47645AE (url), UNIQUE INDEX UNIQ_2074E5752B36786B (title), INDEX IDX_2074E57551A5BC03 (feed_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, api_token_hash VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE pages ADD CONSTRAINT FK_2074E57551A5BC03 FOREIGN KEY (feed_id) REFERENCES feeds (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pages DROP FOREIGN KEY FK_2074E57551A5BC03');
        $this->addSql('DROP TABLE feeds');
        $this->addSql('DROP TABLE pages');
        $this->addSql('DROP TABLE users');
    }
}
