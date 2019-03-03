<?php
class View extends CI_Controller {

    public function __construct(){
        header('Access-Control-Allow-Origin: *');
        header('Content-type: application/json');
        parent::__construct();
        $this->load->model('Model');
        $this->load->database();
        $this->count = 0;
    }

    public function get_ip (){
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

    public function storeNavigation(){
        $this->db->select('site_id,name,logo,intro,site,order,cover');
        $query = $this->db->get('website');
        $result = $query->result_array();
        $x = 0;
        while(isset($result[$x]['logo'])){
            $result[$x]['logo'] = 'https://console.chainwon.com/static/img/logo/'.$result[$x]['logo'].'.png';
            if($result[$x]['cover'] == NULL){
                $result[$x]['cover'] = 'https://i.loli.net/2018/02/13/5a8302bdbadaa.jpg';
            }
            $x += 1;
        }
        $this->Model->end($result);
    }
}