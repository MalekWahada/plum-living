<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220622211306 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add product ikea channel pricing table and indexes';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE product_ikea_channel_pricing (id INT AUTO_INCREMENT NOT NULL, product_ikea_id INT NOT NULL, price INT NOT NULL, channelCode VARCHAR(255) NOT NULL, INDEX IDX_33EE3E867BE04618 (product_ikea_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE product_ikea_channel_pricing ADD CONSTRAINT FK_33EE3E867BE04618 FOREIGN KEY (product_ikea_id) REFERENCES product_ikea (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX product_ikea_code ON product_ikea (code)');
        $this->addSql('ALTER TABLE product_ikea_image DROP FOREIGN KEY FK_C824A7707BE04618');
        $this->addSql('ALTER TABLE product_ikea_image ADD CONSTRAINT FK_C824A7707BE04618 FOREIGN KEY (product_ikea_id) REFERENCES product_ikea (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_ikea_translation DROP price');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE product_ikea_channel_pricing');
        $this->addSql('DROP INDEX product_ikea_code ON product_ikea');
        $this->addSql('ALTER TABLE product_ikea_image DROP FOREIGN KEY FK_C824A7707BE04618');
        $this->addSql('ALTER TABLE product_ikea_image ADD CONSTRAINT FK_C824A7707BE04618 FOREIGN KEY (product_ikea_id) REFERENCES product_ikea (id)');
        $this->addSql('ALTER TABLE product_ikea_translation ADD price INT DEFAULT 0 NOT NULL');
    }
}
