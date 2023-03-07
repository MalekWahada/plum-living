<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211129144041 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update shipping notes field length';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sylius_address CHANGE shipping_notes shipping_notes VARCHAR(150) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sylius_address CHANGE shipping_notes shipping_notes LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`');
    }
}
