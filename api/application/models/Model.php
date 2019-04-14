<?php

class Model extends CI_Model {

    public function __construct(){
        $this->load->database();
        $post_data = array (
            'biao' => '*',
            'ip' => $this->get_client_ip (),
        );
        $username = '';
        $c = '';
        if(isset($_COOKIE['username'])){
            $username = $_COOKIE['username'];
        }
        if(isset($_COOKIE['c'])){
            $c = $_COOKIE['c'];
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://account.mixcm.com/api/view/user');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_COOKIE, "username=$username;c=$c");
        $this->user=json_decode(trim(curl_exec($ch), chr(239) . chr(187) . chr(191)), true);
        curl_close($ch);
    }
    
    public function get_client_ip (){
        if (getenv('HTTP_CLIENT_IP')){
            $ip=getenv('HTTP_CLIENT_IP');
        }elseif (getenv('HTTP_X_FORWARDED_FOR')){
            $ip=getenv('HTTP_X_FORWARDED_FOR');
        }elseif (getenv('HTTP_X_FORWARDED')){
            $ip=getenv('HTTP_X_FORWARDED');
        }elseif (getenv('HTTP_FORWARDED_FOR')){
            $ip=getenv('HTTP_FORWARDED_FOR');
        }elseif (getenv('HTTP_FORWARDED')){
            $ip=getenv('HTTP_FORWARDED');
        }else {
            $ip=$_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
    
    function get_info($url){    
        $ch = curl_init();

        //设置选项，包括URL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//绕过ssl验证
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        //执行并获取HTML文档内容
        $output = curl_exec($ch);

        //释放curl句柄
        curl_close($ch);
        return $output;
    }

    public function end($data,$return=false){

        // Everything has its end.

        if($return){
            return json_encode($data);
        }else{
            echo json_encode($data);
        }
        exit;
    }
}