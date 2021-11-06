<?php

declare(strict_types=1);

namespace Bgreenacre\EnvPaths;

use ArrayAccess;
use RuntimeException;

/**
 * @implements ArrayAccess<string, mixed>
 */
final class EnvPaths implements ArrayAccess
{

    /**
     * Contains the User's ID
     *
     * @var int
     */
    private $uid;

    /**
     * Optional namespace for paths
     *
     * @var string|null
     */
    private $namespace;

    /**
     * User home directory
     *
     * @var string
     */
    private $home;

    /**
     * Container environment paths
     *
     * @var array<string, string|null>
     */
    private $paths = [
        'data'   => null,
        'cache'  => null,
        'config' => null,
        'log'    => null,
        'temp'   => null,
    ];

    /**
     * Constrctor
     *
     * @param string|null $namespace
     * @param string      $suffix
     */
    public function __construct(string $namespace = null, string $suffix = '-php')
    {
        $this->uid = posix_getuid();

        if (! $this->uid) {
            throw new RuntimeException('UID is required and none was found');
        }

        $env = posix_getpwuid($this->uid);

        if (is_array($env) && array_key_exists('dir', $env)) {
            $this->home = $env['dir'];
        }

        if (! $this->home) {
            throw new RuntimeException('A home folder is required and none was found');
        }

        if ($namespace) {
            $this->namespace = $namespace . $suffix;
        }

        $this->setPaths();
    }

    /**
     * join
     *
     * @param array<int, string|null|int>  $to_join
     * @param string                       $sep
     */
    private function join(array $to_join, string $sep = DIRECTORY_SEPARATOR): string
    {
        return array_reduce(
            $to_join,
            function ($path, $part) use ($sep) {
                return $part ? $path . $sep . ltrim((string) $part, $sep) : $path;
            },
            ''
        );
    }

    /**
     * Sets the paths
     */
    private function setPaths(): void
    {
        switch (PHP_OS_FAMILY) {
            case 'Windows':
                $app_data = getenv('APPDATA') !== false
                    ? getenv('APPDATA')
                    : $this->join([ $this->home, 'AppData', 'Roaming' ]);

                $local_app_data = getenv('LOCALAPPDATA') !== false
                    ? getenv('LOCALAPPDATA')
                    : $this->join([ $this->home, 'AppData', 'Local' ]);

                $this->paths = [
                    'data'   => $this->join([ $local_app_data, $this->namespace, 'Data' ]),
                    'cache'  => $this->join([ $local_app_data, $this->namespace, 'Cache' ]),
                    'config' => $this->join([ $app_data, $this->namespace, 'Config' ]),
                    'log'    => $this->join([ $local_app_data, $this->namespace, 'Log' ]),
                    'temp'   => $this->join([ sys_get_temp_dir(), $this->namespace ]),
                ];

                break;
            case 'Darwin':
                $prefix = $this->join([ $this->home, 'Library' ]);

                $this->paths = [
                    'data'   => $this->join([ $prefix, 'Application Support', $this->namespace ]),
                    'cache'  => $this->join([ $prefix, 'Caches', $this->namespace ]),
                    'config' => $this->join([ $prefix, 'Preferences', $this->namespace ]),
                    'log'    => $this->join([ $prefix, 'Logs', $this->namespace ]),
                    'temp'   => $this->join([ sys_get_temp_dir(), $this->namespace ]),
                ];

                break;
            case 'Linux':
            case 'BSD':
            case 'Solaris':
                // https://specifications.freedesktop.org/basedir-spec/basedir-spec-latest.html
                $xdg_data_home = getenv('XDG_DATA_HOME') !== false ? getenv('XDG_DATA_HOME') : null;
                $xdg_cache_home = getenv('XDG_CACHE_HOME') !== false ? getenv('XDG_CACHE_HOME') : null;
                $xdg_config_home = getenv('XDG_CONFIG_HOME') !== false ? getenv('XDG_CONFIG_HOME') : null;
                $xdg_state_home = getenv('XDG_STATE_HOME') !== false ? getenv('XDG_STATE_HOME') : null;

                $this->paths = [
                    'data' => $this->join([
                        $xdg_data_home ?? $this->join([ $this->home, '.local', 'share' ]),
                        $this->namespace,
                    ]),
                    'cache' => $this->join([
                        $xdg_cache_home ?? $this->join([ $this->home, '.cache' ]),
                        $this->namespace,
                    ]),
                    'config' => $this->join([
                        $xdg_config_home ?? $this->join([ $this->home, '.config' ]),
                        $this->namespace,
                    ]),
                    'log' => $this->join([
                        $xdg_state_home ?? $this->join([ $this->home, '.local', 'state' ]),
                        $this->namespace,
                    ]),
                    'temp' => $this->join([ sys_get_temp_dir(), $this->uid, $this->namespace ]),
                ];

                break;
            case 'Unkown':
                throw new RuntimeException('Cannot set paths for unkown environment');
        }
    }

    /**
     * get
     *
     * @param  string $key
     * @return mixed
     */
    public function get(string $key): mixed
    {
        return isset($this->paths[$key]) ? $this->paths[$key] : null;
    }

    /**
     * Cast the paths into an associative array
     *
     * @return array<string, string|null>
     */
    public function toArray(): array
    {
        return $this->paths;
    }

    /**
     * Check if array offset exists
     *
     * @param  string $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return isset($this->paths[$offset]);
    }

    /**
     * Get an offset
     *
     * @param  string $offset
     * @return mixed
     */
    public function offsetGet($offset): mixed
    {
        return isset($this->paths[$offset]) ? $this->paths[$offset] : null;
    }

    /**
     * Set an offset
     *
     * @param  string $offset
     * @param  string  $value
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        $this->paths[$offset] = $value;
    }

    /**
     * Delete an offset which is not allowed
     *
     * @param  string $offset
     * @return void
     */
    public function offsetUnset($offset): void
    {
        throw new RuntimeException('Cannot delete array index for EnvPaths class');
    }
}
