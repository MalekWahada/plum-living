<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210921162338 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add position field in product image';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sylius_product_image ADD position INT DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sylius_product_image DROP position');
    }
}
