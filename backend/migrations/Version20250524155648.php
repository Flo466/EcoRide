<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250524155648 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE carpooling (id INT AUTO_INCREMENT NOT NULL, departure_date DATE NOT NULL, departure_time TIME NOT NULL, departure_place VARCHAR(50) NOT NULL, arrival_date DATE NOT NULL, arrival_time TIME NOT NULL, arrival_place VARCHAR(50) NOT NULL, seat_count INT NOT NULL, price_per_person DOUBLE PRECISION NOT NULL, is_eco TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP TABLE carpooling
        SQL);
    }
}
