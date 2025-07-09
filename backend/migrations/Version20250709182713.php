<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250709182713 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE car ADD pets_allowed TINYINT(1) DEFAULT 0 NOT NULL, ADD seats INT NOT NULL');
        $this->addSql('ALTER TABLE car ADD CONSTRAINT FK_773DE69DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE car ADD CONSTRAINT FK_773DE69D44F5D008 FOREIGN KEY (brand_id) REFERENCES brand (id)');
        $this->addSql('ALTER TABLE carpooling ADD status VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE carpooling ADD CONSTRAINT FK_6CC153F1C3C6F69F FOREIGN KEY (car_id) REFERENCES car (id)');
        $this->addSql('ALTER TABLE carpooling_user ADD id INT AUTO_INCREMENT NOT NULL, ADD is_driver TINYINT(1) NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE carpooling_user ADD CONSTRAINT FK_257FA72FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE carpooling_user ADD CONSTRAINT FK_257FA72FAFB2200A FOREIGN KEY (carpooling_id) REFERENCES carpooling (id)');
        $this->addSql('ALTER TABLE configuration ADD CONSTRAINT FK_A5E2A5D7A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE parameter ADD CONSTRAINT FK_2A97911073F32DD8 FOREIGN KEY (configuration_id) REFERENCES configuration (id)');
        $this->addSql('ALTER TABLE review ADD reviewed_user_id INT NOT NULL, CHANGE status status VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6B9A2A077 FOREIGN KEY (reviewed_user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_794381C6B9A2A077 ON review (reviewed_user_id)');
        $this->addSql('ALTER TABLE user ADD photo_mime_type VARCHAR(50) DEFAULT NULL, ADD is_driver TINYINT(1) DEFAULT 0 NOT NULL, ADD used_car_id INT DEFAULT NULL, CHANGE last_name last_name VARCHAR(50) DEFAULT NULL, CHANGE first_name first_name VARCHAR(50) DEFAULT NULL, CHANGE photo photo LONGBLOB DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6491C40D50E FOREIGN KEY (used_car_id) REFERENCES car (id)');
        $this->addSql('CREATE INDEX IDX_8D93D6491C40D50E ON user (used_car_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE car DROP FOREIGN KEY FK_773DE69DA76ED395');
        $this->addSql('ALTER TABLE car DROP FOREIGN KEY FK_773DE69D44F5D008');
        $this->addSql('ALTER TABLE car DROP pets_allowed, DROP seats');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6491C40D50E');
        $this->addSql('DROP INDEX IDX_8D93D6491C40D50E ON user');
        $this->addSql('ALTER TABLE user DROP photo_mime_type, DROP is_driver, DROP used_car_id, CHANGE last_name last_name VARCHAR(50) NOT NULL, CHANGE first_name first_name VARCHAR(50) NOT NULL, CHANGE photo photo VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6A76ED395');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6B9A2A077');
        $this->addSql('DROP INDEX IDX_794381C6B9A2A077 ON review');
        $this->addSql('ALTER TABLE review DROP reviewed_user_id, CHANGE status status VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE parameter DROP FOREIGN KEY FK_2A97911073F32DD8');
        $this->addSql('ALTER TABLE carpooling DROP FOREIGN KEY FK_6CC153F1C3C6F69F');
        $this->addSql('ALTER TABLE carpooling DROP status');
        $this->addSql('ALTER TABLE carpooling_user DROP FOREIGN KEY FK_257FA72FA76ED395');
        $this->addSql('ALTER TABLE carpooling_user DROP FOREIGN KEY FK_257FA72FAFB2200A');
        $this->addSql('ALTER TABLE carpooling_user DROP id, DROP is_driver, DROP PRIMARY KEY, ADD PRIMARY KEY (carpooling_id, user_id)');
        $this->addSql('ALTER TABLE configuration DROP FOREIGN KEY FK_A5E2A5D7A76ED395');
    }
}
