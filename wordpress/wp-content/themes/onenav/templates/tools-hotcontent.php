<?php
/*
 * @Theme Name:One Nav
 * @Theme URI:https://www.iotheme.cn/
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2021-06-03 08:56:02
 * @LastEditors: iowen
 * @LastEditTime: 2024-11-02 12:37:47
 * @FilePath: /onenav/templates/tools-hotcontent.php
 * @Description: 
 */

/// TODO 待清理
if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<?php 
if(io_get_option('hot_card',false)){ 
    if($hot_list = io_get_option('home_hot_list','')){
?>
    
    <div class="d-flex slider-menu-father mb-4">
        <div class="slider_menu mini_tab" sliderTab="sliderTab" >
            <ul class="nav nav-pills tab-auto-scrollbar menu overflow-x-auto" role="tablist"> 
                <?php 
                for($i=0; $i< count($hot_list); $i++) { 
                    echo '<li class="pagenumber nav-item">
                    <a class="nav-link ajax-home-hot-list '.($i == 0?'active load':'').'" data-toggle="pill" href="#ct-tab-'.$hot_list[$i]['type'].'-'.$hot_list[$i]['order'].$i.'" data-action="load_hot_post" data-datas="'. esc_attr(json_encode(array('data'=>$hot_list[$i]),JSON_UNESCAPED_UNICODE)) .'">'._iol($hot_list[$i]['title']).'</a>
                    </li>';
                } 
                ?>
            </ul>
        </div> 
    </div> 
    <div class="tab-content">
        <?php for($i=0; $i< count($hot_list); $i++) { ?>
        <div id="ct-tab-<?php echo($hot_list[$i]['type'].'-'.$hot_list[$i]['order'].$i) ?>" class="tab-pane<?php echo ($i == 0?' active':'') ?>">
            <div class="row <?php echo $hot_list[$i]['mini']?"row-sm":"" ?> ajax-list-body position-relative">
                <?php 
                if($i ==0) {
                    get_home_hot_card($hot_list[$i]);
                }else{
                    echo '<div class="col-lg-12 customize_nothing"><div class="nothing mb-4"><i class="iconfont icon-loading icon-spin mr-2"></i>'. __('加载中...', 'i_theme' ).'</div></div>';
                }
                ?>
            </div>  
        </div>
        <?php } ?>
    </div> 
<?php 
    }
} 
?>