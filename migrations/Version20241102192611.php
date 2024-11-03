<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241102192611 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
       
        $this->addSql('DROP TABLE picture_habitat');
        $this->addSql('ALTER TABLE animal ADD picture_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE animal ADD CONSTRAINT FK_6AAB231FEE45BDBF FOREIGN KEY (picture_id) REFERENCES picture (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6AAB231FEE45BDBF ON animal (picture_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE picture_habitat (picture_id INT NOT NULL, habitat_id INT NOT NULL, INDEX IDX_2611463AAFFE2D26 (habitat_id), INDEX IDX_2611463AEE45BDBF (picture_id), PRIMARY KEY(picture_id, habitat_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
       
        $this->addSql('ALTER TABLE animal DROP FOREIGN KEY FK_6AAB231FEE45BDBF');
        $this->addSql('DROP INDEX UNIQ_6AAB231FEE45BDBF ON animal');
        $this->addSql('ALTER TABLE animal DROP picture_id');
    }
}
