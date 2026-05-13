<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260504140709 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__product AS SELECT id, name, description, price, stock_quantity, category, thumbnail_image, star_rating FROM product');
        $this->addSql('DROP TABLE product');
        $this->addSql('CREATE TABLE product (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description CLOB NOT NULL, price DOUBLE PRECISION NOT NULL, stock_quantity INTEGER NOT NULL, category VARCHAR(255) NOT NULL, thumbnail_image VARCHAR(255) DEFAULT NULL, star_rating DOUBLE PRECISION DEFAULT NULL, seller_id INTEGER DEFAULT NULL, CONSTRAINT FK_D34A04AD8DE820D9 FOREIGN KEY (seller_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO product (id, name, description, price, stock_quantity, category, thumbnail_image, star_rating) SELECT id, name, description, price, stock_quantity, category, thumbnail_image, star_rating FROM __temp__product');
        $this->addSql('DROP TABLE __temp__product');
        $this->addSql('CREATE INDEX IDX_D34A04AD8DE820D9 ON product (seller_id)');
        $this->addSql('ALTER TABLE user ADD COLUMN store_name VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__product AS SELECT id, name, description, price, stock_quantity, category, thumbnail_image, star_rating FROM product');
        $this->addSql('DROP TABLE product');
        $this->addSql('CREATE TABLE product (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description CLOB NOT NULL, price DOUBLE PRECISION NOT NULL, stock_quantity INTEGER NOT NULL, category VARCHAR(255) NOT NULL, thumbnail_image VARCHAR(255) DEFAULT NULL, star_rating DOUBLE PRECISION DEFAULT NULL)');
        $this->addSql('INSERT INTO product (id, name, description, price, stock_quantity, category, thumbnail_image, star_rating) SELECT id, name, description, price, stock_quantity, category, thumbnail_image, star_rating FROM __temp__product');
        $this->addSql('DROP TABLE __temp__product');
        $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT id, email, roles, password, first_name, last_name, security_pin FROM user');
        $this->addSql('DROP TABLE user');
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles CLOB NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, security_pin VARCHAR(4) NOT NULL)');
        $this->addSql('INSERT INTO user (id, email, roles, password, first_name, last_name, security_pin) SELECT id, email, roles, password, first_name, last_name, security_pin FROM __temp__user');
        $this->addSql('DROP TABLE __temp__user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON user (email)');
    }
}
