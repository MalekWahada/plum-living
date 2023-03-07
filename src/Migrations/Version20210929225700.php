<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210929225700 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename sample promotion type in order to add paint samples';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE `sylius_promotion_rule` SET `type` = \'front_sample_promotion\' WHERE `type` = \'sample_promotion\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('UPDATE `sylius_promotion_rule` SET `type` = \'sample_promotion\' WHERE `type` = \'front_sample_promotion\'');
    }
}
