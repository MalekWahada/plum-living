<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Panther\Client as PantherClient;
use Symfony\Component\Panther\PantherTestCase;

final class PaymentTest extends PantherTestCase
{
    private PantherClient $client;

    protected function setUp(): void
    {
        $this->client = self::createPantherClient();
    }

    public function test(): void
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

        $crawler = $this->client->request('GET', '/fr/cart');

        self::assertSelectorExists('#sylius-cart-grand-total');
        self::assertGreaterThan(0, (int) $crawler->filter('#sylius-cart-grand-total')->text());

        $this->client->clickLink('Procéder au paiement');
        $crawler = $this->client->waitFor(
            '#sylius-shipping-address'
        );

        self::assertSelectorExists('#sylius-shipping-address');

        $form = $crawler->filter('.checkout-order-summary-panel button[type=submit]')->form();

        $this->client->submit(
            $form,
            [
                'sylius_checkout_address[shippingAddress][firstName]' => 'Jean',
                'sylius_checkout_address[shippingAddress][lastName]' => 'Doe',
                'sylius_checkout_address[shippingAddress][phoneNumber][number]' => '0142424242',
                'sylius_checkout_address[shippingAddress][street]' => '10 rue de la paix',
                'sylius_checkout_address[shippingAddress][postcode]' => '75001',
                'sylius_checkout_address[shippingAddress][city]' => 'Paris',
                'sylius_checkout_address[shippingAddress][countryCode]' => 'FR',
            ]
        );

        $crawler = $this->client->request('GET', '/fr/checkout/select-shipping');
        self::assertSelectorExists('#sylius_checkout_select_shipping');

        $form = $crawler->filter('.checkout-order-summary-panel button[type=submit]')->form();
        $this->client->submit(
            $form
        );

        $crawler = $this->client->request('GET', '/fr/checkout/select-payment');
        self::assertSelectorExists('#sylius_checkout_select_payment');

        $form = $crawler->filter('.checkout-order-summary-panel button[type=submit]')->form();
        $crawler = $this->client->submit(
            $form
        );

        self::assertSelectorExists('#sylius_checkout_complete');
        self::assertSame(
            'VALIDER ET PAYER MA COMMANDE',
            $crawler->filter('.checkout-order-summary-panel button[type=submit]')->text()
        );

        $form = $crawler->filter('.checkout-order-summary-panel button[type=submit]')->form();
        $this->client->submit(
            $form
        );
    }

    private function logIn(): Crawler
    {
        $this->client->request('GET', '/fr/login');
        return $this->client->submitForm('Connexion', [
            '_username' => 'basic-user@mail.com',
            '_password' => 'basic-user',
        ]);
    }
}
