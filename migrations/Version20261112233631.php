<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261112233631 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_timer DROP FOREIGN KEY `FK_88122542A76ED395`');
        $this->addSql('ALTER TABLE user_timer DROP FOREIGN KEY `FK_88122542EE98D9B9`');
        $this->addSql('DROP TABLE user_timer');
        $this->addSql('ALTER TABLE seance_attendant DROP hub_subscriber_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_timer (user_id INT NOT NULL, timer_id INT NOT NULL, INDEX IDX_88122542A76ED395 (user_id), INDEX IDX_88122542EE98D9B9 (timer_id), PRIMARY KEY (user_id, timer_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE user_timer ADD CONSTRAINT `FK_88122542A76ED395` FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_timer ADD CONSTRAINT `FK_88122542EE98D9B9` FOREIGN KEY (timer_id) REFERENCES timer (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE seance_attendant ADD hub_subscriber_id VARCHAR(255) DEFAULT NULL');
    }
}
