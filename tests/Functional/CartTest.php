<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Panther\Client as PantherClient;
use Symfony\Component\Panther\PantherTestCase;

final class CartTest extends PantherTestCase
{
    private PantherClient $client;

    protected function setUp(): void
    {
        $this->client = self::createPantherClient();
    }

    public function testPreviousCartIsReplacedByGuestCartOnLogin(): void
    {
        $this->logIn();

        $crawler = $this->client->request('GET', '/fr/accessoires');
        $itemsCount = (int) $crawler->filter('.cart-toggle__count')->text();

        $crawler->filter('#Accessoires .product-modal')->first()->click();
        $this->client->waitForVisibility('#tunnel-modal .tunnel-modal-form__button');
        $crawler->filter('#tunnel-modal #sylius-product-adding-to-cart')->submit();
        $this->client->wait(2)->until(
            WebDriverExpectedCondition::elementTextIs(
                WebDriverBy::cssSelector('.cart-toggle__count'),
                (string) ($itemsCount + 1)
            )
        );
        $initialCartId = $crawler->filter('#plum-cart-summary-form input[name=cart_id]')->getAttribute('value');

        $this->client->request('GET', '/fr/logout');
        $this->client->wait(2)->until(
            WebDriverExpectedCondition::elementTextIs(
                WebDriverBy::cssSelector('.cart-toggle__count'),
                '0'
            )
        );

        $crawler = $this->client->request('GET', '/fr/accessoires');
        $crawler->filter('#Accessoires .product-modal')->first()->click();
        $this->client->waitForVisibility('#tunnel-modal .tunnel-modal-form__button');
        $crawler->filter('#tunnel-modal #sylius-product-adding-to-cart:first-of-type')->submit();
        $this->client->wait(2)->until(
            WebDriverExpectedCondition::elementTextIs(
                WebDriverBy::cssSelector('.cart-toggle__count'),
                '1'
            )
        );

        $crawler = $this->logIn();

        self::assertNotSame(
            $initialCartId,
            $crawler->filter('#plum-cart-summary-form input[name=cart_id]')->getAttribute('value')
        );

        $crawler->filter('.sylius-cart-button.cart-toggle')->click();
        $this->client->waitForVisibility('#ClearCartButton');
        $crawler->selectButton('Vider mon panier')->click();
        $this->client->waitForInvisibility('#ClearCartButton');
        self::assertSelectorNotExists('#plum-cart-summary-form input[name=cart_id]');
    }

    private function logIn(): Crawler
    {
        $this->client->request('GET', '/fr/login');
        return $this->client->submitForm('Connexion', [
            '_username' => 'cart-user@mail.com',
            '_password' => 'cart-user',
        ]);
    }
}
