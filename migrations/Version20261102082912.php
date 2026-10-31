<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261102082912 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE seven_by_nine ADD previous_timer_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE seven_by_nine ADD CONSTRAINT FK_515B5BB6F173C1BD FOREIGN KEY (previous_timer_id) REFERENCES timer (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_515B5BB6F173C1BD ON seven_by_nine (previous_timer_id)');
        $this->addSql('ALTER TABLE timer ADD position INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE seven_by_nine DROP FOREIGN KEY FK_515B5BB6F173C1BD');
        $this->addSql('DROP INDEX UNIQ_515B5BB6F173C1BD ON seven_by_nine');
        $this->addSql('ALTER TABLE seven_by_nine DROP previous_timer_id');
        $this->addSql('ALTER TABLE timer DROP position');
    }
}
