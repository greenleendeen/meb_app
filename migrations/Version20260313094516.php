<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260313094516 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE compte_rendu (id INT AUTO_INCREMENT NOT NULL, technicien_id INT NOT NULL, intervention_id INT NOT NULL, description LONGTEXT NOT NULL, date_creation DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_D39E69D213457256 (technicien_id), INDEX IDX_D39E69D28EAE3863 (intervention_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE document (id INT AUTO_INCREMENT NOT NULL, intervention_id INT NOT NULL, compte_rendu_id INT DEFAULT NULL, filename VARCHAR(255) NOT NULL, original_name VARCHAR(255) DEFAULT NULL, uploaded_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', path VARCHAR(255) DEFAULT NULL, extracted_text LONGTEXT DEFAULT NULL, type VARCHAR(255) NOT NULL, deleted_at DATETIME DEFAULT NULL, INDEX IDX_D8698A768EAE3863 (intervention_id), INDEX IDX_D8698A764BC44A10 (compte_rendu_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE intervention (id INT AUTO_INCREMENT NOT NULL, technicien_id INT DEFAULT NULL, client_nom VARCHAR(255) DEFAULT NULL, reference VARCHAR(255) NOT NULL, adresse VARCHAR(255) DEFAULT NULL, demande LONGTEXT DEFAULT NULL, detail VARCHAR(255) DEFAULT NULL, date_intervention DATE DEFAULT NULL, heure_debut TIME DEFAULT NULL, heure_fin TIME DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_D11814ABAEA34913 (reference), INDEX IDX_D11814AB13457256 (technicien_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE intervention_history (id INT AUTO_INCREMENT NOT NULL, intervention_id INT NOT NULL, modified_by_id INT DEFAULT NULL, filed_name VARCHAR(100) NOT NULL, old_value LONGTEXT DEFAULT NULL, new_value LONGTEXT DEFAULT NULL, action_type VARCHAR(30) NOT NULL, modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', context JSON DEFAULT NULL, INDEX IDX_6C54042E8EAE3863 (intervention_id), INDEX IDX_6C54042E99049ECE (modified_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE role (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(50) NOT NULL, UNIQUE INDEX UNIQ_57698A6A6C6E55B5 (nom), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, identifiant VARCHAR(180) NOT NULL, password VARCHAR(255) NOT NULL, couleur VARCHAR(7) DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649C90409EC (identifiant), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_role (user_id INT NOT NULL, role_id INT NOT NULL, INDEX IDX_2DE8C6A3A76ED395 (user_id), INDEX IDX_2DE8C6A3D60322AC (role_id), PRIMARY KEY(user_id, role_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE compte_rendu ADD CONSTRAINT FK_D39E69D213457256 FOREIGN KEY (technicien_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE compte_rendu ADD CONSTRAINT FK_D39E69D28EAE3863 FOREIGN KEY (intervention_id) REFERENCES intervention (id)');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A768EAE3863 FOREIGN KEY (intervention_id) REFERENCES intervention (id)');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A764BC44A10 FOREIGN KEY (compte_rendu_id) REFERENCES compte_rendu (id)');
        $this->addSql('ALTER TABLE intervention ADD CONSTRAINT FK_D11814AB13457256 FOREIGN KEY (technicien_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE intervention_history ADD CONSTRAINT FK_6C54042E8EAE3863 FOREIGN KEY (intervention_id) REFERENCES intervention (id)');
        $this->addSql('ALTER TABLE intervention_history ADD CONSTRAINT FK_6C54042E99049ECE FOREIGN KEY (modified_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_role ADD CONSTRAINT FK_2DE8C6A3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_role ADD CONSTRAINT FK_2DE8C6A3D60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE compte_rendu DROP FOREIGN KEY FK_D39E69D213457256');
        $this->addSql('ALTER TABLE compte_rendu DROP FOREIGN KEY FK_D39E69D28EAE3863');
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A768EAE3863');
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A764BC44A10');
        $this->addSql('ALTER TABLE intervention DROP FOREIGN KEY FK_D11814AB13457256');
        $this->addSql('ALTER TABLE intervention_history DROP FOREIGN KEY FK_6C54042E8EAE3863');
        $this->addSql('ALTER TABLE intervention_history DROP FOREIGN KEY FK_6C54042E99049ECE');
        $this->addSql('ALTER TABLE user_role DROP FOREIGN KEY FK_2DE8C6A3A76ED395');
        $this->addSql('ALTER TABLE user_role DROP FOREIGN KEY FK_2DE8C6A3D60322AC');
        $this->addSql('DROP TABLE compte_rendu');
        $this->addSql('DROP TABLE document');
        $this->addSql('DROP TABLE intervention');
        $this->addSql('DROP TABLE intervention_history');
        $this->addSql('DROP TABLE role');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_role');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
