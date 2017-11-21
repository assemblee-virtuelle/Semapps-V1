<?php
/**
 * Created by PhpStorm.
 * User: LaFaucheuse
 * Date: 06/03/2017
 * Time: 17:25
 */

namespace semappsBundle\Services;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    private $targetDir;
    private $domaine;

    public function __construct($targetDir,$domaine)
    {
        $this->targetDir = $targetDir;
        $this->domaine = $domaine;
    }

    public function upload(UploadedFile $file)
    {
        $fileName = md5(uniqid()).'.'.$file->guessExtension();

        $file->move($this->targetDir, $fileName);

        return $fileName;
    }

    public function getTargetDir()
    {
        return $this->targetDir;
    }

    public function remove($fileName){
        $fs = new Filesystem();
        $file = new File($this->targetDir.'/'.$fileName);
        $fs->remove($file);

    }

    public function generateUrlForFile($fileName){
        return 'http://'.$this->domaine.'/uploads/pictures/'.$fileName;
    }
}