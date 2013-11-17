<?php

require 'vendor/autoload.php';

if (!isset($_GET['file'])) {
    header('HTTP/1.0 404 Not Found');
    exit('Not found');
}

class Operations extends \Bazalt\Thumbs\Operations
{
    public function watermark(\Imagine\Image\ImageInterface $image, $options, $allOptions)
    {
        $imagine = new \Imagine\Gd\Imagine();
        $wm = $imagine->open(__DIR__ . '/templates/images/watermark.png');

        $size = $image->getSize();
        $wmSize = $wm->getSize();
        list($x, $y) = explode(' ', $options);
        if (!is_numeric($x)) {
            $x = ($x == 'right') ? ($size->getWidth() - $wmSize->getWidth()) : 0;
            if ($x < 0) $x = 0;
        }
        if (!is_numeric($y)) {
            $y = ($y == 'bottom') ? ($size->getHeight() - $wmSize->getHeight()) : 0;
            if ($y < 0) $y = 0;
        }

        $point = new \Imagine\Image\Point($x, $y);
        return $image->paste($wm, $point);
    }

    public function fit(\Imagine\Image\ImageInterface $image, $options, $allOptions)
    {
        $imagine = new \Imagine\Gd\Imagine();

        $width = (int)$allOptions['size']['width'];
        $height = (int)$allOptions['size']['height'];

        $wmSize = $image->getSize();
        $img = $imagine->create(new \Imagine\Image\Box($width, $height));
        $white = new \Imagine\Image\Color('000', 100);

        $fill  = new Imagine\Image\Fill\Gradient\Vertical(
            $height,
            $white,
            $white
        );
        $img = $img->fill($fill);
        $point = new \Imagine\Image\Point(($width - $wmSize->getWidth()) / 2, ($height - $wmSize->getHeight()) / 2);

        return $img->paste($image, $point);
    }

    public function crop(\Imagine\Image\ImageInterface $image, $options, $allOptions)
    {
        $mode = \Imagine\Image\ImageInterface::THUMBNAIL_OUTBOUND;

        $width = (int)$allOptions['size']['width'];
        $height = (int)$allOptions['size']['height'];

        $image = $this->originalImage();
        $size    = new Imagine\Image\Box($width, $height);
        return $image->thumbnail($size, $mode);
    }

    public function grayscale(\Imagine\Image\ImageInterface $image, $options, $allOptions)
    {
        $image->effects()->grayscale();
        return $image;
    }

    public function sepia(\Imagine\Image\ImageInterface $image, $options, $allOptions)
    {
        $image->effects()
            ->grayscale()
            ->colorize(new \Imagine\Image\Color('#643200'));
        return $image;
    }
}

$thumb = \Bazalt\Thumbs\Image::generateThumb(__DIR__ . $_GET['file'], new Operations());
if ($thumb) {
    switch (pathinfo($thumb, PATHINFO_EXTENSION)) {
        case 'png':
            header('Content-Type: image/png');
            break;
        case 'jpg':
        case 'jpeg':
            header('Content-Type: image/jpeg');
            break;
    }
    readfile($thumb);
    exit;
}
