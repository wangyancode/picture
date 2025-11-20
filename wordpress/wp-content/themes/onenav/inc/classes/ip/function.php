<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2022-12-06 20:32:29
 * @LastEditors: iowen
 * @LastEditTime: 2023-03-17 21:38:57
 * @FilePath: \onenav\inc\classes\ip\function.php
 * @Description: 
 */

//IP数据库存储地址
define("IP_DATABASE_ROOT_DIR", wp_upload_dir()['basedir'].'/ip_data');

require dirname(__DIR__) . '/ip/IpParser/IpParserInterface.php';

require dirname(__DIR__) . '/ip/IpLocation.php';

require dirname(__DIR__) . '/ip/IpParser/QQwry.php';
require dirname(__DIR__) . '/ip/IpParser/IpV6wry.php';
require dirname(__DIR__) . '/ip/IpParser/Ip2Region.php';

require dirname(__DIR__) . '/ip/StringParser.php';
