<?php

declare(strict_types=1);

namespace Bgreenacre\EnvPaths;

use PHPUnit\Framework\TestCase;

class EnvPathsTest extends TestCase
{
    public function testDefault()
    {
        $namespace = 'unicorn';
        $paths = new EnvPaths($namespace);

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
}
