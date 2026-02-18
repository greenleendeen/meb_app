<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260124160320 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
{
    $this->addSql(
        'ALTER TABLE document 
         ADD uploaded_at DATETIME DEFAULT NULL 
         COMMENT \'(DC2Type:datetime_immutable)\''
    );
}

public function down(Schema $schema): void
{
    $this->addSql(
        'ALTER TABLE document DROP uploaded_at'
    );
}
}