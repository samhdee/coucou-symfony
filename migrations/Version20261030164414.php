<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261030164414 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE seven_by_nine ADD created_at DATETIME NOT NULL, ADD last_timer_type VARCHAR(255) NOT NULL, ADD created_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE seven_by_nine ADD CONSTRAINT FK_515B5BB6B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_515B5BB6B03A8386 ON seven_by_nine (created_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE seven_by_nine DROP FOREIGN KEY FK_515B5BB6B03A8386');
        $this->addSql('DROP INDEX IDX_515B5BB6B03A8386 ON seven_by_nine');
        $this->addSql('ALTER TABLE seven_by_nine DROP created_at, DROP last_timer_type, DROP created_by_id');
    }
}
