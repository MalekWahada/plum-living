<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220201163636 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add terra club program';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sylius_customer ADD b2b_program TINYINT(1) NOT NULL, ADD company_name VARCHAR(255) DEFAULT NULL, ADD company_instagram VARCHAR(255) DEFAULT NULL, ADD company_website VARCHAR(255) DEFAULT NULL, ADD company_street VARCHAR(255) DEFAULT NULL, ADD company_postcode VARCHAR(255) DEFAULT NULL, ADD company_city VARCHAR(255) DEFAULT NULL, ADD company_country_code VARCHAR(2) DEFAULT NULL, ADD vat_number VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_customer CHANGE plumScannerUserId plum_scanner_user_id VARCHAR(255) DEFAULT NULL, CHANGE howYouKnowAboutUs how_you_know_about_us VARCHAR(255) DEFAULT NULL, CHANGE customerType customer_type VARCHAR(255) DEFAULT NULL, CHANGE howyouknowaboutusdetails how_you_know_about_us_details LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sylius_customer DROP b2b_program, DROP company_name, DROP company_instagram, DROP company_website, DROP company_street, DROP company_postcode, DROP company_city, DROP company_country_code, DROP vat_number');
        $this->addSql('ALTER TABLE sylius_customer CHANGE plum_scanner_user_id plumScannerUserId VARCHAR(255) DEFAULT NULL, CHANGE how_you_know_about_us howYouKnowAboutUs VARCHAR(255) DEFAULT NULL, CHANGE customer_type customerType VARCHAR(255) DEFAULT NULL, CHANGE how_you_know_about_us_details howyouknowaboutusdetails LONGTEXT DEFAULT NULL');
    }
}
