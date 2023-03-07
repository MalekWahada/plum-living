<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220406152449 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Hubspot integration required fields';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sylius_customer ADD crm_signature VARCHAR(40) DEFAULT NULL, ADD crm_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_order ADD crm_signature VARCHAR(40) DEFAULT NULL, ADD crm_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_payment ADD reminded_at DATETIME DEFAULT NULL, CHANGE wiredetails wire_details LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE sylius_product_variant ADD crm_signature VARCHAR(40) DEFAULT NULL, ADD crm_id VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sylius_customer DROP crm_signature, DROP crm_id');
        $this->addSql('ALTER TABLE sylius_order DROP crm_signature, DROP crm_id');
        $this->addSql('ALTER TABLE sylius_payment DROP reminded_at, CHANGE wire_details wireDetails LONGTEXT CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci` COMMENT \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE sylius_product_variant DROP crm_signature, DROP crm_id');
    }
}
