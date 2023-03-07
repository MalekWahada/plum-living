<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220201163840 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update promotion coupon to link a customer';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sylius_promotion_coupon ADD customer_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_promotion_coupon ADD CONSTRAINT FK_B04EBA859395C3F3 FOREIGN KEY (customer_id) REFERENCES sylius_customer (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B04EBA859395C3F3 ON sylius_promotion_coupon (customer_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sylius_promotion_coupon DROP FOREIGN KEY FK_B04EBA859395C3F3');
        $this->addSql('DROP INDEX UNIQ_B04EBA859395C3F3 ON sylius_promotion_coupon');
        $this->addSql('ALTER TABLE sylius_promotion_coupon DROP customer_id');
    }
}
