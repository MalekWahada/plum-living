<?php

declare(strict_types=1);

namespace App\Uploader;

use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

class ImageHttpUrlUploader
{
    private Filesystem $filesystem;
    private LoggerInterface $logger;

    public function __construct(
        FileSystem             $filesystem,
        LoggerInterface        $logger
    ) {
        $this->filesystem = $filesystem;
        $this->logger = $logger;
    }

    public function uploadToTmp(string $url): ?File
    {
        try {
            // create temp image file name
            $tmpImageName = $this->filesystem->tempnam(sys_get_temp_dir(), 'tmp_image');

            // load image content from url
            $image = file_get_contents($url);

            if (false === $image) {
                return null;
            }

            $this->filesystem->dumpFile($tmpImageName, $image);
        } catch (IOException $e) {
            $this->logger->error($e->getMessage());
            return null;
        }

        return is_array(getimagesize($tmpImageName)) ? new File($tmpImageName) : null;
    }
}
