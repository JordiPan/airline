<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210323122016 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `group` (id INT AUTO_INCREMENT NOT NULL, founder_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_6DC044C519113B3C (founder_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE group_customer (id INT AUTO_INCREMENT NOT NULL, the_group_id INT DEFAULT NULL, user_id INT DEFAULT NULL, INDEX IDX_8809C276B7DD4C24 (the_group_id), INDEX IDX_8809C276A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE `group` ADD CONSTRAINT FK_6DC044C519113B3C FOREIGN KEY (founder_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE group_customer ADD CONSTRAINT FK_8809C276B7DD4C24 FOREIGN KEY (the_group_id) REFERENCES `group` (id)');
        $this->addSql('ALTER TABLE group_customer ADD CONSTRAINT FK_8809C276A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE group_customer DROP FOREIGN KEY FK_8809C276B7DD4C24');
        $this->addSql('DROP TABLE `group`');
        $this->addSql('DROP TABLE group_customer');
    }
}
