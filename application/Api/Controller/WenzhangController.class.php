<?php
namespace Api\Controller;
use Think\Controller;
/**
 * Created by PhpStorm.
 * User: tianshi51
 * Date: 2017/3/26
 * Time: 10:55
 */

class WenzhangController extends Controller
{
    function toutiao()
    {
        $id = I("id");
        $term_relationships = M("term_relationships");
        $navcat = $term_relationships->where(array('term_id' => $id, "status" => 1))->order('tid desc')->select();
        if (count($navcat) > 0) {
            $posts = M("posts");
            foreach ($navcat as $r) {
             $wen=$posts->where(array('id'=>$r["object_id"],"post_status"=>1))->find();
             if($wen!=null){
                 $wenzhang[]=$wen;
             }
            
            }
            $this->ajaxReturn($wenzhang);
        }
    }
function chanpin()
    {
        $id = I("id");
        $shop_relationships = M("shop_relationships");
        $navcat = $shop_relationships->where(array('term_id' => $id))->order('tid desc')->select();
        if (count($navcat) > 0) {
            $shop = M("shop");
            
            foreach ($navcat as $r) {
             $wen=$shop->where(array('id'=>$r["object_id"]))->find();
              if($wen!=null){
                 $wenzhang[]=$wen;
             }
            }
            $this->ajaxReturn($wenzhang);
        }
    }
    function chanpinxaingqing(){
        $id = I("id");
        $shop = M("shop");
         $shop_relationships = M("shop_relationships");
         $trems = M("terms");
         $shopid= $shop_relationships->where(array('object_id'=>$id))->find();
           $sh= $trems->where(array('term_id'=>$shopid['term_id']))->find();
//$this->ajaxReturn($shopid['term_id']);
        $wenzhang=$shop->where(array('id'=>$id))->find();
        $wenzhang['fenlei']=$sh['name'];

        $this->ajaxReturn($wenzhang);

    }
    function toutiaoxaingqing(){
        $id = I("id");
        $posts = M("posts");
        $wenzhang=$posts->where(array('id'=>$id))->find();
        $wenzhang['post_content']=str_replace('"',"'",$wenzhang['post_content']);
$wenzhang['post_content']=str_replace('src=\'/','src=\'http://xin.51fale.com/',$wenzhang['post_content']);
       // $wenzhang['post_content']=
        $this->ajaxReturn($wenzhang);

    }


    function guanggao(){
       
        $guanggao = M("guanggao");
        $wenzhang= $guanggao->select();
//         $wenzhang['post_content']=str_replace('"',"'",$wenzhang['post_content']);
// $wenzhang['post_content']=str_replace('src=\'/','src=\'http://xin.51fale.com/',$wenzhang['post_content']);
//        // $wenzhang['post_content']=
        $this->ajaxReturn($wenzhang);

    }
      function shangwu(){
       
        $shangwu = M("shangwu");
        $wenzhang= $shangwu->select();
//         $wenzhang['post_content']=str_replace('"',"'",$wenzhang['post_content']);
// $wenzhang['post_content']=str_replace('src=\'/','src=\'http://xin.51fale.com/',$wenzhang['post_content']);
//        // $wenzhang['post_content']=
        $this->ajaxReturn($wenzhang);

    }

    function toutiaoselect(){
       
        $id = I("id");
        
            $posts = M("posts");
           $condition['post_title'] =array('like',"%".$id."%");
            //$this->ajaxReturn( $condition);
 $condition['post_status'] = 1;
 $condition['_logic'] = 'AND';
             $wen=$posts->where($condition)->select();
             // if($wen!=null){
             //     $wenzhang[]=$wen;
             // }
            
        
            $this->ajaxReturn($wen);
      
       

    }

     function chanpinselect(){
       
        $id = I("id");
        
            $shop = M("shop");
           $condition['post_title'] =array('like',"%".$id."%");
            //$this->ajaxReturn( $condition);
 // $condition['post_status'] = 1;
 // $condition['_logic'] = 'AND';
             $wen=$shop->where($condition)->select();
             // if($wen!=null){
             //     $wenzhang[]=$wen;
             // }
            
        
            $this->ajaxReturn($wen);
      
       

    }
    function fenlei(){
            $terms=M("terms");
          $ret=$terms->where(array('parent'=>1))->select();

 foreach ($ret as $r) {
    if($r['term_id']!=5){
             $e[]=array("id"=>$r['term_id'],'title'=>$r['name']);
             }
            }


     $this->ajaxReturn($e);


    }

}