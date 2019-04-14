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

        $countdown_time=$post['countdown']['year'].'-'.$post['countdown']['month'].'-'.$post['countdown']['day'];
        $post['countdown_time'] = strtotime($countdown_time);

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
            'countdown_time' => strtotime($countdown_time),
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

    public function picupload() {
        $this->load->model('Sina');

        //准备本地图片文件
        $saveFile = $_FILES["file"]["tmp_name"];
        //要上传的文件本地路径

        //生成cookie
        $cookie = $this->Sina->weiboLogin('17671245164',base64_decode('bHV6aWppYW4='));

        //上传图片到微博图床
        $data = $this->Sina->weiboUpload($saveFile,$cookie,$multipart = true) ;

        echo $this->Sina->getImageUrl(json_decode($data,true)['data']['pics']['pic_1']['pid']);

    }

}