<?php
/*
 * @Theme Name:OneNav
 * @Theme URI:https://www.iotheme.cn/
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2021-06-03 08:55:57
 * @LastEditors: iowen
 * @LastEditTime: 2024-04-11 16:28:32
 * @FilePath: /onenav/functions.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/** -------------------------------------------------
 # 
 # 如果开启的验证码后进不了后台，将下面的 true 改为 false
 #
 ** ------------------------------------------------- */ 
define ('LOGIN_007', true );


/* ---------------------- define -------------------------- */
define ('SSL_VERIFY', true);
define ('TIMEZONE', get_option('timezone_string'));
define ('TIME_FORMAT', get_option( 'date_format' ).' '.get_option( 'time_format' ));
define ('SAVE_CHECK_LOG', false);
define ('DUALHOST', array('aaa.pro','ac.cn','ac.kr','ac.mu','aca.pro','acct.pro','ae.org','ah.cn','ar.com','avocat.pro','bar.pro','biz.ki','biz.pl','bj.cn','br.com','busan.kr','chungbuk.kr','chungnam.kr','club.tw','cn.com','co.ag','co.am','co.at','co.bz','co.cm','co.com','co.gg','co.gl','co.gy','co.il','co.im','co.in','co.je','co.kr','co.lc','co.mg','co.ms','co.mu','co.nl','co.nz','co.uk','co.ve','co.za','com.af','com.ag','com.am','com.ar','com.au','com.br','com.bz','com.cm','com.cn','com.co','com.de','com.ec','com.es','com.gl','com.gr','com.gy','com.hn','com.ht','com.im','com.ki','com.lc','com.lv','com.mg','com.ms','com.mu','com.mx','com.nf','com.pe','com.ph','com.pk','com.pl','com.ps','com.pt','com.ro','com.ru','com.sb','com.sc','com.se','com.sg','com.so','com.tw','com.vc','com.ve','cpa.pro','cq.cn','daegu.kr','daejeon.kr','de.com','ebiz.tw','edu.cn','edu.gl','eng.pro','es.kr','eu.com','fin.ec','firm.in','fj.cn','game.tw','gangwon.kr','gb.com','gb.net','gd.cn','gen.in','go.kr','gov.cn','gr.com','gs.cn','gwangju.kr','gx.cn','gyeongbuk.kr','gyeonggi.kr','gyeongnam.kr','gz.cn','ha.cn','hb.cn','he.cn','hi.cn','hk.cn','hl.cn','hn.cn','hs.kr','hu.com','hu.net','idv.tw','in.net','incheon.kr','ind.in','info.ec','info.ht','info.ki','info.nf','info.pl','info.ve','jeju.kr','jeonbuk.kr','jeonnam.kr','jl.cn','jp.net','jpn.com','js.cn','jur.pro','jx.cn','kg.kr','kiwi.nz','kr.com','law.pro','ln.cn','me.uk','med.ec','med.pro','mex.com','mo.cn','ms.kr','ne.kr','net.af','net.ag','net.am','net.br','net.bz','net.cm','net.cn','net.co','net.ec','net.gg','net.gl','net.gr','net.gy','net.hn','net.ht','net.im','net.in','net.je','net.ki','net.lc','net.lv','net.mg','net.mu','net.my','net.nf','net.nz','net.ph','net.pk','net.pl','net.ps','net.ru','net.sb','net.sc','net.so','net.vc','net.ve','nm.cn','no.com','nom.ag','nom.co','nom.es','nom.ro','nx.cn','or.at','or.jp','or.kr','or.mu','org.af','org.ag','org.am','org.bz','org.cn','org.es','org.gg','org.gl','org.gr','org.hn','org.ht','org.il','org.im','org.in','org.je','org.ki','org.lc','org.lv','org.mg','org.ms','org.mu','org.my','org.nz','org.pk','org.pl','org.ps','org.ro','org.ru','org.sb','org.sc','org.so','org.uk','org.vc','org.ve','pe.kr','plc.co.im','pro.ec','qc.com','qh.cn','radio.am','radio.fm','re.kr','recht.pro','ru.com','sa.com','sc.cn','sc.kr','sd.cn','se.com','senet','seoul.kr','sh.cn','sn.cn','sx.cn','tj.cn','tw.cn','uk.com','uk.net','ulsan.kr','us.com','us.org','uy.com','web.ve','xj.cn','xz.cn','yn.cn','za.com','zj.cn'));
/* --------------------- define end ----------------------- */

//--------------- 绕过 CDN 代理IP获取客户真实IP地址 ---------------
if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
    $list = explode(',',$_SERVER['HTTP_X_FORWARDED_FOR']);
    $_SERVER['REMOTE_ADDR'] = $list[0];
}
//------------- 绕过 CDN 代理IP获取客户真实IP地址 end -------------

require get_theme_file_path('/inc/theme-start.php'); 




# ----------------------------------------------------------------- #
#                                                                   #
#       -------------     自定义代码添加到下面      -------------       #
#                                                                   #
#       -------------     请备份，更新将被删除      -------------       #
#                                                                   #
# ----------------------------------------------------------------- #






