<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Page\Page;
use DateTime;
use Symfony\Component\EventDispatcher\GenericEvent;

class PageUpdateListener
{
    public function onPageTranslationUpdate(GenericEvent $event): void
    {
        $page = $event->getSubject();
        if ($page instanceof Page) {
            $page->setUpdatedAt(new DateTime());
        }
    }
}
