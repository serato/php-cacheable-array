<?php
declare(strict_types=1);

namespace Serato\CacheableArray\Test;

use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;
use Serato\CacheableArray\CacheableArray;
use Symfony\Component\Cache\Simple\FilesystemCache;

class CacheableArrayTest extends TestCase
{
    public function testArrayAccess()
    {
        $ac = new CacheableArray($this->getPsrCache(), 'abc');

        $ac['key1'] = 'value1';
        $ac['key2'] = 'value2';

        $this->assertEquals($ac['key1'], 'value1');
        $this->assertEquals($ac['key2'], 'value2');

        $this->assertTrue(isset($ac['key1']));
        $this->assertTrue(isset($ac['key2']));

        $this->assertFalse(isset($ac['no_such_key']));

        unset($ac['key2']);
        $this->assertFalse(isset($ac['key2']));
    }

    public function testCountable()
    {
        $ac = new CacheableArray($this->getPsrCache(), 'abc');

        $ac['key1'] = 'value1';
        $this->assertEquals(count($ac), 1);

        $ac['key2'] = 'value2';
        $this->assertEquals(count($ac), 2);
    }

    public function testIterator()
    {
        $source = [
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3'
        ];

        $ac = new CacheableArray($this->getPsrCache(), 'abc');
        foreach ($source as $k => $v) {
            $ac[$k] = $v;
        }

        foreach ($ac as $k => $v) {
            $this->assertEquals($v, $source[$k]);
        }
    }

    public function testCachePersistence()
    {
        $ac = new CacheableArray($this->getPsrCache(), 'abc');
        $ac['key1'] = 'value1';
        $ac['key2'] = 'value2';
        unset($ac);

        $ac = new CacheableArray($this->getPsrCache(), 'abc');
        $this->assertEquals($ac['key1'], 'value1');
        $this->assertEquals($ac['key2'], 'value2');
        unset($ac);

        $ac = new CacheableArray($this->getPsrCache(), 'abc');
        $this->assertEquals(count($ac), 2);
        unset($ac['key1']);
        unset($ac);

        $ac = new CacheableArray($this->getPsrCache(), 'abc');
        $this->assertEquals(count($ac), 1);
        $this->assertTrue(isset($ac['key2']));
        $this->assertFalse(isset($ac['key1']));
    }

    private function getPsrCache(): CacheInterface
    {
        return new FilesystemCache(str_replace('\\', '-', __CLASS__), 60, $this->getFileCacheDir());
    }

    private function getFileCacheDir(): string
    {
        return '/tmp/' . md5(__CLASS__);
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->rmrf($this->getFileCacheDir());
    }

    /**
     * Remove the directory and its content (all files and subdirectories).
     * @param string $dir the directory name
     */
    private function rmrf($dir)
    {
        foreach (glob($dir) as $file) {
            if (is_dir($file)) {
                $this->rmrf("$file/*");
                rmdir($file);
            } else {
                unlink($file);
            }
        }
    }
}
