<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210502131304 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Change ERP ManyToMany in OneToOne';
    }

    public function up(Schema $schema): void
    {
        // change product ERPEntity link
        $this->addSql('ALTER TABLE sylius_product ADD erp_entity_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_product ADD CONSTRAINT FK_677B9B745C8D661 FOREIGN KEY (erp_entity_id) REFERENCES erp_entity (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_677B9B745C8D661 ON sylius_product (erp_entity_id)');
        $this->addSql('UPDATE `sylius_product` LEFT JOIN product_erpentity ON product_erpentity.product_id = sylius_product.id SET sylius_product.erp_entity_id = product_erpentity.erpentity_id');
        $this->addSql('DROP TABLE product_erpentity');

        // change Product Variant
        $this->addSql('ALTER TABLE sylius_product_variant ADD erp_entity_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_product_variant ADD CONSTRAINT FK_A29B5235C8D661 FOREIGN KEY (erp_entity_id) REFERENCES erp_entity (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A29B5235C8D661 ON sylius_product_variant (erp_entity_id)');
        $this->addSql('UPDATE `sylius_product_variant` LEFT JOIN productvariant_erpentity ON productvariant_erpentity.productvariant_id = sylius_product_variant.id SET sylius_product_variant.erp_entity_id = productvariant_erpentity.erpentity_id');
        $this->addSql('DROP TABLE productvariant_erpentity');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE product_erpentity (product_id INT NOT NULL, erpentity_id INT NOT NULL, INDEX IDX_23AAC5C5FC0FDA2E (erpentity_id), INDEX IDX_23AAC5C54584665A (product_id), PRIMARY KEY(product_id, erpentity_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE productvariant_erpentity (productvariant_id INT NOT NULL, erpentity_id INT NOT NULL, INDEX IDX_89BBB953FC0FDA2E (erpentity_id), INDEX IDX_89BBB9531855BE3F (productvariant_id), PRIMARY KEY(productvariant_id, erpentity_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE product_erpentity ADD CONSTRAINT FK_23AAC5C54584665A FOREIGN KEY (product_id) REFERENCES sylius_product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_erpentity ADD CONSTRAINT FK_23AAC5C5FC0FDA2E FOREIGN KEY (erpentity_id) REFERENCES erp_entity (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE productvariant_erpentity ADD CONSTRAINT FK_89BBB9531855BE3F FOREIGN KEY (productvariant_id) REFERENCES sylius_product_variant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE productvariant_erpentity ADD CONSTRAINT FK_89BBB953FC0FDA2E FOREIGN KEY (erpentity_id) REFERENCES erp_entity (id) ON DELETE CASCADE');

        $this->addSql('ALTER TABLE sylius_product DROP FOREIGN KEY FK_677B9B745C8D661');
        $this->addSql('DROP INDEX UNIQ_677B9B745C8D661 ON sylius_product');
        $this->addSql('ALTER TABLE sylius_product DROP erp_entity_id');
        $this->addSql('ALTER TABLE sylius_product_variant DROP FOREIGN KEY FK_A29B5235C8D661');
        $this->addSql('DROP INDEX UNIQ_A29B5235C8D661 ON sylius_product_variant');
        $this->addSql('ALTER TABLE sylius_product_variant DROP erp_entity_id');
    }
}
