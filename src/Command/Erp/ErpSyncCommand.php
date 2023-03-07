<?php

declare(strict_types=1);

namespace App\Command\Erp;

use App\Broker\PlumScannerApiClient;
use App\Erp\NetsuiteSync;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ErpSyncCommand extends Command
{
    const TIMEOUT = 60 * 10; // timout for ERP
    protected static $defaultName = 'app:erp:sync';

    private NetsuiteSync $erp;

    private PlumScannerApiClient $plumScannerApiClient;

    public function __construct(string $name = null, NetsuiteSync $erp, PlumScannerApiClient $plumScannerApiClient)
    {
        parent::__construct($name);
        $this->erp = $erp;
        $this->plumScannerApiClient = $plumScannerApiClient;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Sync ERP products with local products')
            ->setHelp('This command allows you to sync ERP with current product list...')
            ->addArgument('internalIds', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, 'Sync specific internal ids from ERP prefixed with the item type as follow: [a/g/i/k:]id for respectively assembly, group, inventory or kit. The default type is inventory.')
            ->addOption('json', 'j', InputOption::VALUE_NONE, 'Export each synced items content into a JSON file in var/log/ERP/Items.')
            ->addOption('csv', 'c', InputOption::VALUE_NONE, 'Export the list of synced items into a CSV file in var/log/ERP.')
            ->addOption('no-scanner-sync', 's', InputOption::VALUE_NONE, 'Disable Scanner product sync.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // export this into a cron
        // bin/console app:erp:sync >> file_yy-mm-dd.log && mail

        if (empty($internalIds = $input->getArgument('internalIds'))) {
            $this->erp->syncAll($input->getOption('json'), $input->getOption('csv'));
        } else {
            $this->erp->syncSpecificIds($internalIds, $input->getOption('json'), $input->getOption('csv'));
        }

        if (!$input->getOption('no-scanner-sync')) {
            // ping scanner when erp sync is done
            $this->plumScannerApiClient->synchronizeDatabaseWithScanner(
                PlumScannerApiClient::ENDPOINT_UPDATE_INTERNAL_SYLIUS_DATABASE
            );
            $this->plumScannerApiClient->synchronizeDatabaseWithScanner(
                PlumScannerApiClient::ENDPOINT_ASSESS_DB_CONSISTENCY
            );
        }

        // if success
        return 0;
    }
}
