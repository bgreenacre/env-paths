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
}
