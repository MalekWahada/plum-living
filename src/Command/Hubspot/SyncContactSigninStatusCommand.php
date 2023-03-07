<?php

declare(strict_types=1);

namespace App\Command\Hubspot;

use Exception;
use Noksi\SyliusPlumHubspotPlugin\Service\HubspotNewsletter;
use Noksi\SyliusPlumHubspotPlugin\Service\HubspotClientWrapper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SyncContactSigninStatusCommand extends Command
{
    public const MODE_OPTIN  = 'optin';
    public const MODE_OPTOUT = 'optout';

    protected static $defaultName = 'app:hubspot:sync-signin-status';
    private HubspotNewsletter    $hubspotNewsletter;
    private HubspotClientWrapper $hubspotClientWrapper;


    public function __construct(
        HubspotClientWrapper $hubspotClientWrapper,
        HubspotNewsletter $hubspotNewsletter,
        string                 $name = null
    ) {
        parent::__construct($name);
        $this->hubspotNewsletter = $hubspotNewsletter;
        $this->hubspotClientWrapper = $hubspotClientWrapper;
    }

    protected function configure(): void
    {
        parent::configure();
        $this->addArgument('csvPath', InputArgument::REQUIRED, 'path to csv file');
        $this->addOption('mode', null, InputOption::VALUE_REQUIRED, 'work on OptIn (subscribe if true) column or OptOut column (subscribe if false)', self::MODE_OPTOUT);
        $this->addOption('statusColName', null, InputOption::VALUE_REQUIRED, 'column name with optin/optout value', 'OptOut');
        $this->addOption('emailColName', null, InputOption::VALUE_REQUIRED, 'column name with email', 'Email');
        $this->addOption('separator', null, InputOption::VALUE_REQUIRED, 'column separator in csv', ',');
        $this->addOption('force', '-f', InputOption::VALUE_NONE, 'call hubspot');
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!file_exists($input->getArgument('csvPath'))) {
            $output->writeln('<error>File not found</error>');
            return 1;
        }

        $emailIdx = null;
        $statusIdx = null;

        $handle = fopen($input->getArgument('csvPath'), 'r');

        $headerRow = fgetcsv($handle, 0, $input->getOption('separator'));
        foreach ($headerRow as $idx => $headerVal) {
            if ($headerVal === $input->getOption('statusColName')) {
                $statusIdx = $idx;
            } elseif ($headerVal === $input->getOption('emailColName')) {
                $emailIdx = $idx;
            }
        }

        if (is_null($emailIdx) || is_null($statusIdx)) {
            $output->writeln('<error>Columns not found</error>');
            return 1;
        }

        $emailCollection = [];
        $this->hubspotClientWrapper->useMassClient();
        while ($row = fgetcsv($handle, 0, $input->getOption('separator'))) {
            if ((
                $input->getOption('mode') === self::MODE_OPTIN &&
                    ($row[$statusIdx] === 'TRUE' || $row[$statusIdx] === 1)
            ) || (
                $input->getOption('mode') === self::MODE_OPTOUT &&
                    ($row[$statusIdx] === 'FALSE' || $row[$statusIdx] === 0)
            )
            ) {
                $emailCollection[] = $row[$emailIdx];
            }
        }
        $emailCollection = array_unique($emailCollection);


        if (!$input->getOption('force')) {
            $output->writeln(sprintf('<info>%d adresse(s) to subscribe</info>', count($emailCollection)));
            $output->writeln('<comment>use -f option to really call hubspot</comment>');
            return 0;
        }

        $progressBar = $this->initProgressbar($output, count($emailCollection));
        foreach ($emailCollection as $email) {
            $progressBar->setMessage($email);
            $this->subscribe($email);
            $progressBar->advance();
        }
        $progressBar->finish();
        $output->writeln('');
        $output->writeln('<info>Task completed</info>');
        return 0;
    }

    protected function initProgressbar(OutputInterface $output, int $max): ProgressBar
    {
        ProgressBar::setFormatDefinition('custom', ' %current%/%max% [%bar%] %percent%% | %message%');
        $progressBar = new ProgressBar($output, $max);
        $progressBar->setFormat('custom');
        return $progressBar;
    }

    protected function subscribe(string $email): void
    {
        $this->hubspotNewsletter->subscribe($email, false);
    }
}
