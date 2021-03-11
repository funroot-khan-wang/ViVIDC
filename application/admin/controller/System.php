<?php
/**
 * 系统管理控制器
 * by:小航 QQ:11467102
 */
namespace app\admin\controller;
use app\admin\model\Admin;
use app\admin\validate\Admin as AdminValidate;
use think\facade\Request;
use think\Db;
use app\admin\model\System as SystemModel;
use app\admin\validate\System as SystemValidate;
use think\facade\Session;

class System extends Base
{
    /**
     * 系统信息
     */
    public function system()
    {
        //当前系统信息
        $system = Db::name("system")->where('id',1)->find();
        if (request()->isAjax()){
            //接收所有提交数值
            $data = Request::param();
            //实例化
            $validate = new SystemValidate;
            //验证数据
            if (!$validate->sceneSystem()->check($data)){
                $this->error($validate->getError());
            }
            //执行更新操作
            $res = Db::name('system')->where('id',1)->update($data);
            if ($res){
                $this->success("更新成功！");
            }else{
                $this->error("更新失败！");
            }
        }
        $this->assign(['system'=>$system]);
        return $this->fetch('system');
    }

    /**
     * 安全配置
     */
    public function security()
    {
        //当前的信息
        $info = Db::name('system')->where('id',1)->field('max_logerror,ip,access')->find();
        if (request()->isAjax()){
            //接收数值
            $data = Request::param();
            //实例化
            $validate = new SystemValidate;
            //验证数据
            if (!$validate->sceneSecurity()->check($data)){
                $this->error($validate->getError());
            }
            //判断是否修改了后台入口地址
            if($data['access'] !== $info['access']){
                //执行修改并更新数据表入口地址字段信息
                rename($info['access'],$data['access']);
            }
            //实例化
            $system = new SystemModel();
            if ($data['ip'] == NULL) {
                //仅允许access,max_logerror字段写入
                $res = $system->allowField(['access','max_logerror'])->save($data,['id'=>1]);
            }else{
                //更新数据并过滤数据表中不存在的字段
                $res = $system->allowField(true)->save($data,['id'=>1]);
            }
            if ($res){
                $this->success("更新成功！");
            }else{
                $this->error("更新失败！");
            }
        }
        $this->assign(['system'=>$info]);
        return $this->fetch('security');
    }

    /**
     * 屏蔽词
     */
    public function block()
    {
        $info = Db::name('system')->where('id',1)->field('block')->find();
        if (request()->isAjax()){
            //接收前台传过来的数值
            $data = Request::param();
            $res = Db::name('system')->where('id',1)->update($data);
            if ($res){
                $this->success("更新成功！");
            }else{
                $this->error("更新失败！");
            }
        }
        $this->assign(['system'=>$info]);
        return $this->fetch('block');
    }

    /**
     * 修改密码
     */
    public function passEdit()
    {
        //查询当前管理员用户名
        $info = Db::name('admin')->where('id',Session::get('Admin.id'))->field('id,user,password')->find();
        //判断是否为ajax请求
        if (request()->isAjax()){
            //接收数据
            $data = Request::param();
            //判断原始密码是否正确
            if (password_verify($data['mpassword'],$info['password'])){
                //对数据进行验证
                $validate = new AdminValidate();
                if (!$validate->scenepassEdit()->check($data)){
                    $this->error($validate->getError());
                }
                //对密码进行加密
                $data['password'] = password_hash($data['password'],PASSWORD_BCRYPT);
                //实例化对象
                $admin = new Admin();
                //执行更新并过滤非数据表字段
                $res = $admin->allowField(true)->save($data,['id'=>$data['id']]);
                if ($res){
                    $this->success("修改成功！");
                }else{
                    $this->error("修改失败！");
                }
            }else{
                $this->error("原始密码错误！");
            }
        }
        //给模板赋值
        $this->assign(['pass'=>$info]);
        return $this->fetch('pass');
    }

    /**
     * 系统升级
     */
    public function update()
    {
        //查询系统版本号
        $data = Db::name('system')->where('id',1)->field('version')->find();
        //给模板赋值
        $this->assign(['system'=>$data]);
        return $this->fetch('update');
    }

    /**
     * 开关管理
     */
    public function switch()
    {
        return $this->fetch('switch');
    }
}