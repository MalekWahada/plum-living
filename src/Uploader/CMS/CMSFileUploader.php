<?php

declare(strict_types=1);

namespace App\Uploader\CMS;

use MonsieurBiz\SyliusRichEditorPlugin\Uploader\FileUploader;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CMSFileUploader
{
    private FileUploader $fileUploader;
    private Filesystem $filesystem;
    private string $uploadDirCMS;

    public function __construct(
        FileUploader $fileUploader,
        Filesystem $filesystem,
        string $uploadDirCMS
    ) {
        $this->fileUploader = $fileUploader;
        $this->filesystem = $filesystem;
        $this->uploadDirCMS =  $uploadDirCMS;
    }

    public function uploadCMSFile(string $fileToUpload): string
    {
        if (!$this->filesystem->exists($fileToUpload)) {
            return $fileToUpload;
        }
        $uploadedFile = new UploadedFile($fileToUpload, basename($fileToUpload), null, null, true);
        $file = $this->fileUploader->upload($uploadedFile);
        $this->filesystem->copy($this->uploadDirCMS . $file, $fileToUpload);

        return $file;
    }
}
