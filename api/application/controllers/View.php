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

    public function settingIndex() {
        $this->db->where('uid',10000);
        $query = $this->db->get('user');
        $result = $query->row_array();
        $result['countdown']['year'] = date('Y',$result['countdown_time']);
        $result['countdown']['month'] = date('m',$result['countdown_time']);
        $result['countdown']['day'] = date('d',$result['countdown_time']);
        $this->Model->end($result);
    }

    public function settingNavigation() {
        $this->db->where('uid',10000);
        $this->db->select('site_id,order');
        $this->db->order_by('site_id ASC');
        $query = $this->db->get('relationship');
        $order = $query->result_array();

        $this->db->select('site_id,name,logo,intro,site,order,cover');
        foreach ($order as $site_id){
            $this->db->or_where('site_id',$site_id['site_id']);
        }
        $this->db->order_by('site_id ASC');
        $query = $this->db->get('website');
        $result = $query->result_array();

        $x = 0;
        while(isset($result[$x]['logo'])){
            $result[$x]['logo'] = 'https://console.chainwon.com/static/img/logo/'.$result[$x]['logo'].'.png';
            if($result[$x]['cover'] == NULL){
                $result[$x]['cover'] = 'https://i.loli.net/2018/02/13/5a8302bdbadaa.jpg';
            }
            $result[$x]['order'] = $order[$x]['order'];
            $result[$x]['added'] = true;
            $x += 1;
        }

        array_multisort(array_column($result,'order'),SORT_ASC,$result); // 数组排序

        $this->Model->end($result);
    }

    public function storeNavigation(){
        $post = json_decode(file_get_contents("php://input"),true);

        if(!isset($post['page'])){
            $post['page'] = 1;
        }
        
        $this->db->select('site_id,name,logo,intro,site,order,cover');
        $this->db->limit(32,($post['page']-1)*32);
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