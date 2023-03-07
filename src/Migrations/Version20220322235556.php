<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220322235556 extends AbstractMigration
{
    private const FROM_CODE = 'PLUM_KITCHEN';
    private const TO_CODE = 'PLUM_FR';

    public function getDescription(): string
    {
        return 'Rename main channel code';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE sylius_shipping_method SET configuration = REPLACE(configuration, \'s:'. strlen(self::FROM_CODE) .':"'.self::FROM_CODE.'"\' , \'s:'. strlen(self::TO_CODE) .':"'.self::TO_CODE.'"\')');
        $this->addSql('UPDATE sylius_promotion_rule SET configuration = REPLACE(configuration, \'s:'. strlen(self::FROM_CODE) .':"'.self::FROM_CODE.'"\' , \'s:'. strlen(self::TO_CODE) .':"'.self::TO_CODE.'"\')');
        $this->addSql('UPDATE sylius_promotion_action SET configuration = REPLACE(configuration, \'s:'. strlen(self::FROM_CODE) .':"'.self::FROM_CODE.'"\' , \'s:'. strlen(self::TO_CODE) .':"'.self::TO_CODE.'"\')');
        $this->addSql('UPDATE sylius_channel_pricing SET channel_code = \''. self::TO_CODE .'\' WHERE channel_code = \''. self::FROM_CODE .'\'');
        $this->addSql('UPDATE sylius_channel SET code = \''. self::TO_CODE .'\' WHERE code = \''. self::FROM_CODE .'\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('UPDATE sylius_shipping_method SET configuration = REPLACE(configuration, \'s:'. strlen(self::TO_CODE) .':"'.self::TO_CODE.'"\' , \'s:'. strlen(self::FROM_CODE) .':"'.self::FROM_CODE.'"\')');
        $this->addSql('UPDATE sylius_promotion_rule SET configuration = REPLACE(configuration, \'s:'. strlen(self::TO_CODE) .':"'.self::TO_CODE.'"\' , \'s:'. strlen(self::FROM_CODE) .':"'.self::FROM_CODE.'"\')');
        $this->addSql('UPDATE sylius_promotion_action SET configuration = REPLACE(configuration, \'s:'. strlen(self::TO_CODE) .':"'.self::TO_CODE.'"\' , \'s:'. strlen(self::FROM_CODE) .':"'.self::FROM_CODE.'"\')');
        $this->addSql('UPDATE sylius_channel_pricing SET channel_code = \''. self::FROM_CODE .'\' WHERE channel_code = \''. self::TO_CODE .'\'');
        $this->addSql('UPDATE sylius_channel SET code = \''. self::FROM_CODE .'\' WHERE code = \''. self::TO_CODE .'\'');
    }
}
