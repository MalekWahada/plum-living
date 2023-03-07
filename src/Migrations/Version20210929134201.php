<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210929134201 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename delivery date calculation modes';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE `plum_delivery_date_calculation_config` SET `mode` = \'fixed_date_range_lacquer\' WHERE `mode` = \'fixed_date_range_acta\'');
        $this->addSql('UPDATE `plum_delivery_date_calculation_config` SET `mode` = \'fixed_date_range_wood\' WHERE `mode` = \'fixed_date_range_mbn\'');
        $this->addSql('UPDATE `sylius_product_variant` SET `delivery_calculation_mode` = \'fixed_date_range_lacquer\' WHERE `delivery_calculation_mode` = \'fixed_date_range_acta\'');
        $this->addSql('UPDATE `sylius_product_variant` SET `delivery_calculation_mode` = \'fixed_date_range_wood\' WHERE `delivery_calculation_mode` = \'fixed_date_range_mbn\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('UPDATE `plum_delivery_date_calculation_config` SET `mode` = \'fixed_date_range_acta\' WHERE `mode` = \'fixed_date_range_lacquer\'');
        $this->addSql('UPDATE `plum_delivery_date_calculation_config` SET `mode` = \'fixed_date_range_mbn\' WHERE `mode` = \'fixed_date_range_wood\'');
        $this->addSql('UPDATE `sylius_product_variant` SET `delivery_calculation_mode` = \'fixed_date_range_acta\' WHERE `delivery_calculation_mode` = \'fixed_date_range_lacquer\'');
        $this->addSql('UPDATE `sylius_product_variant` SET `delivery_calculation_mode` = \'fixed_date_range_mbn\' WHERE `delivery_calculation_mode` = \'fixed_date_range_wood\'');
    }
}
