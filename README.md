Env Paths
=========

[![Latest Version](https://img.shields.io/packagist/v/bgreenacre/env-paths.svg?style=flat-square)](https://packagist.org/packages/bgreenacre/env-paths)
[![Total Downloads](https://img.shields.io/packagist/dt/bgreenacre/env-paths.svg?style=flat-square)](https://packagist.org/packages/bgreenacre/env-paths)
[![Software License](https://img.shields.io/badge/License-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Build Status](https://img.shields.io/github/workflow/status/bgreenacre/env-paths/Tests/main.svg?style=flat-square)](https://github.com/bgreenacre/env-paths/actions?query=workflow%3ATests+branch%3Amain)

Provides paths for where to store things like cache, configs, data etc. given the environment and user that is running the application.
Heavily inspired by the [envPaths nodejs package](https://github.com/sindresorhus/env-paths) and composer's way finding home folder of the user.

Requirements
------------

 * PHP (7.2+)

Usage
-----

Example

```php
$paths = Bgreenacre\EnvPaths\EnvPaths::getPaths('MyApp');

/**
 *  - macOS: `~/Library/Application Support/MyApp-php`
 *  - Windows: `%LOCALAPPDATA%\MyApp-php\Data` (for example, `C:\Users\USERNAME\AppData\Local\MyApp-php\Data`)
 *  - Linux: `~/.local/share/MyApp-php` (or `$XDG_DATA_HOME/MyApp-php`)
 */
echo $paths['data'];

/**
 * - macOS: `~/Library/Caches/MyApp-php`
 * - Windows: `%LOCALAPPDATA%\MyApp-php\Cache` (for example, `C:\Users\USERNAME\AppData\Local\MyApp-php\Cache`)
 * - Linux: `~/.cache/MyApp-php` (or `$XDG_CACHE_HOME/MyApp-php`)
 */
echo $paths['cache'];

/**
 *  - macOS: `~/Library/Preferences/MyApp-php`
 *  - Windows: `%APPDATA%\MyApp-php\Config` (for example, `C:\Users\USERNAME\AppData\Roaming\MyApp-php\Config`)
 *  - Linux: `~/.config/MyApp-php` (or `$XDG_CONFIG_HOME/MyApp-php`)
 */
echo $paths['config'];

/**
 *  - macOS: `~/Library/Logs/MyApp-php`
 *  - Windows: `%LOCALAPPDATA%\MyApp-php\Log` (for example, `C:\Users\USERNAME\AppData\Local\MyApp-php\Log`)
 *  - Linux: `~/.local/state/MyApp-php` (or `$XDG_STATE_HOME/MyApp-php`)
 */
echo $paths['log'];

/**
 *  - macOS: `/var/folders/jf/f2twvvvs5jl_m49tf034ffpw0000gn/T/MyApp-php`
 *  - Windows: `%LOCALAPPDATA%\Temp\MyApp-php` (for example, `C:\Users\USERNAME\AppData\Local\Temp\MyApp-php`)
 *  - Linux: `/tmp/USERNAME/MyApp-php`
 */
echo $paths['temp'];
```

License
-------

This library is licensed under the MIT License - see the LICENSE file
for details.
