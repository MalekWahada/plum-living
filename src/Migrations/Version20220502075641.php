<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220502075641 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add channel\'s default country and customer\'s locale / channel code';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sylius_channel ADD default_country_id INT DEFAULT NULL');
        $this->addSql('UPDATE sylius_channel SET default_country_id = (SELECT id FROM sylius_country WHERE code = \'FR\')');
        $this->addSql('ALTER TABLE sylius_channel CHANGE default_country_id default_country_id INT NOT NULL');
        $this->addSql('ALTER TABLE sylius_channel ADD CONSTRAINT FK_16C8119EB10C6A13 FOREIGN KEY (default_country_id) REFERENCES sylius_country (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_16C8119EB10C6A13 ON sylius_channel (default_country_id)');
        $this->addSql('ALTER TABLE sylius_customer ADD locale_code VARCHAR(12) DEFAULT \'fr\' NOT NULL');
        $this->addSql('ALTER TABLE sylius_customer ADD channel_code VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sylius_channel DROP FOREIGN KEY FK_16C8119EB10C6A13');
        $this->addSql('DROP INDEX UNIQ_16C8119EB10C6A13 ON sylius_channel');
        $this->addSql('ALTER TABLE sylius_channel DROP default_country_id');
        $this->addSql('ALTER TABLE sylius_customer DROP locale_code');
        $this->addSql('ALTER TABLE sylius_customer DROP channel_code');
    }
}
