<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211111145317 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Refactor plum project to add any product variant';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE customer_project ADD token VARCHAR(64) DEFAULT NULL, CHANGE option_facade_design option_value_design INT DEFAULT NULL, CHANGE option_facade_finish option_value_finish INT DEFAULT NULL, CHANGE option_facade_color option_value_color INT DEFAULT NULL, CHANGE plumScannerProjectId scanner_project_id VARCHAR(255) NOT NULL, CHANGE plumScannerTransferEmail scanner_transfer_email VARCHAR(255) DEFAULT NULL, CHANGE fetched scanner_fetched TINYINT(1) NOT NULL, CHANGE `status` scanner_status VARCHAR(255) NOT NULL');
        $this->addSql('UPDATE customer_project SET token = scanner_project_id');
        $this->addSql('ALTER TABLE customer_project CHANGE token token VARCHAR(64) NOT NULL, CHANGE scanner_project_id scanner_project_id VARCHAR(64) DEFAULT NULL');
        $this->addSql('ALTER TABLE customer_project_item DROP FOREIGN KEY FK_8594D771166D1F9C');
        $this->addSql('ALTER TABLE customer_project_item ADD CONSTRAINT FK_28522E1F166D1F9C FOREIGN KEY (project_id) REFERENCES customer_project (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE customer_project_item CHANGE ikeaSku ikea_sku VARCHAR(255) DEFAULT NULL, CHANGE ikeaQuantity ikea_quantity INT DEFAULT NULL, CHANGE ikeaUnitPrice ikea_unit_price DOUBLE PRECISION DEFAULT NULL, CHANGE ikeaTotalPrice ikea_total_price DOUBLE PRECISION DEFAULT NULL, CHANGE plumLabel plum_label VARCHAR(255) DEFAULT NULL, CHANGE cabinetReference cabinet_reference VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE customer_project_item_variant DROP sku, DROP syliusId, CHANGE quantity quantity INT NOT NULL');
        $this->addSql('ALTER TABLE erp_entity CHANGE erpid erp_id INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE customer_project DROP token, CHANGE option_value_design option_facade_design INT DEFAULT NULL, CHANGE option_value_finish option_facade_finish INT DEFAULT NULL, CHANGE option_value_color option_facade_color INT DEFAULT NULL, CHANGE scanner_project_id plumScannerProjectId VARCHAR(64) DEFAULT NULL, CHANGE scanner_transfer_email plumScannerTransferEmail VARCHAR(255) DEFAULT NULL, CHANGE scanner_fetched fetched TINYINT(1) NOT NULL, CHANGE scanner_status status VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE customer_project CHANGE plumScannerProjectId plumScannerProjectId VARCHAR(255) NOT NULL'); // Must be split in 2 reqs
        $this->addSql('ALTER TABLE customer_project_item DROP FOREIGN KEY FK_28522E1F166D1F9C');
        $this->addSql('ALTER TABLE customer_project_item ADD CONSTRAINT FK_8594D771166D1F9C FOREIGN KEY (project_id) REFERENCES customer_project (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE customer_project_item CHANGE ikea_sku ikeaSku VARCHAR(255) DEFAULT NULL, CHANGE ikea_quantity ikeaQuantity INT DEFAULT NULL, CHANGE ikea_unit_price ikeaUnitPrice DOUBLE PRECISION DEFAULT NULL, CHANGE ikea_total_price ikeaTotalPrice DOUBLE PRECISION DEFAULT NULL, CHANGE plum_label plumLabel VARCHAR(255) DEFAULT NULL, CHANGE cabinet_reference cabinetReference VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE customer_project_item_variant ADD sku VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, ADD syliusId INT NOT NULL, CHANGE quantity quantity INT DEFAULT NULL');

        $this->addSql('UPDATE customer_project_item_variant
            INNER JOIN sylius_product_variant ON customer_project_item_variant.product_variant_id = sylius_product_variant.id
            SET sku = `code`,
            syliusId = sylius_product_variant.id');
        $this->addSql('ALTER TABLE erp_entity CHANGE erp_id erpId INT NOT NULL');
    }
}
