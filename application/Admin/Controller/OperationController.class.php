<?php
/**
 * Created by PhpStorm.
 * User: hui
 * Date: 2016/8/11
 * Time: 10:29
 * 运维控制器
 */

namespace Admin\Controller;

use Common\Controller\AdminbaseController;

class OperationController extends AdminbaseController
{
    protected $onlineHelp;      //在线帮助
    protected $helpType;        //在线帮助类别
    protected $message;         //消息表
    protected $couponInfo;      //优惠信息
    protected $pushModel;//数据推送
    protected $couponInfoModel;//数据推送
    protected $taskModel;//数据推送
    protected $coinRuleModel;//数据推送
    protected $resTypeModel;//数据推送
    protected $cancleReasonModel;//数据推送
    protected $onlineFeedbackModel;//数据推送
    protected $userDiscountModel;
    protected $versionlogModel;

    function _initialize()
    {
        parent::_initialize();
        $this->onlineHelp = D("Common/OnlineHelp");
        $this->helpType = D("Common/HelpType");
        $this->message = D("Common/Message");
        $this->couponInfo = D("Common/CouponInfo");
        $this->pushModel = D("Common/Push");
        $this->couponInfoModel = D("Common/CouponInfo");
        $this->taskModel = D("Common/Task");
        $this->coinRuleModel = D("Common/CoinRule");
        $this->resTypeModel = D("Common/ResType");
        $this->cancleReasonModel = D("Common/CancleReason");
        $this->onlineFeedbackModel = D("Common/OnlineFeedback");
        $this->userDiscountModel = D("Common/UserDiscount");
        $this->versionlogModel = D("Common/Versionlog");
    }

    /**
     * 帮助列表
     */
    function online_help()
    {
        $returns = $this->onlineHelp->select();
        $this->assign('onlineData', $returns);
        $this->display();
    }

    /**
     * 帮助类型列表
     */
    function help_type()
    {
        $returns = $this->helpType->select();
        $tree = $this->order_list($returns);
        $this->assign('helpTypeData', $tree);
        $this->display();
    }

    /**
     * @param $cate
     * @param int $pid
     * @param int $level
     * @param string $html
     * @return array
     * 帮助列表递归
     */
    function order_list($cate, $pid = 0, $level = 0, $html = '--')
    {
        $tree = array();
        foreach ($cate as $v) {
            if ($v['parent_id'] == $pid) {
                $htm = str_repeat($html, $level);
                $v['help_name'] = $htm . $v['help_name'];
                $tree[] = $v;
                $tree = array_merge($tree, self::order_list($cate, $v['help_type_id'], $v['level'] + 1, $html));
            }
        }
        return $tree;
    }

    /**
     * 增加帮助类型
     */
    function add_help_type()
    {
        if (IS_POST) {
            if ($this->helpType->create()) {
                if (I('parent_id') == 0) {
                    $data = array(
                        'level' => 0,
                        'help_code' => I('help_code'),
                        'parent_id' => I('parent_id'),
                        'remark' => I('remark'),
                        'help_name' => I('help_name')
                    );
                    $ret = $this->helpType->add($data);
                    if ($ret) {
                        $this->success("新增成功！", U("help_type"));
                    } else {
                        $this->error("新增失败！", U("help_type"));
                    }
                } else {
                    $ret = M("help_type")->where(array('help_type_id' => I('parent_id')))->field('level')->find();
                    if ($ret) {
                        $level = $ret['level'] + 1;
                        $data = array(
                            'level' => $level,
                            'help_code' => I('help_code'),
                            'parent_id' => I('parent_id'),
                            'remark' => I('remark'),
                            'help_name' => I('help_name')
                        );
                        $ret = $this->helpType->add($data);
                        if ($ret) {
                            $this->success("新增成功！", U("help_type"));
                        } else {
                            $this->error("新增失败！", U("help_type"));
                        }
                    } else {
                        $this->error("新增失败！", U("help_type"));
                    }
                }
            } else {
                $this->error($this->helpType->getError());
            }
        } else {
            $returns = $this->helpType->select();
            $tree = $this->order_list($returns);
            $this->assign('helpTypeData', $tree);
            $return = array(
                'help_type_id' => I('help_type_id'),
            );
            $this->assign("helpType_data", $return);
            $this->display();
        }

    }

    /**
     * 修改帮助类别
     */
    function help_type_edit()
    {
        if (IS_POST) {
            // 根据表单提交的POST数据创建数据对象
            if ($this->helpType->create()) {
                $ret = $this->helpType->save();
                if ($ret) {
                    $this->success("修改成功！", U("help_type"));
                } else {
                    $this->error("修改失败！");
                }
            } else {
                $this->error($this->helpType->getError());
            }
        } else {
            $returns = $this->helpType->select();
            $tree = $this->order_list($returns);
            $this->assign('helpTypeData', $tree);
            $ret = $this->helpType->where(array('help_type_id' => I('help_type_id')))->find();
            $this->assign("helpType_data", $ret);
            $this->display();
        }
    }

    /**
     * 删除帮助类型
     */
    function help_type_del()
    {
        $rets = $this->helpType->where(array('parent_id' => I('help_type_id')))->find();
        if ($rets) {
            $this->error("此类存在子类型，请先删除子类型", U("help_type"));
        } else {
            $ret = $this->helpType->where(array('help_type_id' => I('help_type_id')))->delete();
            if ($ret) {
                $this->success("删除成功！", U("help_type"));
            } else {
                $this->error("删除失败！", U("help_type"));
            }
        }

    }

    /**
     * 增加帮助
     */
    function add_help()
    {
        if (IS_POST) {
            $data = $this->onlineHelp->create();

            if ($data) {
                if ($data['help_type_id'] == '请选择帮助类别')
                    $this->error("帮助类别不能为空！");

                $ret = $this->onlineHelp->add();
                if ($ret) {
                    $this->success("新增成功！", U("online_help"));
                } else {
                    $this->error("新增失败！", U("online_help"));
                }
            } else {
                $this->error($this->onlineHelp->getError());
            }
        } else {

            $onlines = $this->onlineHelp->field('group_concat(help_type_id) as help_type_ids')->find();

//                $returns = $this->helpType->where('help_type_id not in ('.$onlines['help_type_ids'].')')->select();
            $returns = $this->helpType->where('parent_id = 0')->select();

            $this->assign('helpTypeRootData', $returns);
            $this->display();
        }

    }

    /**
     * 删除帮助
     */
    function online_help_del()
    {
        $ret = $this->onlineHelp->where(array('help_id' => I('help_id')))->delete();
        if ($ret) {
            $this->success("删除成功！", U("online_help"));
        } else {
            $this->error("删除失败！", U("online_help"));
        }
    }

    /**
     * 修改帮助
     */
    function online_help_edit()
    {
        if (IS_POST) {
            if ($this->onlineHelp->create()) {
                $ret = $this->onlineHelp->save();
                if ($ret) {
                    $this->success("修改成功！", U("online_help"));
                } else {
                    $this->error("修改失败！");
                }
            } else {
                $this->error($this->onlineHelp->getError());
            }
        } else {
            $ret = $this->onlineHelp->where(array('help_id' => I('help_id')))->find();
            $returns = $this->helpType->where(array('level' => 1))->select();

            $returns = $this->helpType->where('parent_id = 0')->select();
            $this->assign('helpTypeRootData', $returns);

            $help_types = $this->helpType->where('help_type_id=' . $ret['help_type_id'])->find();
            $ret['parent_id'] = $help_types['parent_id'];

            $childs = $this->helpType->where('parent_id = ' . $help_types['parent_id'])->select();

            $this->assign('onlineHelpData', $ret);
            $this->assign('helpTypeRootData', $returns);
            if ($help_types)
                $this->assign('helpTypeData', $childs);
            $this->display();
        }
    }

    /**
     * 修改帮助
     */
    function online_help_see()
    {

        $ret = $this->onlineHelp->where(array('help_id' => I('help_id')))->find();
        $returns = $this->helpType->where(array('level' => 2))->select();
        $this->assign('helpTypeData', $returns);
        $this->assign('onlineHelpData', $ret);
        $this->display();

    }

    /**
     * 通知公告列表=========================================
     */
    function notice_list()
    {
        $message = M("message");
        $count = $message->count();
        $page = $this->page($count, 20);
        $lists = $message
            ->order("send_time DESC")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
        $this->assign('message_data', $lists);
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }

    /**
     * 通知公告新增
     */
    function notice_add()
    {
        if (IS_POST) {
            if ($this->message->create()) {
                $ret = $this->message->add();
                if ($ret) {
                    $this->success("新增成功！", U("notice_list"));
                } else {
                    $this->error("新增失败！", U("notice_list"));
                }
            } else {
                $this->error($this->message->getError());
            }

        } else {
            $this->display();
        }

    }

    /**
     * 通知公告编辑
     */
    function notice_edit()
    {
        if (IS_POST) {
            // 根据表单提交的POST数据创建数据对象
            if ($this->message->create()) {
                $ret = $this->message->save();
                if ($ret) {
                    $this->success("修改成功！", U("notice_list"));
                } else {
                    $this->error("修改失败！");
                }
            } else {
                $this->error($this->message->getError());
            }
        } else {
            $ret = $this->message->where(array('message_id' => I('message_id')))->find();
            $this->assign("message_data", $ret);
            $this->display();
        }
    }

    /**
     * 公告数据推送
     */
    function notice_push()
    {
        $type = (int)I('type');
        $message_id = I('message_id');
        $message = M("message");
        $ret = $message->where(array('message_id'=>$message_id))->find();
        switch ($type) {
            case 1:
                //导入极光推送
                import("Vendor.Jpush.ptJpush");
                $ptJpush = new \ptJpush();
                //$push_status = $ptJpush->push(array('registration_id'=>array('1114a89792a6f69cfdb')), '', $ret['title'], array('message_id' =>$message_id));
                $push_status = $ptJpush->push('all', '', $ret['title'], array('wid' =>$ret['wid']));
                if($push_status){
                    $this->success("推送成功！");
                }else{
                    $this->error("推送失败！");
                }
                break;
            // case 2:
            //     import("Vendor.Jpush.pnJpush");
            //     $pnJpush = new \pnJpush();
            //     //$push_status = $pnJpush->push(array('registration_id'=>array('1114a89792a6f69cfdb')), '', $ret['title'], array('message_id' =>$message_id));
            //     $push_status = $pnJpush->push('all', '', $ret['title'], array('message_id' =>$message_id));
            //     if($push_status){
            //         $this->success("推送成功！");
            //     }else{
            //         $this->error("推送失败！");
            //     }
            //     break;

        }


    }


        /**
         * 通知公告删除
         */
        function notice_del()
        {
            $ret = $this->message->where(array('message_id' => I('message_id')))->delete();
            if ($ret) {
                $this->success("删除成功！", U("notice_list"));
            } else {
                $this->error("删除失败！", U("notice_list"));
            }

        }

        /**
         * 数据推送列表===============================================
         */
        function push_list()
        {
            $push_db = M("push");
            $count = $push_db->count();
            $page = $this->page($count, 20);

            $this->assign("datas", $push_db->limit($page->firstRow . ',' . $page->listRows)->select());
            $this->assign("page", $page->show('Admin'));
            $this->display();
        }

        /**
         * 数据推送新增（ios和android双平台选择推送）
         */
        function push_add()
        {
            if (IS_POST) {
                $_POST['push_time'] = date('Y-m-d H:i:s', time());

                if ($this->pushModel->create()) {
                    $ret = $this->pushModel->add();

                    //推送流程
                    if ($ret) {
                        $platform = "";
                        switch (I('platform')) {
                            case 1:
                                $platform = array('android', 'ios');
                                break;
                            case 2:
                                $platform = array('android');
                                break;
                            case 3:
                                $platform = array('ios');
                                break;
                        }
                        import('Vendor.Jpush.ptJpush');
                        $app_key = "d08a955863056cb9ac495d40";
                        $master_secret = "b4765d0e3ac1d198f25c041d";
                        // if (I('push_type') == 1) {//1跑腿
                        //     $app_key = C('PT_APP_KEY'); //待发送的应用程序(appKey)，只能填一个。
                        //     $master_secret = C('PT_MASTER_SECRET'); //主密码
                        // } else {//2跑客
                        //     $app_key = C('PN_APP_KEY');//'38c5d5654981e7885c1d55ab';            //待发送的应用程序(appKey)，只能填一个。
                        //     $master_secret = C('PN_MASTER_SECRET');//'71a5bf3e1f7801fa4fd8fb8b';      //主密码
                        // }

                        $ptJpush = new \ptJpush($app_key, $master_secret);//实例化对象
                        $rets = $ptJpush->push($platform, I('title'), I('content'));//推送
 $this->error($rets);
                        $upret = $this->pushModel->where('pid=' . $ret)->save(array('push_status' => 1));//修改db_push的推送状态字段
                        if ($upret) {
                            $this->success("新增推送成功！", U("push_list"));
                        } else {
                            $this->success("新增推送成功！状态修改失败！", U("push_list"));
                        }
                    } else {
                        $this->error("新增失败！", U("push_list"));
                    }
                } else {
                    $this->error($this->pushModel->getError());
                }
            } else {
                $this->display();
            }
        }

        /**
         * 数据推送查看
         */
        function push_see()
        {
            $ret = $this->pushModel->where(array('pid' => I('pid')))->find();
            $this->assign('datas', $ret);
            $this->display();
        }

        /**
         * 版本日志列表==============================================
         */
        function version_log_list()
        {
            $version_db = M("versionlog");
            $count = $version_db->count();
            $page = $this->page($count, 20);
            $ret = $version_db->limit($page->firstRow . ',' . $page->listRows)->order('time desc')->select();
            $this->assign("datas", $ret);
            $this->assign("page", $page->show('Admin'));
            $this->display();
        }

        /**
         * 版本日志新增
         */
        function version_log_add()
        {
            if (IS_POST) {
                $_POST['time'] = date('Y-m-d H:i:s', time());
                if ($this->versionlogModel->create()) {
                    $ret = $this->versionlogModel->add();
                    if ($ret) {
                        $this->success("新增成功！", U("version_log_list"));
                    } else {
                        $this->error("新增失败！", U("version_log_list"));
                    }
                } else {
                    $this->error($this->versionlogModel->getError());
                }
            } else {
                $this->display();
            }
        }

        /**
         * 版本日志编辑
         */
        function version_log_edit()
        {

            if (IS_POST) {

                if ($this->versionlogModel->create())
                    $ret = $this->versionlogModel->save($_POST);
                if ($ret) {
                    $this->success("修改成功！", U("version_log_list"));
                } else {
                    $this->error($this->versionlogModel->getError());
                }

            } else {
                $ret = $this->versionlogModel->where(array('vid' => I('vid')))->find();

                $this->assign('data', $ret);
                $this->display();
            }
        }

        /*
         * 版本日志删除
         */
        function version_log_del()
        {
            $version_db = M("versionlog");
            $ret = $version_db->delete(I('vid'));
            if ($ret) {
                $this->success("删除成功！", U("version_log_list"));
            } else {
                $this->error("删除失败");
            }
        }

        /**
         * 优惠券列表=================================
         */
        function coupon_list()
        {
            $is_complete = I('is_complete');

            $formget = array('is_complete' => $is_complete);

            $where = ' 1 ';
            if ($is_complete == 0)//未兑换完
                $where .= ' and surplus_num > 0 ';
            if ($is_complete == 1)//已兑换完
                $where .= ' and surplus_num =0 ';

            $count = $this->couponInfo->where($where)->count();
            import("Org.Util.Pagemumayi");
            $page = new \Pagemumayi($count, 20);
            //分页跳转的时候保证查询条件
            foreach ($formget as $key => $val) {
                $page->parameter .= "$key=" . urlencode($val) . "&";
            }

            $ret = $this->couponInfo
                ->field("coupon_id,city_id,title,coupon_key,coupon_type,surplus_num,DATE_FORMAT(valid_end_time,'%Y-%m-%d') as valid_end_time,is_push,num,effective_days")
                ->where($where)
                ->order('coupon_id desc')
                ->limit($page->firstRow . ',' . $page->listRows)
                ->select();

            $this->assign("page", $page->show('Admin'));
            $this->assign('coupon_data', $ret);
            $this->assign('formget', $formget);
            $this->display();;
        }
         /**
         * 优惠券列表=================================
          * @param $ret['effective_days']有效天数
          * 12.12后用
          */
        function coupon_list2()
        {
            $is_complete = I('is_complete');

            $formget = array('is_complete' => $is_complete);

            $where = ' 1 ';
            if ($is_complete == 0)//未兑换完
                $where .= ' and surplus_num > 0 ';
            if ($is_complete == 1)//已兑换完
                $where .= ' and surplus_num =0 ';

            $count = $this->couponInfo->where($where)->count();
            import("Org.Util.Pagemumayi");
            $page = new \Pagemumayi($count, 20);
            //分页跳转的时候保证查询条件
            foreach ($formget as $key => $val) {
                $page->parameter .= "$key=" . urlencode($val) . "&";
            }

//            $ret = $this->couponInfo
//                ->field("coupon_id,city_id,title,coupon_key,coupon_type,surplus_num,DATE_FORMAT(valid_end_time,'%Y-%m-%d') as valid_end_time,is_push,num")
//                ->where($where)
//                ->order('coupon_id desc')
//                ->limit($page->firstRow . ',' . $page->listRows)
//                ->select();

              $ret = $this->couponInfo
                ->field("coupon_id,city_id,title,coupon_key,coupon_type,surplus_num,effective_days,valid_end_time,is_push,num")
                ->where($where)
                ->order('coupon_id desc')
                ->limit($page->firstRow . ',' . $page->listRows)
                ->select();
//              dump($ret);
//              die;
            $this->assign("page", $page->show('Admin'));
            $this->assign('coupon_data', $ret);
            $this->assign('formget', $formget);
            $this->display();;
        }

        /**
         * 优惠券编辑
         */
       function coupon_edit()
        {
            if (IS_POST) {
//                dump($_POST);
                if ($this->couponInfo->create()) {
                    $ret = $this->couponInfo->save($_POST);
                    if ($ret) {
                        $this->success("成功", U("coupon_list?is_complete=" . I('is_complete')));
                    } else {
                        $this->error("失败", $this->couponInfo->getError());
                    }
                } else {
                    $this->error($this->couponInfo->getError());
                }
            } else {
                $ret = $this->couponInfo->where(array('coupon_id' => I('coupon_id')))->find();
                if ($ret['city_id']) {
                    $ret['province_id'] = getProvinceId($ret['city_id']);

                    $citys = getCityList($ret['province_id']);
                    if ($citys)
                        $this->assign('citys', $citys);
                }

                $province = M('province');
                $this->assign('province_data', $province->select());

                $this->assign('coupon_data', $ret);
                $this->assign("formget", array('is_complete' => I('is_complete')));
                $this->display();
            }
        }
 /**
 * 优惠券编辑
 * @param $ret['effective_days']有效天数
 * 12.12后使用
 */
function coupon_edit2()
        {
 
            if (IS_POST) {
                if ($this->couponInfo->create()) {
                    $ret = $this->couponInfo->save($_POST); 
//                    echo $this->couponInfo->getLastSql();
//                    dump($ret);
//                    die;
                    if ($ret) {
                        $this->success("成功", U("coupon_list?is_complete=" . I('is_complete')));
                    } else {
                        $this->error("失败".$this->couponInfo->getError());
                    }
                } else {
                    $this->error($this->couponInfo->getError());
                }
            } else {
                $ret = $this->couponInfo->where(array('coupon_id' => I('coupon_id')))->find();
                if ($ret['city_id']) {
                    $ret['province_id'] = getProvinceId($ret['city_id']);

                    $citys = getCityList($ret['province_id']);
                    if ($citys)
                        $this->assign('citys', $citys);
                }

                $province = M('province');
                $this->assign('province_data', $province->select());

                $this->assign('coupon_data', $ret);
                //dump($ret);
                $this->assign("formget", array('is_complete' => I('is_complete')));
                $this->display();
            }
        }

  
      /**
         * 优惠券添加
         */
        function conpon_add()
        {
            if (IS_POST) {
                $_POST['surplus_num'] = I('num');
                if ($this->couponInfo->create()) {
//                    dump($this->couponInfo["data"]['effective_days']);
                    $ret = $this->couponInfo->add();
                    if ($ret) {
                        $this->success("成功", U("Operation/coupon_list?is_complete=-1"));
                    } else {
                        $this->error("失败", $this->couponInfo->getError());
                    }
                } else {
                    $this->error($this->couponInfo->getError());
                }
            } else {
                $province = M('province');
                $this->assign('province_data', $province->select());
                $this->display();
            }

        }
        /**
         * 优惠券删除
         */
        function coupon_del()
        {
            $version_db = M("coupon_info");
            $ret = $version_db->where('coupon_id=' . I('coupon_id') . ' and is_push=0')->delete();
            if ($ret) {
                $this->success("删除成功！", U("coupon_list?is_complete=" . I('is_complete')));
            } else {
                $this->error("删除失败");
            }
        }

        /**
         * 查看优惠券激活信息
         */
        function coupon_active()
        {
            $user_coupondb = M('user_coupon');
            $user_coupon_id = intval(I('coupon_id'));
            $count = $user_coupondb->where(array('coupon_id' => $user_coupon_id))->count();
            $page = $this->page($count, 20);

            $user_coupon = $user_coupondb->where(array('coupon_id' => $user_coupon_id))->limit($page->firstRow . ',' . $page->listRows)->order('is_used')->select();

            $this->assign('datas', $user_coupon);
            $this->assign("page", $page->show('Admin'));
            $this->assign("formget", array('is_complete' => I('is_complete')));

            $this->display();
        }

        //发布
        public
        function coupon_push()
        {
            $user_coupon_id = intval(I('coupon_id'));
            if ($user_coupon_id) {
                $app_users = M("coupon_info");
                $rst = $app_users->where(array("coupon_id" => $user_coupon_id))->setField('is_push', 1);
                if ($rst) {
                    $this->success("发布成功！", U("Operation/coupon_list?is_complete=" . I('is_complete')));
                } else {
                    $this->error('发布失败！');
                }
            } else {
                $this->error('数据传入失败！');
            }
        }

        /**
         * 任务列表=================================
         */
        function task_list()
        {
            $version_db = M("task");
            $count = $version_db->count();
            $page = $this->page($count, 20);

            $ret = $version_db->limit($page->firstRow . ',' . $page->listRows)->select();

            $this->assign("datas", $ret);
            $this->assign("page", $page->show('Admin'));
            $this->display();
        }

        /**
         * 任务添加
         */
        function task_add()
        {
            if (IS_POST) {
                if ($this->taskModel->create()) {
                    $ret = $this->taskModel->add();
                    if ($ret) {
                        $this->success("成功", U("task_list"));
                    } else {
                        $this->error("失败", $this->taskModel->getError());
                    }
                } else {
                    $this->error($this->taskModel->getError());
                }
            } else {
                $task = M('task');
                $this->assign('data', $task->find());
                $this->display();
            }
        }

        /**
         * 任务编辑
         */
        function task_edit()
        {
            if (IS_POST) {
                // 根据表单提交的POST数据创建数据对象
                if ($this->taskModel->create()) {
                    $ret = $this->taskModel->save();
                    if ($ret) {
                        $this->success("修改成功！", U("task_list"));
                    } else {
                        $this->error("修改失败！");
                    }
                } else {
                    $this->error($this->taskModel->getError());
                }
            } else {
                $ret = $this->taskModel->where(array('task_id' => I('task_id')))->find();
                $this->assign("data", $ret);
                $this->display();
            }
        }

        /**
         * 任务删除
         */
        function task_del()
        {
            $version_db = M("task");
            $ret = $version_db->delete(I('task_id'));
            if ($ret) {
                $this->success("删除成功！", U("task_list"));
            } else {
                $this->error("删除失败");
            }
        }


        /**
         * 跑客任务列表=================================
         */
        function pntask_list()
        {
            $pntask_db = M('dispatch_task t');

            $mobile = I("mobile");//电话
            $start_time = I("start_time");//开始时间
            $end_time = I("end_time");//结束时间
            $is_complete = intval(I("is_complete"));//是否完成

            $formget = array('mobile' => $mobile
            , 'is_complete' => $is_complete,
                'start_time' => $start_time,
                'end_time' => $end_time);

            $where = " 1=1 ";

            if (!empty($mobile))
                $where = $where . " and d.mobile like '%" . $mobile . "%'";
            if (!empty($is_complete) && $is_complete != -1)
                $where = $where . ' and is_complete = ' . $is_complete;

            if (!empty($start_time))
                $where = $where . " and complete_time >= '" . $start_time . " 00:00:00'";
            if (!empty($end_time))
                $where = $where . " and complete_time <= '" . $end_time . " 23:59:59'";

            $count = $pntask_db->where($where)->join('db_dispatch d ON t.dispatch_id=d.dispatch_id')->count();
            import("Org.Util.Pagemumayi");
            $page = new \Pagemumayi($count, 20);
            //分页跳转的时候保证查询条件
            foreach ($formget as $key => $val) {
                $page->parameter .= "$key=" . urlencode($val) . "&";
            }

            $data = $pntask_db->field('d.mobile,t.*')->join('db_dispatch d ON t.dispatch_id=d.dispatch_id')
                ->where($where)
                ->order(' t.dispatch_id,complete_time desc')
                ->limit($page->firstRow . ',' . $page->listRows)
                ->select();

            $this->assign('datas', $data);
            $this->assign('page', $page->show('Admin'));
            $this->assign("formget", $formget);
            $this->display();
        }

        /**
         * 帮币奖惩规则列表=================================
         */
        function coin_rule_list()
        {
            $count = $this->coinRuleModel->count();
            $page = $this->page($count, 20);
            $this->assign('datas', $this->coinRuleModel->limit($page->firstRow . ',' . $page->listRows)->select());
            $this->assign('page', $page->show('Admin'));
            $this->display();;
        }

        /**
         * 帮币奖惩规则添加
         */
        function coin_rule_add()
        {
            if (IS_POST) {
                if ($this->coinRuleModel->create()) {
                    $ret = $this->coinRuleModel->add();
                    if ($ret) {
                        $this->success("成功", U("coin_rule_list"));
                    } else {
                        $this->error("失败", $this->coinRuleModel->getError());
                    }
                } else {
                    $this->error($this->coinRuleModel->getError());
                }
            } else {
                $province = M('coin_rule');
                $this->assign('datas', $province->select());
                $this->display();
            }
        }

        /**
         * 帮币奖惩规则编辑
         */
        function coin_rule_edit()
        {
            if (IS_POST) {
                // 根据表单提交的POST数据创建数据对象
                if ($this->coinRuleModel->create()) {
                    $ret = $this->coinRuleModel->save();
                    if ($ret) {
                        $this->success("修改成功！", U("coin_rule_list"));
                    } else {
                        $this->error("修改失败！");
                    }
                } else {
                    $this->error($this->coinRuleModel->getError());
                }
            } else {
                $ret = $this->coinRuleModel->where(array('rule_id' => I('rule_id')))->find();
                $this->assign("data", $ret);
                $this->display();
            }
        }

        /**
         * 帮币奖惩规则删除
         */
        function coin_rule_del()
        {
            $version_db = M("coin_rule");
            $ret = $version_db->delete(I('rule_id'));
            if ($ret) {
                $this->success("删除成功！", U("coin_rule_list"));
            } else {
                $this->error("删除失败");
            }
        }

        /**
         * 物品类型列表=================================
         */
        function res_type_list()
        {
            $count = $this->resTypeModel->count();
            $page = $this->page($count, 20);

            $this->assign('datas', $this->resTypeModel->limit($page->firstRow . ',' . $page->listRows)->order('type,sort')->select());
            $this->assign('page', $page->show('Admin'));
            $this->display();
        }

        /**
         * 物品类型添加
         */
        function res_type_add()
        {
            if (IS_POST) {
                if ($this->resTypeModel->create()) {
                    $ret = $this->resTypeModel->add();
                    if ($ret) {
                        $this->success("成功", U("res_type_list"));
                    } else {
                        $this->error("失败", $this->resTypeModel->getError());
                    }
                } else {
                    $this->error($this->resTypeModel->getError());
                }
            } else {
                $province = M('res_type');
                $this->assign('datas', $province->find());

                $this->display();
            }
        }

        /**
         * 物品类型编辑
         */
        function res_type_edit()
        {
            if (IS_POST) {
                // 根据表单提交的POST数据创建数据对象
                if ($this->resTypeModel->create()) {
                    $ret = $this->resTypeModel->save();
                    if ($ret) {
                        $this->success("修改成功！", U("res_type_list"));
                    } else {
                        $this->error("修改失败！");
                    }
                } else {
                    $this->error($this->resTypeModel->getError());
                }
            } else {
                $ret = $this->resTypeModel->where(array('res_type_id' => I('res_type_id')))->find();
                $this->assign("data", $ret);
                $this->display();
            }
        }

        /**
         * 物品类型删除
         */
        function res_type_del()
        {
            $version_db = M("res_type");
            $ret = $version_db->delete(I('res_type_id'));
            if ($ret) {
                $this->success("删除成功！", U("res_type_list"));
            } else {
                $this->error("删除失败");
            }
        }

        //排序
        public function res_type_sorts()
        {

            $res_type_db = M('res_type');
            $ids = $_POST['listorders'];

            foreach ($ids as $key => $r) {
                $data['sort'] = $r;
                $res_type_db->where(array('res_type_id' => $key))->setField($data);
            }

            $this->success("排序更新成功！");

        }

        /**
         * 订单取消类型列表=================================
         */
        function cancle_reason_list()
        {
            $count = $this->cancleReasonModel->count();
            $page = $this->page($count, 20);
            $this->assign('datas', $this->cancleReasonModel->limit($page->firstRow . ',' . $page->listRows)->select());
            $this->assign('page', $page->show('Admin'));
            $this->display();;
        }

        /**
         * 订单取消类型添加
         */
        function cancle_reason_add()
        {
            if (IS_POST) {
                $_POST['surplus_num'] = I('num');
                if ($this->cancleReasonModel->create()) {
                    $ret = $this->cancleReasonModel->add();
                    if ($ret) {
                        $this->success("成功", U("cancle_reason_list"));
                    } else {
                        $this->error("失败", $this->cancleReasonModel->getError());
                    }
                } else {
                    $this->error($this->cancleReasonModel->getError());
                }
            } else {
                $province = M('cancle_reason');
                $this->assign('data', $province->find());
                $this->display();
            }
        }

        /**
         * 物品类型编辑
         */
        function cancle_reason_edit()
        {
            if (IS_POST) {
                // 根据表单提交的POST数据创建数据对象
                if ($this->cancleReasonModel->create()) {
                    $ret = $this->cancleReasonModel->save();
                    if ($ret) {
                        $this->success("修改成功！", U("cancle_reason_list"));
                    } else {
                        $this->error("修改失败！");
                    }
                } else {
                    $this->error($this->cancleReasonModel->getError());
                }
            } else {
                $ret = $this->cancleReasonModel->where(array('cancle_reason_id' => I('cancle_reason_id')))->find();
                $this->assign("data", $ret);
                $this->display();
            }
        }

        /**
         * 物品类型删除
         */
        function cancle_reason_del()
        {
            $version_db = M("cancle_reason");
            $ret = $version_db->delete(I('cancle_reason_id'));
            if ($ret) {
                $this->success("删除成功！", U("cancle_reason_list"));
            } else {
                $this->error("删除失败");
            }
        }


        /**
         *  推荐人
         */
        function recommend_list()
        {

            $water_db = M('recommend r');

            $formget = I('formget');
            $mobile = $formget["mobile"];//推荐人手机
            $recom_type = $formget['recom_type'];//推荐人类型
            $recommended_mobile = $formget["recommended_mobile"];//被推荐人手机
            $recommended_type = $formget["recommended_type"];//被推荐人手机
            $recommended_status = $formget['recommended_status'];//激活状态
            $start_time = $formget["start_time"];//开始时间
            $end_time = $formget["end_time"];//结束时间
            $is_sign_recommended = $formget["is_sign_recommended"];//被推荐跑客是否签约


            $formget = array('mobile' => $mobile,
                'recommended_mobile' => $recommended_mobile
            , 'recom_type' => $recom_type
            , 'recommended_type' => $recommended_type
            , 'recommended_status' => $recommended_status
            , 'start_time' => $start_time
            , 'end_time' => $end_time
            , 'is_sign_recommended' => $is_sign_recommended);
            
            $where = " 1=1 ";


            //推荐人手机号查询
            if (!empty($mobile)) {
                if ($recom_type == '1')
                    $where = $where . " and a1.mobile like '%" . $mobile . "%'";
                else if ($recom_type == '2')
                    $where = $where . " and d1.mobile like '%" . $mobile . "%'";
                else
                    $this->error("请选择被推荐人的类别", U("recommend_list"));

                $where = $where . " and r.recom_person_type =" . $recom_type;
            } else {
                if ($recom_type) {
                    $where = $where . " and r.recom_person_type =" . $recom_type;
                }
            }

            if (!empty($recommended_mobile)) {
                if ($recommended_type == '1')
                    $where = $where . " and a2.mobile like '%" . $recommended_mobile . "%'";
                else if ($recommended_type == '2')
                    $where = $where . " and d2.mobile like '%" . $recommended_mobile . "%'";
                else
                    $this->error("请选择被推荐人的类别", U("recommend_list"));

                $where = $where . " and r.recommended_person_type =" . $recommended_type;
            } else {
                if ($recommended_type)
                    $where = $where . " and r.recommended_person_type =" . $recommended_type;
            }

            if ($recommended_status != "" && $recommended_status != -1) {
                $where = $where . " and r.recommended_status =" . $recommended_status;
            }
            if ($start_time)
                $where = $where . " and r.recommended_time >= '" . $start_time . "  00:00:00'";
            if ($end_time)
                $where = $where . " and r.recommended_time <= '" . $end_time . " 23:59:59' ";

            if($recommended_type == 2 ){
                if($is_sign_recommended==2)
                    $where = $where . " and d2.is_sign=2 ";
                elseif($is_sign_recommended==1)
                    $where = $where . " and d2.is_sign != 2 ";
            }
            $count = $water_db->where($where)->join('left  join db_app_users a1 on r.recom_person_id = a1.user_id and r.recom_person_type=1')
                ->join('left join db_dispatch d1 on r.recom_person_id = d1.dispatch_id and r.recom_person_type =2')
                ->join('left join db_app_users a2 on a2.user_id = r.recommended_person_id and r.recommended_person_type = 1')
                ->join('left join db_dispatch d2 on d2.dispatch_id = r.recommended_person_id and r.recommended_person_type=2')
                ->count();//left join 不影响记录数，但包含查询条件
            import("Org.Util.Pagemumayi");
            $page = new \Pagemumayi($count, 20);
            //分页跳转的时候保证查询条件
            foreach($formget as $key=>$val) {
                $page->parameter .= 'formget['."$key"."]=".urlencode($val)."&";
            }

            $data = $water_db->field('case when r.recom_person_type = 1 then a1.mobile' .
                ' when r.recom_person_type = 2 then d1.mobile end  as mobile' .
                ',case when r.recommended_person_type = 1 then a2.mobile' .
                ' when r.recommended_person_type = 2 then d2.mobile end  as recommend_mobile,r.*')
                ->join('left  join db_app_users a1 on r.recom_person_id = a1.user_id and r.recom_person_type=1')
                ->join('left join db_dispatch d1 on r.recom_person_id = d1.dispatch_id and r.recom_person_type =2')
                ->join('left join db_app_users a2 on a2.user_id = r.recommended_person_id and r.recommended_person_type = 1')
                ->join('left join db_dispatch d2 on d2.dispatch_id = r.recommended_person_id and r.recommended_person_type=2')
                ->where($where)
                ->order('mobile,r.recommended_time')
                ->limit($page->firstRow . ',' . $page->listRows)
                ->select();
         //  dump($data);
            foreach($data as $k => $v)
            {
              $data2 = serach_tel_location($v["recommend_mobile"]); 
              //dump($data2);
              $data[$k]["recommend_mobile_place"] = $data2 ["result"]["province"].$data2 ["result"]["city"].$data2 ["result"]["company"];
            }
           // dump($data);
            //die;
            $this->assign('datas', $data);
            $this->assign('total_count', $count);
            $this->assign('page', $page->show('Admin'));
//            dump($formget);
            $this->assign("formget", $formget);
            $this->display();
        }

    /**
     * 推荐人树
     */
    function recommend_tree_list(){

        $recom_person_id = I('recom_person_id');//推荐人id（父节点id）
        $recom_person_type = I('recom_person_type');//推荐人类别（父节点类别，1用户，2跑客）

        $tree_list = recom_tree($recom_person_id,$recom_person_type);
        $this->assign("formget", I('formget'));

        $this->assign('trees',json_encode($tree_list));
        $this->display();
    }
        /**
         * 推荐人删除
         */
        function recommend_del()
        {
            $version_db = M("recommend");
            $ret = $version_db->delete(I('recommend_id'));
            if ($ret) {
                $this->success("删除成功！", U("recommend_list"));
            } else {
                $this->error("删除失败");
            }
        }

        /**
         * 在线反馈
         */
        function feedback_list()
        {
            $water_db = M('online_feedback f');

            $formget = I("formget");//获取参数数组formget的值
            $mobile = $formget["mobile"];//反馈人手机
            $feedback_type = $formget["feedback_type"];//反馈类型
            $user_type = $formget["user_type"];//反馈人类型
            $start_time = $formget["start_time"];//反馈开始时间
            $end_time = $formget["end_time"];//反馈接受时间

            $where = " 1=1 ";

            if (!empty($mobile))
                $where = $where . " and au.mobile like '%" . $mobile . "%'";
            if (!empty($pay_method) && $pay_method != -1)
                $where = $where . ' and feedback_type = ' . $feedback_type;
            if (!empty($user_type) && $user_type != -1)
                $where = $where . ' and user_type = ' . $user_type;

            if (!empty($start_time))
                $where = $where . " and feedback_time >= '" . $start_time . " 00:00:00'";
            if (!empty($end_time))
                $where = $where . " and feedback_time <= '" . $end_time . " 23:59:59'";

            $count = $water_db->where($where)->join('db_app_users au ON f.user_id=au.user_id')
                ->count();
            import("Org.Util.Pagemumayi");
            $page = new \Pagemumayi($count, 20);
            //分页跳转的时候保证查询条件
            foreach ($formget as $key => $val) {
                $page->parameter .= 'formget[' . "$key" . "]=" . urlencode($val) . "&";
            }

            $data = $water_db->field('au.mobile,f.*')
                ->join('db_app_users au ON f.user_id=au.user_id')
                ->where($where)
                ->order(' user_type,feedback_type,au.mobile')
                ->limit($page->firstRow . ',' . $page->listRows)
                ->select();

            $this->assign('datas', $data);
            $this->assign('page', $page->show('Admin'));
            $this->assign("formget", $formget);//参数formget为list页面的传递查询条件值
            $this->display();
        }

        /**
         * 在线反馈 回复
         */
        function feedback_rdit()
        {
            if (IS_POST) {
                $_POST['feedback_status'] = 1;
                if ($this->onlineFeedbackModel->create()) {
                    $ret = $this->onlineFeedbackModel->save();
                    if ($ret) {
                        $this->success("成功", U("feedback_list", array("formget" => I('formget'))));//参数formget为“回复”页面的提交操作传递查询条件值到list界面
                    } else {
                        $this->error("失败", $this->onlineFeedbackModel->getError());
                    }
                } else {
                    $this->error($this->onlineFeedbackModel->getError());
                }
            } else {
                $feed_db = M('online_feedback f');
                $feed_id = I('get.feedback_id');
                $this->assign('data', $feed_db->join('db_app_users au ON f.user_id=au.user_id')->where(' feedback_id = ' . $feed_id)->find());
                $this->assign("formget", I('formget'));//参数formget为list页面的“回复”链接传递查询条件值到rdit界面
                $this->display();
            }
        }

        /**
         * 在线反馈删除
         */
        function feedback_del()
        {
            $version_db = M("online_feedback");
            $ret = $version_db->delete(I('feedback_id'));
            if ($ret) {
                $this->success("删除成功！", U("feedback_list", array("formget" => I('formget'))));//参数formget为list页面的“删除”操作记忆查询条件
            } else {
                $this->error("删除失败");
            }
        }

        /**
         * 在线反馈 查看
         */
        function feedback_see()
        {
            $feed_db = M('online_feedback f');
            $feed_id = I('get.feedback_id');
            $this->assign('data', $feed_db->join('db_app_users au ON f.user_id=au.user_id')->where(' feedback_id = ' . $feed_id)->find());
            $this->assign("formget", I('formget'));//参数formget为list页面的“查看”链接传递查询条件到see页面

            $this->display();
        }

        /**
         * 优惠信息
         */
        function discount_list()
        {
            $water_db = M('user_discount d');

            $formget = I("formget");//获取参数数组formget的值

            $title = $formget["title"];
            $start_time = $formget["start_time"];//开始时间
            $end_time = $formget["end_time"];//接受时间

            $formget = array('title' => $title,
                'start_time' => $start_time,
                'end_time' => $end_time);

            $where = " 1=1 ";

            if (!empty($title))
                $where = $where . " and title like '%" . $title . "%'";

            if (!empty($start_time))
                $where = $where . " and addtime >= '" . $start_time . " 00:00:00'";
            if (!empty($end_time))
                $where = $where . " and addtime <= '" . $end_time . " 23:59:59'";

            $count = $water_db->where($where)->count();
            import("Org.Util.Pagemumayi");
            $page = new \Pagemumayi($count, 20);
            //分页跳转的时候保证查询条件
            foreach ($formget as $key => $val) {
                $page->parameter .= 'formget[' . "$key" . "]=" . urlencode($val) . "&";
            }

            $data = $water_db->where($where)
                ->order(' cityid,addtime desc')
                ->limit($page->firstRow . ',' . $page->listRows)
                ->select();

            $this->assign('datas', $data);
            $this->assign('page', $page->show('Admin'));
            $this->assign("formget", $formget);
            $this->display();
        }

        /**
         * 优惠信息删除
         */
        function discount_del()
        {
            $version_db = M("user_discount");
            $ret = $version_db->delete(I('id'));
            if ($ret) {
                $this->success("删除成功！", U("discount_list", array("formget" => I('formget'))));//参数formget为list页面的“删除”操作记忆查询条件
            } else {
                $this->error("删除失败");
            }
        }

        /**
         * 优惠信息 查看
         */
        function discount_see()
        {
            $feed_db = M('user_discount d');
            $feed_id = I('get.id');
            $this->assign('data', $feed_db->where(' id = ' . $feed_id)->find());
            $this->assign("formget", I('formget'));//参数formget为list页面的“查看”链接传递查询条件到see页面
            $this->display();

        }

        /**
         * 用户优惠信息 新增
         */
        function discount_add()
        {
            if (IS_POST) {
                $_POST['addtime'] = date('Y-m-d H:i:s', time());
                if ($this->userDiscountModel->create()) {
                    if ($this->userDiscountModel->add()) {
                        $this->success("新增成功！", U("discount_list"));
                    } else {
                        $this->error("新增失败！", U("discount_list"));
                    }
                } else {
                    $this->error($this->userDiscountModel->getError());
                }
            } else {
                $province = M('province');
                $this->assign('province_data', $province->select());
                $this->display();
            }
        }

        /**
         * 在线反馈 回复
         */
        function discount_edit()
        {
            if (IS_POST) {

                if ($this->userDiscountModel->create()) {
                    $ret = $this->userDiscountModel->save();
                    if ($ret) {
                        $this->success("成功", U("discount_list", array("formget" => I('formget'))));//参数formget为“回复”页面的提交操作传递查询条件值到list界面
                    } else {
                        $this->error("失败", $this->userDiscountModel->getError());
                    }
                } else {
                    $this->error($this->userDiscountModel->getError());
                }
            } else {
                $dis_data = $this->userDiscountModel->where('id = ' . I('id'))->find();

                if ($dis_data['cityid']) {
                    $dis_data['province_id'] = getProvinceId($dis_data['cityid']);

                    $citys = getCityList($dis_data['province_id']);
                    if ($citys)
                        $this->assign('citys', $citys);
                }

                $province = M('province');
                $this->assign('province_data', $province->select());

                $this->assign("formget", I('formget'));//参数formget为list页面的“修改”链接传递查询条件值到rdit界面
                $this->assign('data', $dis_data);
                $this->display();
            }
        }

        /**
         * 根据省id获得城市集合
         */
        function location_api()
        {
            if (IS_AJAX) {
                $location = M('location');
                $ret = $location->where(array('province_id' => I('province_id')))->order('sort asc')->select();
                if ($ret) {
                    RET(0, "成功", $ret);
                } else {
                    RET_NO_V(1, "失败");
                }
            }
        }

        /**
         * 数据推送，根据推送类型（跑腿、跑客）获取前10条公告集合
         */
        function message_api()
        {
            if (IS_AJAX) {
                $location = M('message');
                $ret = $location->field('message_id,title')
                    ->where(array('message_type' => I('push_type')))
                    ->order('send_time DESC')
                    ->limit('0,10')
                    ->select();
                if ($ret) {
                    RET(0, "成功", $ret);
                } else {
                    RET_NO_V(1, "失败");
                }
            }
        }

        function phone_user()
        {
            $this->display();
        }

        function phone_get()
        {
            $app_users = M('app_users');
            $ret = $app_users->field('mobile')->select();
            $num = count($ret);
            $count = ceil($num / 1500);
            for ($j = 0; $j < $count; $j++) {
                $id = $j * 1500;
                $file = "./Uploads/user_phone/number" . $j . ".txt";
                $myfile = fopen($file, "w");
                if ($myfile) {
                    for ($i = $id; $i < $num; $i++) {
                        fwrite($myfile, $ret[$i]['mobile']);
                        if ($i != $num - 1) {
                            fwrite($myfile, ',');
                        }
                    }
                    $this->success("新增成功！", U("phone_user"));
                    fclose($myfile);
                } else {
                    $this->error("失败", U("phone_user"));
                }

            }

        }

        /**
         * 根据父类别id获得帮助类别集合（除去已经被添加的帮助类别,用于添加页码）
         */
        function help_type_addapi()
        {
            if (IS_AJAX) {
                $location = M('help_type');
                $onlines = M('online_help')->field('group_concat(help_type_id) as help_type_ids')->find();
                $ret = $location->where('parent_id=' . I('parent_id') . ' and help_type_id not in(' . $onlines['help_type_ids'] . ')')->order('help_code asc')->select();
                if ($ret) {
                    RET(0, "成功", $ret);
                } else {
                    RET_NO_V(1, "失败");
                }
            }
        }

        /**
         * 根据父类别id获得帮助类别集合（用于修改页面）
         */
        function help_type_upapi()
        {
            if (IS_AJAX) {
                $location = M('help_type');
                $ret = $location->where('parent_id=' . I('parent_id'))->order('help_code asc')->select();
                if ($ret) {
                    RET(0, "成功", $ret);
                } else {
                    RET_NO_V(1, "失败");
                }
            }
        }

        /**
         * 补偿方案
         */
        function compensate()
        {
            //实例化推荐表
            $recommend = M('recommend');
            //实例化用户优惠券表
            $dispatch_balance = M('dispatch_balance');
            //查询所有数组（已激活，并且推荐人是用户）
            $ret = $recommend->where(array('recommended_status' => 1, 'recom_person_type' => 2))->select();
            //筛选数据
            foreach ($ret as $v => $k) {

                $d = $dispatch_balance->where(array('order_id' => $k['recommend_id']))->find();
                //如果匹配到则移出此数据
                if ($d) {
                    unset($ret[$v]);
                }
            }
            $this->assign('compensate_data', $ret);
            $this->display();
        }

        /**
         * 补偿方案处理
         */
        function compensate_do()
        {
            //实例化推荐表
            $recommend = M('recommend');
            $dispatch = M('dispatch');
            $recommend->startTrans();
            $ret = $recommend->where(array('recommend_id' => I('recommend_id')))->find();
            $status = dispatchPurseLog($ret['recom_person_id'], 5, 1, "推荐补偿收入", "推荐补偿收入", I('recommend_id'));
            dump($status);
            if ($status) {

                $recommend_amount = $dispatch->where(array('dispatch_id' => $ret['recom_person_id']))->field('recommend_amount,cash_balance')->find();
                $dispatch->where(array('dispatch_id' => $ret['recom_person_id']))->save(array('recommend_amount' => format_money($recommend_amount['recommend_amount']) + 1, 'cash_balance' => format_money($recommend_amount['cash_balance']) + 1));
                $msg_ret = create_dispatch_message($ret['recom_person_id'], '推荐补偿收入', '新推出活动，补偿之前推荐奖励1元成功', '新推出活动，补偿之前推荐奖励1元成功');

                if ($msg_ret) {
                    $recommend->commit();
                    $this->success("恭喜，发放补偿成功！");
                } else {
                    $recommend->rollback();
                    $this->error("抱歉，发送数据失败！");
                }
            } else {
                $recommend->rollback();
                $this->error("抱歉，补偿失败！");
            }

        }
    }