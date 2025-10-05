<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251005083233 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE intervention (id INT AUTO_INCREMENT NOT NULL, client_nom VARCHAR(255) DEFAULT NULL, reference VARCHAR(255) DEFAULT NULL, adresse VARCHAR(255) DEFAULT NULL, demande VARCHAR(255) DEFAULT NULL, detail VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE document ADD intervention_id INT DEFAULT NULL, CHANGE type type VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A768EAE3863 FOREIGN KEY (intervention_id) REFERENCES intervention (id)');
        $this->addSql('CREATE INDEX IDX_D8698A768EAE3863 ON document (intervention_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A768EAE3863');
        $this->addSql('DROP TABLE intervention');
        $this->addSql('DROP INDEX IDX_D8698A768EAE3863 ON document');
        $this->addSql('ALTER TABLE document DROP intervention_id, CHANGE type type VARCHAR(100) NOT NULL');
    }
}
