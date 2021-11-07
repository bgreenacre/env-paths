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
     * The os php was built on
     *
     * @var string
     */
    private $os = PHP_OS_FAMILY;

    /**
     * String used as directory separator for paths
     *
     * @var string
     */
    private $dirSeparator = DIRECTORY_SEPARATOR;

    /**
     * Tracks if the paths have been set yet
     *
     * @var boolean
     */
    private $pathsSet = false;

    /**
     * Constructor
     *
     * @param  string|null $namespace
     * @param  string      $suffix
     * @throws RuntimeException Thrown when no uid is found or when no home dir is found
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
            $this->namespace = $namespace . '-' . ltrim($suffix, '-');
        }
    }

    /**
     * join
     *
     * @param array<int, string|null|int>  $toJoin
     */
    private function join(array $toJoin): string
    {
        return array_reduce(
            $toJoin,
            function ($path, $part) {
                return $part
                    ? $path . $this->dirSeparator . ltrim((string) $part, $this->dirSeparator)
                    : $path;
            },
            ''
        );
    }

    /**
     * Sets the paths
     */
    private function setPaths(): void
    {
        $this->pathsSet = true;

        switch ($this->os) {
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
            default:
                throw new RuntimeException('Cannot set paths for unkown environment');
        }
    }

    /**
     * Cast the paths into an associative array
     *
     * @return array<string, string|null>
     */
    public function toArray(): array
    {
        if ($this->pathsSet === false) {
            $this->setPaths();
        }

        return $this->paths;
    }

    /**
     * Check if a key is set in the $paths array
     *
     * @param  string  $key
     * @return bool
     */
    public function has(string $key): bool
    {
        if ($this->pathsSet === false) {
            $this->setPaths();
        }

        return isset($this->paths[$key]);
    }

    /**
     * Get a value from the $paths array by provided key
     *
     * @param  string $key
     * @return mixed
     */
    public function get(string $key)
    {
        return $this->has($key) ? $this->paths[$key] : null;
    }

    /**
     * Set the os prop
     *
     * @param string $os
     */
    public function setOs(string $os): self
    {
        $this->os = $os;
        return $this;
    }

    /**
     * Get the os prop
     *
     * @return string
     */
    public function getOs(): string
    {
        return $this->os;
    }

    /**
     * Set the dirSeparator prop
     *
     * @param string $sep
     */
    public function setDirSeparator(string $sep): self
    {
        $this->dirSeparator = $sep;
        return $this;
    }

    /**
     * Get the dirSeparator prop
     *
     * @return string
     */
    public function getDirSeparator(): string
    {
        return $this->dirSeparator;
    }

    /**
     * Set the home prop
     *
     * @param string $home
     */
    public function setHome(string $home): self
    {
        $this->home = $home;
        return $this;
    }

    /**
     * Get the home prop
     *
     * @return string
     */
    public function getHome(): string
    {
        return $this->home;
    }

    /**
     * Check if array offset exists $paths prop
     *
     * @param  string $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }

    /**
     * Get an offset
     *
     * @param  string $offset
     * @return string|null
     */
    public function offsetGet($offset)
    {
        return $this->has($offset) ? $this->paths[$offset] : null;
    }

    /**
     * Set an offset
     *
     * @param  string $offset
     * @param  string $value
     * @throws RuntimeException Always thrown as $paths is immutable
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        throw new RuntimeException('Unable to set index of immutable array for EnvPaths');
    }

    /**
     * Delete an offset which is not allowed
     *
     * @param  string $offset
     * @throws RuntimeException Always thrown as $paths is immutable
     * @return void
     */
    public function offsetUnset($offset): void
    {
        throw new RuntimeException('Unable to unset index of immutable array for EnvPaths');
    }
}
