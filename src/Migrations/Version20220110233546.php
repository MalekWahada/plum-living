<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220110233546 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add customer project global comment';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE customer_project ADD comment VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE customer_project DROP comment');
    }
}
