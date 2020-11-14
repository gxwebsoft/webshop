<?php

namespace app\common\model\article;

use think\Cache;
use app\common\model\BaseModel;

/**
 * 文章分类模型
 * Class Category
 * @package app\common\model
 */
class Category extends BaseModel
{
    protected $name = 'article_category';

    /**
     * 分类图片
     * @return \think\model\relation\HasOne
     */
    public function image()
    {
        return $this->hasOne('uploadFile', 'file_id', 'image_id');
    }

    /**
     * 所有分类
     * @return mixed
     */
    public static function getALL()
    {
        $model = new static;
        if (!Cache::get('article_category_' . $model::$wxapp_id)) {
            $data = $model->order(['sort' => 'asc', 'create_time' => 'asc'])->select();
            $all = !empty($data) ? $data->toArray() : [];
            Cache::tag('cache')->set('article_category_' . $model::$wxapp_id, $all);
        }
        return Cache::get('article_category_' . $model::$wxapp_id);
    }

    public function catTree(){
        $model = new static;
        $cats = $model->order('sort asc, category_id asc')->select()->toArray();
        $list= $model->tree($cats);
        foreach ($list as $k=>$v)
        {
            if($v['level']==2) $list[$k]['name']='&nbsp;&nbsp;&nbsp;├ &nbsp'.$v['name'] ;
            if($v['level']==3) $list[$k]['name']='&nbsp;&nbsp;&nbsp;├ &nbsp;&nbsp;&nbsp;&nbsp;├ &nbsp'.$v['name'] ;
            if($v['level']==4) $list[$k]['name']='&nbsp;&nbsp;&nbsp;├&nbsp;&nbsp;&nbsp;&nbsp;├ &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;├ &nbsp'.$v['name'] ;
        }
        return $list;
    }

    //定义一个方法，对给定的数组，递归形成树
    public function tree($arr,$pid=0,$level=1){
        static $tree = array();
        foreach($arr as $k=>$v){
            if($v['parent_id']==$pid){
                $v['level']=$level;
                $tree[]=$v;
                unset($arr[$k]);//已经排好等级的,从数组中移除，提高性能
                $this->tree($arr,$v['category_id'],$level+1);
            }
        }
        return $tree;

    }


}
