<?php

namespace Chanpin\Controller;
use Think\Controller;
/**
 * 首页
 */
class IndexController extends Controller {
	
    
 function index() {
$users=M("users");
 	$shop=M("shop");
 	$shop_relationships=M("shop_relationships");
 	$result=$shop_relationships->where(array("term_id"=>10))->select();
 	foreach ($result as $key) {
 	 	 $wen=$shop->where(array('id'=>$key["object_id"]))->select();
 	 	 // /$wen[0]['i']=$users->where(array("id"=>$wen[0]['faburenid']))->select();
 	 	 
 	 	 $wenzhang[]=$wen;
 	 	 //$this->ajaxReturn($wenzhang);
		}
	//$this->ajaxReturn($wenzhang);
		$this->assign("wenzhang",$wenzhang);
    	$this->display();
    }

	
}
  


