<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250524172053 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE configuration ADD CONSTRAINT FK_A5E2A5D7A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE parameter ADD configuration_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE parameter ADD CONSTRAINT FK_2A97911073F32DD8 FOREIGN KEY (configuration_id) REFERENCES configuration (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_2A97911073F32DD8 ON parameter (configuration_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE parameter DROP FOREIGN KEY FK_2A97911073F32DD8
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_2A97911073F32DD8 ON parameter
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE parameter DROP configuration_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE configuration DROP FOREIGN KEY FK_A5E2A5D7A76ED395
        SQL);
    }
}
