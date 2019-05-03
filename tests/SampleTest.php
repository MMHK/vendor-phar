<?php

/**
 * Created by PhpStorm.
 * User: mixmedia
 * Date: 2019/4/30
 * Time: 20:05
 */
class SampleTest extends \PHPUnit\Framework\TestCase
{
    public function test_sample() {
        $vendor_dir = dirname(__DIR__).'/vendor';
        $dist_dir = __DIR__;

        $packager = new \MMHK\VendorPhar\Packager($vendor_dir);

        $packager->export($dist_dir);

        $this->assertTrue(true);
    }


    public function test_getRelativePath() {
        $vendor_dir = dirname(__DIR__).'/vendor';

        $packager = new \MMHK\VendorPhar\Packager($vendor_dir);

        $path = $packager->getRelativePath(__DIR__, dirname(__DIR__));

        var_dump($path);
    }

    public function test_preg_match() {
        $path = '/readme.md';

        $result = preg_match('/sebastian\/(.*)/i', $path);

        var_dump($result);
    }
}