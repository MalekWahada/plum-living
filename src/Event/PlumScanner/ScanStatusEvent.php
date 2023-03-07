<?php

declare(strict_types=1);

namespace App\Event\PlumScanner;

use App\Entity\CustomerProject\Project;

class ScanStatusEvent
{
    const NAME = 'plum_scanner.scan_status';
    const STATUS_OK = 'ok';
    const STATUS_KO = 'ko';

    private Project $project;
    private ?array  $plumScannerResult;
    private string  $status;
    private ?string $reason;

    public function __construct(Project $project, ?array $plumScannerResult, string $status, string $reason = null)
    {
        $this->project           = $project;
        $this->status            = $status;
        $this->reason            = $reason;
        $this->plumScannerResult = $plumScannerResult;
    }

    /**
     * @return Project
     */
    public function getProject(): Project
    {
        return $this->project;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return string|null
     */
    public function getReason(): ?string
    {
        return $this->reason;
    }

    /**
     * @return array
     */
    public function getPlumScannerResult(): ?array
    {
        return $this->plumScannerResult;
    }
}
