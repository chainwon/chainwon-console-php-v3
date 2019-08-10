<?php

class Edit extends CI_Model {

    public function __construct(){
        
    }

    public function websiteArchive($data,$site_id) {
        $data['site_id'] = $site_id;
        $data['time'] = time();
        unset($data['public']);
        $this->db->insert('website_archive', $data);
        return $this->db->insert_id();
    }

    public function websiteChange($archive_id,$site_id) {
        $data['archive_id'] = $archive_id;
        $data['site_id'] = $site_id;
        $data['time'] = time();
        $this->load->model('Model');
        $data['uid'] = $this->Model->user['uid'];
        $this->db->insert('website_change', $data);
        return $this->db->insert_id();
    }

    public function editChange($site_id){
        $this->db->where('site_id',$site_id);
        $query = $this->db->get('website_change');
        $result = $query->result_array();
        for($x=0;$x<count($result);$x++){
            $result[$x]['time'] = date('Y年m月d日 H:i:s',$result[$x]['time']);
        }
        return $result;
    }

    public function editChangeAuthor($uid){
        $this->db->where('uid',$uid);
        $query = $this->db->get('user');
        $result = $query->result_array();
        
    }
}