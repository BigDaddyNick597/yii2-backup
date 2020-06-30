<?php

namespace amoracr\backup\archive;

use Yii;
use \ZipArchive;
use amoracr\backup\archive\Archive;

/**
 * Description of Zip
 *
 * @author alonso
 */
class Zip extends Archive
{

    public function init()
    {
        parent::init();
        $this->extension = '.zip';
        $this->backup = Yii::getAlias($this->path) . DIRECTORY_SEPARATOR;
        $this->backup .= $this->name . $this->extension;
    }

    public function addFileToBackup($name, $file)
    {
        $relativePath = $name . DIRECTORY_SEPARATOR;
        $relativePath .= pathinfo($file, PATHINFO_BASENAME);
        $zipFile = new ZipArchive();
        $zipFile->open($this->backup, ZipArchive::CREATE);
        $zipFile->addFile($file, $relativePath);
        return $zipFile->close();
    }

    public function addFolderToBackup($name, $folder)
    {
        $zipFile = new ZipArchive();
        $zipFile->open($this->backup, ZipArchive::CREATE);
        $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator(Yii::getAlias($folder)), \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            $fileName = $file->getFilename();
            if (!$file->isDir() && !in_array($fileName, $this->skipFiles)) {
                $filePath = $file->getRealPath();
                $relativePath = $name . DIRECTORY_SEPARATOR . substr($filePath, strlen(Yii::getAlias($folder)) + 1);
                $zipFile->addFile($filePath, $relativePath);
            }
        }
        return $zipFile->close();
    }

}