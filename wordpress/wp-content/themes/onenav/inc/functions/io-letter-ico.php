<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2022-06-26 14:42:58
 * @LastEditors: iowen
 * @LastEditTime: 2024-11-18 17:59:09
 * @FilePath: /onenav/inc/functions/io-letter-ico.php
 * @Description: 
 */

/**
 * 首字母头像
 * @param string $text
 * @param int $size
 * @param bool $show
 * @return string
 */
function io_letter_ico( $text, $size = 100, $show = true ){
    $total              = unpack('L', hash('adler32', $text, true))[1];
    $hue                = $total % 360;
    list($r, $g, $b)    = hsv2rgb($hue / 360, io_get_option('letter_ico_s', 40)/100, io_get_option('letter_ico_b', 90)/100);
    list($r1, $g1, $b1) = hsv2rgb(($hue - 45) / 360, .5, .9);
    list($r2, $g2, $b2) = hsv2rgb(($hue + 45) / 360, .5, .9);
    $bg                 = "rgb({$r},{$g},{$b})";
    $bg1                = "rgb({$r1},{$g1},{$b1})";
    $bg2                = "rgb({$r2},{$g2},{$b2})";
    $font_color         = "#ffffff";
    $first              = mb_strtoupper(mb_substr($text, 0, 1));

    $half_size          = $size / 2; 
    $circle1            = mb_substr($total, 0, 2);//$total % $size;
    $circle2            = mb_substr($total, -2);//$total % $half_size;

    $src    = '<svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="'.$size.'" height="'.$size.'">
                <rect fill="'.$bg.'" x="0" y="0" width="'.$size.'" height="'.$size.'"></rect>
                <circle fill="'.$bg1.'" cx="'.$circle1.'" cy="'.($circle1 / 2).'" r="'.($size * .6).'"  opacity=".4"></circle>
                <circle fill="'.$bg2.'" cx="'.$circle2.'" cy="'.($circle2 * 2).'" r="'.($size * .5).'"  opacity=".6"></circle>
                <text x="'.$half_size.'" y="'.$half_size.'" font-size="'.$half_size.'" text-copy="fast" fill="'
                .$font_color.'" text-anchor="middle" text-rights="admin" alignment-baseline="central" font-family="\'PingFang SC\',\'Microsoft Yahei\'">'.$first.'</text></svg>';

    if($show){
        return 'data:image/svg+xml;base64,' . base64_encode($src);
    }
    return $src;
}


function hsv2rgb($h, $s, $v){
    $r = $g = $b = 0;
    $i = floor($h * 6);
    $f = $h * 6 - $i;
    $p = $v * (1 - $s);
    $q = $v * (1 - $f * $s);
    $t = $v * (1 - (1 - $f) * $s);
    switch ($i % 6) {
        case 0:
            $r = $v;
            $g = $t;
            $b = $p;
            break;
        case 1:
            $r = $q;
            $g = $v;
            $b = $p;
            break;
        case 2:
            $r = $p;
            $g = $v;
            $b = $t;
            break;
        case 3:
            $r = $p;
            $g = $q;
            $b = $v;
            break;
        case 4:
            $r = $t;
            $g = $p;
            $b = $v;
            break;
        case 5:
            $r = $v;
            $g = $p;
            $b = $q;
            break;
    }
    return array(
        floor($r * 255),
        floor($g * 255),
        floor($b * 255)
    );
}
/**
 * 挂载字母头像生成 js 文件
 */
function io_letter_avatar_js()
{
    if (is_bookmark()) {
        return;
    }
    $avatar_colors = array(
        '#1abc9c',
        '#2ecc71',
        '#3498db',
        '#9b59b6',
        '#3fe95e',
        '#16a085',
        '#27ae60',
        '#2980b9',
        '#8e44ad',
        '#fc3e50',
        '#f1c40f',
        '#e67e22',
        '#e74c3c',
        '#00bcd4',
        '#95aa36',
        '#f39c12',
        '#d35400',
        '#c0392b',
        '#b2df1e',
        '#7ffc8d'
    );
    ?><script>(function(a,b){a.ioLetterAvatar=function(d,l,j){d=d||"";l=l||60;var h="<?php echo implode(' ', $avatar_colors); ?>".split(" "),f,c,k,g,e,i,t,m;f=String(d).toUpperCase();f=f?f.charAt(0):"?";if(a.devicePixelRatio){l=(l*a.devicePixelRatio)}c=parseInt((((f=="?"?72:f.charCodeAt(0))-64)*12345).toString().slice(0,5));k=c%(h.length-1);t=(c+1)%(h.length-1);m=(c-1)%(h.length-1);g=b.createElement("canvas");g.width=l;g.height=l;e=g.getContext("2d");e.fillStyle=j?j:h[k];e.fillRect(0,0,g.width,g.height); e.arc((c*180)%l,(c*150)%l, (c/120)%l ,0 ,360 );e.fillStyle=h[t];e.globalAlpha = .6;e.fill();e.save();e.beginPath();e.fillStyle=h[m];e.globalAlpha = .4;e.arc((c*20)%l,(c*50)%l, ((99999-c)/80)%l,0 ,360 );e.fill();e.font=Math.round(g.width/2)+"px 'Microsoft Yahei'";e.textAlign="center";e.fillStyle="#fff";e.globalAlpha = 1;e.fillText(f,l/2,l/1.5);i=g.toDataURL();g=null;return i}})(window,document);</script><?php
}
if(io_get_option('is_letter_ico',false) && io_get_option('first_api_ico',false)){
    add_action('wp_head', 'io_letter_avatar_js');
}
/**
 * 挂载字母头像替换
 */
function io_letter_avatar($avatar, $id_or_email = '', $size = '40', $default = '', $alt = '') { 
    // 匹配出原始头像中的信息
    $src = io_get_html_tag_attribute($avatar, 'src');
    if(!$src) return $avatar;
    
    $alt = io_get_avatar_name($id_or_email);
    $alt = $alt? $alt: io_get_html_tag_attribute($avatar, 'alt', $alt);
    if(!$alt) return $avatar;
    
    // 去除原始头像链接中的默认头像，改为输出 404
    $src = htmlspecialchars_decode($src);
    $src = preg_replace('/[\?\&]d[^&]+/is', '', $src);
    $src = $src.'&d=404';
    $src = htmlspecialchars($src);
    
    $class = io_get_html_tag_attribute($avatar, 'class', 'avatar avatar-'.$size.' photo');
    $title = io_get_html_tag_attribute($avatar, 'title', $alt);
    
    $avatar = '<img src="'.$src.'" alt="'.$alt.'" title="'.$title.'" class="'.$class.' " height="'.$size.'" width="'.$size.'" onerror="onerror=null;src=ioLetterAvatar(alt,'.$size.')" />';
    return $avatar;
}
if(!is_admin()) {
    //add_filter('get_avatar', 'io_letter_avatar', 999999, 5);
}
/**
 * 取出头像对应的用户名
 */ 
function io_get_avatar_name($id_or_email) {
    if(have_comments()) {
        return get_comment_author();
    }
    
    $user = null;
    if(empty($id_or_email)) {
        return null;
    } else if(is_object($id_or_email)) {
        if(!empty($id_or_email->comment_author)) {
            return $id_or_email->comment_author;
        } else if(!empty($id_or_email->user_id)) {
            $id = (int) $id_or_email->user_id;
            $user = get_user_by('id', $id);
        }
    } else if(is_numeric($id_or_email)) { // 是数字，尝试获取该 ID 对应的用户名
        $id = (int) $id_or_email;
        $user = get_user_by('id', $id);
    } else if(is_string($id_or_email)) {
        if (!filter_var($id_or_email, FILTER_VALIDATE_EMAIL)) { // 不是邮箱，当做用户名
            return $id_or_email;
        } else {
            $user = get_user_by('email', $id_or_email);
        }
    }
    // 尝试从用户对象中取出用户名
    if(!empty($user) && is_object($user)) {
        return $user->display_name;
    }
    return null;
} 
/**
 * 获取 Html 标签内的指定属性值
 * 
 * @param string $html      原始 html 标签代码
 * @param string $attribute 要获取的标签
 * @param string $default   如果没有获取到，返回的默认值
 * @return string
 */
function io_get_html_tag_attribute($html, $attribute, $default = '') {
    if(preg_match('/'.$attribute.'\s*=\s*[\"\']([^\"\']*)[\"\']/isU', $html, $result)) {
        if(isset($result[1])) return $result[1];
    }
    return $default;
}


