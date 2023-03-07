<?php

declare(strict_types=1);

namespace App\Import;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Importer\ImporterResultInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Importer\ImportResultLoggerInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Importer\ResourceImporter as BaseResourceImporter;
use FriendsOfSylius\SyliusImportExportPlugin\Exception\ImporterException;
use FriendsOfSylius\SyliusImportExportPlugin\Exception\ItemIncompleteException;
use FriendsOfSylius\SyliusImportExportPlugin\Processor\ResourceProcessorInterface;
use Port\Reader\ReaderFactory;
use Psr\Log\LoggerInterface;

class ResourceImporter extends BaseResourceImporter
{
    private ReaderFactory $readerFactory;
    private EntityManagerInterface $entityManager;
    private LoggerInterface $csvImportLogger;

    public function __construct(
        ReaderFactory $readerFactory,
        ObjectManager $objectManager,
        EntityManagerInterface $entityManager,
        ResourceProcessorInterface $resourceProcessor,
        ImportResultLoggerInterface $importerResult,
        LoggerInterface $csvImportLogger,
        int $batchSize,
        bool $failOnIncomplete,
        bool $stopOnFailure
    ) {
        parent::__construct($readerFactory, $objectManager, $resourceProcessor, $importerResult, $batchSize, $failOnIncomplete, $stopOnFailure);
        $this->readerFactory = $readerFactory;
        $this->entityManager = $entityManager;
        $this->csvImportLogger = $csvImportLogger;
    }

    public function import(string $fileName): ImporterResultInterface
    {
        $this->csvImportLogger->info(sprintf('Importing using %s', get_class($this->resourceProcessor)));
        $reader = $this->readerFactory->getReader(new \SplFileObject($fileName));

        $this->result->start();

        $this->entityManager->getConnection()->beginTransaction();
        try {
            foreach ($reader as $i => $row) {
                $this->importData((int) $i, $row);
            }

            if ($this->batchCount) {
                $this->objectManager->flush();
                $this->entityManager->getConnection()->commit();
            }
        } catch (ImporterException|Exception $exception) {
            $this->entityManager->getConnection()->rollBack();

            if ($exception instanceof ConnectionException) {
                $this->result->setMessage($exception->getMessage());
                $this->result->failed(0); // Will show error message
            }
        } finally {
            $this->result->stop();
        }

        $this->csvImportLogger->info(sprintf(
            'Import finished with %s (Time taken in ms: %s, Succeeded %s, Skipped %s, Failed %s)',
            count($this->result->getFailedRows()) > 0 ? 'failure' : 'success',
            $this->result->getDuration(),
            count($this->result->getSuccessRows()),
            count($this->result->getSkippedRows()),
            count($this->result->getFailedRows())
        ));

        return $this->result;
    }

    /**
     * @throws ImporterException
     */
    public function importData(int $i, array $row): bool
    {
        try {
            $this->resourceProcessor->process($row);
            $this->result->success($i);

            ++$this->batchCount;
            if ($this->batchSize && $this->batchCount === $this->batchSize) {
                $this->objectManager->flush();
                $this->objectManager->clear();
                $this->batchCount = 0;
            }
        } catch (ItemIncompleteException $e) {
            $this->csvImportLogger->error(sprintf('Item is incomplete: %s', $e->getMessage()));
            $this->result->setMessage($e->getMessage());
            $this->result->getLogger()->critical($e->getMessage());
            if ($this->failOnIncomplete) {
                $this->result->failed($i);
                if ($this->stopOnFailure) {
                    throw new ImporterException();
                }
            } else {
                $this->result->skipped($i);
            }
        } catch (ImporterException $e) {
            $this->csvImportLogger->error(sprintf('Import exception: %s', $e->getMessage()));
            $this->result->failed($i);
            $this->result->setMessage($e->getMessage());
            $this->result->getLogger()->critical($e->getMessage());
            if ($this->stopOnFailure) {
                throw new ImporterException();
            }
        }

        return false;
    }
}
