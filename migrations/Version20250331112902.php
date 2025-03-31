<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250331112902 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TEMPORARY TABLE __temp__l3_users AS SELECT id, pays_id, login, roles, password, nom, prenom, date_naissance, is_admin FROM l3_users
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE l3_users
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE l3_users (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, pays_id INTEGER DEFAULT NULL, login VARCHAR(180) NOT NULL, roles CLOB NOT NULL --(DC2Type:json)
            , password VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, date_naissance DATE NOT NULL, is_admin BOOLEAN NOT NULL, CONSTRAINT FK_54943D84A6E44244 FOREIGN KEY (pays_id) REFERENCES pays (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO l3_users (id, pays_id, login, roles, password, nom, prenom, date_naissance, is_admin) SELECT id, pays_id, login, roles, password, nom, prenom, date_naissance, is_admin FROM __temp__l3_users
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE __temp__l3_users
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_54943D84A6E44244 ON l3_users (pays_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_IDENTIFIER_LOGIN ON l3_users (login)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TEMPORARY TABLE __temp__l3_users AS SELECT id, pays_id, login, roles, password, nom, prenom, date_naissance, is_admin FROM l3_users
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE l3_users
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE l3_users (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, pays_id INTEGER DEFAULT NULL, login VARCHAR(180) NOT NULL, roles CLOB NOT NULL, password VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, date_naissance DATE NOT NULL, is_admin BOOLEAN NOT NULL, CONSTRAINT FK_54943D84A6E44244 FOREIGN KEY (pays_id) REFERENCES pays (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO l3_users (id, pays_id, login, roles, password, nom, prenom, date_naissance, is_admin) SELECT id, pays_id, login, roles, password, nom, prenom, date_naissance, is_admin FROM __temp__l3_users
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE __temp__l3_users
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_54943D84A6E44244 ON l3_users (pays_id)
        SQL);
    }
}
