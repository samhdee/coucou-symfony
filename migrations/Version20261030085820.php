<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261030085820 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE seven_by_nine (id INT AUTO_INCREMENT NOT NULL, count_tasks INT NOT NULL, count_pauses INT NOT NULL, current_timer_id INT NOT NULL, INDEX IDX_515B5BB673E0A393 (current_timer_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE seven_by_nine ADD CONSTRAINT FK_515B5BB673E0A393 FOREIGN KEY (current_timer_id) REFERENCES timer (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE seven_by_nine DROP FOREIGN KEY FK_515B5BB673E0A393');
        $this->addSql('DROP TABLE seven_by_nine');
    }
}
