<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220322235551 extends AbstractMigration
{
    private const FROM_LOCALE = 'fr_FR';
    private const TO_LOCALE = 'fr';

    public function getDescription(): string
    {
        return 'Update locale code for internationalization';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE monsieurbiz_cms_page ADD reference_locale_code VARCHAR(12) DEFAULT \''.self::TO_LOCALE.'\' NOT NULL, ADD translations_published TINYINT(1) DEFAULT \'0\' NOT NULL');

        $this->addSql('UPDATE sylius_admin_user SET locale_code = \''.self::TO_LOCALE.'\' WHERE locale_code = \''.self::FROM_LOCALE.'\'');
        $this->addSql('UPDATE sylius_locale SET code = \''.self::TO_LOCALE.'\' WHERE code = \''.self::FROM_LOCALE.'\'');
        $this->addSql('UPDATE sylius_order SET locale_code = \''.self::TO_LOCALE.'\' WHERE locale_code = \''.self::FROM_LOCALE.'\'');

        $this->addSql('UPDATE monsieurbiz_cms_page_translation SET locale = \''.self::TO_LOCALE.'\' WHERE locale = \''.self::FROM_LOCALE.'\'');
        $this->addSql('UPDATE plum_product_complete_info_translation SET locale = \''.self::TO_LOCALE.'\' WHERE locale = \''.self::FROM_LOCALE.'\'');
        $this->addSql('UPDATE product_group_translation SET locale = \''.self::TO_LOCALE.'\' WHERE locale = \''.self::FROM_LOCALE.'\'');
        $this->addSql('UPDATE sylius_payment_method_translation SET locale = \''.self::TO_LOCALE.'\' WHERE locale = \''.self::FROM_LOCALE.'\'');
        $this->addSql('UPDATE sylius_product_association_type_translation SET locale = \''.self::TO_LOCALE.'\' WHERE locale = \''.self::FROM_LOCALE.'\'');
        $this->addSql('UPDATE sylius_product_attribute_translation SET locale = \''.self::TO_LOCALE.'\' WHERE locale = \''.self::FROM_LOCALE.'\'');
        $this->addSql('UPDATE sylius_product_attribute_translation SET locale = \''.self::TO_LOCALE.'\' WHERE locale = \''.self::FROM_LOCALE.'\'');
        $this->addSql('UPDATE sylius_product_option_translation SET locale = \''.self::TO_LOCALE.'\' WHERE locale = \''.self::FROM_LOCALE.'\'');
        $this->addSql('UPDATE sylius_product_option_value_translation SET locale = \''.self::TO_LOCALE.'\' WHERE locale = \''.self::FROM_LOCALE.'\'');
        $this->addSql('UPDATE sylius_product_translation SET locale = \''.self::TO_LOCALE.'\' WHERE locale = \''.self::FROM_LOCALE.'\'');
        $this->addSql('UPDATE sylius_product_variant_translation SET locale = \''.self::TO_LOCALE.'\' WHERE locale = \''.self::FROM_LOCALE.'\'');
        $this->addSql('UPDATE sylius_shipping_method_translation SET locale = \''.self::TO_LOCALE.'\' WHERE locale = \''.self::FROM_LOCALE.'\'');
        $this->addSql('UPDATE sylius_taxon_translation SET locale = \''.self::TO_LOCALE.'\' WHERE locale = \''.self::FROM_LOCALE.'\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE monsieurbiz_cms_page DROP reference_locale_code, DROP translations_published');

        $this->addSql('UPDATE sylius_admin_user SET locale_code = \''.self::FROM_LOCALE.'\' WHERE locale_code = \''.self::TO_LOCALE.'\'');
        $this->addSql('UPDATE sylius_locale SET code = \''.self::FROM_LOCALE.'\' WHERE code = \''.self::TO_LOCALE.'\'');
        $this->addSql('UPDATE sylius_order SET locale_code = \''.self::FROM_LOCALE.'\' WHERE locale_code = \''.self::TO_LOCALE.'\'');

        $this->addSql('UPDATE monsieurbiz_cms_page_translation SET locale = \''.self::FROM_LOCALE.'\' WHERE locale = \''.self::TO_LOCALE.'\'');
        $this->addSql('UPDATE plum_product_complete_info_translation SET locale = \''.self::FROM_LOCALE.'\' WHERE locale = \''.self::TO_LOCALE.'\'');
        $this->addSql('UPDATE product_group_translation SET locale = \''.self::FROM_LOCALE.'\' WHERE locale = \''.self::TO_LOCALE.'\'');
        $this->addSql('UPDATE sylius_payment_method_translation SET locale = \''.self::FROM_LOCALE.'\' WHERE locale = \''.self::TO_LOCALE.'\'');
        $this->addSql('UPDATE sylius_product_association_type_translation SET locale = \''.self::FROM_LOCALE.'\' WHERE locale = \''.self::TO_LOCALE.'\'');
        $this->addSql('UPDATE sylius_product_attribute_translation SET locale = \''.self::FROM_LOCALE.'\' WHERE locale = \''.self::TO_LOCALE.'\'');
        $this->addSql('UPDATE sylius_product_attribute_translation SET locale = \''.self::FROM_LOCALE.'\' WHERE locale = \''.self::TO_LOCALE.'\'');
        $this->addSql('UPDATE sylius_product_option_translation SET locale = \''.self::FROM_LOCALE.'\' WHERE locale = \''.self::TO_LOCALE.'\'');
        $this->addSql('UPDATE sylius_product_option_value_translation SET locale = \''.self::FROM_LOCALE.'\' WHERE locale = \''.self::TO_LOCALE.'\'');
        $this->addSql('UPDATE sylius_product_translation SET locale = \''.self::FROM_LOCALE.'\' WHERE locale = \''.self::TO_LOCALE.'\'');
        $this->addSql('UPDATE sylius_product_variant_translation SET locale = \''.self::FROM_LOCALE.'\' WHERE locale = \''.self::TO_LOCALE.'\'');
        $this->addSql('UPDATE sylius_shipping_method_translation SET locale = \''.self::FROM_LOCALE.'\' WHERE locale = \''.self::TO_LOCALE.'\'');
        $this->addSql('UPDATE sylius_taxon_translation SET locale = \''.self::FROM_LOCALE.'\' WHERE locale = \''.self::TO_LOCALE.'\'');
    }
}
