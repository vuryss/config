<?php

namespace Vuryss\Config;

use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;
use stdClass;

class ConfigTest extends TestCase
{
    public function testInitialization()
    {
        $cache = $this->getMockBuilder(CacheInterface::class)
            ->getMock();

        $config = new Config([TEST_DATA_DIR . '/config.yaml', TEST_DATA_DIR . '/config-2.yaml'], $cache);
        $this->assertTrue($config instanceof ConfigInterface);
    }

    public function testInvalidFiles()
    {
        $this->expectException(Exception::class);
        new Config(true);
    }

    public function testInvalidFiles2()
    {
        $this->expectException(Exception::class);
        new Config([true]);
    }

    public function testInvalidFiles3()
    {
        $this->expectException(Exception::class);
        new Config(['/invalid-file']);
    }

    public function testHas()
    {
        $config = new Config([TEST_DATA_DIR . '/config.yaml', TEST_DATA_DIR . '/config-2.yaml']);
        $this->assertTrue($config instanceof ConfigInterface);

        $this->assertTrue($config->has('key1'));
        $this->assertTrue($config->has('key3.subkey1.final-key-1'));
        $this->assertFalse($config->has('key3.subkey1.'));
        $this->assertFalse($config->has('key3.subkey1.missing-key'));
        $this->assertFalse($config->has('key-invalid'));

        $cache = $this->getMockBuilder(CacheInterface::class)
            ->getMock();

        $config = new Config([TEST_DATA_DIR . '/config.yaml', TEST_DATA_DIR . '/config-2.yaml'], $cache);
        $this->assertTrue($config instanceof ConfigInterface);

        $this->assertTrue($config->has('key1'));
        $this->assertTrue($config->has('key3.subkey1.final-key-1'));
        $this->assertFalse($config->has('key3.subkey1.'));
        $this->assertFalse($config->has('key3.subkey1.missing-key'));
        $this->assertFalse($config->has('key-invalid'));
    }

    public function testGet()
    {
        $config = new Config([TEST_DATA_DIR . '/config.yaml', TEST_DATA_DIR . '/config-2.yaml']);
        $this->assertTrue($config instanceof ConfigInterface);

        $this->assertEquals('value1', $config->get('key1'));
        $this->assertEquals(['array', 'with', 'values'], $config->get('key3.subkey1.final-key-1'));
        $this->assertNull($config->get('key3.subkey1.'));
        $this->assertEquals('default-value', $config->get('key3.subkey1.missing-key', 'default-value'));

        $cache = $this->getMockBuilder(CacheInterface::class)
            ->getMock();

        $config = new Config([TEST_DATA_DIR . '/config.yaml', TEST_DATA_DIR . '/config-2.yaml'], $cache);
        $this->assertTrue($config instanceof ConfigInterface);

        $this->assertEquals('value1', $config->get('key1'));
        $this->assertEquals(['array', 'with', 'values'], $config->get('key3.subkey1.final-key-1'));
        $this->assertNull($config->get('key3.subkey1.'));
        $this->assertEquals('default-value', $config->get('key3.subkey1.missing-key', 'default-value'));
    }

    public function testSet()
    {
        $config = new Config([TEST_DATA_DIR . '/config.yaml', TEST_DATA_DIR . '/config-2.yaml']);
        $this->assertTrue($config instanceof ConfigInterface);

        $this->assertTrue($config->set('key1', 'new-value'));
        $this->assertEquals('new-value', $config->get('key1'));

        $this->assertTrue($config->set('key3.subkey1.final-key-1', ['array', 'of', 'new', 'values']));
        $this->assertTrue($config->set('key3.subkey1.other-key-1', 'other-value'));

        $this->assertEquals(['array', 'of', 'new', 'values'], $config->get('key3.subkey1.final-key-1'));
        $this->assertEquals('other-value', $config->get('key3.subkey1.other-key-1'));
        $this->assertFalse($config->set('key3.subkey1.', 'bla'));

        $config = new Config([TEST_DATA_DIR . '/config.yaml', TEST_DATA_DIR . '/config-2.yaml']);
        $this->assertTrue($config instanceof ConfigInterface);

        $this->assertEquals('value1', $config->get('key1'));
        $this->assertEquals(['array', 'with', 'values'], $config->get('key3.subkey1.final-key-1'));
        $this->assertFalse($config->has('key3.subkey1.other-key-1'));

        $cache = $this->getMockBuilder(CacheInterface::class)
            ->getMock();

        $config = new Config([TEST_DATA_DIR . '/config.yaml', TEST_DATA_DIR . '/config-2.yaml'], $cache);
        $this->assertTrue($config instanceof ConfigInterface);

        $this->assertTrue($config->set('key1', 'new-value'));
        $this->assertEquals('new-value', $config->get('key1'));
        $this->assertTrue($config->set('key3.subkey1.final-key-1', ['array', 'of', 'new', 'values']));
        $this->assertEquals(['array', 'of', 'new', 'values'], $config->get('key3.subkey1.final-key-1'));
        $this->assertFalse($config->set('key3.subkey1.', 'bla'));
    }

    public function testCaching()
    {
        // Without cached config
        $cache = $this->getMockBuilder(CacheInterface::class)
            ->getMock();

        $cache->expects($this->exactly(1))
            ->method('get')
            ->with(Config::CACHE_KEY . ':cache-config')
            ->willReturn(null);

        $cache->expects($this->exactly(1))
            ->method('setMultiple')
            ->willReturn(true);

        $config = new Config([TEST_DATA_DIR . '/config.yaml', TEST_DATA_DIR . '/config-2.yaml'], $cache);
        $this->assertTrue($config instanceof ConfigInterface);

        // With cached config, without checking for updates
        $cache = $this->getMockBuilder(CacheInterface::class)
            ->getMock();

        $cache->expects($this->exactly(1))
            ->method('get')
            ->with(Config::CACHE_KEY . ':cache-config')
            ->willReturn(['key1' => 'cached-value']);

        $config = new Config([TEST_DATA_DIR . '/config.yaml', TEST_DATA_DIR . '/config-2.yaml'], $cache, false);
        $this->assertTrue($config instanceof ConfigInterface);
        $this->assertEquals('cached-value', $config->get('key1'));

        // With cached config, with check for updated, but missing data for the last cache
        $cache = $this->getMockBuilder(CacheInterface::class)
            ->getMock();

        $cache->expects($this->exactly(1))
            ->method('get')
            ->with(Config::CACHE_KEY . ':cache-config')
            ->willReturn(['key1' => 'cached-value']);

        $config = new Config([TEST_DATA_DIR . '/config.yaml', TEST_DATA_DIR . '/config-2.yaml'], $cache, true);
        $this->assertTrue($config instanceof ConfigInterface);
        $this->assertEquals('value1', $config->get('key1'));

        // With cached config, with check for updates, with need to update
        $cache = $this->getMockBuilder(CacheInterface::class)
            ->getMock();

        $cache->expects($this->exactly(1))
            ->method('get')
            ->with(Config::CACHE_KEY . ':cache-config')
            ->willReturn(['key1' => 'cached-value']);

        $cache->expects($this->exactly(1))
            ->method('getMultiple')
            ->with([Config::CACHE_KEY . ':cache-time', Config::CACHE_KEY . ':cache-files'])
            ->willReturn([Config::CACHE_KEY . ':cache-time' => 5, Config::CACHE_KEY . ':cache-files' => md5('invalid')]);

        $config = new Config([TEST_DATA_DIR . '/config.yaml', TEST_DATA_DIR . '/config-2.yaml'], $cache, true);
        $this->assertTrue($config instanceof ConfigInterface);
        $this->assertEquals('value1', $config->get('key1'));
    }
}
