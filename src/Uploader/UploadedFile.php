<?php

namespace App\Uploader;

use Symfony\Component\HttpFoundation\File\UploadedFile as BaseUploadedFile;

class UploadedFile extends BaseUploadedFile
{
    public function move($directory, $name = null)
    {
        $target = $this->getTargetFile($directory, $name);
        @rename($this->getPathname(), $target);
        @chmod($target, 0666 & ~umask());

        return $target;
    }
}
