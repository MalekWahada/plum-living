<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210301154910 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE webgriffe_sylius_shipping_table_rate (id INT AUTO_INCREMENT NOT NULL, currency_id INT NOT NULL, code VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, weightLimitToRate JSON NOT NULL COMMENT \'(DC2Type:json_array)\', INDEX IDX_1D5F4E4138248176 (currency_id), UNIQUE INDEX code_idx (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE webgriffe_sylius_shipping_table_rate ADD CONSTRAINT FK_1D5F4E4138248176 FOREIGN KEY (currency_id) REFERENCES sylius_currency (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE webgriffe_sylius_shipping_table_rate');
    }
}
