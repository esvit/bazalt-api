<?php
/**
 * relativePath
 *
 * @category   Core
 * @package    Core
 * @subpackage Helpers
 * @author     Vitalii Savchuk <esvit666@gmail.com>
 * @author     Alex Slubsky <aslubsky@gmail.com>
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version    SVN: $Rev: 110 $
 * @link       http://bazalt.org.ua/
 */

/**
 * Повертає відносний шлях ($path відносно $relPath)
 *
 * @param string $path    Шлях
 * @param string $relPath Шлях, відносно якого повертається частина
 *
 * <code>
 * relativePath('/var/www/sites/myproject/apps/Site/Model','/var/www/sites/myproject/apps') -> /Site/Model
 * </code>
 *
 * @return string
 */
function relativePath($path, $relPath = SITE_DIR)
{
    $siteDir = str_replace('\\', '/', $relPath);
    $path = str_replace('\\', '/', $path);

    $path = str_replace($siteDir, '', $path);
    return $path;
}