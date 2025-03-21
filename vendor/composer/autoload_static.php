<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitb7293da0efd8ccec6635120ca3bcf95a
{
    public static $prefixLengthsPsr4 = array (
        'c' => 
        array (
            'chillerlan\\Settings\\' => 20,
            'chillerlan\\QRCode\\' => 18,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'chillerlan\\Settings\\' => 
        array (
            0 => __DIR__ . '/..' . '/chillerlan/php-settings-container/src',
        ),
        'chillerlan\\QRCode\\' => 
        array (
            0 => __DIR__ . '/..' . '/chillerlan/php-qrcode/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitb7293da0efd8ccec6635120ca3bcf95a::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitb7293da0efd8ccec6635120ca3bcf95a::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitb7293da0efd8ccec6635120ca3bcf95a::$classMap;

        }, null, ClassLoader::class);
    }
}
