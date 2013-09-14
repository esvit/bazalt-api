<?php

namespace CMS\Uploader;

/**
 * Handle file uploads via regular form post (uses the $_FILES array)
 */
class Form
{
    /**
     * Save the file to the specified path
     * @return boolean TRUE on success
     */
    public function save($path)
    {
        if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            throw new FormException($_FILES['file']['error']);
        }
        if(!move_uploaded_file($_FILES['file']['tmp_name'], $path)){
            return false;
        }
        return true;
    }

    public function getName()
    {
        return $_FILES['file']['name'];
    }

    public function getSize()
    {
        return $_FILES['file']['size'];
    }
}