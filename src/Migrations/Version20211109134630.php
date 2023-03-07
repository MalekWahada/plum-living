<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211109134630 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename plum scanner project to customer project';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('RENAME TABLE plum_scanner_project_item TO customer_project_item');
        $this->addSql('RENAME TABLE plum_scanner_project_item_variant TO customer_project_item_variant');
        $this->addSql('RENAME TABLE plum_scanner_project TO customer_project');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('RENAME TABLE customer_project_item TO plum_scanner_project_item');
        $this->addSql('RENAME TABLE customer_project_item_variant TO plum_scanner_project_item_variant');
        $this->addSql('RENAME TABLE customer_project TO plum_scanner_project');
    }
}
