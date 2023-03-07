<?php

declare(strict_types=1);

namespace App\TagManager;

use Xynnn\GoogleTagManagerBundle\Service\GoogleTagManagerInterface;

final class GoogleTagManagerEvent
{
    private GoogleTagManagerInterface $googleTagManger;

    public function __construct(GoogleTagManagerInterface $googleTagManager)
    {
        $this->googleTagManger = $googleTagManager;
    }

    public function push(string $event, array $data = null): void
    {
        $dataLayer = ['event' => $event];
        if (is_array($data)) {
            $dataLayer = array_merge($dataLayer, $data);
        }
        $this->googleTagManger->addPush($dataLayer);
    }

    /**
     * @param mixed $value
     */
    public function setData(string $key, $value): void
    {
        $this->googleTagManger->setData($key, $value);
    }
}
