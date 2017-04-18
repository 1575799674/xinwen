<?php
namespace Comment\Controller;

use Common\Controller\AdminbaseController;

class CommentadminController extends AdminbaseController{
	
	protected $comments_model;
	protected $app_pinglun;
	
	public function _initialize(){
		parent::_initialize();
		$this->comments_model=D("Common/Comments");
		$this->app_pinglun=M("app_pinglun");
	}

	// 后台评论列表
	public function index($table=""){
		$where=array();
		if(!empty($table)){
			$where['post_table']=$table;
		}
		
		$post_id=I("get.post_id");
		if(!empty($post_id)){
			$where['post_id']=$post_id;
		}
		$count=$this->app_pinglun->where($where)->count();
		$page = $this->page($count, 20);
		$comments=$this->app_pinglun
		->join('__APP_USER__ ON __APP_PINGLUN__.userid = __APP_USER__.id')
		->where($where)
		->limit($page->firstRow . ',' . $page->listRows)
		->order("pingjiatime DESC")
		->select();
		$this->assign("comments",$comments);
		$this->assign("page", $page->show('Admin'));
		$this->display(":index");
	}
	
	// 后台评论删除
	public function delete(){
		if(isset($_GET['id'])){
			$id = intval(I("get.id"));
			if ($this->app_pinglun->where("wid=$id")->delete()!==false) {
				$this->success("删除成功！");
			} else {
				$this->error("删除失败！");
			}
		}
		if(isset($_POST['ids'])){
			$ids=join(",",$_POST['ids']);
			if ($this->app_pinglun->where("wid in ($ids)")->delete()!==false) {
				$this->success("删除成功！");
			} else {
				$this->error("删除失败！");
			}
		}
	}
	
	// 后台评论审核
	public function check(){
		if(isset($_POST['ids']) && $_GET["check"]){
			$data["status"]=1;
				
			$ids=join(",",$_POST['ids']);
			
			if ( $this->comments_model->where("id in ($ids)")->save($data)!==false) {
				$this->success("审核成功！");
			} else {
				$this->error("审核失败！");
			}
		}
		if(isset($_POST['ids']) && $_GET["uncheck"]){
				
			$data["status"]=0;
			$ids=join(",",$_POST['ids']);
			if ( $this->comments_model->where("id in ($ids)")->save($data)!==false) {
				$this->success("取消审核成功！");
			} else {
				$this->error("取消审核失败！");
			}
		}
	}
	
}