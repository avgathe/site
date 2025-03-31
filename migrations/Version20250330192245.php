<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Modification des tables et des contraintes sans utiliser syntaxe non supportée par SQLite.
 */
final class Version20250330192245 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Renomme les tables et met à jour les contraintes pour être compatibles avec SQLite.';
    }

    public function up(Schema $schema): void
    {
        // Renommer les tables
        $this->addSql(<<<'SQL'
            ALTER TABLE panier RENAME TO l3_paniers
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" RENAME TO l3_users
        SQL);

        // Pour SQLite, les contraintes doivent être recréées au lieu de les renommer
        // Supprimer les contraintes liées à l3_paniers (ancien panier)
        $this->addSql(<<<'SQL'
            PRAGMA foreign_keys = OFF
        SQL); // Désactivation temporaire des clés étrangères
        $this->addSql(<<<'SQL'
            CREATE TABLE l3_paniers_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                produit_id INTEGER NOT NULL,
                client_id INTEGER NOT NULL,
                quantite INTEGER NOT NULL,
                CONSTRAINT FK_661EDEBDF347EFB FOREIGN KEY (produit_id) REFERENCES produit (id) NOT DEFERRABLE INITIALLY IMMEDIATE,
                CONSTRAINT FK_661EDEBD19EB6921 FOREIGN KEY (client_id) REFERENCES l3_users (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO l3_paniers_new (id, produit_id, client_id, quantite)
            SELECT id, produit_id, client_id, quantite FROM l3_paniers
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE l3_paniers
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE l3_paniers_new RENAME TO l3_paniers
        SQL);
        $this->addSql(<<<'SQL'
            PRAGMA foreign_keys = ON
        SQL); // Réactivation des clés étrangères

        // Même opération pour l3_users (ancien user)
        $this->addSql(<<<'SQL'
            PRAGMA foreign_keys = OFF
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE l3_users_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                pays_id INTEGER DEFAULT NULL,
                login VARCHAR(180) NOT NULL,
                roles CLOB NOT NULL,
                password VARCHAR(255) NOT NULL,
                nom VARCHAR(255) NOT NULL,
                prenom VARCHAR(255) NOT NULL,
                date_naissance DATE NOT NULL,
                is_admin BOOLEAN NOT NULL,
                CONSTRAINT FK_54943D84A6E44244 FOREIGN KEY (pays_id) REFERENCES pays (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO l3_users_new (id, pays_id, login, roles, password, nom, prenom, date_naissance, is_admin)
            SELECT id, pays_id, login, roles, password, nom, prenom, date_naissance, is_admin FROM l3_users
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE l3_users
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE l3_users_new RENAME TO l3_users
        SQL);
        $this->addSql(<<<'SQL'
            PRAGMA foreign_keys = ON
        SQL);
    }

    public function down(Schema $schema): void
    {
        // Répéter l'opération inverse pour revenir aux noms d'origine

        // Renommer les tables
        $this->addSql(<<<'SQL'
            ALTER TABLE l3_paniers RENAME TO panier
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE l3_users RENAME TO "user"
        SQL);

        // Supprimer les nouvelles contraintes et restaurer les anciennes
        $this->addSql(<<<'SQL'
            PRAGMA foreign_keys = OFF
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE panier_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                produit_id INTEGER NOT NULL,
                client_id INTEGER NOT NULL,
                quantite INTEGER NOT NULL,
                CONSTRAINT FK_24CC0DF2F347EFB FOREIGN KEY (produit_id) REFERENCES produit (id) NOT DEFERRABLE INITIALLY IMMEDIATE,
                CONSTRAINT FK_24CC0DF219EB6921 FOREIGN KEY (client_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO panier_new (id, produit_id, client_id, quantite)
            SELECT id, produit_id, client_id, quantite FROM panier
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE panier
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE panier_new RENAME TO panier
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                pays_id INTEGER DEFAULT NULL,
                login VARCHAR(180) NOT NULL,
                roles CLOB NOT NULL,
                password VARCHAR(255) NOT NULL,
                nom VARCHAR(255) NOT NULL,
                prenom VARCHAR(255) NOT NULL,
                date_naissance DATE NOT NULL,
                is_admin BOOLEAN NOT NULL,
                CONSTRAINT FK_8D93D649A6E44244 FOREIGN KEY (pays_id) REFERENCES pays (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO user_new (id, pays_id, login, roles, password, nom, prenom, date_naissance, is_admin)
            SELECT id, pays_id, login, roles, password, nom, prenom, date_naissance, is_admin FROM "user"
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE "user"
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_new RENAME TO "user"
        SQL);
        $this->addSql(<<<'SQL'
            PRAGMA foreign_keys = ON
        SQL);
    }
}