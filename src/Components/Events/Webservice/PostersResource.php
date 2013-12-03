<?php

namespace Components\Events\Webservice;

/**
 * PostersResource
 *
 * @uri /events/posters
 */
class PostersResource extends \Bazalt\Rest\Resource
{
    /**
     * @method GET
     * @json
     */
    public function getPosters()
    {
        $user = \Bazalt\Auth::getUser();
        if ($user->isGuest()) {
            return new \Bazalt\Rest\Response(403, 'Access denied');
        }

        $collection = \Components\Events\Model\Poster::getCollection(false, null, null, null);
        $news = $collection->getPage((int)$_GET['page'], 20);
        $res = [];
        foreach ($news as $article) {
            $res [] = $article->toArray();
        }
        $data = [
            'data' => $res,
            'pager' => [
                'current'           => $collection->page(),
                'count'         => $collection->getPagesCount(),
                'total'         => $collection->count(),
                'countPerPage'  => $collection->countPerPage()
            ]
        ];
        return new \Bazalt\Rest\Response(\Bazalt\Rest\Response::OK, $data);
    }

    /**
     * @method POST
     * @accepts multipart/form-data
     * @json
     */
    public function uploadPoster()
    {
        if (!isset($_FILES['file']) || $_FILES['file']['error'] != 0) {
            return new \Bazalt\Rest\Response(500, ['error' => $this->codeToMessage($_FILES['file']['error'])]);
        }
        $file = md5(time());
        $path = SITE_DIR . '/images/posters/';

        if (!is_dir($path)) mkdir($path, 0777, true);

        $path .= $file{0} . $file{1} . '/' ;

        if (!is_dir($path)) mkdir($path, 0777, true);

        $path .= $file{2} . $file{3} . '/' ;

        if (!is_dir($path)) mkdir($path, 0777, true);

        $path .= $file;

        $path .= '.' . strToLower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        move_uploaded_file($_FILES['file']['tmp_name'], $path);

        $file = relativePath($path, SITE_DIR);
        $thumb = thumb($file, '200x300', ['crop' => true]);
        return new \Bazalt\Rest\Response(\Bazalt\Rest\Response::OK, ['file' => $file, 'thumb' => $thumb]);
    }

    private function codeToMessage($code)
    {
        switch ($code) {
            case UPLOAD_ERR_INI_SIZE:
                $message = "Ви намагаєтесь завантажити файл великого розміру";
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $message = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
                break;
            case UPLOAD_ERR_PARTIAL:
                $message = "The uploaded file was only partially uploaded";
                break;
            case UPLOAD_ERR_NO_FILE:
                $message = "No file was uploaded";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $message = "Missing a temporary folder";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $message = "Failed to write file to disk";
                break;
            case UPLOAD_ERR_EXTENSION:
                $message = "File upload stopped by extension";
                break;

            default:
                $message = "Unknown upload error";
                break;
        }
        return $message;
    }

    /**
     * @method POST
     * @accepts application/json
     * @json
     */
    public function savePoster()
    {
        $res = new PosterResource($this->app, $this->request);

        return $res->savePoster();
    }

    public static function getRemoteIp()
    {
        $ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ?
            (isset($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['HTTP_X_REAL_IP'] : $_SERVER['HTTP_X_FORWARDED_FOR']) :
            (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1');
        //localhost for CLI mode

        return $ip;
    }

    public static function getUserAgent()
    {
        if (!empty($_SERVER['HTTP_USER_AGENT'])) {
            return $_SERVER['HTTP_USER_AGENT'];
        }
        return 'unknown';
    }
}
