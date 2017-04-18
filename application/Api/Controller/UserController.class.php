<?php
namespace Api\Controller;
use Think\Controller;

class UserController extends Controller
{
  /**
   * 用户注册
   * 
   * @return [type] [description]
   */
	function zhuce(){
 $phone = I("phone");
  $mima = I("mima");
  // $phone = I("phone");
  $app_user=M("app_user");
  $resulr=$app_user->where( array("phone" =>$phone))->count();
   //$this->ajaxReturn($resulr);
   if($resulr!=0){
   	$e=array("nr"=>0);
   	$this->ajaxReturn($e);
   }else{
   	if($app_user->add(array("phone"=>$phone,"nicheng"=>$phone,"mima"=>md5($mima) ,"shijian"=>date("Y-m-d H:i:s",time())))){
   			$e=array("nr"=>1);
   	$this->ajaxReturn($e);
   	}else{
   		$e=array("nr"=>2);
   	$this->ajaxReturn($e);
   	}

   }
	}

/**
 * [denglu description]
 *用户登录
 * @return [type] [description]
 */
	function denglu(){
		 $phone = I("phone");
  			$mima = I("mima");
  			$app_user=M("app_user");
  		$resulr=$app_user->where(array("phone" =>$phone,"mima"=>md5($mima)))->find();
 		if($resulr==null){
   			$e=array("nr"=>0);
  		 }else{
			$e=array("nr"=>1,"id"=>$resulr["id"]);
   		}
			$this->ajaxReturn($e);
	}
/** 

通过id查询用户
 */
  function finduser(){
      $id=I("id");
    //  $this->ajaxReturn($id);
      $app_user=M("app_user");
      $resulr=$app_user->where(array("id" =>$id))->find();
      $this->ajaxReturn($resulr);
  }

  /**
   * 查询用户收藏
   * @return [type] [description]
   */
  function findshoucang(){
      $id=I("id");
      $userid=I("userid");
      $app_shoucang=M("app_shoucang");
      $resulr=$app_shoucang->where(array("wenid" =>$id,"userid"=>$userid))->count();
      $e=array("nr"=>$resulr);
      $this->ajaxReturn($e);


  }
  /**
   * 查询用户所有收藏
   * @return [type] [description]
   */
  function findshou(){
      //$id=I("id");
      $userid=I("userid");
      $app_shoucang=M("app_shoucang");
      $resulr=$app_shoucang->join('__POSTS__ ON __APP_SHOUCANG__.wenid = __POSTS__.id')->where(array("userid"=>$userid))->select();
     
      $this->ajaxReturn($resulr);


  }
  /**
   * 增加用户收藏
   */
  function addshoucang(){
      $id=I("id");
      $userid=I("userid");
      $app_shoucang=M("app_shoucang");
      if($app_shoucang->add(array("wenid" =>$id,"userid"=>$userid,'shoucangtime'=>time()))){

      $e=array("nr"=>1);
    $this->ajaxReturn($e);
    }else{
      $e=array("nr"=>2);
    $this->ajaxReturn($e);
    }
     // $this->ajaxReturn($resulr);
  }
/**
 * 删除用户收藏
 * @return [type] [description]
 */
  function delshoucang(){
 $id=I("id");
      $userid=I("userid");
      $app_shoucang=M("app_shoucang");
      if($app_shoucang->where(array("wenid" =>$id,"userid"=>$userid))->delete()){

      $e=array("nr"=>1);
    $this->ajaxReturn($e);
    }else{
      $e=array("nr"=>2);
    $this->ajaxReturn($e);
    }


  }
  /**
   * 添加评论
   */

  function addpinglun(){
    $id=I("id");
      $userid=I("userid");
      $neirong=I("neirong");
       $app_pinglun=M("app_pinglun");
      if($app_pinglun->add(array("wenid" =>$id,"userid"=>$userid,'neirong'=>$neirong,'pingjiatime'=>date("Y-m-d H:i:s",time())))){

      $e=array("nr"=>1);
    $this->ajaxReturn($e);
    }else{
      $e=array("nr"=>0);
    $this->ajaxReturn($e);
    }

  }
  /**
   * 查询文章内的所有评论
   */
  function findpinglun(){
    $id=I("id");
    $app_pinglun=M("app_pinglun");
   $wen= $app_pinglun->join('__APP_USER__ ON __APP_PINGLUN__.userid = __APP_USER__.id')->where(array('wenid' =>$id,'zhuangtai'=>0  ))->field('nicheng,pingjiatime,neirong')->select();
    $this->ajaxReturn($wen);
  }
  /**
   * 每次查看加一
   * @return [type] [description]
   */
  function addkan(){
      $db = M('posts');
         $id = I('id');
         $where = array('id'=>$id);
         $db->where($where)->setInc('post_hits', 1);

  }
  /**
   * 查询全部产品
   */
function allchan(){
      $shop = M("shop");
        $wen=$shop->order('id desc')->select();
$this->ajaxReturn($wen);

}
//上传图片
function update(){

  $allow_type=array("jpg","png","gif");//允许上传的图片类型

$name=$_FILES['pic']['name']; //客户端文件的原名称
$upload_dir="data/upload/". date('Y-m-d',time()) ."/";//图片保存目录


$a=$this->createDir($upload_dir) ;
//$this->ajaxReturn($upload_dir);

//strtolower 将字符串转为小写
$file_suffix=strtolower(substr($name,-3,3));

if(!in_array($file_suffix,$allow_type))
{
    //implode 把数组元素组合为字符串
    $all_type=implode("、",$allow_type);
    exit("<script>alert('您只能上传 $all_type 格式的图片！');history.back();</script>");
}
//explode 把字符串分割为数组
$name_array=explode(".",$name);

do
{
    // $name_array[0]=randomFileName();
    //  $name=implode(".",$name_array);
    $upload_file=$upload_dir.$name;
    $upload_files= ''.$upload_dir.'s'.$name.'';

}while(file_exists($upload_file));
$tmp_name=$_FILES['pic']['tmp_name'];
//is_uploaded_file 判断文件是否是通过HTTP POST上传的


if(is_uploaded_file($tmp_name))

{

    /*
     //限制上传文件的大小，此例只保存缩略图就不需要了
    if($_FILES['upImg']['size'] > 204800)
    {
          exit("<script>alert('上传的图片请不要超过200KB！');history.back();</script>");
    }
    */
    //move_uploaded_file 将上传文件移至指定目录
    $zy=move_uploaded_file($tmp_name,$upload_file);
// $this->ajaxReturn($zy);
    if($zy)
    {

        //mkThumbnail($upload_file, 100, 65,$upload_files);
       // echo '{"statu":"1","path":"http://xin.51fale.com/data/upload/'. date("Y-m-d",time()) .'/'.$name.'"}';
       // $img= strip_tags(I("img"));
       $img="http://xin.51fale.com/data/upload/".date("Y-m-d",time())."/".$name;
$img = str_replace(array("[","]","","\\"),"",$img);
        $wen=array("statu"=>1,"path"=>$img);
$this->ajaxReturn($wen);
        //  $sql="insert into zc_sytp (tupian,paixu,suolue) values ('".$name."',".$paixu.",'s".$name."')";
        //  $zhixing=$conn->query($sql);

    }
}

}
/**
 * 生成缩略图函数（支持图片格式：gif、jpeg、png和bmp）
 * @author ruxing.li
 * @param  string $src      源图片路径
 * @param  int    $width    缩略图宽度（只指定高度时进行等比缩放）
 * @param  int    $width    缩略图高度（只指定宽度时进行等比缩放）
 * @param  string $filename 保存路径（不指定时直接输出到浏览器）
 * @return bool
 */
protected function mkThumbnail($src, $width = null, $height = null, $filename = null) {
    if (!isset($width) && !isset($height))
        return false;
    if (isset($width) && $width <= 0)
        return false;
    if (isset($height) && $height <= 0)
        return false;

    $size = getimagesize($src);
    if (!$size)
        return false;

    list($src_w, $src_h, $src_type) = $size;
    $src_mime = $size['mime'];
    switch($src_type) {
        case 1 :
            $img_type = 'gif';
            break;
        case 2 :
            $img_type = 'jpeg';
            break;
        case 3 :
            $img_type = 'png';
            break;
        case 15 :
            $img_type = 'wbmp';
            break;
        default :
            return false;
    }

    if (!isset($width))
        $width = $src_w * ($height / $src_h);
    if (!isset($height))
        $height = $src_h * ($width / $src_w);

    $imagecreatefunc = 'imagecreatefrom' . $img_type;
    $src_img = $imagecreatefunc($src);
    $dest_img = imagecreatetruecolor($width, $height);
    imagecopyresampled($dest_img, $src_img, 0, 0, 0, 0, $width, $height, $src_w, $src_h);

    $imagefunc = 'image' . $img_type;
    if ($filename) {
        $imagefunc($dest_img, $filename);
    } else {
        header('Content-Type: ' . $src_mime);
        $imagefunc($dest_img);
    }
    imagedestroy($src_img);
    imagedestroy($dest_img);
    return true;
}

protected  function createDir($aimUrl) {
    $aimUrl = str_replace('', '/', $aimUrl);

    $aimDir = '';
    $arr = explode('/', $aimUrl);
    $result = true;
    foreach ($arr as $str) {
        $aimDir .= $str . '/';
 
       $kkk=file_exists($aimDir);

        if (!file_exists($aimDir)) {
            $result = mkdir($aimDir);
            
        }
    }

    return $result;
}

public function add_postf(){

      $shop = D("shop");
      $shop_relationships = D("shop_relationships");

    if (IS_POST) {
      // if(empty($_POST['term'])){
      //   $this->error("请至少选择一个分类！");
      // }

      if( !empty(I("img"))){
 $img= strip_tags(I("img"));
$img = str_replace(array("&quot;","[","]","","\\"),"",$img);
 //$wen=array($img);
 $img = explode(",", strip_tags($img));
  
     
        foreach ( array_keys($img) as $key){
         // $wen=array("nr"=>I("img"));
          $photourl=$img[$key];
         // $wen=array("nr"=>$photourl);
           $article['smeta']['photo'][]=array("url"=>$photourl);
        }
        // for($i=0;$i<sizeof()($img);$i++){
        //     $photourl=$img[$i];
        //   $wen=array("nr"=>$photourl);
        // }
      }
     
      $article['smeta']['thumb'] = sp_asset_relative_url( $article['smeta']['thumb']);
   //$wen=array("nr"=> $article['smeta'] );
      $article['shijian']=date("Y-m-d H:i:s",time());
      //$_POST['post']['faburenid']=get_current_admin_id();

      $article['phone']=I("phone");
      $article['faburenid']=I("faburenid");
      $article['faburen']=I("faburen");
      $article['post_title']=I("post_title");
      $article['price']=I("price");
      $article['zhaiyao']=I("zhaiyao");
        $article['xuqiu']=I("xuqiu");

      $article['smeta']=json_encode($article['smeta']);
    //$wen=array("nr"=> $article['smeta']);
      $article['xuqiu']=htmlspecialchars_decode($article['xuqiu']);

     $result=$shop->add($article);
// $this->ajaxReturn(I("term"));
      if ($result) {
        
          $shop_relationships->add(array("term_id"=>I("term"),"object_id"=>$result));
        
        
        $wen=array("nr"=>1);
      } else {
        $wen=array("nr"=>0);
      }
   
      $this->ajaxReturn($wen);
    }
  }
  public function addyijian(){
        $a['shebei']=I('shebei');
        $a['xitong']=I('xitong');
        $a['banben'] =I('banben');
       $a['kehuduan']=I('kehuduan');
        $a['uid']=I('uid');
       $a['phone']=I("phone");
        $a['yijian']=I("yijian");
         $a['shijian']=date("Y-m-d H:i:s",time());
        $yi=M("yijian");
 $result= $yi->add($a);
  if ($result) {
  $wen=array("nr"=>1);
      } else {
        $wen=array("nr"=>0);
      }
   
      $this->ajaxReturn($wen);

  }

}