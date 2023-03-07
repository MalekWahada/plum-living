<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210326081600 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX product_enabled ON sylius_product (enabled)');
        $this->addSql('CREATE INDEX product_variant_enabled ON sylius_product_variant (enabled)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX product_enabled ON sylius_product');
        $this->addSql('DROP INDEX product_variant_enabled ON sylius_product_variant');
    }
}
