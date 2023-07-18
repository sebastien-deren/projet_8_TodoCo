<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230718075517 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE task ADD creator_id INT');
        $this->addSql('INSERT INTO user_app(id,username,email,password) VALUES(nextval(\'user_app_id_seq\'),\'anonymous\',\'anonymous@test.com\',\'none\')');
        $this->addSql('UPDATE task SET creator_id= (SELECT id FROM user_app WHERE username=\'anonymous\') WHERE creator_id IS NULL');
        $this->addSql('ALTER TABLE task ALTER COLUMN creator_id SET NOT NULL');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB2561220EA6 FOREIGN KEY (creator_id) REFERENCES user_app (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_527EDB2561220EA6 ON task (creator_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA IF NOT EXISTS public');
        $this->addSql('ALTER TABLE task DROP CONSTRAINT FK_527EDB2561220EA6');
        $this->addSql('DROP INDEX IDX_527EDB2561220EA6');
        $this->addSql('ALTER TABLE task DROP creator_id');
        $this->addSql('DELETE FROM user_app WHERE username=\'anonymous\'');
    }
}
