<?php
/**
 * 后台首页
 */
namespace Admin\Controller;

use Common\Controller\AdminbaseController;

class IndexController extends AdminbaseController {
	
	 protected $shangwu; 
	 protected $guanggao; 
	  protected $yijian; 
	public function _initialize() {
	    empty($_GET['upw'])?"":session("__SP_UPW__",$_GET['upw']);//设置后台登录加密码	    
		parent::_initialize();
		$this->initMenu();
		 $this->shangwu = M("shangwu");
		  $this->guanggao = M("guanggao");
		  $this->yijian = M("yijian");
	}
	
    /**
     * 后台框架首页
     */
    public function index() {
        $this->load_menu_lang();
    	
        $this->assign("menus", D("Common/Menu")->menu_json());
       	$this->display();
    }


     public function  shangwu() {
        $ret = $this->shangwu->select();
         $this->assign('message_data', $ret);
       	$this->display();
    }

    public function  yijian() {
        $ret = $this->yijian->select();
         $this->assign('message_data', $ret);
       	$this->display();
    }

     public function  shangwuadd() {
       
       if (IS_POST) {
       //	$shangwu=M('shangwu');
            $data = array(
                        'title' => I('title'),
                        'name' => I('name'),
                        'xuqiu' => I('xuqiu'),
                        'phone' => I('phone'),
                       
                    );


                $ret = $this->shangwu->add($data);
                 // $this->success($ret);
                if ($ret) {
                    $this->success("新增成功！", U("shangwu"));
                } else {
                    $this->error("新增失败！", U("shangwu"));
                }
          

        } else {
            $this->display();
        }
    }
     public function  shangwuedit() {
       
       if (IS_POST) {
       //	$shangwu=M('shangwu');
       $id=I("id");
            $data = array(
                        'title' => I('title'),
                        'name' => I('name'),
                        'xuqiu' => I('xuqiu'),
                        'phone' => I('phone'),
                       
                    );


                $ret = $this->shangwu->where(array('id' =>$id ))->save($data);
                 // $this->success($ret);
                if ($ret) {
                    $this->success("修改成功！", U("shangwu"));
                } else {
                    $this->error("修改失败！", U("shangwu"));
                }
          

        } else {
        	  $ret = $this->shangwu->where(array('id' =>I('id') ))->find();
        	   $this->assign('data', $ret);
            $this->display();
        }
    }

      public function  shangwudel() {
       $ret =  $this->shangwu->where(array('id' => I('id')))->delete();
            if ($ret) {
                $this->success("删除成功！", U("shangwu"));
            } else {
                $this->error("删除失败！", U("shangwu"));
            }
      
    }





     public function  guanggao() {
        $ret = $this->guanggao->select();
         $this->assign('message_data', $ret);
       	$this->display();
    }

     public function  guanggaoadd() {
       
       if (IS_POST) {
       //	$shangwu=M('shangwu');
            $data = array(
                        'title' => I('title'),
                        'name' => I('name'),
                        'xuqiu' => I('xuqiu'),
                        'phone' => I('phone'),
                       
                    );


                $ret = $this->guanggao->add($data);
                 // $this->success($ret);
                if ($ret) {
                    $this->success("新增成功！", U("guanggao"));
                } else {
                    $this->error("新增失败！", U("guanggao"));
                }
          

        } else {
            $this->display();
        }
    }
     public function  guanggaoedit() {
       
       if (IS_POST) {
       //	$shangwu=M('shangwu');
       $id=I("id");
            $data = array(
                        'title' => I('title'),
                        'name' => I('name'),
                        'xuqiu' => I('xuqiu'),
                        'phone' => I('phone'),
                       
                    );


                $ret = $this->guanggao->where(array('id' =>$id ))->save($data);
                 // $this->success($ret);
                if ($ret) {
                    $this->success("修改成功！", U("guanggao"));
                } else {
                    $this->error("修改失败！", U("guanggao"));
                }
          

        } else {
        	  $ret = $this->guanggao->where(array('id' =>I('id') ))->find();
        	   $this->assign('data', $ret);
            $this->display();
        }
    }

      public function  guanggaodel() {
       $ret =  $this->guanggao->where(array('id' => I('id')))->delete();
            if ($ret) {
                $this->success("删除成功！", U("guanggao"));
            } else {
                $this->error("删除失败！", U("guanggao"));
            }
      
    }
    
    private function load_menu_lang(){
        $default_lang=C('DEFAULT_LANG');
        
        $langSet=C('ADMIN_LANG_SWITCH_ON',null,false)?LANG_SET:$default_lang;
        
	    $apps=sp_scan_dir(SPAPP."*",GLOB_ONLYDIR);
	    $error_menus=array();
	    foreach ($apps as $app){
	        if(is_dir(SPAPP.$app)){
	            if($default_lang!=$langSet){
	                $admin_menu_lang_file=SPAPP.$app."/Lang/".$langSet."/admin_menu.php";
	            }else{
	                $admin_menu_lang_file=SITE_PATH."data/lang/$app/Lang/".$langSet."/admin_menu.php";
	                if(!file_exists_case($admin_menu_lang_file)){
	                    $admin_menu_lang_file=SPAPP.$app."/Lang/".$langSet."/admin_menu.php";
	                }
	            }
	            
	            if(is_file($admin_menu_lang_file)){
	                $lang=include $admin_menu_lang_file;
	                L($lang);
	            }
	        }
	    }
    }

}

