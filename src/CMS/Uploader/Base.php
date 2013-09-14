<?php

namespace CMS\Uploader;

define('DEFAULT_MAX_SIZE', 10485760);

class Base
{
    private $allowedExtensions = array();
    private $sizeLimit = DEFAULT_MAX_SIZE;
    private $file;

    public function __construct(array $allowedExtensions = array(), $sizeLimit = DEFAULT_MAX_SIZE)
    {
        $allowedExtensions = array_map("strtolower", $allowedExtensions);

        $this->allowedExtensions = $allowedExtensions;
        $this->sizeLimit = $sizeLimit;

        if (isset($_GET['files'])) {
            $this->file = new Ajax();
        } elseif (isset($_FILES['file'])) {
            $this->file = new Form();
        } else {
            $this->file = false;
        }
    }

    /**
     * Returns array('success'=>true) or array('error'=>'error message')
     */
    public function handleUpload($uploadDirectory, $url)
    {
        if (!is_writable($uploadDirectory)){
            return array('error' => "Server error. Upload directory isn't writable.");
        }

        if (!$this->file){
            return array('error' => 'No files were uploaded.');
        }

        $size = $this->file->getSize();

        if ($size == 0) {
            return array('error' => 'File is empty.');
        }

        if ($size > $this->sizeLimit) {
            return array('error' => 'File is too large.');
        }

        $pathinfo = pathinfo($this->file->getName());

        $filename = md5(uniqid());
        $ext = $pathinfo['extension'];

        if($this->allowedExtensions && !in_array(strtolower($ext), $this->allowedExtensions)){
            $these = implode(', ', $this->allowedExtensions);
            return array(
                'error' => 'File has an invalid extension, it should be one of '. $these . '.'
            );
        }

        $fullname = $uploadDirectory . '/' . $filename[0] . $filename[1] . '/' . $filename[2] . $filename[3] . '/' . $filename . '.' . $ext;
        $url = $url . '/' . $filename[0] . $filename[1] . '/' . $filename[2] . $filename[3] . '/' . $filename . '.' . $ext;
        mkdir(dirname($fullname), 0777, true);

        try {
            $res = $this->file->save($fullname);
        } catch(\Exception $ex) {
            return array('error' => $ex->getMessage());
        }
        return array(
            'success' => true,
            'name' => $this->file->getName(),
            'url' => $url,
            'thumbnailUrl' => thumb($url, '80x80'),
            'type' => $ext,
            'size' => $size
        );
    }
}