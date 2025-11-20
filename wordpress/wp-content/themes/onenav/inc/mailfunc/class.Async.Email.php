<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2021-08-07 21:18:28
 * @LastEditors: iowen
 * @LastEditTime: 2023-03-27 23:57:12
 * @FilePath: \onenav\inc\mailfunc\class.Async.Email.php
 * @Description: 
 */
?>
<?php

/**
 * Class AsyncEmail
 */
final class AsyncEmail extends WPAsyncTask {
    protected $action = 'send_mail';

    protected $argument_count = 5;

    /**
     * 准备异步请求的数据
     *
     * @throws Exception If for any reason the request should not happen
     *
     * @param array $data An array of data sent to the hook
     *
     * @return array
     */
    protected function prepare_data( $data ) {
        // $from, $to, $title = '', $args = array(), $template = 'comment'
        return array(
            'to' => $data[0],
            'title' => $data[1],
            'content' => $data[2],
        );
    }

    /**
     * 运行异步任务操作
     */
    protected function run_action() {
        //$data = $this->_body_data;
        $args = json_decode(base64_decode($_POST['args']));
        $args = $args ? (array)$args : $_POST['args'];
        $data = array(
            'to' => $_POST['to'],
            'title' => $_POST['title'],
            'content' => $_POST['content']
        );
        //do_action( $action, $data['from'], $data['to'], $data['title'], $data['args'], $data['template'] ); //执行io_mail
        io_mail( $data['to'], $data['title'],  $data['content']); // 也可以直接io_mail(), 则不需要在io_mail下写add_action('io_async_send_mail', xx);
    }
}