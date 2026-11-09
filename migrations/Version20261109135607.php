<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261109135607 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE seven_by_nine_user (seven_by_nine_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_90747E012644B20 (seven_by_nine_id), INDEX IDX_90747E01A76ED395 (user_id), PRIMARY KEY (seven_by_nine_id, user_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE seven_by_nine_user ADD CONSTRAINT FK_90747E012644B20 FOREIGN KEY (seven_by_nine_id) REFERENCES seven_by_nine (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE seven_by_nine_user ADD CONSTRAINT FK_90747E01A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE timers_preset CHANGE count count INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE seven_by_nine_user DROP FOREIGN KEY FK_90747E012644B20');
        $this->addSql('ALTER TABLE seven_by_nine_user DROP FOREIGN KEY FK_90747E01A76ED395');
        $this->addSql('DROP TABLE seven_by_nine_user');
        $this->addSql('ALTER TABLE timers_preset CHANGE count count INT DEFAULT 1');
    }
}
