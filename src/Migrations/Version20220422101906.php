<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220422101906 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add CMS page options';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE monsieurbiz_cms_page_option (id INT AUTO_INCREMENT NOT NULL, page_id INT DEFAULT NULL, type VARCHAR(255) NOT NULL, value VARCHAR(255) NOT NULL, INDEX IDX_E6D0EA37C4663E4 (page_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE monsieurbiz_cms_page_option ADD CONSTRAINT FK_E6D0EA37C4663E4 FOREIGN KEY (page_id) REFERENCES monsieurbiz_cms_page (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE monsieurbiz_cms_page_option');
    }
}
