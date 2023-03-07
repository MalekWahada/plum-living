<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210907142302 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE plum_scanner_project CHANGE plumScannerTransferEmail plumScannerTransferEmail VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE plum_scanner_project CHANGE plumScannerTransferEmail plumScannerTransferEmail VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`');
        $this->addSql('UPDATE plum_scanner_project SET plumScannerTransferEmail= "" WHERE plumScannerTransferEmail is null');
    }
}
