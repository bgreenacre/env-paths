<?php

declare(strict_types=1);

final class EnvPaths {

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

    public function __construct($namespace = '', $uid = null)
    {
        $this->uid = $uid ?? \posix_getuid();
        $this->shell = \posix_getpwuid($this->uid);
        $this->setPaths();
    }

    protected function setPaths()
    {
        switch(PHP_OS_FAMILY)
        {
            case 'Windows':
                $this->paths = [
                    'data' => $shell['dir'] . DIRECTORY_SEPARATOR . '.local' . DIRECTORY_SEPARATOR . 'share' . ($this->namespace ? DIRECTORY_SEPARATOR . $this->namespace : ''),
                    'cache' => $shell['dir'] . DIRECTORY_SEPARATOR . '.cache' . ($this->namespace ? DIRECTORY_SEPARATOR . $this->namespace : ''),
                    'config' => $shell['dir'] . DIRECTORY_SEPARATOR . '.config' . ($this->namespace ? DIRECTORY_SEPARATOR . $this->namespace : ''),
                    'log' => $shell['dir'] . DIRECTORY_SEPARATOR . '.local' . DIRECTORY_SEPARATOR . 'state' . ($this->namespace ? DIRECTORY_SEPARATOR . $this->namespace : ''),
                    'temp' => \sys_get_temp_dir() . DIRECTORY_SEPARATOR . $this->uid . ($this->namespace ? DIRECTORY_SEPARATOR . $this->namespace : ''),
                ];

                break;
            case 'Darwin':
                $prefix = $shell['dir'] . DIRECTORY_SEPARATOR . 'Library' . DIRECTORY_SEPARATOR;

                $this->paths = [
                    'data' => $prefix . 'Application Support' . DIRECTORY_SEPARATOR . ($this->namespace ? DIRECTORY_SEPARATOR . $this->namespace : ''),
                    'cache' => $prefix . 'Caches' . ($this->namespace ? DIRECTORY_SEPARATOR . $this->namespace : ''),
                    'config' => $prefix . 'Preferences' . ($this->namespace ? DIRECTORY_SEPARATOR . $this->namespace : ''),
                    'log' => $prefix . 'Logs' . ($this->namespace ? DIRECTORY_SEPARATOR . $this->namespace : ''),
                    'temp' => \sys_get_temp_dir() . ($this->namespace ? DIRECTORY_SEPARATOR . $this->namespace : ''),
                ];

                break;
            case 'Linux':
            case 'BSD':
            case 'Solaris':
                $this->paths = [
                    'data' => $shell['dir'] . DIRECTORY_SEPARATOR . '.local' . DIRECTORY_SEPARATOR . 'share' . ($this->namespace ? DIRECTORY_SEPARATOR . $this->namespace : ''),
                    'cache' => $shell['dir'] . DIRECTORY_SEPARATOR . '.cache' . ($this->namespace ? DIRECTORY_SEPARATOR . $this->namespace : ''),
                    'config' => $shell['dir'] . DIRECTORY_SEPARATOR . '.config' . ($this->namespace ? DIRECTORY_SEPARATOR . $this->namespace : ''),
                    'log' => $shell['dir'] . DIRECTORY_SEPARATOR . '.local' . DIRECTORY_SEPARATOR . 'state' . ($this->namespace ? DIRECTORY_SEPARATOR . $this->namespace : ''),
                    'temp' => \sys_get_temp_dir() . DIRECTORY_SEPARATOR . $this->uid . ($this->namespace ? DIRECTORY_SEPARATOR . $this->namespace : ''),
                ];

                break;
            case 'Unkown':
            default:
                throw new \RuntimeException('Cannot register paths for unkown environment.');
                break;
        }
    }
}
