<?php

declare(strict_types=1);

namespace App\Tests\Controller\CustomerProject;

use App\Entity\Channel\Channel;
use App\Entity\CustomerProject\Project;
use App\Entity\CustomerProject\ProjectItem;
use App\Entity\Product\ProductOption;
use App\Entity\Product\ProductOptionValue;
use App\Repository\Product\ProductOptionValueRepository;
use App\Tests\DatabaseAwareTrait;
use App\Tests\SecurityAwareTrait;
use Sylius\Component\User\Model\UserInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Routing\Router;

class ProjectBackendTest extends WebTestCase
{
    use SecurityAwareTrait;
    use DatabaseAwareTrait;

    public const USERNAME = 'basic-user@mail.com';
    public const PASSWORD = 'basic-user';
    private const PROJECT_SHARE_USER_EMAIL = 'cart-user@mail.com';

    private KernelBrowser $client;
    private Router $router;
    private string $projectShowUrl;
    private string $locale;
    private ?UserInterface $user;
    private ?Project $project;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->initDatabase($kernel);

        $this->client = self::createClient();
        $this->client->disableReboot();
        $this->router = self::$container->get('router');

        $this->locale = self::$container->get('sylius.context.locale')->getLocaleCode();
        $this->project = self::$container->get('app.repository.project')->findOneBy([]);
        self::assertNotNull($this->project);
        $this->projectShowUrl = $this->router->generate('app_customer_project_show', [
            'token' => $this->project->getToken(),
            '_locale' => $this->locale
        ]);
        $this->user = $this->project->getCustomer()->getUser();
        self::assertNotNull($this->user, 'User not found');
    }

    public function testProjectShow(): void
    {
        // Test restricted project
        $this->client->request('GET', $this->projectShowUrl);
        $locale = $this->locale;
        self::assertResponseRedirects($this->client->getRequest()->getSchemeAndHttpHost() . "/${locale}/login");

        // Test logged user
        $this->loginUser($this->client, $this->user, 'shop');
        $this->client->request('GET', $this->projectShowUrl);
        self::assertResponseStatusCodeSame(200);
    }

    public function testProjectShare(): void
    {
        $shareUser = $this->getShopUser(self::PROJECT_SHARE_USER_EMAIL);
        self::assertNotNull($shareUser, 'Share user not found');
        $this->loginUser($this->client, $shareUser);
        $this->client->request('GET', $this->router->generate('app_customer_project_share', [
            'token' => $this->project->getToken(),
            '_locale' => $this->locale
        ]));
        $latestProjects = $this->entityManager->getRepository(Project::class)->findBy([], ['id' => 'DESC'], 1, 0);
        self::assertNotEmpty($latestProjects);
        $clonedProject = $latestProjects[0];
        self::assertNotSame($this->project, $clonedProject, 'Project has not been cloned');

        self::assertResponseRedirects($this->router->generate('app_customer_project_show', [
            'token' => $clonedProject->getToken(),
            '_locale' => $this->locale
        ]), 302, 'Invalid cloned address for the project');
        self::assertSame($this->project->getItems()->count(), $clonedProject->getItems()->count(), 'Project has not the same number of items');
    }

    /**
     * This test will get a project and get details for each item.
     * The first option of each select will be used
     * @throws \JsonException
     */
    public function testBulkProjectItemDetails(): void
    {
        $this->loginUser($this->client, $this->user);
        $crawler = $this->client->request('GET', $this->projectShowUrl);

        $items = $crawler->filter('.ps-project-item');
        self::assertNotEmpty($items, 'No items found in project');

        $itemsData = [];
        $items->each(function (Crawler $node) use (&$itemsData) {
            $item = [
                'itemId' => $node->filter('.ps-project-item__id')->attr('value'),
            ];
            self::assertNotEmpty($item['itemId'], 'Item id is empty');

            $options = $this->getItemOptionsFromNode($node);
            !empty($options['designs']) && $item['design'] = $options['designs'][0];
            !empty($options['finishes']) && $item['finish'] = $options['finishes'][0];
            !empty($options['colors']) && $item['color'] = $options['colors'][0];
            !empty($options['variants']) && $item['variant'] = $options['variants'][0];
            $itemsData[] = $item;
        });

        $requestPayload = [
            'channelCode' => Channel::DEFAULT_CODE,
            'itemsData' => $itemsData
        ];

        $this->client->xmlHttpRequest('POST', $this->router->generate('app_customer_project_bulk_item_details', [
            'token' => $this->project->getToken(),
            '_locale' => $this->locale
        ]), $requestPayload);
        self::assertResponseStatusCodeSame(200);

        $response = $this->client->getResponse()->getContent();
        $response = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
        self::assertNotEmpty($response, 'Response is empty');

        // Process each result. Each options must be valid
        foreach ($response as $id => $item) {
            self::assertArrayNotHasKey('error', $item, "Item id ${id} has returned an error");
            self::assertNotEmpty($item, "Item id ${id} is empty");
            self::assertArrayHasKey('validOptions', $item, "Item id ${id} has no valid options array");
            self::assertArrayHasKey('unitPrice', $item, "Item id ${id} has no valid price");
            foreach ($item['validOptions'] as $optionName => $option) {
                self::assertTrue($option, "Item id ${id} has option ${optionName} not valid");
            }
        }
    }

    /**
     * Generate a save payload from the project show page. Select the first option for each item.
     * @todo: Handle removed and new items
     * @todo: yield for invalid payload types that should return a 400
     * @throws \JsonException
     */
    public function testProjectAutoSave(): void
    {
        /** @var ProductOptionValueRepository $optionValueRepository */
        $optionValueRepository = $this->entityManager->getRepository(ProductOptionValue::class);

        $this->loginUser($this->client, $this->user);
        $crawler = $this->client->request('GET', $this->projectShowUrl);

        $items = $crawler->filter('.ps-project-item');
        self::assertNotEmpty($items, 'No items found in project');

        $updatedItems = [];
        $items->each(function (Crawler $node) use (&$updatedItems) {
            $item = [
                'itemId' => $node->filter('.ps-project-item__id')->attr('value'),
                'quantity' => (int)$node->filter('.ps-project-item__quantity')->attr('value'),
            ];
            self::assertNotEmpty($item['itemId'], 'Item id is empty');
            self::assertNotEmpty($item['quantity'], 'Item quantity is empty');

            $options = $this->getItemSelectedOptionsFromNode($node);
            !empty($options['design']) && $item['design'] = $options['design'];
            !empty($options['finish']) && $item['finish'] = $options['finish'];
            !empty($options['color']) && $item['color'] = $options['color'];
            !empty($options['variant']) && $item['variant'] = $options['variant'];
            $updatedItems[] = $item;
        });

        $designs = $optionValueRepository->findByOptionCode(ProductOption::PRODUCT_OPTION_DESIGN);
        $finishes = $optionValueRepository->findByOptionCode(ProductOption::PRODUCT_OPTION_FINISH);
        $colors = $optionValueRepository->findByOptionCode(ProductOption::PRODUCT_OPTION_COLOR);
        $globalDesign = $designs[array_rand($designs, 1)];
        $globalFinish = $finishes[array_rand($finishes, 1)];
        $globalColor = $colors[array_rand($colors, 1)];
        self::assertNotNull($globalDesign, 'Global design option is not found');
        self::assertNotNull($globalFinish, 'Global finish option is not found');
        self::assertNotNull($globalColor, 'Global color option is not found');

        $this->client->xmlHttpRequest('POST', $this->router->generate('app_customer_project_auto_save', [
            'token' => $this->project->getToken(),
            '_locale' => $this->locale
        ]), [
            'name' => 'New project name',
            'globalOptions' => [
                'design' => (string)$globalDesign->getCode(),
                'finish' => (string)$globalFinish->getCode(),
                'color' => (string)$globalColor->getCode(),
            ],
            'updatedItems' => $updatedItems
        ]);

        // Assert result
        self::assertResponseStatusCodeSame(200);
        /** @var Project $project */
        $project = $this->entityManager->getRepository(Project::class)->findOneBy(['token' => $this->project->getToken()]); // Reload project
        self::assertNotNull($project);
        self::assertSame('New project name', $project->getName());
        self::assertSame($globalDesign, $project->getDesign());
        self::assertSame($globalFinish, $project->getFinish());
        self::assertSame($globalColor, $project->getColor());

        $response = $this->client->getResponse()->getContent();
        $response = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
        self::assertNotEmpty($response, 'Response is empty');
        self::assertArrayHasKey('updatedItems', $response, 'Response has no updated items');

        // Assert items
        foreach ($response['updatedItems'] as $id => $item) {
            self::assertSame('SUCCESS', $item, "Item id ${id} has returned an error");
            $validItemFound = $project->getItems()->exists(function (int $key, ProjectItem $projectItem) use ($id) {
                return $projectItem->getId() === (int)$id && null !== $projectItem->getChosenVariant();
            });
            self::assertTrue($validItemFound, "Item id ${id} is not valid");
        }
    }

    public function testProjectAddToCart(): void
    {
        $this->loginUser($this->client, $this->user);
        $crawler = $this->client->request('GET', $this->projectShowUrl);
        self::assertResponseStatusCodeSame(200);

        // Remove cart before checkout
        $this->client->submitForm('app_project[addToCart]');

        self::assertResponseRedirects($this->router->generate('sylius_shop_cart_summary'));
    }

    /**
     * TODO make it work again
     */
    public function testProjectCheckout(): void
    {
//        $this->loginUser($this->client, $this->user);
//        self::$container->get('request_stack')->push($this->client->getRequest());
//
//        $cartContext = self::$container->get('sylius.context.cart');
//        $cart = $cartContext->getCart();
//        if (null !== $cart) {
//            $cart = self::$container->get('sylius.context.cart.new')->getCart();
//        }
//        $token = self::$container->get('security.csrf.token_manager')->getToken($cart->getId() ?? '');
//        $this->client->request('DELETE', $this->router->generate('sylius_shop_cart_clear'), [
//            '_csrf_token' => $token->getValue(),
//        ]);
//        self::assertResponseRedirects($this->router->generate('sylius_shop_cart_summary'));
//
//        $this->client->request('GET', $this->router->generate('app_customer_project_checkout', [
//            'token' => $this->project->getToken(),
//            '_locale' => $this->locale
//        ]));
//
//        self::assertCount($this->project->getItems()->count(), $cartContext->getCart()->getItems(), 'Cart has not the same number of project items');
    }

    private function getItemSelectedOptionsFromNode(Crawler $node): array
    {
        return [
            'design' => array_values(array_filter($node->filter('.ps-project-item__design-field')->filter('option[selected]')->extract(['value']), static fn($value) => !empty($value)))[0] ?? null,
            'finish' => array_values(array_filter($node->filter('.ps-project-item__finish-field')->filter('option[selected]')->extract(['value']), static fn($value) => !empty($value)))[0] ?? null,
            'color' => array_values(array_filter($node->filter('.ps-project-item__color-field')->filter('option[selected]')->extract(['value']), static fn($value) => !empty($value)))[0] ?? null,
            'variant' => array_values(array_filter($node->filter('.ps-project-item__variant-field')->filter('option[selected]')->extract(['value']), static fn($value) => !empty($value)))[0] ?? null
        ];
    }

    private function getItemOptionsFromNode(Crawler $node): array
    {
        return [
            'designs' => array_values(array_filter($node->filter('.ps-project-item__design-field')->filter('option')->extract(['value']), static fn($value) => !empty($value))),
            'finishes' => array_values(array_filter($node->filter('.ps-project-item__finish-field')->filter('option')->extract(['value']), static fn($value) => !empty($value))),
            'colors' => array_values(array_filter($node->filter('.ps-project-item__color-field')->filter('option')->extract(['value']), static fn($value) => !empty($value))),
            'variants' => array_values(array_filter($node->filter('.ps-project-item__variant-field')->filter('option')->extract(['value']), static fn($value) => !empty($value)))
        ];
    }
}
