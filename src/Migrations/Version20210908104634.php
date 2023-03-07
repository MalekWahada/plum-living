<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210908104634 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add mailing workflow field column in order';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sylius_order CHANGE minDateDelivery min_date_delivery DATE DEFAULT NULL, CHANGE maxDateDelivery max_date_delivery DATE DEFAULT NULL, ADD mailing_workflow_type VARCHAR(32) DEFAULT NULL, CHANGE targeted_room targeted_room VARCHAR(32) DEFAULT NULL, CHANGE erpregistered erp_registered INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sylius_order CHANGE min_date_delivery minDateDelivery DATE DEFAULT NULL, CHANGE max_date_delivery maxDateDelivery DATE DEFAULT NULL, DROP mailing_workflow_type, CHANGE targeted_room targeted_room VARCHAR(63) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, CHANGE erp_registered erpRegistered INT DEFAULT NULL');
    }
}
