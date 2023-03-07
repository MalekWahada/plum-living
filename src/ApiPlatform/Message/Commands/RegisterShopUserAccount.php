<?php

declare(strict_types=1);

namespace App\ApiPlatform\Message\Commands;

use Sylius\Component\Core\Model\Customer;

/**
 * Model class for command message
 * Class RegisterShopUserAccount
 * @package App\ApiPlatform\Message\Commands
 */
class RegisterShopUserAccount
{
    /**
     * @var int|null
     */
    private ?int $customerId;

    /**
     * Shop user password
     * @var string|null
     */
    private ?string $password;

    /**
     * Account is enabled
     * @var bool|null
     */
    private ?bool $enabled;

    /**
     * Email is verified
     * @var bool|null
     */
    private ?bool $emailVerified;

    /**
     * RegisterShopUserAccount constructor.
     * @param int|null $customerId
     * @param string|null $password
     * @param bool|null $enabled
     * @param bool|null $emailVerified
     */
    public function __construct(?int $customerId = null, ?string $password = null, ?bool $enabled = null, ?bool $emailVerified = null) // Default value must be set for validation
    {
        $this->customerId = $customerId;
        $this->password = $password;
        $this->enabled = $enabled;
        $this->emailVerified = $emailVerified;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @return int|null
     */
    public function getCustomerId(): ?int
    {
        return $this->customerId;
    }

    /**
     * @return bool|null
     */
    public function getEnabled(): ?bool
    {
        return $this->enabled;
    }

    /**
     * @return bool|null
     */
    public function getEmailVerified(): ?bool
    {
        return $this->emailVerified;
    }
}
