<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220530122921 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add product ikea table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE product_ikea (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product_ikea_image (id INT AUTO_INCREMENT NOT NULL, product_ikea_id INT NOT NULL, type VARCHAR(255) DEFAULT NULL, path VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_C824A7707BE04618 (product_ikea_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product_ikea_translation (id INT AUTO_INCREMENT NOT NULL, translatable_id INT NOT NULL, name VARCHAR(255) NOT NULL, price INT DEFAULT 0 NOT NULL, locale VARCHAR(255) NOT NULL, INDEX IDX_50DD9C8C2C2AC5D3 (translatable_id), UNIQUE INDEX product_ikea_translation_uniq_trans (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE product_ikea_image ADD CONSTRAINT FK_C824A7707BE04618 FOREIGN KEY (product_ikea_id) REFERENCES product_ikea (id)');
        $this->addSql('ALTER TABLE product_ikea_translation ADD CONSTRAINT FK_50DD9C8C2C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES product_ikea (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product_ikea_image DROP FOREIGN KEY FK_C824A7707BE04618');
        $this->addSql('ALTER TABLE product_ikea_translation DROP FOREIGN KEY FK_50DD9C8C2C2AC5D3');
        $this->addSql('DROP TABLE product_ikea');
        $this->addSql('DROP TABLE product_ikea_image');
        $this->addSql('DROP TABLE product_ikea_translation');
    }
}
