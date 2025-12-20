<?php

namespace Kmin\WbmBlog;

class Install
{
    const WEBMAN_PLUGIN = true;

    // 安装类
    protected static $installClass = [
        "\\plugin\\wbm_blog\\api\\Install",
    ];

    // 应用映射关系
    public static $appRelation = array(
        "wbm_blog" => "plugin/wbm_blog",
    );

    // 路径映射关系
    protected static $pathRelation = array();

    /**
     * Install
     * @return void
     */
    public static function install()
    {
        static::installByRelation();
        static::installByAppRelation();
    }

    /**
     * Uninstall
     * @return void
     */
    public static function uninstall()
    {
        self::uninstallByRelation();
        self::uninstallByAppRelation();
    }

    /**
     * installByRelation
     * @return void
     */
    public static function installByRelation()
    {
        foreach (static::$pathRelation as $source => $dest) {
            if ($pos = strrpos($dest, '/')) {
                $parent_dir = base_path() . '/' . substr($dest, 0, $pos);
                if (!is_dir($parent_dir)) {
                    mkdir($parent_dir, 0777, true);
                }
            }
            //symlink(__DIR__ . "/$source", base_path()."/$dest");
            copy_dir(__DIR__ . "/$source", base_path() . "/$dest");
            echo "Create $dest";
        }
    }

    /**
     * uninstallByRelation
     * @return void
     */
    public static function uninstallByRelation()
    {
        foreach (static::$pathRelation as $source => $dest) {
            $path = base_path() . "/$dest";
            if (!is_dir($path) && !is_file($path)) {
                continue;
            }
            echo "Remove $dest";
            if (is_file($path) || is_link($path)) {
                unlink($path);
                continue;
            }
            remove_dir($path);
        }
    }

    /**
     * 安装应用映射
     * 
     * @return void
     */
    public static function installByAppRelation()
    {
        try {
            foreach (static::$appRelation as $source => $dest) {
                $context = null;
                $new_version = static::newVersion($source);
                $old_version = static::oldVersion($source);
                $install_class = "\\plugin\\$source\\api\\Install";
                if ($pos = strrpos($dest, '/')) {
                    $parent_dir = base_path() . '/' . substr($dest, 0, $pos);
                    if (!is_dir($parent_dir)) {
                        mkdir($parent_dir, 0777, true);
                    }
                }
                copy_dir(__DIR__ . "/$source", base_path() . "/$dest");
                echo "Create app $source";
                if ($old_version) {
                    if (class_exists($install_class) && method_exists($install_class, 'beforeUpdate')) {
                        $context = call_user_func([$install_class, 'beforeUpdate'], $old_version, $new_version);
                    }
                    if (class_exists($install_class) && method_exists($install_class, 'update')) {
                        call_user_func([$install_class, 'update'], $old_version, $new_version, $context);
                    }
                } else {
                    if (class_exists($install_class) && method_exists($install_class, 'install')) {
                        call_user_func([$install_class, 'install'], $new_version);
                    }
                }
                echo "Install app $source $new_version";
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public static function uninstallByAppRelation(){
        foreach (static::$appRelation as $source => $dest) {
            $path = base_path() . "/$dest";
            if (!is_dir($path) && !is_file($path)) {
                continue;
            }
            echo "Remove $dest";
            if (is_file($path) || is_link($path)) {
                unlink($path);
                continue;
            }
            remove_dir($path);
        }
    }

    /**
     * 获取新应用版本号
     * 
     * @param string $app 应用名称
     * @return string|null 版本号或null
     */
    public static function newVersion($app): string|null
    {
        if (!is_file($file = __DIR__ . "/$app/config/app.php")) {
            return null;
        }
        $app_config = include $file;
        return $app_config['version'] ?? null;
    }

    /**
     * 获取旧应用版本号
     * 
     * @param string $app 应用名称
     * @return string|null 版本号或null
     */
    public static function oldVersion($app): string|null
    {
        if (!is_file($file = base_path() . "/plugin/$app/config/app.php")) {
            return null;
        }
        $app_config = include $file;
        return $app_config['version'] ?? null;
    }
}
