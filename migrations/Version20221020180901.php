<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221020180901 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE trick ADD slug VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D8F0A91E5E237E06 ON trick (name)');
        $this->addSql('ALTER TABLE user CHANGE username username VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE user_token DROP INDEX UNIQ_BDF55A63A76ED395, ADD INDEX IDX_BDF55A63A76ED395 (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_D8F0A91E5E237E06 ON trick');
        $this->addSql('ALTER TABLE trick DROP slug');
        $this->addSql('ALTER TABLE user CHANGE username username VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE user_token DROP INDEX IDX_BDF55A63A76ED395, ADD UNIQUE INDEX UNIQ_BDF55A63A76ED395 (user_id)');
    }
}
