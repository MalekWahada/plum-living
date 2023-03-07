<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211013164001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update product main taxon for paints release';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE sylius_product
            INNER JOIN sylius_product_taxon ON sylius_product_taxon.product_id = sylius_product.id
            SET main_taxon_id = sylius_product_taxon.taxon_id
            WHERE
                sylius_product_taxon.taxon_id = ( SELECT id FROM sylius_taxon WHERE `code` = \'echantillon_facade\' )');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('UPDATE sylius_product
            SET main_taxon_id = ( SELECT id FROM sylius_taxon WHERE `code` = \'echantillon\' )
            WHERE
                main_taxon_id = ( SELECT id FROM sylius_taxon WHERE `code` = \'echantillon_facade\' )');
    }
}
