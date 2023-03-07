<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220517213408 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update page translations flag. Add product complete info flag for published translation content';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE monsieurbiz_cms_page CHANGE translations_published translations_published_at VARCHAR(255) DEFAULT NULL');
        $this->addSql('UPDATE monsieurbiz_cms_page SET translations_published_at = null WHERE translations_published_at = 0');
        $this->addSql('UPDATE monsieurbiz_cms_page SET translations_published_at = NOW() WHERE translations_published_at = 1');
        $this->addSql('ALTER TABLE monsieurbiz_cms_page CHANGE translations_published_at translations_published_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE plum_product_complete_info ADD translations_published_at DATETIME DEFAULT NULL, ADD translations_published_content_hash VARCHAR(40) DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_taxon ADD translations_published_at DATETIME DEFAULT NULL, ADD translations_published_content_hash VARCHAR(40) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE monsieurbiz_cms_page CHANGE translations_published_at translations_published_at VARCHAR(255) DEFAULT NULL');
        $this->addSql('UPDATE monsieurbiz_cms_page SET translations_published_at = 1 WHERE translations_published_at IS NOT NULL');
        $this->addSql('UPDATE monsieurbiz_cms_page SET translations_published_at = 0 WHERE translations_published_at IS NULL');
        $this->addSql('ALTER TABLE monsieurbiz_cms_page CHANGE translations_published_at translations_published TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE plum_product_complete_info DROP translations_published_at, DROP translations_published_content_hash');
        $this->addSql('ALTER TABLE sylius_taxon DROP translations_published_at, DROP translations_published_content_hash');
    }
}
