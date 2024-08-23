<?php
namespace app\user\controller;
use think\Db;
use think\facade\Session;
use think\Controller;

class Register extends Controller
{
    public function index()
    {
        $this->assign("SETTING_BGURL",Db::table("icp_settings")->where("key_name","bg_url")->value("key_value"));
        $this->assign("SETTING_SITENAME",Db::table("icp_settings")->where("key_name","site_name")->value("key_value"));
        $this->assign("PAGE_TITLE","注册");
        return $this->fetch("public@user/register_0");
    }

    public function number()
    {
        $this->assign("SETTING_BGURL",Db::table("icp_settings")->where("key_name","bg_url")->value("key_value"));
        $this->assign("SETTING_SITENAME",Db::table("icp_settings")->where("key_name","site_name")->value("key_value"));
        $this->assign("PAGE_TITLE","选号");
        return $this->fetch("public@user/register_1");
    }

    public function number_can($number)
    {
        $db = Db::table("icp_list")->where("icp_number",$number)->select();
        if(count($db) > 0) return "1";
        else return "0";
    }

    public function info($n){
        $this->assign("SETTING_BGURL",Db::table("icp_settings")->where("key_name","bg_url")->value("key_value"));
        $this->assign("SETTING_SITENAME",Db::table("icp_settings")->where("key_name","site_name")->value("key_value"));
        $this->assign("PAGE_TITLE","填写基本信息");
        $this->assign("number",$n);
        return $this->fetch("public@user/register_2");
    }

    public function info_check(){
        $username = input('post.username');
        $displayname = input('post.displayname');
        $password = input('post.password');
        $email = input('post.email');
        $number = input('post.number');
        $captcha = input('post.captcha');

        // 验证码校验
        if(empty($captcha)){
            return "请输入验证码";
        }
        $sessionCaptcha = Session::get('captcha');
        if(empty($sessionCaptcha) || $captcha != $sessionCaptcha){
            return "验证码错误或已过期";
        }
        Session::delete('captcha'); // 使用后立即删除验证码

        if(mb_strlen($username) < 4) return "用户名至少要4个字";
        if(mb_strlen($username) > 12) return "用户名过长";
        if(mb_strlen($displayname) < 1) return "显示名称至少要1个字";
        if(mb_strlen($displayname) > 12) return "显示名称过长";
        if(mb_strlen($password) < 6) return "密码至少要6个字";
        if(mb_strlen($password) > 18) return "密码过长";
        if(mb_strlen($email) < 1) return "邮箱至少要写字";
        if(mb_strlen($email) > 25) return "邮箱过长";
        if($this->number_can($number) == "1") return "备案号已存在";
        if(count(Db::table("icp_users")->where("username",$username)->select()) > 0) return "用户名已存在";
        if(count(Db::table("icp_users")->where("email",$email)->select()) > 0) return "邮箱已存在";

        $newi = Db::table("icp_users")->insertGetId(["username"=>$username,"displayname"=>$displayname,"u_description"=>"","password"=>md5($password),"email"=>$email,"qq"=>"","u_type"=>0,"join_time"=>time(),"u_number"=>""]);
        Session::set("login",$newi);
        return "ok;".$newi;
    }

    public function generateCaptcha()
    {
        $captcha = mt_rand(1000, 9999); // 生成4位随机数
        Session::set('captcha', $captcha); // 将验证码存储在会话中
        
        // 创建图片
        $image = imagecreatetruecolor(100, 30);
        $bgcolor = imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $bgcolor);
        
        // 添加干扰
        for($i = 0; $i < 200; $i++) {
            $pointcolor = imagecolorallocate($image, mt_rand(50, 200), mt_rand(50, 200), mt_rand(50, 200));
            imagesetpixel($image, mt_rand(1, 99), mt_rand(1, 29), $pointcolor);
        }
        
        // 添加文字
        $textcolor = imagecolorallocate($image, 0, 0, 0);
        imagestring($image, 5, 30, 7, $captcha, $textcolor);
        
        // 输出图片
        header('Content-type: image/png');
        imagepng($image);
        imagedestroy($image);
        exit;
    }

    public function end($n,$i){
        $this->assign("SETTING_BGURL",Db::table("icp_settings")->where("key_name","bg_url")->value("key_value"));
        $this->assign("SETTING_SITENAME",Db::table("icp_settings")->where("key_name","site_name")->value("key_value"));
        $this->assign("PAGE_TITLE","填写备案信息");
        $this->assign("number",$n);
        $this->assign("userid",mb_substr($i,3,mb_strlen($i)));
        return $this->fetch("public@user/register_3");
    }

    public function end_check(){
        $sitename = input('post.sitename');
        $sitedesc = input('post.sitedesc');
        $icpowner = input('post.icpowner');
        $icpurl = input('post.icpurl');
        $mainurl = input('post.mainurl');
        $number = input('post.number');
        $userid = input('post.userid');
        $captcha = input('post.captcha');

        // 验证码校验
        if(empty($captcha)){
            return "请输入验证码";
        }
        $sessionCaptcha = Session::get('captcha');
        if(empty($sessionCaptcha) || $captcha != $sessionCaptcha){
            return "验证码错误或已过期";
        }
        Session::delete('captcha'); // 使用后立即删除验证码

        if(mb_strlen($sitename) > 12) return "站点名称过长";
        if(mb_strlen($sitedesc) > 20) return "站点描述过长";
        if(mb_strlen($mainurl) > 30) return "首页链接过长";
        if(mb_strlen($icpurl) > 30) return "备案链接过长";
        if(mb_strlen($icpowner) > 18) return "所有人过长";
        if($this->number_can($number) == "1") return "备案号已存在";
        if(count(Db::table("icp_users")->where("id",$userid)->select()) < 0) return "用户ID不存在";

        Db::table("icp_list")->insert(["site_name"=>$sitename,"site_description"=>$sitedesc,"site_main_url"=>$mainurl,"site_icp_url"=>$icpurl,"site_owner"=>$icpowner,"icp_time"=>time(),"icp_status"=>Db::table("icp_settings")->where("key_name","default_icp_status")->value("key_value"),"icp_number"=>$number,"by_user"=>$userid]);
        return "ok";
    }
}