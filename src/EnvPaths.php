<?php

declare(strict_types=1);

namespace Bgreenacre\EnvPaths;

use RuntimeException;

final class EnvPaths
{
    /**
     * Get the current users ID
     *
     * @return int
     */
    public static function getUID(): int
    {
        if (\function_exists('posix_getuid')) {
            return posix_getuid();
        }

        throw new RuntimeException('Could not determine user ID');
    }

    /**
     * @return bool Whether the host machine is running a Windows OS
     */
    public static function isWindows()
    {
        return \defined('PHP_WINDOWS_VERSION_BUILD');
    }

    /**
     * @throws \RuntimeException If the user home could not reliably be determined
     * @return string            The formal user home as detected from environment parameters
     */
    public static function getUserDirectory(): string
    {
        if (false !== ($home = getenv('HOME'))) {
            return $home;
        }

        if (self::isWindows() && false !== ($home = getenv('USERPROFILE'))) {
            return $home;
        }

        if (\function_exists('posix_getuid') && \function_exists('posix_getpwuid')) {
            $info = posix_getpwuid(posix_getuid());

            if (is_array($info) && isset($info['dir'])) {
                return $info['dir'];
            }
        }

        throw new RuntimeException('Could not determine user directory');
    }

    /**
     * @return string
     */
    public static function getTempDirectory(): string
    {
        return sys_get_temp_dir();
    }

    /**
     * join
     *
     * @param array<int, string|null|int>  $toJoin
     * @param string                       $sep
     */
    public static function join(array $toJoin, string $sep = DIRECTORY_SEPARATOR): string
    {
        return array_reduce(
            $toJoin,
            function ($path, $part) use ($sep) {
                return $part
                    ? $path . $sep . trim((string) $part, $sep . ' ')
                    : $path;
            },
            ''
        );
    }

    /**
     * Get environment paths
     *
     * @param  string|null           $namespace
     * @param  string                $suffix
     * @return array<string, string>
     */
    public static function getPaths(string $namespace = null, string $suffix = 'php'): array
    {
        $home = self::getUserDirectory();
        $uid  = self::getUID();
        $temp = self::getTempDirectory();

        if ($namespace !== null && $suffix !== null) {
            $namespace = $namespace . '-' . ltrim($suffix, '-');
        }

        switch (PHP_OS_FAMILY) {
            case 'Windows':
                $appData = getenv('APPDATA') !== false
                    ? getenv('APPDATA')
                    : self::join([ $home, 'AppData', 'Roaming' ]);

                $localAppData = getenv('LOCALAPPDATA') !== false
                    ? getenv('LOCALAPPDATA')
                    : self::join([ $home, 'AppData', 'Local' ]);

                return [
                    'data'   => self::join([ $localAppData, $namespace, 'Data' ]),
                    'cache'  => self::join([ $localAppData, $namespace, 'Cache' ]),
                    'config' => self::join([ $appData, $namespace, 'Config' ]),
                    'log'    => self::join([ $localAppData, $namespace, 'Log' ]),
                    'temp'   => self::join([ $temp, $namespace ]),
                ];
            case 'Darwin':
                $prefix = self::join([ $home, 'Library' ]);

                return [
                    'data'   => self::join([ $prefix, 'Application Support', $namespace ]),
                    'cache'  => self::join([ $prefix, 'Caches', $namespace ]),
                    'config' => self::join([ $prefix, 'Preferences', $namespace ]),
                    'log'    => self::join([ $prefix, 'Logs', $namespace ]),
                    'temp'   => self::join([ $temp, $namespace ]),
                ];
            case 'Linux':
            case 'BSD':
            case 'Solaris':
                // https://specifications.freedesktop.org/basedir-spec/basedir-spec-latest.html
                $xdgDataHome   = getenv('XDG_DATA_HOME') !== false ? getenv('XDG_DATA_HOME') : null;
                $xdgCacheHome  = getenv('XDG_CACHE_HOME') !== false ? getenv('XDG_CACHE_HOME') : null;
                $xdgConfigHome = getenv('XDG_CONFIG_HOME') !== false ? getenv('XDG_CONFIG_HOME') : null;
                $xdgStateHome  = getenv('XDG_STATE_HOME') !== false ? getenv('XDG_STATE_HOME') : null;

                return [
                    'data' => self::join([
                        $xdgDataHome ?? self::join([ $home, '.local', 'share' ]),
                        $namespace,
                    ]),
                    'cache' => self::join([
                        $xdgCacheHome ?? self::join([ $home, '.cache' ]),
                        $namespace,
                    ]),
                    'config' => self::join([
                        $xdgConfigHome ?? self::join([ $home, '.config' ]),
                        $namespace,
                    ]),
                    'log' => self::join([
                        $xdgStateHome ?? self::join([ $home, '.local', 'state' ]),
                        $namespace,
                    ]),
                    'temp' => self::join([ $temp, $uid, $namespace ]),
                ];
        }

        throw new RuntimeException('Cannot generate paths for environment');
    }
}
