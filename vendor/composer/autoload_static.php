<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitd9ee670dab8a6f622c5bbeeab2a3d80d
{
    public static $prefixLengthsPsr4 = array (
        's' => 
        array (
            'src\\' => 4,
        ),
        'c' => 
        array (
            'core\\' => 5,
        ),
        'C' => 
        array (
            'ClanCats\\Hydrahon\\' => 18,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'src\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
        'core\\' => 
        array (
            0 => __DIR__ . '/../..' . '/core',
        ),
        'ClanCats\\Hydrahon\\' => 
        array (
            0 => __DIR__ . '/..' . '/clancats/hydrahon/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitd9ee670dab8a6f622c5bbeeab2a3d80d::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitd9ee670dab8a6f622c5bbeeab2a3d80d::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitd9ee670dab8a6f622c5bbeeab2a3d80d::$classMap;

        }, null, ClassLoader::class);
    }
}
