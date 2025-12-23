<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250709184436 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE brand (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(50) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE car (id INT AUTO_INCREMENT NOT NULL, model VARCHAR(50) NOT NULL, color VARCHAR(50) DEFAULT NULL, licence_plate VARCHAR(50) NOT NULL, energy VARCHAR(50) NOT NULL, first_registration_date DATE DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, pets_allowed TINYINT(1) DEFAULT 0 NOT NULL, seats INT NOT NULL, user_id INT NOT NULL, brand_id INT NOT NULL, INDEX IDX_773DE69DA76ED395 (user_id), INDEX IDX_773DE69D44F5D008 (brand_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE carpooling (id INT AUTO_INCREMENT NOT NULL, departure_date DATE NOT NULL, departure_time TIME NOT NULL, departure_place VARCHAR(50) NOT NULL, arrival_date DATE NOT NULL, arrival_time TIME NOT NULL, arrival_place VARCHAR(50) NOT NULL, seat_count INT NOT NULL, price_per_person DOUBLE PRECISION NOT NULL, is_eco TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, status VARCHAR(255) NOT NULL, car_id INT NOT NULL, INDEX IDX_6CC153F1C3C6F69F (car_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE carpooling_user (id INT AUTO_INCREMENT NOT NULL, is_driver TINYINT(1) NOT NULL, user_id INT NOT NULL, carpooling_id INT NOT NULL, INDEX IDX_257FA72FA76ED395 (user_id), INDEX IDX_257FA72FAFB2200A (carpooling_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE configuration (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, INDEX IDX_A5E2A5D7A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE parameter (id INT AUTO_INCREMENT NOT NULL, configuration_id INT NOT NULL, INDEX IDX_2A97911073F32DD8 (configuration_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE review (id INT AUTO_INCREMENT NOT NULL, comment LONGTEXT NOT NULL, ratting INT NOT NULL, status VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, user_id INT NOT NULL, reviewed_user_id INT NOT NULL, INDEX IDX_794381C6A76ED395 (user_id), INDEX IDX_794381C6B9A2A077 (reviewed_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, last_name VARCHAR(50) DEFAULT NULL, first_name VARCHAR(50) DEFAULT NULL, phone VARCHAR(50) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, birth_date DATETIME DEFAULT NULL, photo LONGBLOB DEFAULT NULL, photo_mime_type VARCHAR(50) DEFAULT NULL, user_name VARCHAR(50) NOT NULL, credits INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, api_token VARCHAR(255) NOT NULL, is_driver TINYINT(1) DEFAULT 0 NOT NULL, used_car_id INT DEFAULT NULL, INDEX IDX_8D93D6491C40D50E (used_car_id), UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE car ADD CONSTRAINT FK_773DE69DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE car ADD CONSTRAINT FK_773DE69D44F5D008 FOREIGN KEY (brand_id) REFERENCES brand (id)');
        $this->addSql('ALTER TABLE carpooling ADD CONSTRAINT FK_6CC153F1C3C6F69F FOREIGN KEY (car_id) REFERENCES car (id)');
        $this->addSql('ALTER TABLE carpooling_user ADD CONSTRAINT FK_257FA72FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE carpooling_user ADD CONSTRAINT FK_257FA72FAFB2200A FOREIGN KEY (carpooling_id) REFERENCES carpooling (id)');
        $this->addSql('ALTER TABLE configuration ADD CONSTRAINT FK_A5E2A5D7A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE parameter ADD CONSTRAINT FK_2A97911073F32DD8 FOREIGN KEY (configuration_id) REFERENCES configuration (id)');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6B9A2A077 FOREIGN KEY (reviewed_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6491C40D50E FOREIGN KEY (used_car_id) REFERENCES car (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE car DROP FOREIGN KEY FK_773DE69DA76ED395');
        $this->addSql('ALTER TABLE car DROP FOREIGN KEY FK_773DE69D44F5D008');
        $this->addSql('ALTER TABLE carpooling DROP FOREIGN KEY FK_6CC153F1C3C6F69F');
        $this->addSql('ALTER TABLE carpooling_user DROP FOREIGN KEY FK_257FA72FA76ED395');
        $this->addSql('ALTER TABLE carpooling_user DROP FOREIGN KEY FK_257FA72FAFB2200A');
        $this->addSql('ALTER TABLE configuration DROP FOREIGN KEY FK_A5E2A5D7A76ED395');
        $this->addSql('ALTER TABLE parameter DROP FOREIGN KEY FK_2A97911073F32DD8');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6A76ED395');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6B9A2A077');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6491C40D50E');
        $this->addSql('DROP TABLE brand');
        $this->addSql('DROP TABLE car');
        $this->addSql('DROP TABLE carpooling');
        $this->addSql('DROP TABLE carpooling_user');
        $this->addSql('DROP TABLE configuration');
        $this->addSql('DROP TABLE parameter');
        $this->addSql('DROP TABLE review');
        $this->addSql('DROP TABLE user');
    }
}
