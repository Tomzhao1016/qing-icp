<?php
namespace app\index\controller;
use think\Db;
use think\Controller;
class Index extends Controller
{
    public function index()
    {
        $this->assign("SETTING_BGURL", Db::table("icp_settings")->where("key_name", "bg_url")->value("key_value"));
        $this->assign("SETTING_SITENAME", Db::table("icp_settings")->where("key_name", "site_name")->value("key_value"));
        $this->assign("SETTING_SEARCHPLACEHOLDER", Db::table("icp_settings")->where("key_name", "search_placeholder")->value("key_value"));
        $this->assign("PAGE_TITLE", "主页");
        return $this->fetch("public@index/home");
    }

    public function has($keyword)
    {
        $db = Db::table("icp_list")->where("site_icp_url", $keyword)->whereOr("site_name", "LIKE", "%" . $keyword . "%")->whereOr("icp_number", $keyword)->select();
        if (count($db) > 0) return "1";
        else return "0";
    }

    public function where($keyword)
    {
        $this->assign("SETTING_BGURL", Db::table("icp_settings")->where("key_name", "bg_url")->value("key_value"));
        $this->assign("SETTING_SITENAME", Db::table("icp_settings")->where("key_name", "site_name")->value("key_value"));
        $db = Db::table("icp_list")->where("site_icp_url", $keyword)->whereOr("site_name", "LIKE", "%" . $keyword . "%")->whereOr("icp_number", $keyword)->find();
        if (empty($db)) {
            header("Location: /");
            exit("null");
        }
        $this->assign("db", $db);
        $this->assign("PAGE_TITLE", "查询结果");
        return $this->fetch("public@index/where");
    }
}
