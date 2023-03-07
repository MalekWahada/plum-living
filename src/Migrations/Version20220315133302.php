<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220315133302 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add theme field on CMS Page';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE monsieurbiz_cms_page ADD theme VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE monsieurbiz_cms_page DROP theme');
    }
}
