<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210903164247 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE plum_product_complete_info (id INT AUTO_INCREMENT NOT NULL, product_id INT DEFAULT NULL, enabled TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_3A4EBB7C4584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE plum_product_complete_info_translation (id INT AUTO_INCREMENT NOT NULL, translatable_id INT NOT NULL, content LONGTEXT DEFAULT NULL, locale VARCHAR(255) NOT NULL, INDEX IDX_8C52BF7C2C2AC5D3 (translatable_id), UNIQUE INDEX plum_product_complete_info_translation_uniq_trans (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE plum_product_complete_info ADD CONSTRAINT FK_3A4EBB7C4584665A FOREIGN KEY (product_id) REFERENCES sylius_product (id)');
        $this->addSql('ALTER TABLE plum_product_complete_info_translation ADD CONSTRAINT FK_8C52BF7C2C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES plum_product_complete_info (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE plum_product_complete_info_translation DROP FOREIGN KEY FK_8C52BF7C2C2AC5D3');
        $this->addSql('DROP TABLE plum_product_complete_info');
        $this->addSql('DROP TABLE plum_product_complete_info_translation');
    }
}
