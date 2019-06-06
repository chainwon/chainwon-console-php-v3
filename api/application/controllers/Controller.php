<?php
class Controller extends CI_Controller {

    public function __construct(){
        parent::__construct();
        header('Access-Control-Allow-Origin: *');
        header('Content-type: application/json');
        $this->load->model('Model');
        $this->load->database();
        
    }

    public function saveSetting() {
        $a = array(
            'state' => 1,
            'info' => '保存成功！',
        );

        $post = json_decode(file_get_contents("php://input"),true);
        $post = $post['form'];

        $post['countdown_time'] = strtotime($post['countdown']['time']);

        if(!isset($post['appearad'])){
            $post['appearad']=0;
        }
        if(!isset($post['unaudited'])){
            $post['unaudited']=0;
        }
        if(!isset($post['ban'])){
            $post['ban']=0;
        }
        if(!isset($post['debug'])){
            $post['debug']=0;
        }
        if(!isset($post['newest'])){
            $post['newest']=0;
        }

        if(isset($post['url'])) {
            if(!preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i",$post['url'])){
                $a['state'] = 0;
                $a['info'] = '你输入的 URL 不正确！请检查是否带上 http 或 https ！';
                $this->Model->end($a);
            }
        }

        $data = array(
            'search' => $post['search'],
            'countdown_name' => $post['countdown_name'],
            'countdown_time' => $post['countdown_time'],
            'css' => $post['css'],
            'ad' => $post['appearad'],
            'unaudited' => $post['unaudited'],
            'ban' => $post['ban'],
            'url' => $post['url'],
            'debug' => $post['debug'],
            'newest' => $post['newest'],
        );

        $this->db->where('uid',$this->Model->user['uid']);
        $this->db->update('user', $data);

        $this->Model->end($a);
    }

    public function addNavigation(){
        $a = array(
            'state' => 1,
            'info' => '添加成功！'
        );

        $post = json_decode(file_get_contents("php://input"),true);

        if($this->Model->user['uid']==0){
            $a['state'] = 0;
            $a['info'] = '你没有登录！';
            $this->Model->end($a);
        }

        if(!isset($post['site_id'])){
            $a['state'] = 0;
            $a['info'] = '添加失败，网站ID为空！';
            $this->Model->end($a);
        }

        $this->db->where('uid',$this->Model->user['uid']);
        $this->db->where('site_id',$post['site_id']);
        $this->db->from('relationship');
        if($this->db->count_all_results() > 0){
            $a['state'] = 0;
            $a['info'] = '添加失败，您已经添加过该网站！';
            $this->Model->end($a);
        }

        $this->db->insert('relationship', array(
            'uid' => $this->Model->user['uid'],
            'site_id' => $post['site_id'],
        ));

        $this->Model->end($a);
    }

    public function removeNavigation(){
        $a = array(
            'state' => 1,
            'info' => '移除成功！'
        );

        $post = json_decode(file_get_contents("php://input"),true);

        if($this->Model->user['uid']==0){
            $a['state'] = 0;
            $a['info'] = '你没有登录！';
            $this->Model->end($a);
        }

        if(!isset($post['site_id'])){
            $a['state'] = 0;
            $a['info'] = '移除失败，网站ID为空！';
            $this->Model->end($a);
        }

        $this->db->where('uid',$this->Model->user['uid']);
        $this->db->where('site_id', $post['site_id']);
        $this->db->delete('relationship');

        $this->Model->end($a);
    }

    public function uploadImage() {
        $a = array(
            'state' => 0,
            'notice' => '请选择一张图片！',
        );
        if(isset($_FILES["file"])){
            if((($_FILES["file"]["type"] == "image/png")
                || ($_FILES["file"]["type"] == "image/jpeg")
                || ($_FILES["file"]["type"] == "image/jpg"))
                && ($_FILES["file"]["size"] < 200000)){
                if ($_FILES["file"]["error"] > 0){
                    $a['notice'] = $_FILES["file"]["error"];
                }else{
                    $info=getimagesize($_FILES["file"]["tmp_name"]);
                    if($info[0] < 100 or $info[1] < 100){
                        $a['notice'] = '请上传分辨率至少为100×100px的图片！';
                        $this->Model->end($a);
                    }elseif($info[0] != $info[1]){
                        $a['notice'] = '请上传正方形图片！';
                        $this->Model->end($a);
                    }else{
                        $name=time().'.png';
                        move_uploaded_file($_FILES["file"]["tmp_name"],$this->Model->root."upload/".$name);
                        $a['state'] = 1;
                        $a['notice'] = '上传成功';
                        $a['url'] = 'https://console.chainwon.com/upload/'.$name;
                    }
                }
            }else{
                $a['notice'] = "请上传 .png 或.jpeg 或 .jpg 文件，并且图片大小不要超过200kb！";
                $this->Model->end($a);
            }
        }

        $this->Model->end($a);

    }

    public function newNavigation(){
        
        $post = json_decode(file_get_contents("php://input"),true);

        $a = array(
            'state' => 1,
            'notice' => '你的站点已被收录，如果可以请反个链！谢谢！',
        );

        $data = array(
            'title' => '网站标题',
            'site' => '网站链接',
            'intro' => '网站介绍',
            'logo' => '网站图标',
        );

        for ($x=0; $x<4; $x++) {
            if($post[array_keys($data)[$x]]==''){
                $a['state'] = 0;
                $a['notice'] = $data[array_keys($data)[$x]].'不能为空！';
                $this->Model->end($a);
            }
        }

        if (!preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i",$post['site'])) {
            $a['state'] = 0;
            $a['notice'] = '你输入的 URL 不正确！请检查是否带上 http 或 https ！';
        }

        
        $site = parse_url($post['site'])['host'];
        $this->db->like('site',$site);
        $this->db->select('site,site_id');
        $query = $this->db->get('website');
        $rows = $query->result_array();
        foreach($rows as $row){
            if(parse_url($row['site'])['host'] == $site){
                $a['state'] = 2;
                $a['notice'] = '已收录了该网址，现在他已经自动添加到了你的网址导航中！';
                $a['id'] = $row['site_id'];
                $this->Model->end($a);
            }
        }
    

        
        copy($post['logo'],$this->Model->root.'static/img/logo/'.md5(parse_url($post['site'])['host']).'.png');
        $data = array(
            'name' => $post['title'],
            'intro' => $post['intro'],
            'site' => $post['site'],
            'logo' => md5(parse_url($post['site'])['host']),
            'isdefault' => 0,
            'uid' => $this->Model->user['uid'],
        );
        $this->db->insert('website', $data);
        $this->db->where($data);
        $this->db->select('site_id');
        $query = $this->db->get('website');
        $row = $query->row_array();
        $a['id'] = $row['site_id'];
    

        $this->Model->end($a);
    }

}