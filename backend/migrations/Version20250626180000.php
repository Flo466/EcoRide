<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250626180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE car ADD CONSTRAINT FK_773DE69DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE car ADD CONSTRAINT FK_773DE69D44F5D008 FOREIGN KEY (brand_id) REFERENCES brand (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE carpooling ADD driver_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE carpooling ADD CONSTRAINT FK_6CC153F1C3423909 FOREIGN KEY (driver_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE carpooling ADD CONSTRAINT FK_6CC153F1C3C6F69F FOREIGN KEY (car_id) REFERENCES car (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6CC153F1C3423909 ON carpooling (driver_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE carpooling_user ADD CONSTRAINT FK_257FA72FAFB2200A FOREIGN KEY (carpooling_id) REFERENCES carpooling (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE carpooling_user ADD CONSTRAINT FK_257FA72FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE configuration ADD CONSTRAINT FK_A5E2A5D7A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE parameter ADD CONSTRAINT FK_2A97911073F32DD8 FOREIGN KEY (configuration_id) REFERENCES configuration (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE review ADD CONSTRAINT FK_794381C6A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE review DROP FOREIGN KEY FK_794381C6A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE parameter DROP FOREIGN KEY FK_2A97911073F32DD8
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE carpooling_user DROP FOREIGN KEY FK_257FA72FAFB2200A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE carpooling_user DROP FOREIGN KEY FK_257FA72FA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE carpooling DROP FOREIGN KEY FK_6CC153F1C3423909
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE carpooling DROP FOREIGN KEY FK_6CC153F1C3C6F69F
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_6CC153F1C3423909 ON carpooling
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE carpooling DROP driver_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE configuration DROP FOREIGN KEY FK_A5E2A5D7A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE car DROP FOREIGN KEY FK_773DE69DA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE car DROP FOREIGN KEY FK_773DE69D44F5D008
        SQL);
    }
}
