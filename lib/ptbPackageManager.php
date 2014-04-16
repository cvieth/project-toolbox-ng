<?php
/**
 * PHP Project Toolbox NG - Package Manager
 * @author Christoph Vieth <christoph@vieth.me>
 */

// Load Configuration
require_once(ptbCoreConfig::pathRoot . ptbCoreConfig::pathConfig . 'ptbPackageConfig.php');

/**
 * Class ptbPackageManager
 */
class ptbPackageManager
{
    public static function packageIsInstalled($packageName)
    {
        if (file_exists(ptbCoreConfig::pathRoot . ptbCoreConfig::pathPackages . $packageName . '.json')) {
            return true;
        } else return false;
    }

    /**
     * getPackageDependencies($packageName)
     *
     * Returns a list of dependent packages fpr a given package
     *
     * @param $packageName
     * @return array
     */
    public static function getPackageDependencies($packageName)
    {
        $packageDependencies = array();
        if (is_array(self::getPackageProperty($packageName, "dependencies"))) {
            $packageDependencies = self::getPackageProperty($packageName, "dependencies");
        }

        $collectedDependencies = array();
        foreach ($packageDependencies as $dependencie) {
            $recursiveDependencies = self::getPackageDependencies($dependencie);
            if ((isset($recursiveDependencies)) && is_array($recursiveDependencies)) {
                $collectedDependencies = array_unique(array_merge($collectedDependencies, $recursiveDependencies));
            }
        }

        return array_unique(array_merge($packageDependencies, $collectedDependencies));
    }

    /**
     * getPackageProperty($packageName, $propertyName)
     *
     * Get
     *
     * @param $packageName
     * @param $propertyName
     * @return mixed
     * @throws Exception
     */
    public static function getPackageProperty($packageName, $propertyName)
    {
        if (self::packageIsAvailable($packageName)) {
            $packageDefinition = new ReflectionObject(self::getPackageDefinition($packageName));
            $packageDefinitionTitles = $packageDefinition->getProperties();
            foreach ($packageDefinitionTitles as $packageDefinitionTitle) {
                if ($packageDefinitionTitle->getName() == $propertyName) {
                    return $packageDefinitionTitle->getValue(self::getPackageDefinition($packageName));
                }
            }
            throw new Exception("Property not found");
        } else throw new Exception("Package not available");
    }

    /**
     * packageIsAvailable($packageName)
     *
     * This function checks if a given package is available in the cache
     *
     * @param $packageName
     * @return bool
     * @throws Exception
     */
    public static function packageIsAvailable($packageName)
    {
        // Check if cache is available
        if (self::cacheIsAvailable()) {
            // Get Packet Cache
            $packageCache = new ReflectionObject(self::parsePackageCache());
            $cachePackageNames = $packageCache->getProperties();
            foreach ($cachePackageNames as $cachePackageName) {
                if ($cachePackageName->getName() == $packageName) {
                    return true;
                }
            }
            return false;
        } else throw new Exception("Cache not available");
    }

    /**
     * cacheIsAvailable()
     *
     * Checks if package cache is available and populated
     *
     * @return bool
     */
    public static function cacheIsAvailable()
    {
        if (file_exists(ptbCoreConfig::pathRoot . ptbCoreConfig::pathTemporary . ptbPackageConfig::$filePackageCache)) {
            if (self::getNumAvailablePackets() > 0) {
                return true;
            } else return false;
        }
        return false;
    }

    /**
     * getNumAvailablePackets()
     *
     * get number of available packages
     *
     * @return int
     */
    public static function getNumAvailablePackets()
    {
        $packageCache = self::parsePackageCache();
        if ((isset($packageCache)) && (is_object($packageCache))) {
            return count(get_object_vars($packageCache));
        } else {
            return 0;
        }
    }

    /**
     * parsePackageCache()
     *
     * This function fist checks if the cache file exists, loads the file and parses it and returns the result.
     * If no file exists it returns false.
     *
     * @return bool|object
     * @throws Exception
     */
    public static function parsePackageCache()
    {
        if (file_exists(ptbCoreConfig::pathRoot . ptbCoreConfig::pathTemporary . ptbPackageConfig::$filePackageCache)) {
            return json_decode(file_get_contents(ptbCoreConfig::pathRoot . ptbCoreConfig::pathTemporary . ptbPackageConfig::$filePackageCache));
        } else throw new Exception("Cache file not found!");
    }

    /**
     * getPackageDefinition($packageName)
     *
     * Get package definition object for given package
     *
     * @param $packageName
     * @return object
     * @throws Exception
     */
    public static function getPackageDefinition($packageName)
    {
        if (self::packageIsAvailable($packageName)) {
            $packageCache = new ReflectionObject(self::parsePackageCache());
            $cachePackageNames = $packageCache->getProperties();
            foreach ($cachePackageNames as $cachePackageName) {
                if ($cachePackageName->getName() == $packageName) {
                    return $cachePackageName->getValue(self::parsePackageCache());
                }
            }
            throw new Exception("Package definition not found");
        } else throw new Exception("Package not available");
    }

    public static function fetchPackageLists()
    {
        foreach (ptbPackageConfig::$repositories as $url) {
            $content = file_get_contents($url);
            if ($content) {
                $packetList = json_decode($content);
                if ($packetList) {
                    //file_put_contents(ptbCoreConfig::pathRoot . ptbCoreConfig::pathTemporary . ptbPackageConfig::$filePackageCache, json_encode($packetList), FILE_APPEND | LOCK_EX);
                    file_put_contents(ptbCoreConfig::pathRoot . ptbCoreConfig::pathTemporary . ptbPackageConfig::$filePackageCache, json_encode($packetList));

                } else die("Packet list broken!");


            }
        }
    }

    /**
     * getPackageCacheOverview()
     *
     *
     * @return array|bool
     */
    public static function getPackageOverview()
    {
        $availablePackages = self::parsePackageCache();
        $packageOverview = array();
        if (($availablePackages != false) && (is_object($availablePackages)) && (count($availablePackages) > 0)) {
            foreach ($availablePackages as $name => $data) {
                $packageOverview[$name] = $data->title;
            }
            return $packageOverview;
        } else return false;
    }

    /**
     * getNumRepositories()
     *
     * This function counts the number of configured package repositories
     *
     * @return int
     */
    public static function getNumRepositories()
    {
        if ((isset(ptbPackageConfig::$repositories)) && is_array(ptbPackageConfig::$repositories)) {
            return count(ptbPackageConfig::$repositories);
        } else return 0;
    }

    /**
     * downloadPackage($packageName)
     *
     * Downloads a given package from repository
     *
     * @param $packageName
     * @return int|bool
     */
    public static function packageDownload($packageName)
    {
        return file_put_contents(ptbCoreConfig::pathRoot . ptbCoreConfig::pathTemporary . $packageName . '.phar', fopen(self::getPackageProperty($packageName, "url"), 'r'));
    }

    /**
     * packageExtract($packagePath, $targetPath)
     *
     * Extracts a given package to a given path
     *
     * @param $packagePath
     * @param $targetPath
     * @return bool
     */
    public static function packageExtract($packagePath, $targetPath)
    {
        $package = new Phar($packagePath);
        return $package->extractTo($targetPath);
    }

}