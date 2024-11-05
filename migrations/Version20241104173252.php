<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241104173252 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
{
    
    
    
    
    // 3. Supprimer d'abord la contrainte sur picture_data si elle existe
    $this->addSql('ALTER TABLE habitat MODIFY picture_data VARCHAR(255) NULL');
    
    // 4. Puis supprimer la colonne
    $this->addSql('ALTER TABLE habitat DROP COLUMN picture_data');
}

public function down(Schema $schema): void
{
    // Restaurer la colonne si nécessaire
    $this->addSql('ALTER TABLE habitat ADD picture_data VARCHAR(255) DEFAULT NULL');
    $this->addSql('DROP TABLE habitat_picture');
}
    
}
