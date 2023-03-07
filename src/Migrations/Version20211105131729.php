<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211105131729 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add cms page position field';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE monsieurbiz_cms_page ADD position INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE monsieurbiz_cms_page DROP position');
    }
}
