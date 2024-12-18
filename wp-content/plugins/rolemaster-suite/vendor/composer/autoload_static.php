<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit905f697cb50e4fa0b69efa3c3fb28174
{
    public static $files = array (
        '405e185c699a18f8d77f6f8817edae03' => __DIR__ . '/../..' . '/Inc/functions.php',
    );

    public static $prefixLengthsPsr4 = array (
        'R' => 
        array (
            'ROLEMASTER\\Libs\\License\\' => 24,
            'ROLEMASTER\\Libs\\' => 16,
            'ROLEMASTER\\Inc\\Admin\\' => 21,
            'ROLEMASTER\\Inc\\' => 15,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'ROLEMASTER\\Libs\\License\\' => 
        array (
            0 => __DIR__ . '/../..' . '/Libs/License',
        ),
        'ROLEMASTER\\Libs\\' => 
        array (
            0 => __DIR__ . '/../..' . '/Libs',
        ),
        'ROLEMASTER\\Inc\\Admin\\' => 
        array (
            0 => __DIR__ . '/../..' . '/Inc/Admin',
        ),
        'ROLEMASTER\\Inc\\' => 
        array (
            0 => __DIR__ . '/../..' . '/Inc',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit905f697cb50e4fa0b69efa3c3fb28174::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit905f697cb50e4fa0b69efa3c3fb28174::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit905f697cb50e4fa0b69efa3c3fb28174::$classMap;

        }, null, ClassLoader::class);
    }
}
