﻿<?php
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

    public function user() {
        $this->Model->end(array(
            'uid' => $this->Model->user['uid'],
            'avatar' => $this->Model->user['avatar'],
            'name' => $this->Model->user['name'],
            'username' => $this->Model->user['username'],
        ));
    }

    public function settingIndex() {
        $this->db->where('uid',$this->Model->user['uid']);
        $query = $this->db->get('user');
        $result = $query->row_array();
        $result['countdown']['time'] = date('Y',$result['countdown_time']).'-'.date('m',$result['countdown_time']).'-'.date('d',$result['countdown_time']);
        $this->Model->end($result);
    }

    public function settingStyle() {
        $this->db->where('uid',$this->Model->user['uid']);
        $this->db->select('css,theme');
        $query = $this->db->get('user');
        $user = $query->row_array();

        if($user['theme']==0){
            $user['theme']='1';
        }
        
        $query = $this->db->get('theme');
        $themes = $query->result_array();

        $a = array(
            'user' => $user,
            'themes' => $themes 
        );

        $this->Model->end($a);
    }

    public function verifyfile(){   
        $this->db->where('site_id',$_GET['id']);
        $this->db->select('site_id,name,site');
        $query = $this->db->get('website');
        $row = $query->row_array();
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="chainwon_verify.html"');
        header('Content-Transfer-Encoding: binary');
        echo md5($this->Model->user['uid'].$row['site_id'].$row['site'].$row['name'].'chainwon_verify');
    }

    public function verifiedSite(){   
        $this->db->where('uid',$this->Model->user['uid']);
        $this->db->where('verify',1);
        $this->db->select('site_id,name,site,logo,intro');
        $query = $this->db->get('website');
        $result = $query->result_array();
        for($x=0;$x<count($result);$x++){
            $result[$x]['logo'] = 'https://cdn.chainwon.com/img/logo/'.$result[$x]['logo'].'.png';
        }
        $this->Model->end($result);
    }

    public function settingNavigation() {
       
        $this->db->where('uid',$this->Model->user['uid']);
        $this->db->select('site_id,re_order');
        $this->db->order_by('site_id ASC');
        $query = $this->db->get('relationship');
        $order = $query->result_array();
        if(isset($order[0])){
            $this->db->select('site_id,name,logo,intro,site,we_order,cover');
            foreach ($order as $site_id){
                $this->db->or_where('site_id',$site_id['site_id']);
            }
            $this->db->order_by('site_id ASC');
            $query = $this->db->get('website');
            $result = $query->result_array();

            $x = 0;
            while(isset($result[$x]['logo'])){
                $result[$x]['logo'] = 'https://cdn.chainwon.com/img/logo/'.$result[$x]['logo'].'.png';
                if($result[$x]['cover'] == NULL){
                    $result[$x]['cover'] = 'https://i.loli.net/2018/02/13/5a8302bdbadaa.jpg';
                }

                $result[$x]['we_order'] = $order[$x]['re_order'];
                $result[$x]['added'] = true;
                $x += 1;
            }

            array_multisort(array_column($result,'we_order'),SORT_ASC,$result); // 数组排序

            $this->Model->end($result);
        }
        
    }

    public function storeNavigation(){
        $post = json_decode(file_get_contents("php://input"),true);

        if(!isset($post['page'])){
            $post['page'] = 1;
        }

        if(isset($post['tag_id'])){
            $this->db->where('tag_id', $post['tag_id']);
            $query = $this->db->get('tag_relationship');
            $result = $query->result_array();
            if(count($result)>0){
                $this->db->group_start();
                foreach($result as $row){
                    $this->db->or_where('site_id',$row['site_id']);
                }
                $this->db->group_end();
            }
        }

        if(isset($post['keyword'])){
            if($post['keyword']!==''){
                $this->db->group_start();
                $this->db->or_like('name',$post['keyword']);
                $this->db->or_like('intro',$post['keyword']);
                $this->db->or_like('keywords',$post['keyword']);
                $this->db->or_like('site',$post['keyword']);
                $this->db->group_end();
            }
        }

        $this->db->select('site_id,name,logo,intro,site,we_order,cover');
        $this->db->limit(36,($post['page']-1)*36);

        if($this->Model->chainwon_user['ban']!=1){
            $this->db->where('isdefault != ', 4);
        }elseif($this->Model->chainwon_user['unaudited']!=1){
            $this->db->where('isdefault != ', 2);
        }
        if($this->Model->chainwon_user['newest'] == 1){
            $this->db->order_by('we_order DESC, site_id DESC');
        }else{
            $this->db->order_by('we_order DESC, site_id ASC');
        }
        
        $query = $this->db->get('website');
        $result = $query->result_array();

        $x = 0;
        while(isset($result[$x]['logo'])){
            $result[$x]['logo'] = 'https://cdn.chainwon.com/img/logo/'.$result[$x]['logo'].'.png';
            if($result[$x]['cover'] == NULL){
                $result[$x]['cover'] = 'https://i.loli.net/2018/02/13/5a8302bdbadaa.jpg';
            }
            $this->db->where('uid',$this->Model->user['uid']);
            $this->db->where('site_id',$result[$x]['site_id']);
            $this->db->from('relationship');
            if($this->db->count_all_results()>0){
                $result[$x]['added'] = true;
            }
            $x += 1;
        }

        $this->Model->end($result);
    }

    public function storeTag(){
        $post = json_decode(file_get_contents("php://input"),true);

        $query = $this->db->get('tag');
        $result = $query->result_array();

        $this->Model->end($result);
    }

    public function edit(){
        $post = json_decode(file_get_contents("php://input"),true);

        $this->db->where('site_id',$post['site_id']);
        $this->db->select('logo,name,site,intro,public,uid,verify');
        $query = $this->db->get('website');
        $row = $query->row_array();
        $row['logo'] = 'https://cdn.chainwon.com/img/logo/'.$row['logo'].'.png';
        if($row['uid'] == $this->Model->user['uid'] && $row['verify'] == 1){
            if($row['public'] == 1){
                $row['banButton'] = 1;
            }else{
                $row['banButton'] = 0;
            }
        }

        $this->load->model('Edit');
        $row['change'] = $this->Edit->editChange($post['site_id']);

        $this->Model->end($row);
    }
}