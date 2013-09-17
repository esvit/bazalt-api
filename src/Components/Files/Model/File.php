<?php

namespace Components\Files\Model;

use Framework\CMS as CMS,
    Framework\Core\Helper\Url,
    Bazalt\ORM,
    Bazalt\Routing\Route;

class File extends Base\File
{
    const ACCESS_READ = 1;

    const ACCESS_WRITE = 2;

    const ACCESS_LOCKED = 4;

    const ACCESS_HIDDEN = 8;

    protected static $root = null;

    protected static $finfo = null;

    public static function create()
    {
        $f = new File();
        $f->site_id = \Bazalt\Site::getId();
        $f->user_id = \Bazalt\Auth::getUser()->isGuest() ? null : \Bazalt\Auth::getUser()->id;
        $f->access = self::ACCESS_READ | self::ACCESS_WRITE;
        $f->mimetype = 'directory';
        $f->path = '/';

        return $f;
    }

    public function isReadable()
    {
        return $this->access & self::ACCESS_READ;
    }

    public function isWriteable()
    {
        return $this->access & self::ACCESS_WRITE;
    }

    public function isLocked()
    {
        return $this->access & self::ACCESS_LOCKED;
    }

    public function isHidden()
    {
        return $this->access & self::ACCESS_HIDDEN;
    }

    public static function createRoot($title, $siteId, $componentId = null)
    {
        $root = self::create();

        $root->name = $title;
        $root->alias = cleanUrl($title);
        $root->lft = 1;
        $root->rgt = 2;

        $root->save();
        return $root;
    }

    public static function getBySite($siteId, $componentId = null)
    {
        $q = File::select()
                ->where('site_id = ?', $siteId)
                ->andWhere('lft = ?', 1);

        if ($componentId == null) {
            $q->andWhere('component_id IS NULL');
        } else {
            $q->andWhere('component_id = ?', (int)$componentId);
        }
        return $q->fetchAll();
    }

    public function getStat($driver)
    {
        $parent = $this->parentElement;
        if (!$parent) {
            $parent = $this->Elements->getParent();
        }
        $stat = array(
            'name'      => $this->name,
            //'alias'      => '123',
            'size'      => $this->size,
            'mtime'     => strToTime($this->updated_at),
            'mime'      => $this->mimetype,
            'read'      => $this->isReadable(),
            'write'     => $this->isWriteable(),
            'locked'    => $this->isLocked(),
            'hidden'    => $this->isHidden(),
            'width'     => $this->width,
            'height'    => $this->height,
            'url'       => $this->getUrl(),
            'tmb'       => 1,
            'dirs'      => ($this->Elements->count() > 0),
        );
        if ($parent && $driver) {
            $stat['phash'] = $driver->encodePath($parent->id);
        }
        if ($stat['mime'] == 'directory') {
            unset($stat['width']);
            unset($stat['height']);
        } else {
            unset($stat['dirs']);
        }
        return $stat;
    }

    public function getPath()
    {
        return UPLOAD_DIR . $this->path;
    }

    public static function getById($id)
    {
        $q = ORM::select(__CLASS__)->where('id = ?', $id)
                                   ->andWhere('site_id = ?', \Bazalt\Site::getId());

        return $q->fetch();
    }

    public static function getTree($item)
    {
        $info = $item->getStat();
        $info['dirs'] = array();
        if(!isset($item->Childrens)) {
            $item->Childrens = $item->Items->get();
        }
        foreach($item->Childrens as $child) {
            if($child->path == null) {
                $info['dirs'] []= self::getTree($child);
            }
        }
        return $info;
    }
    
    public static function getFilesCollection(File $folder)
    {
        $q = $folder->Items->getQuery();
        $q->andWhere('path IS NOT NULL')
          ->andWhere('site_id = ?', \Bazalt\Site::getId());

        return new CMS\ORM_Collection($q);
    }

    public static function setRoot(File $item)
    {
        self::$root = $item;
    }
    
    public static function getRoot()
    {
        if(self::$root != null) {
            return self::$root;
        }
        $item = self::create();
        $item->site_id = (!is_null(CMS\Bazalt::getSite()) ? CMS\Bazalt::getSite()->id : null);
        $root = $item->Elements->getRoot();
        if ($root) {
            return $root;
        }
        $item->name = 'Home';
        $item->alias = 'downloads';
        $item->is_system = 1;
        $item->access = self::ACCESS_READ | self::ACCESS_WRITE | self::ACCESS_LOCKED;
        $item->mimetype = 'directory';
        $item->lft = 1;
        $item->rgt = 2;
        $item->save();
        return $item;
    }
    
    public static function getComponentFolder(CMS\Model\Component $component)
    {
        $root = self::getRoot();
        $q = File::select()
            ->where('lft > ?', $root->lft)
            ->andWhere('rgt < ?', $root->rgt)
            ->andWhere('component_id = ?', $component->id)
            ->limit(1);
        $pf = $q->fetch();
        if(!$pf) {
            $item = new File();
            $item->site_id = (!is_null(CMS\Bazalt::getSite()) ? CMS\Bazalt::getSite()->id : 0);
            $item->is_system = 1;
            $item->name = $component->title;
            $item->alias = $component->title;
            $item->component_id = $component->id;
            $pf = $root->Items->add($item);
        }
        return $pf;
    }
    
    public static function getUserFolder(CMS\Model\User $user)
    {
        $component = CMS\Model\Component::getComponent('ComUsers');
        $root = self::getComponentFolder($component);
        $q = File::select()
            ->where('lft > ?', $root->lft)
            ->andWhere('rgt < ?', $root->rgt)
            ->andWhere('path IS NULL')
            ->andWhere('is_system = ?', 1)
            ->andWhere('user_id = ?', $user->id)
            ->limit(1);
        $pf = $q->fetch();
        if(!$pf) {
            $item = new File();
            $item->site_id = (!is_null(CMS\Bazalt::getSite()) ? CMS\Bazalt::getSite()->id : 0);
            $item->is_system = 1;
            $item->user_id = $user->id;
            $item->name = $user->getName();
            $item->alias = Url::cleanUrl($user->getName());
            $item->component_id = $component->id;
            $pf = $root->Items->add($item);
        }
        return $pf;
    }
    
    public static function mkdir($name, File $parent, $isSystem = false)
    {
        $newItem = new File();
        $newItem->name = $name;
        $newItem->alias = Url::cleanUrl($name);
        $newItem->site_id = (!is_null(CMS\Bazalt::getSite()) ? CMS\Bazalt::getSite()->id : 0);
        $newItem->is_system = $isSystem;
        $newItem->component_id = $parent->component_id;
        
        return $parent->Items->add($newItem);
    }
    
    public static function mkfile($name, $path, File $parent, $isSystem = false)
    {
        $newItem = new File();
        $newItem->name = $name;
        $newItem->alias = Url::cleanUrl($name);
        $newItem->path = $path;
        $newItem->site_id = (!is_null(CMS\Bazalt::getSite()) ? CMS\Bazalt::getSite()->id : 0);
        $newItem->is_system = $isSystem;
        $newItem->component_id = $parent->component_id;
        return $parent->Items->add($newItem);
    }
    
    public static function move(File $item, File $dst)
    {
        $newItem = new File();
        $data = $item->toArray();
        unset($data['id']);
        $newItem->fromArray($data);
        $newItem->component_id = $dst->component_id;
        $dst->Items->insert($newItem);
        $item->Items->getParent()->Items->remove($item);
    }
    
    public static function copy(File $item, File $dst)
    {
        $newItem = new File();
        $data = $item->toArray();
        unset($data['id']);
        $newItem->fromArray($data);
        $newItem->component_id = $dst->component_id;
        $dst->Items->add($newItem);
    }
    
    public static function isExists($name, File $parent, $isFile) 
    {
        $q = File::select('COUNT(*) as c')
            ->where('lft > ?', $parent->lft)
            ->andWhere('rgt < ?', $parent->rgt)
            ->andWhere('depth = ?', $parent->depth + 1)
            ->andWhere('name = ?', $name)
            ->limit(1);
        if($isFile) {
            $q->andWhere('path IS NOT NULL');
        } else {
            $q->andWhere('path IS NULL');
        }
        $res = $q->fetch();
        return ($res->c > 0);
    }
    
    /**
     * Select last element by path, example /categories/кикбокс/8-9-лет/до-35-кг/
     */
    public static function getByPath(array $parts, File $root = null)
    {
        $last = count($parts)-1;
        
        $aliases = array();
        $aliasVals = array();
        // $languages = ComI18N::getLanguages();
        // foreach ($languages as $lang) {
            // $aliases []= 'e'.$last.'.alias_' . $lang->alias . ' = ?';
            // $aliasVals []= end($parts);
        // }
        $aliases []= 'e'.$last.'.alias = ?';
        $aliasVals []= end($parts);

        $q = ORM::select('File e'.$last, 'e'.$last.'.*')
                ->where('e' . $last . '.site_id = ?', \Bazalt\Site::getId())
                ->andWhere('('.implode(' OR ', $aliases).')', $aliasVals);

        if ($root) {
            $q->andWhere('e'.$last.'.lft between ? and ?', array($root->lft, $root->rgt));
        }
        $i = 0;
        $parts = array_reverse($parts);
        array_shift($parts);
        foreach($parts as $part) {
            $aliasVals = array();
            $aliases = array();
            // foreach ($languages as $lang) {
                // $aliases []= 'e'.$i.'.alias_' . $lang->alias . ' = ?';
                // $aliasVals []= $part;
            // }
            $aliases []= 'e'.$i.'.alias = ?';
            $aliasVals []= $part;
            
            $q->from('File e'.$i);
            $q->andWhere(' (e'.$last.'.lft between e'.$i.'.lft and e'.$i.'.rgt and ('.implode(' OR ', $aliases).'))', $aliasVals)
              ->andWhere('e'.$i.'.site_id = ?', \Bazalt\Site::getId());

            if($componentId) {
                $q->andWhere('e'.$i.'.component_id = ?', $componentId);
            }
            $i++;
        }
        return $q->fetch();
    }

    public function getUrl($withHost = false, $preview = false)
    {
        return $this->path;
        if (!empty($this->path) && $this->mimetype != 'directory'){
            return Route::urlFor('Files.File', ['id' => $this->id], $withHost);//relativePath(UPLOAD_DIR . $this->path);
        }
        return '#'; Route::urlFor('Files.Folder', array('category' => $this->Items));
    }
}
