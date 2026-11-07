<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261107165808 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE timer ADD session_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE timer ADD CONSTRAINT FK_6AD0DE1A613FECDF FOREIGN KEY (session_id) REFERENCES session (id)');
        $this->addSql('CREATE INDEX IDX_6AD0DE1A613FECDF ON timer (session_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE timer DROP FOREIGN KEY FK_6AD0DE1A613FECDF');
        $this->addSql('DROP INDEX IDX_6AD0DE1A613FECDF ON timer');
        $this->addSql('ALTER TABLE timer DROP session_id');
    }
}
