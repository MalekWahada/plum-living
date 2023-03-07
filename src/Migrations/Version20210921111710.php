<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210921111710 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add delivery date calculation configs';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sylius_product_variant CHANGE minDayDelivery min_day_delivery INT NOT NULL, CHANGE maxDayDelivery max_day_delivery INT NOT NULL, ADD delivery_calculation_mode VARCHAR(32) NOT NULL DEFAULT \'dynamic\'');
        $this->addSql('CREATE TABLE plum_delivery_date_calculation_config (id INT AUTO_INCREMENT NOT NULL, mode VARCHAR(32) NOT NULL, min_date_delivery DATE DEFAULT NULL, max_date_delivery DATE DEFAULT NULL, UNIQUE INDEX UNIQ_B82C1FD997CA47AB (mode), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('INSERT INTO plum_delivery_date_calculation_config (mode, min_date_delivery, max_date_delivery) VALUES (\'fixed_date_range_acta\', NULL, NULL)');
        $this->addSql('INSERT INTO plum_delivery_date_calculation_config (mode, min_date_delivery, max_date_delivery) VALUES (\'fixed_date_range_mbn\', NULL, NULL)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sylius_product_variant CHANGE min_day_delivery minDayDelivery INT NOT NULL, CHANGE max_day_delivery maxDayDelivery INT NOT NULL, DROP delivery_calculation_mode');
        $this->addSql('DROP TABLE plum_delivery_date_calculation_config');
    }
}
