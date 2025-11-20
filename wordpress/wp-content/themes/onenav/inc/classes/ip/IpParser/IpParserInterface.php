<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2023-02-06 14:34:35
 * @LastEditors: iowen
 * @LastEditTime: 2023-02-06 22:25:43
 * @FilePath: \onenav\inc\classes\ip\IpParser\IpParserInterface.php
 * @Description: 
 */

namespace itbdw\Ip\IpParser;

interface IpParserInterface
{
    function setDBPath($filePath);

    /**
     * @param $ip
     * @return mixed ['ip', 'country', 'area']
     */
    function getIp($ip);
}