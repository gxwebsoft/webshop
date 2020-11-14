<?php

namespace app\common\model;

use think\Cache;

/**
 * 拼团商品分类模型
 * Class Category
 * @package app\common\model
 */
class Category extends BaseModel
{
    protected $name = 'category';

    /**
     * 分类图片
     * @return \think\model\relation\HasOne
     */
    public function image()
    {
        return $this->hasOne('uploadFile', 'file_id', 'image_id');
    }


    public function content()
    {
        return $this->hasMany('Goods')->where('is_delete','=',0)->order(['goods_sort'=>'asc','create_time'=>'desc']);
    }


    /**
     * 所有分类
     * @return mixed
     */
    public static function getALL()
    {
        $model = new static;
        if (!Cache::get('category_' . $model::$wxapp_id)) {
            $data = $model->with(['image'])->order(['sort' => 'asc', 'create_time' => 'asc'])->select();
            $all = !empty($data) ? $data->toArray() : [];
            $tree = [];
            foreach ($all as $first) {
                if ($first['parent_id'] != 0) continue;
                $twoTree = [];
                foreach ($all as $two) {
                    if ($two['parent_id'] != $first['category_id']) continue;
                    $threeTree = [];
                    foreach ($all as $three)
                        $three['parent_id'] == $two['category_id']
                        && $threeTree[$three['category_id']] = $three;
                    !empty($threeTree) && $two['child'] = $threeTree;
                    $twoTree[$two['category_id']] = $two;
                }
                if (!empty($twoTree)) {
                    array_multisort(array_column($twoTree, 'sort'), SORT_ASC, $twoTree);
                    $first['child'] = $twoTree;
                }
                $tree[$first['category_id']] = $first;
            }
            Cache::tag('cache')->set('category_' . $model::$wxapp_id, compact('all', 'tree'));
        }
        return Cache::get('category_' . $model::$wxapp_id);
    }

    public function catTree(){
        $cats = $this->order('sort asc')->select()->toArray();
        $list= $this->tree($cats);
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


    /**
     * 所有分类
     * @return mixed
     */
    public static function getSectionALL()
    {
        $model = new static;
        if (!Cache::get('section_' . $model::$wxapp_id)) {
            $data = $model->with(['image'])->order(['sort' => 'asc', 'create_time' => 'asc'])->select();
            $all = !empty($data) ? $data->toArray() : [];
            $tree = [];
            foreach ($all as $first) {
                if ($first['parent_id'] != 0) continue;
                $twoTree = [];
                foreach ($all as $two) {
                    if ($two['parent_id'] != $first['category_id']) continue;
                    $threeTree = [];
                    foreach ($all as $three)
                        $three['parent_id'] == $two['category_id']
                        && $threeTree[$three['category_id']] = $three;
                    !empty($threeTree) && $two['child'] = $threeTree;
                    $twoTree[$two['category_id']] = $two;
                }
                if (!empty($twoTree)) {
                    array_multisort(array_column($twoTree, 'sort'), SORT_ASC, $twoTree);
                    $first['child'] = $twoTree;
                }
                $tree[$first['category_id']] = $first;
            }
            Cache::tag('cache')->set('category_' . $model::$wxapp_id, compact('all', 'tree'));
        }
        return Cache::get('category_' . $model::$wxapp_id);
    }




    /**
     * 获取所有分类
     * @return mixed
     */
    public static function getCacheAll()
    {
        return self::getALL()['all'];
    }

    /**
     * 获取所有分类(树状结构)
     * @return mixed
     */
    public static function getCacheTree()
    {
        return self::getALL()['tree'];
    }
    /**
     * 获取所有分类(树状结构)
     * @return mixed
     */
    public static function getSectionCacheTree()
    {
        return self::getALL()['tree'];
    }
    /**
     * 获取所有分类(树状结构)
     * @return string
     */
    public static function getSectionCacheTreeJson()
    {
        return json_encode(static::getSectionCacheTree());
    }

    /**
     * 获取所有分类(树状结构)
     * @return string
     */
    public static function getCacheTreeJson()
    {
        return json_encode(static::getCacheTree());
    }

    /**
     * 获取指定分类下的所有子分类id
     * @param $parent_id
     * @param array $all
     * @return array
     */
    public static function getSubCategoryId($parent_id, $all = [])
    {
        $arrIds = [$parent_id];
        empty($all) && $all = self::getCacheAll();
        foreach ($all as $key => $item) {
            if ($item['parent_id'] == $parent_id) {
                unset($all[$key]);
                $subIds = self::getSubCategoryId($item['category_id'], $all);
                !empty($subIds) && $arrIds = array_merge($arrIds, $subIds);
            }
        }
        return $arrIds;
    }

    /**
     * 指定的分类下是否存在子分类
     * @param $parentId
     * @return bool
     */
    protected static function hasSubCategory($parentId)
    {
        $all = self::getCacheAll();

        foreach ($all as $item) {
            if ($item['parent_id'] == $parentId) {
                return true;
            }
        }
        return false;
    }

}
