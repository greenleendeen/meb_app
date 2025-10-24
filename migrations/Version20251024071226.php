<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251024071226 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE intervention ADD technicien_id INT DEFAULT NULL, ADD date_intervention DATE DEFAULT NULL, ADD heure_debut TIME DEFAULT NULL, ADD heure_fin TIME DEFAULT NULL');
        $this->addSql('ALTER TABLE intervention ADD CONSTRAINT FK_D11814AB13457256 FOREIGN KEY (technicien_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_D11814AB13457256 ON intervention (technicien_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE intervention DROP FOREIGN KEY FK_D11814AB13457256');
        $this->addSql('DROP INDEX IDX_D11814AB13457256 ON intervention');
        $this->addSql('ALTER TABLE intervention DROP technicien_id, DROP date_intervention, DROP heure_debut, DROP heure_fin');
    }
}
