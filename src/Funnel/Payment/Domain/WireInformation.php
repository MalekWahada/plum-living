<?php

declare(strict_types=1);

namespace App\Funnel\Payment\Domain;

class WireInformation
{
    public string $reference;

    public string $accountName;

    public string $bic;

    public string $country;

    public string $iban;

    public function __construct(
        string $reference,
        string $accountName,
        string $bic,
        string $country,
        string $iban
    ) {
        $this->reference = $reference;
        $this->accountName = $accountName;
        $this->bic = $bic;
        $this->country = $country;
        $this->iban = $iban;
    }
}
