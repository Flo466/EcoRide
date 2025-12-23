<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250713140804 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD roles JSON NOT NULL, ADD last_name VARCHAR(50) DEFAULT NULL, ADD first_name VARCHAR(50) DEFAULT NULL, ADD phone VARCHAR(50) DEFAULT NULL, ADD address VARCHAR(255) DEFAULT NULL, ADD birth_date DATETIME DEFAULT NULL, ADD photo LONGBLOB DEFAULT NULL, ADD photo_mime_type VARCHAR(50) DEFAULT NULL, ADD user_name VARCHAR(50) NOT NULL, ADD credits INT NOT NULL, ADD api_token VARCHAR(255) NOT NULL, ADD is_driver TINYINT(1) DEFAULT 0 NOT NULL, ADD used_car_id INT DEFAULT NULL, CHANGE email email VARCHAR(180) NOT NULL, CHANGE username password VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6491C40D50E FOREIGN KEY (used_car_id) REFERENCES car (id)');
        $this->addSql('CREATE INDEX IDX_8D93D6491C40D50E ON user (used_car_id)');
        $this->addSql('ALTER TABLE user RENAME INDEX uniq_8d93d649e7927c74 TO UNIQ_IDENTIFIER_EMAIL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6491C40D50E');
        $this->addSql('DROP INDEX IDX_8D93D6491C40D50E ON user');
        $this->addSql('ALTER TABLE user ADD username VARCHAR(255) NOT NULL, DROP roles, DROP password, DROP last_name, DROP first_name, DROP phone, DROP address, DROP birth_date, DROP photo, DROP photo_mime_type, DROP user_name, DROP credits, DROP api_token, DROP is_driver, DROP used_car_id, CHANGE email email VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE user RENAME INDEX uniq_identifier_email TO UNIQ_8D93D649E7927C74');
    }
}
