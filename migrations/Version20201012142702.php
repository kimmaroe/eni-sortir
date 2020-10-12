<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201012142702 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD last_name VARCHAR(50) NOT NULL, ADD first_name VARCHAR(50) NOT NULL, ADD phone VARCHAR(20) NOT NULL, ADD is_active TINYINT(1) NOT NULL, ADD date_created DATETIME NOT NULL, ADD date_updated DATETIME DEFAULT NULL, ADD picture VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP last_name, DROP first_name, DROP phone, DROP is_active, DROP date_created, DROP date_updated, DROP picture');
    }
}
