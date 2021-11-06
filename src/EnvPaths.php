<?php

declare(strict_types=1);

namespace Bgreenacre\EnvPaths;

use ArrayAccess;
use RuntimeException;

final class EnvPaths implements ArrayAccess {

    private $uid;
    private $namespace;
    private $shell = [];
    private $paths = [
        'data': null,
        'cache': null,
        'config': null,
        'log': null,
        'temp': null,
    ];

    public function __construct($namespace = '', $suffix = '-php', $uid = null): void
    {
        $this->uid = $uid ?? posix_getuid();
        $this->shell = posix_getpwuid($this->uid);

        if ($namespace) {
            $this->namespace = $namespace . $suffix;
        }

        $this->setPaths();
    }

    private function join(array $to_join, string $sep = DIRECTORY_SEPARATOR): string
    {
        return array_reduce($to_join, fn($path, $part) => $part ? $path . $sep . $part : $path, '');
    }

    private function setPaths(): void
    {
        switch(PHP_OS_FAMILY)
        {
            case 'Windows':
                $app_data = getenv('APPDATA') ?? $this->join([ $shell['dir'], 'AppData', 'Roaming' ]);
                $local_app_data = getenv('LOCALAPPDATA') ?? $this->join([ $shell['dir'], 'AppData', 'Local' ]);

                $this->paths = [
                    'data' => $this->join([ $local_app_data, $this->namespace, 'Data' ]),
                    'cache' => $this->join([ $local_app_data, $this->namespace, 'Cache' ]),
                    'config' => $this->join([ $app_data, $this->namespace, 'Config' ]),
                    'log' => $this->join([ $local_app_data, $this->namespace, 'Log' ]),
                    'temp' => $this->join([ sys_get_temp_dir(), $this->namespace ]),
                ];

                break;
            case 'Darwin':
                $prefix = $this->join([ $shell['dir'], 'Library' ]);

                $this->paths = [
                    'data' => $this->join([ $prefix, 'Application Support', $this->namespace ]),
                    'cache' => $this->join([ $prefix, 'Caches', $this->namespace ]),
                    'config' => $this->join([ $prefix, 'Preferences', $this->namespace ]),
                    'log' => $this->join([ $prefix, 'Logs', $this->namespace ]),
                    'temp' => $this->join([ sys_get_temp_dir(), $this->namespace ]),
                ];

                break;
            case 'Linux':
            case 'BSD':
            case 'Solaris':
                // https://specifications.freedesktop.org/basedir-spec/basedir-spec-latest.html
                $this->paths = [
                    'data' => $this->join([
                        getenv('XDG_DATA_HOME') ?? $this->join([ $shell['dir'], '.local', 'share' ]),
                        $this->namespace,
                    ]),
                    'cache' => $this->join([
                        getenv('XDG_CACHE_HOME') ?? $this->join([ $shell['dir'], '.cache' ]),
                        $this->namespace,
                    ]),
                    'config' => $this->join([
                        getenv('XDG_CONFIG_HOME') ?? $this->join([ $shell['dir'], '.config' ]),
                        $this->namespace,
                    ]),
                    'log' => $this->join([
                        getenv('XDG_STATE_HOME') ?? $this->join([ $shell['dir'], '.local', 'state' ]),
                        $this->namespace,
                    ]),
                    'temp' => $this->join([ sys_get_temp_dir(), $this->uid, $this->namespace ]),
                ];

                break;
            case 'Unkown':
            default:
                throw new RuntimeException('Cannot set paths for unkown environment.');
                break;
        }
    }

    public function get($key): void
    {
        return isset($this->paths[$key]) ? $this->paths[$key] : null;
    }

    public function toArray(): array
    {
        return $this->paths;
    }

    public function offsetExists($offset): bool
    {
        return isset($this->paths[$offset]);
    }

    public function offsetGet($offset): mixed
    {
        return isset($this->paths[$offset]) ? $this->paths[$offset] : null;
    }

    public function offsetSet($offset, $value): void
    {
        $this->paths[$offset] = $value;
    }

    public function offsetUnset($offset): void
    {
        unset($this->paths[$offset]);
    }

}
