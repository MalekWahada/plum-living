<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210505074349 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'add product_info field taxon_translation entity';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE sylius_taxon_translation ADD product_info LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE sylius_taxon_translation DROP product_info');
    }
}
