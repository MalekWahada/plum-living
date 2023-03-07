<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220317172939 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create page theme table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE monsieurbiz_cms_page_theme (id INT AUTO_INCREMENT NOT NULL, page_id INT DEFAULT NULL, theme INT NOT NULL, INDEX IDX_A71C7AB9C4663E4 (page_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE monsieurbiz_cms_page_theme ADD CONSTRAINT FK_A71C7AB9C4663E4 FOREIGN KEY (page_id) REFERENCES monsieurbiz_cms_page (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE monsieurbiz_cms_page DROP theme');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE monsieurbiz_cms_page_theme');
        $this->addSql('ALTER TABLE monsieurbiz_cms_page ADD theme VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`');
    }
}
