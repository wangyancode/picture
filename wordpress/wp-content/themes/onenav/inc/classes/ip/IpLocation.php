<?php


namespace itbdw\Ip;

use itbdw\Ip\IpParser\QQwry;
use itbdw\Ip\IpParser\IpV6wry;
use itbdw\Ip\IpParser\Ip2Region;

use itbdw\Ip\StringParser;
/**
 * Class IpLocation
 * @package itbdw\Ip
 */
class IpLocation {
    /**
     * @var
     */
    private static $isQQwry;
    /**
     * @var
     */
    private static $ipV4Path;
    /**
     * @var
     */
    private static $ipV6Path;

    /**
     * 获取位置而不进行分析
     * @param $ip
     * @param string $ipV4Path
     * @param string $ipV6Path
     * @return array
     */
    public static function getLocationWithoutParse($ip, $ipV4Path='', $ipV6Path='') {

        //if  ipV4Path 记录位置
        if (strlen($ipV4Path)) {
            self::setIpV4Path($ipV4Path);
        }

        //if  ipV6Path 记录位置
        if (strlen($ipV6Path)) {
            self::setIpV6Path($ipV6Path);
        }

        if (self::isIpV4($ip)) {
            if (self::$isQQwry) {
                $ins = new QQwry();
            }else{
                $ins = new Ip2Region();
            }
            $ins->setDBPath(self::getIpV4Path());
            $location = $ins->getIp($ip);
        } else if (self::isIpV6($ip)) {
            $ins = new IpV6wry();
            $ins->setDBPath(self::getIpV6Path());
            $location = $ins->getIp($ip);

        } else {
            $location = [
                'error' => 'IP Invalid'
            ];
        }

        return $location;
    }

    /**
     * 获取位置并且进行分析
     * @param $ip
     * @param string $ipV4Path
     * @param string $ipV6Path
     * @return array|mixed
     */
    public static function getLocation($ip,$isQQwry, $ipV4Path='', $ipV6Path='') {
        self::$isQQwry = $isQQwry;
        $location = self::getLocationWithoutParse($ip, $ipV4Path, $ipV6Path);
        if (isset($location['error'])) {
            return $location;
        }
        if($location['show']){
            return $location['data'];
        }
        return StringParser::parse($location['data']);
    }

    /**
     * 设置 IPV4 数据库地址
     * @param $path
     */
    public static function setIpV4Path($path)
    {
        self::$ipV4Path = $path;
    }

    /**
     * 设置 IPV6 数据库地址
     * @param $path
     */
    public static function setIpV6Path($path)
    {
        self::$ipV6Path = $path;
    }

    /**
     * IPV4 数据库地址
     * @return string
     */
    private static function getIpV4Path() {
        $_ipPath = self::src('/ip2region.xdb');
        if (self::$isQQwry)
            $_ipPath = self::src('/qqwry.dat');
        return self::$ipV4Path ?: $_ipPath;
    }

    /**
     * IPV6 数据库地址
     * @return string
     */
    private static function getIpV6Path() {
        return self::$ipV6Path ? : self::src('/ipv6wry.db');
    }

    /**
     * 判断 ipv4
     * @param $ip
     * @return bool
     */
    private static function isIpV4($ip) {
        return false !== filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
    }

    /**
     * 判断 ipv6
     * @param $ip
     * @return bool
     */
    private static function isIpV6($ip) {
        return false !== filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
    }

    /**
     * @param $filename
     * @return string
     */
    public static function src($filename) {
        return self::root($filename);
    }

    /**
     * @param $filename
     * @return string
     */
    public static function root($filename) {
        return IP_DATABASE_ROOT_DIR . $filename;
    }
}