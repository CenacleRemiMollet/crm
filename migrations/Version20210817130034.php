<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210817130034 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE account (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, login VARCHAR(255) NOT NULL, password VARCHAR(1024) NOT NULL, roles JSON NOT NULL, has_access TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_7D3656A4AA08CB10 (login), INDEX IDX_7D3656A4A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE account_password_request (id INT AUTO_INCREMENT NOT NULL, account_id INT NOT NULL, uuid VARCHAR(40) NOT NULL, create_date DATETIME NOT NULL, INDEX IDX_75FE62449B6B5FBA (account_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE account_session_history (id INT AUTO_INCREMENT NOT NULL, account_id INT NOT NULL, ip VARCHAR(48) NOT NULL, user_agent VARCHAR(400) NOT NULL, start_datetime DATETIME NOT NULL, INDEX IDX_D5E5ECCB9B6B5FBA (account_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE city (id INT AUTO_INCREMENT NOT NULL, county_number INT NOT NULL, city_name VARCHAR(255) NOT NULL, zip_code VARCHAR(10) NOT NULL, latitude DOUBLE PRECISION NOT NULL, longitude DOUBLE PRECISION NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE club (id INT AUTO_INCREMENT NOT NULL, uuid VARCHAR(64) NOT NULL, name VARCHAR(255) NOT NULL, logo VARCHAR(255) NOT NULL, website_url VARCHAR(512) DEFAULT NULL, facebook_url VARCHAR(512) DEFAULT NULL, mailing_list VARCHAR(512) DEFAULT NULL, active TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_B8EE3872D17F50A6 (uuid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE club_lesson (id INT AUTO_INCREMENT NOT NULL, club_location_id INT NOT NULL, club_id INT NOT NULL, uuid VARCHAR(16) NOT NULL, point INT NOT NULL, discipline VARCHAR(255) NOT NULL, age_level VARCHAR(512) DEFAULT NULL, day_of_week VARCHAR(20) NOT NULL, start_time TIME NOT NULL, end_time TIME NOT NULL, INDEX IDX_DDBBC879D8BF7905 (club_location_id), INDEX IDX_DDBBC87961190A32 (club_id), UNIQUE INDEX UNIQ_DDBBC879D17F50A6 (uuid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE club_location (id INT AUTO_INCREMENT NOT NULL, uuid VARCHAR(64) NOT NULL, name VARCHAR(255) NOT NULL, address VARCHAR(255) NOT NULL, city VARCHAR(255) NOT NULL, zipcode VARCHAR(255) NOT NULL, county VARCHAR(255) NOT NULL, country VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_5D252362D17F50A6 (uuid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE menu_item (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) NOT NULL, priority INT NOT NULL, available_for_roles JSON NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, uuid VARCHAR(16) NOT NULL, lastname VARCHAR(255) NOT NULL, firstname VARCHAR(255) NOT NULL, sex VARCHAR(1) NOT NULL, birthday DATE NOT NULL, address VARCHAR(512) DEFAULT NULL, zipcode VARCHAR(32) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, phone VARCHAR(32) DEFAULT NULL, phone_emergency VARCHAR(32) DEFAULT NULL, nationality VARCHAR(64) DEFAULT NULL, mails JSON NOT NULL, created DATETIME NOT NULL, blacklist_date DATE DEFAULT NULL, blacklist_reason VARCHAR(1000) DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649D17F50A6 (uuid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_club_subscribe (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, club_id INT NOT NULL, roles JSON NOT NULL, subscribe_date DATE DEFAULT NULL, unsubscribe_date DATE DEFAULT NULL, INDEX IDX_AC352081A76ED395 (user_id), INDEX IDX_AC35208161190A32 (club_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_history (id INT AUTO_INCREMENT NOT NULL, modifier_user_id INT NOT NULL, modified_user_id INT NOT NULL, modification_date DATETIME NOT NULL, element_name VARCHAR(64) NOT NULL, previous_value VARCHAR(255) DEFAULT NULL, new_value VARCHAR(255) DEFAULT NULL, action VARCHAR(1) NOT NULL, INDEX IDX_7FB76E4165787AC2 (modifier_user_id), INDEX IDX_7FB76E41BAA24139 (modified_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE account ADD CONSTRAINT FK_7D3656A4A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE account_password_request ADD CONSTRAINT FK_75FE62449B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id)');
        $this->addSql('ALTER TABLE account_session_history ADD CONSTRAINT FK_D5E5ECCB9B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id)');
        $this->addSql('ALTER TABLE club_lesson ADD CONSTRAINT FK_DDBBC879D8BF7905 FOREIGN KEY (club_location_id) REFERENCES club_location (id)');
        $this->addSql('ALTER TABLE club_lesson ADD CONSTRAINT FK_DDBBC87961190A32 FOREIGN KEY (club_id) REFERENCES club (id)');
        $this->addSql('ALTER TABLE user_club_subscribe ADD CONSTRAINT FK_AC352081A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_club_subscribe ADD CONSTRAINT FK_AC35208161190A32 FOREIGN KEY (club_id) REFERENCES club (id)');
        $this->addSql('ALTER TABLE user_history ADD CONSTRAINT FK_7FB76E4165787AC2 FOREIGN KEY (modifier_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_history ADD CONSTRAINT FK_7FB76E41BAA24139 FOREIGN KEY (modified_user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE account_password_request DROP FOREIGN KEY FK_75FE62449B6B5FBA');
        $this->addSql('ALTER TABLE account_session_history DROP FOREIGN KEY FK_D5E5ECCB9B6B5FBA');
        $this->addSql('ALTER TABLE club_lesson DROP FOREIGN KEY FK_DDBBC87961190A32');
        $this->addSql('ALTER TABLE user_club_subscribe DROP FOREIGN KEY FK_AC35208161190A32');
        $this->addSql('ALTER TABLE club_lesson DROP FOREIGN KEY FK_DDBBC879D8BF7905');
        $this->addSql('ALTER TABLE account DROP FOREIGN KEY FK_7D3656A4A76ED395');
        $this->addSql('ALTER TABLE user_club_subscribe DROP FOREIGN KEY FK_AC352081A76ED395');
        $this->addSql('ALTER TABLE user_history DROP FOREIGN KEY FK_7FB76E4165787AC2');
        $this->addSql('ALTER TABLE user_history DROP FOREIGN KEY FK_7FB76E41BAA24139');
        $this->addSql('DROP TABLE account');
        $this->addSql('DROP TABLE account_password_request');
        $this->addSql('DROP TABLE account_session_history');
        $this->addSql('DROP TABLE city');
        $this->addSql('DROP TABLE club');
        $this->addSql('DROP TABLE club_lesson');
        $this->addSql('DROP TABLE club_location');
        $this->addSql('DROP TABLE menu_item');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_club_subscribe');
        $this->addSql('DROP TABLE user_history');
    }
}
