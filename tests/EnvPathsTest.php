<?php

declare(strict_types=1);

namespace Bgreenacre\EnvPaths;

use RuntimeException;
use PHPUnit\Framework\TestCase;

class EnvPathsTest extends TestCase
{
    public function testSettersAndGetters()
    {
        $namespace = 'unicorn';
        $paths = new EnvPaths($namespace);

        $paths->setHome('this is my home');
        $paths->setOs('ReactOS');
        $paths->setDirSeparator('9');

        $this->assertEquals('this is my home', $paths->getHome());
        $this->assertEquals('ReactOS', $paths->getOs());
        $this->assertEquals('9', $paths->getDirSeparator());
    }

    public function testLinux()
    {
        $namespace = 'unicorn';
        $paths = new EnvPaths($namespace);
        $paths->setOs('Linux');
        $paths->setDirSeparator('/');
        $paths->setHome('/home/user');

        foreach ($paths->toArray() as $key => $path) {
            $this->assertStringEndsWith($namespace . '-php', $path);
        }
    }

    public function testCustomSuffix()
    {
        $suffix = 'superawesome';
        $namespace = 'unicorn';
        $paths = new EnvPaths($namespace, $suffix);

        foreach ($paths->toArray() as $key => $path) {
            $this->assertStringEndsWith($namespace . '-' . $suffix, $path);
        }
    }

    public function testKeysExist()
    {
        $namespace = 'unicorn';
        $paths = new EnvPaths($namespace);

        $this->assertArrayHasKey('data', $paths);
        $this->assertArrayHasKey('cache', $paths);
        $this->assertArrayHasKey('config', $paths);
        $this->assertArrayHasKey('log', $paths);
        $this->assertArrayHasKey('temp', $paths);
    }

    public function testUnsetException()
    {
        $namespace = 'unicorn';
        $paths = new EnvPaths($namespace);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to unset index of immutable array for EnvPaths');
        unset($paths['log']);
    }

    public function testSetException()
    {
        $namespace = 'unicorn';
        $paths = new EnvPaths($namespace);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to set index of immutable array for EnvPaths');
        $paths['log'] = 'new value';
    }
}
