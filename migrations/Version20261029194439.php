<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261029194439 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE timer ADD user_id INT');
        $this->addSql('ALTER TABLE timer ADD CONSTRAINT FK_6AD0DE1AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_6AD0DE1AA76ED395 ON timer (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE timer DROP FOREIGN KEY FK_6AD0DE1AA76ED395');
        $this->addSql('DROP INDEX IDX_6AD0DE1AA76ED395 ON timer');
        $this->addSql('ALTER TABLE timer DROP user_id');
    }
}
