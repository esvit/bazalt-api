<?php

namespace CMS\Uploader;

/**
 * Handle file uploads via XMLHttpRequest
 */
class Ajax
{
    /**
     * Save the file to the specified path
     * @return boolean TRUE on success
     */
    public function save($path)
    {
        $input = fopen('php://input', 'r');
        $temp = tmpfile();
        $realSize = stream_copy_to_stream($input, $temp);
        fclose($input);

        if ($realSize != $this->getSize()){
            throw new FormException(0);
        }

        $target = fopen($path, 'w');
        fseek($temp, 0, SEEK_SET);
        stream_copy_to_stream($temp, $target);
        fclose($target);

        return true;
    }

    public function getName()
    {
        return $_GET['files'];
    }

    public function getSize()
    {
        if (isset($_SERVER['CONTENT_LENGTH'])){
            return (int)$_SERVER['CONTENT_LENGTH'];
        } else {
            throw new \Exception('Getting content length is not supported.');
        }
    }
}