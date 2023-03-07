<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211219112938 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add product group table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE product_group (id INT AUTO_INCREMENT NOT NULL, main_taxon INT NOT NULL, code VARCHAR(255) NOT NULL, position INT NOT NULL, INDEX IDX_CC9C3F99F83B90ED (main_taxon), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product_group_products (group_id INT NOT NULL, product_id INT NOT NULL, INDEX IDX_E9E36809FE54D947 (group_id), INDEX IDX_E9E368094584665A (product_id), PRIMARY KEY(group_id, product_id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product_group_translation (id INT AUTO_INCREMENT NOT NULL, translatable_id INT NOT NULL, name VARCHAR(255) NOT NULL, locale VARCHAR(255) NOT NULL, INDEX IDX_255468FD2C2AC5D3 (translatable_id), UNIQUE INDEX product_group_translation_uniq_trans (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE product_group ADD CONSTRAINT FK_CC9C3F99F83B90ED FOREIGN KEY (main_taxon) REFERENCES sylius_taxon (id)');
        $this->addSql('ALTER TABLE product_group_products ADD CONSTRAINT FK_E9E36809FE54D947 FOREIGN KEY (group_id) REFERENCES product_group (id)');
        $this->addSql('ALTER TABLE product_group_products ADD CONSTRAINT FK_E9E368094584665A FOREIGN KEY (product_id) REFERENCES sylius_product (id)');
        $this->addSql('ALTER TABLE product_group_translation ADD CONSTRAINT FK_255468FD2C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES product_group (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product_group_products DROP FOREIGN KEY FK_E9E36809FE54D947');
        $this->addSql('ALTER TABLE product_group_translation DROP FOREIGN KEY FK_255468FD2C2AC5D3');
        $this->addSql('DROP TABLE product_group');
        $this->addSql('DROP TABLE product_group_products');
        $this->addSql('DROP TABLE product_group_translation');
    }
}
