<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260218123856 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE intervention_history (id INT AUTO_INCREMENT NOT NULL, intervention_id INT NOT NULL, modified_by_id INT DEFAULT NULL, filed_name VARCHAR(100) NOT NULL, old_value LONGTEXT DEFAULT NULL, new_value LONGTEXT DEFAULT NULL, action_type VARCHAR(30) NOT NULL, modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', context JSON DEFAULT NULL, INDEX IDX_6C54042E8EAE3863 (intervention_id), INDEX IDX_6C54042E99049ECE (modified_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE intervention_history ADD CONSTRAINT FK_6C54042E8EAE3863 FOREIGN KEY (intervention_id) REFERENCES intervention (id)');
        $this->addSql('ALTER TABLE intervention_history ADD CONSTRAINT FK_6C54042E99049ECE FOREIGN KEY (modified_by_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE intervention_history DROP FOREIGN KEY FK_6C54042E8EAE3863');
        $this->addSql('ALTER TABLE intervention_history DROP FOREIGN KEY FK_6C54042E99049ECE');
        $this->addSql('DROP TABLE intervention_history');
    }
}
