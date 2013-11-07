<?php
use Components\Files\Model\File;

/**
 * elFinder driver for Bazalt CMS.
 **/
class elFinderVolumeORM extends elFinderVolumeDriver
{
    /**
     * Driver id
     * Must be started from letter and contains [a-z0-9]
     * Used as part of volume id
     *
     * @var string
     **/
    protected $driverId = 'o';

    /**
     * Directory for tmp files
     * If not set driver will try to use tmbDir as tmpDir
     *
     * @var string
     **/
    protected $tmpPath = '';

    /**
     * Constructor
     * Extend options with required fields
     *
     * @return void
     **/
    public function __construct()
    {
        $this->options['mimeDetect'] = 'internal';
    }

    /*********************************************************************/
    /*                        INIT AND CONFIGURE                         */
    /*********************************************************************/

    /**
     * Prepare driver before mount volume.
     * Connect to db, check required tables and fetch root path
     *
     * @return bool
     **/
    protected function init()
    {
        $this->updateCache($this->options['path'], $this->_stat($this->options['path']));
        return true;
    }

    /**
     * Set tmp path
     *
     * @return void
     **/
    protected function configure()
    {
        parent::configure();

        if (($tmp = $this->options['tmpPath'])) {
            if (!file_exists($tmp) && @mkdir($tmp)) {
                @chmod($tmp, $this->options['tmbPathMode']);
            }
            $this->tmpPath = is_dir($tmp) && is_writable($tmp) ? $tmp : false;
        }
        if (!$this->tmpPath && $this->tmbPath && $this->tmbPathWritable) {
            $this->tmpPath = $this->tmbPath;
        }
        $this->mimeDetect = 'internal';
    }

    /**
     * Close connection
     *
     * @return void
     **/
    public function umount()
    {
    }

    /**
     * Create empty object with required mimetype
     *
     * @param  string  $path  parent dir path
     * @param  string  $name  object name
     * @param  string  $mime  mime type
     * @return bool
     **/
    protected function make($path, $name, $mime)
    {
        $parent = File::getById((int)$path);

        $file = File::create();
        $file->name = $name;
        $file->alias = cleanUrl($name);
        $file->mimetype = $mime;

        $access = 0;
        if ($this->defaults['read']) {
            $access |= File::ACCESS_READ;
        }
        if ($this->defaults['write']) {
            $access |= File::ACCESS_WRITE;
        }
        $file->access = $access;
        if ($mime != 'directory') {
            $fileName = self::_getFilename($name . '.txt', 'files');
            file_put_contents($fileName, '');
            $file->path = relativePath($fileName);
        }

        $parent->Elements->add($file);
        return $file->id != null;
    }

    /**
     * Return temporary file path for required file
     *
     * @param  string  $path   file path
     * @return string
     **/
    protected function tmpname($path)
    {
        return $this->tmpPath . DIRECTORY_SEPARATOR . md5($path);
    }

    /*********************************************************************/
    /*                               FS API                              */
    /*********************************************************************/

    /**
     * Cache dir contents
     *
     * @param  string  $path  dir path
     * @return void
     **/
    protected function cacheDir($path)
    {
        $this->dirsCache[$path] = array();

        $folder = File::getById((int)$path);

        if ($folder) {
            $files = $folder->Elements->get(1);

            foreach ($files as $file) {
                $stat = $this->updateCache($file->id, $file->getStat($this));

                if (($stat = $this->updateCache($file->id, $file->getStat($this))) && empty($stat['hidden'])) {
                    $this->dirsCache[$path][] = $file->id;
                }
            }
        }
        return $this->dirsCache[$path];
    }

    /**
     * Return array of parents paths (ids)
     *
     * @param  int   $path  file path (id)
     * @return array
     **/
    protected function getParents($path)
    {
        $parents = array();

        while ($path) {
            if ($file = $this->stat($path)) {
                array_unshift($parents, $path);
                $path = isset($file['phash']) ? $this->decode($file['phash']) : false;
            }
        }
        if (count($parents)) {
            array_pop($parents);
        }
        return $parents;
    }

    /**
     * Return correct file path for LOAD_FILE method
     *
     * @param  string $path  file path (id)
     * @return string
     **/
    protected function loadFilePath($path)
    {
        $realPath = realpath($path);
        if (DIRECTORY_SEPARATOR == '\\') { // windows
            $realPath = str_replace('\\', '\\\\', $realPath);
        }
        return $realPath;
    }

    /*********************** paths/urls *************************/

    /**
     * Return parent directory path
     *
     * @param  string  $path  file path
     * @return string
     * @author Dmitry (dio) Levashov
     **/
    protected function _dirname($path)
    {
        return ($stat = $this->stat($path)) ? ($stat['phash'] ? $this->decode($stat['phash']) : $this->root) : false;
    }

    /**
     * Return file name
     *
     * @param  string  $path  file path
     * @return string
     **/
    protected function _basename($path)
    {
        return ($stat = $this->stat($path)) ? $stat['name'] : false;
    }

    /**
     * Join dir name and file name and return full path
     *
     * @param  string  $dir
     * @param  string  $name
     * @return string
     **/
    protected function _joinPath($dir, $name)
    {
        $folder = File::getById((int)$dir);

        $q = $folder->Items->getQuery()
                    ->andWhere('name = ?', $name);

        if ($file = $q->fetch()) {
            $this->updateCache($file->id, $file->getStat($this));
            return $file->id;
        }
        return -1;
    }

    /**
     * Return normalized path, this works the same as os.path.normpath() in Python
     *
     * @param  string  $path  path
     * @return string
     **/
    protected function _normpath($path)
    {
        return $path;
    }

    /**
     * Return file path related to root dir
     *
     * @param  string  $path  file path
     * @return string
     **/
    protected function _relpath($path)
    {
        return $path;
    }

    /**
     * Convert path related to root dir into real path
     *
     * @param  string  $path  file path
     * @return string
     **/
    protected function _abspath($path)
    {
        return $path;
    }

    /**
     * Return fake path started from root dir
     *
     * @param  string  $path  file path
     * @return string
     **/
    protected function _path($path) {
        if (($file = $this->stat($path)) == false) {
            return '';
        }
        
        $parentsIds = $this->getParents($path);
        $path = '';
        foreach ($parentsIds as $id) {
            $dir = $this->stat($id);
            $path .= $dir['name'].$this->separator;
        }
        return $path.$file['name'];
    }

    /**
     * Return true if $path is children of $parent
     *
     * @param  string  $path    path to check
     * @param  string  $parent  parent path
     * @return bool
     **/
    protected function _inpath($path, $parent)
    {
        return ($path == $parent) ? true : in_array($parent, $this->getParents($path));
    }

    /***************** file stat ********************/
    /**
     * Return stat for given path.
     * Stat contains following fields:
     * - (int)    size    file size in b. required
     * - (int)    ts      file modification time in unix time. required
     * - (string) mime    mimetype. required for folders, others - optionally
     * - (bool)   read    read permissions. required
     * - (bool)   write   write permissions. required
     * - (bool)   locked  is object locked. optionally
     * - (bool)   hidden  is object hidden. optionally
     * - (string) alias   for symlinks - link target path relative to root path. optionally
     * - (string) target  for symlinks - link target path. optionally
     *
     * If file does not exists - returns empty array.
     *
     * @param  string  $path    file path 
     * @return array
     **/
    protected function _stat($path)
    {
        $file = File::getById((int)$path);

        return $file ? $file->getStat($this) : array();
    }

    /**
     * Return true if path is dir and has at least one childs directory
     *
     * @param  string  $path  dir path
     * @return bool
     **/
    protected function _subdirs($path)
    {
        return ($stat = $this->stat($path)) && isset($stat['dirs']) ? $stat['dirs'] : false;
    }

    /**
     * Return object width and height
     * Usualy used for images, but can be realize for video etc...
     *
     * @param  string  $path  file path
     * @param  string  $mime  file mime type
     * @return string
     **/
    protected function _dimensions($path, $mime)
    {
        return ($stat = $this->stat($path)) && isset($stat['width']) && isset($stat['height']) ? $stat['width'] . 'x' . $stat['height'] : '';
    }

    /******************** file/dir content *********************/
        
    /**
     * Return files list in directory.
     *
     * @param  string  $path  dir path
     * @return array
     **/
    protected function _scandir($path)
    {
        return isset($this->dirsCache[$path]) ? $this->dirsCache[$path] : $this->cacheDir($path);
    }

    /**
     * Open file and return file pointer
     *
     * @param  string  $path  file path
     * @param  string  $mode  open file mode (ignored in this driver)
     * @return resource|false
     **/
    protected function _fopen($path, $mode = 'rb')
    {
        $fp = $this->tmbPath ? @fopen($this->tmpname($path), 'w+') : @tmpfile();

        if ($fp) {
            $file = File::getById((int)$path);
            if ($this->tmpPath && $file) {
                $fileInfo = $file->getStat($this);
                if ($fileInfo['read']) {
                    $tmp = SITE_DIR . $file->path;

                    return fopen($tmp, $mode);
                }
            }
        }
        return false;
    }

    /**
     * Close opened file
     *
     * @param  resource  $fp  file pointer
     * @return bool
     **/
    protected function _fclose($fp, $path = '')
    {
        @fclose($fp);
        if ($path) {
            @unlink($this->tmpname($path));
        }
    }

    /********************  file/dir manipulations *************************/

    /**
     * Create dir and return created dir path or false on failed
     *
     * @param  string  $path  parent dir path
     * @param string  $name  new directory name
     * @return string|bool
     **/
    protected function _mkdir($path, $name)
    {
        return $this->make($path, $name, 'directory') ? $this->_joinPath($path, $name) : false;
    }

    /**
     * Create file and return it's path or false on failed
     *
     * @param  string  $path  parent dir path
     * @param string  $name  new file name
     * @return string|bool
     **/
    protected function _mkfile($path, $name)
    {
        return $this->make($path, $name, 'text/plain') ? $this->_joinPath($path, $name) : false;
    }

    /**
     * Create symlink. FTP driver does not support symlinks.
     *
     * @param  string  $target  link target
     * @param  string  $path    symlink path
     * @return bool
     **/
    protected function _symlink($target, $path, $name)
    {
        return false;
    }

    /**
     * Copy file into another file
     *
     * @param  string  $source     source file path
     * @param  string  $targetDir  target directory path
     * @param  string  $name       new file name
     * @return bool
     **/
    protected function _copy($source, $targetDir, $name)
    {
        $file = File::getById((int)$source);
        $file->id = null;
        $file->name = $name;
        $file->alias = cleanUrl($name);
        $parent = $file->Elements->getParent();
        if ($parent->id != $targetDir) {
            $folder = File::getById((int)$targetDir);
            $folder->Elements->add($file);
        }
        return true;
    }

    /**
     * Move file into another parent dir.
     * Return new file path or false.
     *
     * @param  string  $source  source file path
     * @param  string  $target  target dir path
     * @param  string  $name    file name
     * @return string|bool
     **/
    protected function _move($source, $targetDir, $name)
    {
        $file = File::getById((int)$source);
        $file->name = $name;
        $file->alias = $name;
        $file->save();
        $parent = $file->Elements->getParent();
        if ($parent->id != $targetDir) {
            $parent->Elements->remove($file);

            $folder = File::getById((int)$targetDir);
            $folder->Elements->add($file);
        }
        return $file->id;
    }

    /**
     * Remove file
     *
     * @param  string  $path  file path
     * @return bool
     **/
    protected function _unlink($path)
    {
        $folder = File::getById((int)$path);
        if (!$folder) {
            return false;
        }
        $folder->Items->getParent()->Items->remove($folder);
        return true;
    }

    /**
     * Remove dir
     *
     * @param  string  $path  dir path
     * @return bool
     **/
    protected function _rmdir($path)
    {
        return $this->_unlink($path);
    }

    /**
     * Generate filename with folders
     */
    private static function _getFilename($file, $type)
    {
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        $folder = UPLOAD_DIR . DIRECTORY_SEPARATOR . $type;
        $fileKey = md5(time() . $file);

        $path  = rtrim($folder, DIRECTORY_SEPARATOR)  . DIRECTORY_SEPARATOR;
        $path .= $fileKey{0} . $fileKey{1} . DIRECTORY_SEPARATOR;
        $path .= $fileKey{2} . $fileKey{3} . DIRECTORY_SEPARATOR;

        if (!is_dir($path) && !mkdir($path, 0777, true)) {
            throw new \Exception('Cant create folder "' . $path . '"');
        }
        return $path . $fileKey . '.' . strToLower($ext);
    }

    /**
     * Create new file and write into it from file pointer
     * Return new file path or false on error.
     *
     * @param  resource  $fp   file pointer
     * @param  string    $dir  target dir path
     * @param  string    $name file name
     * @return bool|string
     * @author Dmitry (dio) Levashov
     **/
    protected function _save($fp, $dir, $name, $stat)
    {
        clearstatcache();

        $id = $this->_joinPath($dir, $name);

        if ($this->tmpPath) {
            $tmp = $this->tmpPath . DIRECTORY_SEPARATOR.$name;
            if (!($target = @fopen($tmp, 'wb'))) {
                return false;
            }

            while (!feof($fp)) {
                fwrite($target, fread($fp, 8192));
            }
            fclose($target);
            $mime  = parent::mimetype($tmp);

            $parent = File::getById((int)$dir);
            if ($id > 0) {
                $file = File::getById((int)$id);
                $file->name = $name;
            } else {
                $file = File::create();
                $file->name = $name;
            }
            $stat = fstat($fp);
            $file->size = $stat['size'];
            //$file->width = $w;
            //$file->height = $h;
            $file->alias = cleanUrl($name);
            if ($mime != 'directory') {
                $fileName = self::_getFilename($tmp, 'files');
                copy($tmp, $fileName);
                $file->path = relativePath($fileName);
            }
            $file->mimetype = $mime;
            if ($parent) {
                $parent->Items->insert($file);
            }
            return $file->id;
            
            /*$sql = $id > 0
                ? 'REPLACE INTO %s (id, parent_id, name, content, size, mtime, mime, width, height) VALUES ('.$id.', %d, "%s", LOAD_FILE("%s"), %d, %d, "%s", %d, %d)'
                : 'INSERT INTO %s (parent_id, name, content, size, mtime, mime, width, height) VALUES (%d, "%s", LOAD_FILE("%s"), %d, %d, "%s", %d, %d)';
            $sql = sprintf($sql, $this->tbf, $dir, $this->db->real_escape_string($name), realpath($tmp), filesize($tmp), time(), $mime, $width, $height);*/
            
        } else {
            $this->mimeDetect = 'internal';
            $mime = parent::mimetype($name);
            $stat = fstat($fp);
            $size = $stat['size'];
            $content = '';
            while (!feof($fp)) {
                $content .= fread($fp, 8192);
            }

            $sql = $id > 0
                ? 'REPLACE INTO %s (id, parent_id, name, content, size, mtime, mime, width, height) VALUES ('.$id.', %d, "%s", "%s", %d, %d, "%s", %d, %d)'
                : 'INSERT INTO %s (parent_id, name, content, size, mtime, mime, width, height) VALUES (%d, "%s", "%s", %d, %d, "%s", %d, %d)';
            $sql = sprintf($sql, $this->tbf, $dir, $this->db->real_escape_string($name), '0x' . bin2hex($content), $size, time(), $mime, 0, 0);
        }

        if ($this->query($sql)) {
            if ($tmp) {
                unlink($tmp);
            }
            return $id > 0 ? $id : $this->db->insert_id;
        }
        if ($tmp) {
            unlink($tmp);
        }
        return false;
    }

    /**
     * Get file contents
     *
     * @param  string  $path  file path
     * @return string|false
     **/
    protected function _getContents($path)
    {
        $file = File::getById((int)$path);
        if (!$file || !is_readable(SITE_DIR . $file->path)) {
            return false;
        }
        return file_get_contents(SITE_DIR . $file->path);
    }

    /**
     * Write a string to a file
     *
     * @param  string  $path     file path
     * @param  string  $content  new file content
     * @return bool
     **/
    protected function _filePutContents($path, $content)
    {
        $file = File::getById((int)$path);
        if (!$file || !is_writable(SITE_DIR . $file->path)) {
            return false;
        }
        file_put_contents(SITE_DIR . $file->path, $content);
        $file->size = strlen($content);
        $file->save();
        return true;
    }

    /**
     * Detect available archivers
     *
     * @return void
     **/
    protected function _checkArchivers()
    {
        return;
    }

    /**
     * Unpack archive
     *
     * @param  string  $path  archive path
     * @param  array   $arc   archiver command and arguments (same as in $this->archivers)
     * @return void
     **/
    protected function _unpack($path, $arc)
    {
        return;
    }

    /**
     * Recursive symlinks search
     *
     * @param  string  $path  file/dir path
     * @return bool
     **/
    protected function _findSymlinks($path)
    {
        return false;
    }

    /**
     * Extract files from archive
     *
     * @param  string  $path  archive path
     * @param  array   $arc   archiver command and arguments (same as in $this->archivers)
     * @return true
     **/
    protected function _extract($path, $arc)
    {
        return false;
    }

    /**
     * Create archive and return its path
     *
     * @param  string  $dir    target dir
     * @param  array   $files  files names list
     * @param  string  $name   archive name
     * @param  array   $arc    archiver options
     * @return string|bool
     **/
    protected function _archive($dir, $files, $name, $arc)
    {
        return false;
    }

    public function encodePath($path)
    {
        return $this->encode($path);
    }

    public function resize($hash, $width, $height, $x, $y, $mode = 'resize', $bg = '', $degree = 0)
    {
        if ($this->commandDisabled('resize')) {
            return $this->setError(elFinder::ERROR_PERM_DENIED);
        }
        
        if (($file = $this->file($hash)) == false) {
            return $this->setError(elFinder::ERROR_FILE_NOT_FOUND);
        }
        
        if (!$file['write'] || !$file['read']) {
            return $this->setError(elFinder::ERROR_PERM_DENIED);
        }
        
        $path = $this->decode($hash);
        
        if (!$this->canResize($path, $file)) {
            return $this->setError(elFinder::ERROR_UNSUPPORT_TYPE);
        }

        $item = File::getById((int)$path);
        $img = SITE_DIR . $item->path;

        switch($mode) {
            
            case 'propresize':
                $result = $this->imgResize($img, $width, $height, true, true);
                break;

            case 'crop':
                $result = $this->imgCrop($img, $width, $height, $x, $y);
                break;

            case 'fitsquare':
                $result = $this->imgSquareFit($img, $width, $height, 'center', 'middle', $bg ? $bg : $this->options['tmbBgColor']);
                break;

            case 'rotate':
                $result = $this->imgRotate($img, $degree, ($bg ? $bg : $this->options['tmbBgColor']));
                break;
            
            default:
                $result = $this->imgResize($img, $width, $height, false, true);
                break;
        }
        
        if (!$result) {
            return false;
        }
        $item->save();

        $this->rmTmb($file);
        $this->clearcache();
        return $this->stat($path);
    }

    protected function tmbname($stat)
    {
        $name = md5($stat['hash'] . strToTime($stat['mtime']));

        $path = $name{0} . $name{1} . DIRECTORY_SEPARATOR;
        $path .= $name{2} . $name{3} . DIRECTORY_SEPARATOR;

        if (!is_dir($this->tmbPath . DIRECTORY_SEPARATOR . $path) && !mkdir($this->tmbPath . DIRECTORY_SEPARATOR .$path, 0777, true)) {
            throw new Exception('Cant create folder "' . $path . '"');
        }
        return str_replace('\\', '/', $path . $name . '.png');
    }

    public function desc($target, $newdesc = null)
    {
        $path = $this->decode($target);
        $file = File::getById((int)$path);
        if (!$file) {
            return -1;
        }
        if ($newdesc != NULL) {
            $file->body = $newdesc;
            $file->save();
        } else {
            return $file->body;
        }
        return $newdesc;
    }

    public function downloadcount($target)
    {
        $path = $this->decode($target);
        $file = File::getById((int)$path);
        if (!$file) {
            return -1;
        }
        return $file->downloads;
    }

    public function owner($target)
    {
        $path = $this->decode($target);
        $file = File::getById((int)$path);
        if (!$file) {
            return -1;
        }
        return $file->user_id;
    }
}