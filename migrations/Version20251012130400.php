<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251012130400 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE grupa (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, nazwa VARCHAR(255) NOT NULL, INDEX IDX_6BB6B6D4727ACA70 (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE jednostka (id INT AUTO_INCREMENT NOT NULL, skrot VARCHAR(64) NOT NULL, nazwa VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE material (id INT AUTO_INCREMENT NOT NULL, grupa_id INT DEFAULT NULL, jednostka_id INT DEFAULT NULL, kod VARCHAR(64) NOT NULL, nazwa VARCHAR(255) NOT NULL, wartosc NUMERIC(20, 2) NOT NULL, INDEX IDX_7CBE75957C5C4730 (grupa_id), INDEX IDX_7CBE7595641E7B2E (jednostka_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE grupa ADD CONSTRAINT FK_6BB6B6D4727ACA70 FOREIGN KEY (parent_id) REFERENCES grupa (id)');
        $this->addSql('ALTER TABLE material ADD CONSTRAINT FK_7CBE75957C5C4730 FOREIGN KEY (grupa_id) REFERENCES grupa (id)');
        $this->addSql('ALTER TABLE material ADD CONSTRAINT FK_7CBE7595641E7B2E FOREIGN KEY (jednostka_id) REFERENCES jednostka (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE grupa DROP FOREIGN KEY FK_6BB6B6D4727ACA70');
        $this->addSql('ALTER TABLE material DROP FOREIGN KEY FK_7CBE75957C5C4730');
        $this->addSql('ALTER TABLE material DROP FOREIGN KEY FK_7CBE7595641E7B2E');
        $this->addSql('DROP TABLE grupa');
        $this->addSql('DROP TABLE jednostka');
        $this->addSql('DROP TABLE material');
    }
}
