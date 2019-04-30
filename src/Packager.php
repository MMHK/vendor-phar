<?php
/**
 * Created by PhpStorm.
 * User: mixmedia
 * Date: 2019/4/30
 * Time: 19:48
 */

namespace MMHK\VendorPhar;


class Packager
{

    /**
     * @var string
     */
    protected $vendor_dir;
    /**
     * Packager constructor.
     */
    public function __construct($vendor)
    {
        $this->vendor_dir = str_replace(DIRECTORY_SEPARATOR, '/', $vendor);
    }


    public function export($outPath) {
        $outPath = rtrim(rtrim($outPath, DIRECTORY_SEPARATOR), '/');
        $outPath = str_replace(DIRECTORY_SEPARATOR, '/', $outPath);

        $phar = new \Phar($outPath.'/vendor.phar',
            \FilesystemIterator::CURRENT_AS_FILEINFO,
            'vendor.phar');

        $vendor_base_path = dirname($this->vendor_dir);
        $vendor_base_path = $this->getRelativePath($outPath, $vendor_base_path);
        $vendor_base_path = '/' . rtrim($vendor_base_path, '/');

        $phar->startBuffering();
        $vendor_list = new \ArrayIterator($this->getVendorFiles($this->vendor_dir));
        $phar->buildFromIterator($vendor_list, $this->vendor_dir);
        $this->fixBaseDir($phar);
        $phar->setStub("
<?php
\Phar::interceptFileFuncs();
\Phar::mount(\Phar::running(true) . '/.mount/', __DIR__.'{$vendor_base_path}/');
return require_once 'phar://' . __FILE__ . DIRECTORY_SEPARATOR . 'autoload.php';
__HALT_COMPILER();
        ");
        $phar->stopBuffering();
    }

    /**
     * 更新 composer 相关 baseDir
     * @param \Phar $phar
     * @param string $file
     */
    public function updateBaseDir(\Phar $phar, string $file) {
        $content = \file_get_contents($phar[$file]->getPathname());
        if (false !== $content) {
            $phar[$file] = \preg_replace(
                '/\$baseDir\s*=\s*dirname\(\$vendorDir\);/m',
                '$baseDir=\\\\Phar::running(true).\'/.mount\';',
                $content
            );
        }
    }

    /**
     * 修正 composer 相关路径问题
     * @param \Phar $phar
     */
    public function fixBaseDir(\Phar $phar) {
        if (isset($phar['composer/autoload_classmap.php'])) {
            $this->updateBaseDir($phar, 'composer/autoload_classmap.php');
        }
        if (isset($phar['composer/autoload_files.php'])) {
            $this->updateBaseDir($phar, 'composer/autoload_files.php');
        }
        if (isset($phar['composer/autoload_namespaces.php'])) {
            $this->updateBaseDir($phar, 'composer/autoload_namespaces.php');
        }
        if (isset($phar['composer/autoload_psr4.php'])) {
            $this->updateBaseDir($phar, 'composer/autoload_psr4.php');
        }
        if (isset($phar['composer/autoload_static.php'])) {
            $content = file_get_contents($phar['composer/autoload_static.php']->getPathname());
            if (false !== $content) {
                /**
                 * @var string $autoloadStaticContent
                 */
                $autoloadStaticContent = preg_replace(
                    '/__DIR__\s*\.\s*\'\/..\/..\'\s*\.\s*/m',
                    'PHAR_RUNNING . ',
                    $content,
                    -1,
                    $replaced
                );
                if ($replaced > 0) {
                    $autoloadStaticContent = str_replace(
                        'namespace Composer\Autoload;',
                        'namespace Composer\Autoload;' . PHP_EOL . PHP_EOL . "define('PHAR_RUNNING',\\Phar::running(true).'/.mount');",
                        $autoloadStaticContent
                    );
                }
                $phar['composer/autoload_static.php'] = $autoloadStaticContent;
            }

        }
    }

    /**
     * 获取 vendor 目录的所有文件
     * @param string $vendor_dir
     * @return array
     */
    public function getVendorFiles($vendor_dir) {
        $directory = new \RecursiveDirectoryIterator($vendor_dir);
        $iterator = new \RecursiveIteratorIterator($directory);
        $files = [];
        foreach ($iterator as $info) {
            /**
             * @var $info \SplFileInfo
             */
            /**
             * 忽略目录
             */
            if ($info->isDir()) {
                continue;
            }
            $path = str_replace(DIRECTORY_SEPARATOR, '/', $info->getRealPath());

            /**
             * 忽略隐藏文件
             */
            if (stripos($path, '/.') !== false) {
                continue;
            }
            /**
             * 忽略测试文件
             */
            if (stripos($path, '/tests') !== false) {
                continue;
            }


            $files[] = $info;
        }

        return $files;
    }

    public function getRelativePath($from, $to)
    {
        // some compatibility fixes for Windows paths
        $from = is_dir($from) ? rtrim($from, '\/') . '/' : $from;
        $to   = is_dir($to)   ? rtrim($to, '\/') . '/'   : $to;
        $from = str_replace('\\', '/', $from);
        $to   = str_replace('\\', '/', $to);

        $from     = explode('/', $from);
        $to       = explode('/', $to);
        $relPath  = $to;

        foreach($from as $depth => $dir) {
            // find first non-matching dir
            if($dir === $to[$depth]) {
                // ignore this directory
                array_shift($relPath);
            } else {
                // get number of remaining dirs to $from
                $remaining = count($from) - $depth;
                if($remaining > 1) {
                    // add traversals up to first matching dir
                    $padLength = (count($relPath) + $remaining - 1) * -1;
                    $relPath = array_pad($relPath, $padLength, '..');
                    break;
                } else {
                    $relPath[0] = './' . $relPath[0];
                }
            }
        }
        return implode('/', $relPath);
    }
}