<?php

declare(strict_types=1);

namespace App\Tests\Functional\CustomerProject;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Panther\Client as PantherClient;
use Symfony\Component\Panther\PantherTestCase;

class AccountProjectTest extends PantherTestCase
{
    private PantherClient $client;
    public const USERNAME = 'basic-user@mail.com';
    public const PASSWORD = 'basic-user';

    protected function setUp(): void
    {
        $this->client = self::createPantherClient([]);
        $this->logIn();
    }

    public function testOpenProjectsList(): void
    {
        $crawler = $this->client->request('GET', '/fr/account/projects');
        $this->assertSame(200, $this->client->getInternalResponse()->getStatusCode());
        $this->client->waitForVisibility('.small-link-button');

        self::assertSelectorWillContain('.small-link-button', 'Voir mon projet');
    }

    public function testDuplicateDeleteProjectsList(): void
    {
        $crawler = $this->client->request('GET', '/fr/account/projects');
        $this->assertSame(200, $this->client->getInternalResponse()->getStatusCode());
        $this->client->waitForVisibility('.small-link-button');

        $projetsCount = ($crawler->filter('.panel__content .baseline tr')->count() + 1) / 3; // 3 lines per project unlike the last one

        $crawler->selectLink('Dupliquer mon projet')->click();
        $crawler = $this->client->waitForVisibility('.small-link-button');
        $newProjetsCount = ($crawler->filter('.panel__content .baseline tr')->count() + 1) / 3;
        self::assertSame($projetsCount + 1, $newProjetsCount);

        $crawler->selectLink('Supprimer mon projet')->click();
        $crawler = $this->client->waitForVisibility('.small-link-button');
        $newProjetsCount = ($crawler->filter('.panel__content .baseline tr')->count() + 1) / 3;
        self::assertSame($projetsCount, $newProjetsCount);
    }

    public function testCreateProject(): void
    {
        $crawler = $this->client->request('GET', '/fr/account/projects/create');
        $this->assertSame(200, $this->client->getInternalResponse()->getStatusCode());
        $this->client->waitForVisibility(".ps-project");
        self::assertSelectorExists(".ps-project");
    }

    private function logIn(): Crawler
    {
        $this->client->request('GET', '/fr/login');
        self::assertSelectorExists('.sylius_shop_login', 'Login form is not found');
        return $this->client->submitForm('Connexion', [
            '_username' => self::USERNAME,
            '_password' => self::PASSWORD,
        ]);
    }
}
