<?php

declare(strict_types=1);

namespace Bgreenacre\EnvPaths;

use RuntimeException;
use PHPUnit\Framework\TestCase;

class EnvPathsTest extends TestCase
{
    public function testDefault()
    {
        putenv('HOME=/home/test');
        $namespace = 'unicorn';
        $paths = EnvPaths::getPaths($namespace);

        foreach ($paths as $path) {
            $this->assertStringContainsString($namespace . '-php', $path);
        }
    }

    public function testCustomSuffix()
    {
        putenv('HOME=/home/test');
        $suffix = 'superawesome';
        $namespace = 'unicorn';
        $paths = EnvPaths::getPaths($namespace, $suffix);

        foreach ($paths as $path) {
            $this->assertStringContainsString($namespace . '-' . $suffix, $path);
        }
    }

    public function testKeysExist()
    {
        putenv('HOME=/home/test');
        $namespace = 'unicorn';
        $paths = EnvPaths::getPaths($namespace);

        $this->assertArrayHasKey('data', $paths);
        $this->assertArrayHasKey('cache', $paths);
        $this->assertArrayHasKey('config', $paths);
        $this->assertArrayHasKey('log', $paths);
        $this->assertArrayHasKey('temp', $paths);
    }

    public function testIsWindows()
    {
        // Compare 2 common tests for Windows to the built-in Windows test
        $this->assertEquals(('\\' === DIRECTORY_SEPARATOR), EnvPaths::isWindows());
        $this->assertEquals(defined('PHP_WINDOWS_VERSION_MAJOR'), EnvPaths::isWindows());
    }

    public function testJoin()
    {
        $result = EnvPaths::join(['  first ', ' second', 'last '], '|');

        $this->assertEquals('|first|second|last', $result);
    }
}
