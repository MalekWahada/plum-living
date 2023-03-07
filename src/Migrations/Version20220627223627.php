<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220627223627 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change column name from channelCode to channel_code and define price as nullable';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product_ikea_channel_pricing CHANGE price price INT DEFAULT NULL, CHANGE channelCode channel_code VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product_ikea_channel_pricing CHANGE price price INT NOT NULL, CHANGE channel_code channelCode VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`');
    }
}
