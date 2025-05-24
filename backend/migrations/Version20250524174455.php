<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250524174455 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE carpooling_user (carpooling_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_257FA72FAFB2200A (carpooling_id), INDEX IDX_257FA72FA76ED395 (user_id), PRIMARY KEY(carpooling_id, user_id)) DEFAULT CHARACTER SET utf8mb4
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
        $this->addSql(<<<'SQL'
            ALTER TABLE role_user ADD CONSTRAINT FK_332CA4DDD60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE role_user ADD CONSTRAINT FK_332CA4DDA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE carpooling_user DROP FOREIGN KEY FK_257FA72FAFB2200A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE carpooling_user DROP FOREIGN KEY FK_257FA72FA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE carpooling_user
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE parameter DROP FOREIGN KEY FK_2A97911073F32DD8
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE role_user DROP FOREIGN KEY FK_332CA4DDD60322AC
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE role_user DROP FOREIGN KEY FK_332CA4DDA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE review DROP FOREIGN KEY FK_794381C6A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE configuration DROP FOREIGN KEY FK_A5E2A5D7A76ED395
        SQL);
    }
}
