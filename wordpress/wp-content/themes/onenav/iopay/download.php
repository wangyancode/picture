<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2023-04-08 14:29:26
 * @LastEditors: iowen
 * @LastEditTime: 2023-04-11 11:11:53
 * @FilePath: \onenav\iopay\download.php
 * @Description: 下载跳转页
 */

require dirname(__FILE__) . '/../../../../wp-load.php';

if (!isset($_GET['index']) || empty($_GET['post_id']) || empty($_GET['post_type'])) {
    wp_safe_redirect(home_url());
}

$index     = $_GET['index'];
$post_id   = $_GET['post_id'];
$post_type = $_GET['post_type'];

//安全验证
if (!isset($_GET['key']) || !wp_verify_nonce($_GET['key'], 'pay_down')) {
    wp_die('环境异常！请重新获取下载链接');
    exit();
}

//判断是否已经购买
$paid = iopay_is_buy($post_id, $index);
if (!$paid) {
    wp_die('支付信息获取失败，请刷新后重试！');
    exit;
}
if ($paid['paid_type'] == 'free' && io_get_option('pay_no_login') && !is_user_logged_in()) {
    wp_die('登录信息异常，请重新登录！');
    exit;
}
# TODO 待实现
$down = iopay_get_post_down_list($post_id, $post_type);

if (empty($down[$index]['link'])) {
    wp_die('未获取到资源文件或下载链接已失效，请与管理员联系！');
    exit;
}

$file_dir = str_replace('&amp;', '&', trim($down[$index]['link']));

$home = home_url('/');

if (stripos($file_dir, $home) === 0) {
    $file_dir_local = chop(str_replace($home, "", $file_dir)); //本地
    $file_dir_local = ABSPATH . $file_dir;

    if (file_exists($file_dir)) {
        $file_dir = $file_dir_local;
    }
}

//远程地址
preg_match('/^(http|https|thunder|qqdl|ed2k|Flashget|qbrowser|magnet|ftp):\/\//i', $file_dir, $matches);
if ($matches) {
    $file_path = chop($file_dir);

    header('location:' . $file_path);
    echo '<html><head><meta name="robots" content="noindex,nofollow"><script>location.href = "' . $file_path . '";</script></head><body></body></html>';
    exit;
}

$pathinfo  = pathinfo($file_dir);

header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: public");
header("Content-Description: File Transfer");
header("Content-type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"" . $pathinfo['filename'] . '_' . $home . '.' . $pathinfo['extension'] . "\"");
header("Content-Transfer-Encoding: binary");
header("Content-Length: " . filesize($file_dir));

if (io_get_option('down_limit', 0)) {
    // 0 为不限速
    ob_end_flush();
    @readfile($file_dir);
} else {
    ob_end_clean(); //缓冲区结束
    ob_implicit_flush(); //强制每当有输出的时候,即刻把输出发送到浏览器
    header('X-Accel-Buffering: no'); // 不缓冲数据
    $buffer      = (int)io_get_option('down_limit', 256) * 1024; // 下载限速
    $bufferCount = 0;

    while (!feof($file) && $fileSize - $bufferCount > 0) { //循环读取文件数据
        $data        = fread($file, $buffer);
        $bufferCount += $buffer;
        echo $data; //输出文件
        sleep(1);
    }
    fclose($file);
}
exit;