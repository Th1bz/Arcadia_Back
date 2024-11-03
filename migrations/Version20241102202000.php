<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241102202000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addsql('DROP TABLE picture');
        $this->addSql('ALTER TABLE animal DROP FOREIGN KEY FK_6AAB231FEE45BDBF');
        $this->addSql('DROP INDEX UNIQ_6AAB231FEE45BDBF ON animal');
        $this->addSql('ALTER TABLE animal DROP picture_id');
        $this->addSql('ALTER TABLE picture ADD animal_id INT DEFAULT NULL, CHANGE picture_data picture_data VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE picture ADD CONSTRAINT FK_16DB4F898E962C16 FOREIGN KEY (animal_id) REFERENCES animal (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_16DB4F898E962C16 ON picture (animal_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE animal ADD picture_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE animal ADD CONSTRAINT FK_6AAB231FEE45BDBF FOREIGN KEY (picture_id) REFERENCES picture (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6AAB231FEE45BDBF ON animal (picture_id)');
        $this->addSql('ALTER TABLE picture DROP FOREIGN KEY FK_16DB4F898E962C16');
        $this->addSql('DROP INDEX UNIQ_16DB4F898E962C16 ON picture');
        $this->addSql('ALTER TABLE picture DROP animal_id, CHANGE picture_data picture_data LONGBLOB NOT NULL');
    }
}
