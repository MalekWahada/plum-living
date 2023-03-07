<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211208165052 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update design cane code';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE`sylius_product_option_value` SET `code` = \'design_cannage_classique\' WHERE `code` = \'design_cannage\';');
        $this->addSql('UPDATE sylius_product_variant_option_value AS val
            INNER JOIN sylius_product_variant ON val.variant_id = sylius_product_variant.id
            INNER JOIN sylius_product ON sylius_product_variant.product_id = sylius_product.id
            SET val.option_value_id = ( SELECT id FROM sylius_product_option_value WHERE `code` = \'design_cannage_arche\' )
            WHERE
                sylius_product.`code` IN ( \'37201-11CR-P\', \'37201-12CR-P\' )
                AND val.option_value_id = ( SELECT id FROM sylius_product_option_value WHERE `code` = \'design_cannage_classique\' );');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('UPDATE sylius_product_variant_option_value AS val
            INNER JOIN sylius_product_variant ON val.variant_id = sylius_product_variant.id
            INNER JOIN sylius_product ON sylius_product_variant.product_id = sylius_product.id
            SET val.option_value_id = ( SELECT id FROM sylius_product_option_value WHERE `code` = \'design_cannage_classique\' )
            WHERE
                sylius_product.`code` IN ( \'37201-11CR-P\', \'37201-12CR-P\' )
                AND val.option_value_id = ( SELECT id FROM sylius_product_option_value WHERE `code` = \'design_cannage_arche\' );');
        $this->addSql('UPDATE`sylius_product_option_value` SET `code` = \'design_cannage\' WHERE `code` = \'design_cannage_classique\';');
    }
}
