<?php
declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use Webmozart\Assert\Assert;

class CsvToYamlCombinationsFixtureCommand extends Command
{
    protected static $defaultName = 'app:fixtures:generate:combinations-from-csv';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        file_put_contents(
            'config/fixtures_suites/setup_combinations.yaml',
            "#file generated via the command: " . self::$defaultName . PHP_EOL . $this->prepareYamlFile()
        );

        return 0;
    }

    public function prepareYamlFile(): string
    {
        $file = "fixtures/fixturesCombinations.csv";
        Assert::fileExists($file, 'The file %s does not exist');

        $fileArray = file($file);
        \assert(is_array($fileArray));

        $csv = array_map('str_getcsv', $fileArray);
        array_walk($csv, function (&$a) use ($csv) {
            $a = array_combine($csv[0], $a);
        });
        array_shift($csv); # remove column header

        $yamlContentArray = [
            'sylius_fixtures' => [
                'suites' => [
                    'setup_combinations' => [
                        'fixtures' => [
                            'combination' => [
                                'options' => [
                                    'custom' => $csv,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return Yaml::dump($yamlContentArray, 9);
    }
}
