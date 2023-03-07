<?php declare(strict_types=1);

namespace App\Alice\Locator;

use Hautelook\AliceBundle\FixtureLocatorInterface;
use Nelmio\Alice\IsAServiceTrait;

final class CustomOrderFilesLocator implements FixtureLocatorInterface
{
    use IsAServiceTrait;

    private FixtureLocatorInterface $decoratedFixtureLocator;
    
    private String $projectDir;

    public function __construct(FixtureLocatorInterface $decoratedFixtureLocator, string $projectDir)
    {
        $this->decoratedFixtureLocator = $decoratedFixtureLocator;
        $this->projectDir = $projectDir.'fixtures/';
    }

    /**
     * Re-order the files found by the decorated finder.
     *
     * {@inheritdoc}
     */
    public function locateFiles(array $bundles, string $environment): array
    {
        return $this->decoratedFixtureLocator->locateFiles($bundles, $environment);
        /*return [
            $this->projectDir.'customer.yaml',
            $this->projectDir.'address.yaml',
            $this->projectDir.'shop_user.yaml',
            $this->projectDir.'order.yaml',
        ];*/
    }
}
