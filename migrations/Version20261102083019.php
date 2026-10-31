<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261102083019 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE seven_by_nine DROP FOREIGN KEY `FK_515B5BB6F173C1BD`');
        $this->addSql('DROP INDEX UNIQ_515B5BB6F173C1BD ON seven_by_nine');
        $this->addSql('ALTER TABLE seven_by_nine ADD sequence INT NOT NULL, DROP previous_timer_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE seven_by_nine ADD previous_timer_id INT DEFAULT NULL, DROP sequence');
        $this->addSql('ALTER TABLE seven_by_nine ADD CONSTRAINT `FK_515B5BB6F173C1BD` FOREIGN KEY (previous_timer_id) REFERENCES timer (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_515B5BB6F173C1BD ON seven_by_nine (previous_timer_id)');
    }
}
