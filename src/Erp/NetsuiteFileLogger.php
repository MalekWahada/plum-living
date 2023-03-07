<?php


namespace App\Erp;

use App\Model\Erp\ErpItemModel;
use Exception;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

class NetsuiteFileLogger
{
    private Filesystem $filesystem;
    private string $logPath;
    private string $csvFilename;
    private bool $csvHeaderCreated = false;

    public function __construct(
        KernelInterface $kernel
    ) {
        $this->filesystem = new Filesystem();
        $this->logPath = $kernel->getLogDir() . '/ERP';
        $this->csvFilename = $this->logPath . "/Erp_items_" . date("d-m-Y_h-i-s") . ".csv";
    }

    /**
     * Save ERP information to dump files for debug.
     * @param ErpItemModel|null $erpItem
     * @return bool
     */
    public function logErpModel(?ErpItemModel $erpItem): bool
    {
        try {
            if (null !== $erpItem && $erpItem->isValid()) {
                $this->filesystem->dumpFile($this->logPath . '/Items/Erp_item_' . $erpItem->getId() . '.json', json_encode((array)$erpItem, JSON_THROW_ON_ERROR));
                return true;
            }
        } catch (Exception $e) {
            dump($e);
        }
        return false;
    }

    public function logErpModelToCsv(?ErpItemModel $erpItem): bool
    {
        try {
            if (null !== $erpItem && $erpItem->isValid()) {
                if (!$this->csvHeaderCreated) {
                    $this->filesystem->appendToFile($this->csvFilename, "ID;TYPE;CODE;PARENT_ID;SYLIUS_RESULT;IS_SKIPPED;IS_ENABLED;IS_ONLINE" . PHP_EOL);
                    $this->csvHeaderCreated = true;
                }

                $out = sprintf("%s;%s;%s;%s;%s;%d;%d;%d" . PHP_EOL, $erpItem->getId(), $erpItem->getType(), $erpItem->getCode(), $erpItem->getParentId(), $this->getProductType($erpItem), $erpItem->isSkipped(), !$erpItem->isInactive(), $erpItem->isOnline());
                $this->filesystem->appendToFile($this->csvFilename, $out);
                return true;
            }
        } catch (Exception $e) {
            dump($e);
        }
        return false;
    }

    private function getProductType(ErpItemModel $erpItemModel): string
    {
        if ($erpItemModel->isParent()) {
            return "Product";
        }
        return $erpItemModel->isChild() ? "ProductVariant" : "SimpleProduct";
    }
}
