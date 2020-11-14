<?php

namespace app\api\controller;

use app\api\model\Category as CategoryModel;
use app\api\model\WxappCategory as WxappCategoryModel;
use app\api\model\Goods as GoodsModel;
use app\api\controller\Cart as CartModel;
use think\Cache;
use think\Db;

/**
 * 商品分类控制器
 * Class Goods
 * @package app\api\controller
 */
class Category extends Controller
{
    /**
     * 分类页面
     * @return array
     * @throws \think\exception\DbException
     */
    public function index()
    {

        // 分类模板
        $templet = WxappCategoryModel::detail();
        // 商品分类列表getCacheTree
        $list = array_values(CategoryModel::getCacheTree());
        // 商品列表
        $goodsmodel = new GoodsModel;
        foreach ($list as $key => $item){
            $param = array_merge($this->request->param(), [
                'status' => 10,
                'category_id' => $list[$key]['category_id']
            ]);
            $list[$key]['goodslist'] = $goodsmodel->getList($param,$this->getUser(false));
        }
        return $this->renderSuccess(compact('templet', 'list'));
    }

/*三级分类*/
    public function three()
    {
        $templet = WxappCategoryModel::detail();
      $cate_id=  input('category_id');
      if($cate_id!='undefined') {
          $list = Db::name('category')->where('parent_id', input('category_id'))->order('sort asc')->select()->toArray();
          foreach ($list as $k => $v) {
              $list[$k]['image'] = DB::name('upload_file')->where('file_id', $v['image_id'])->find();
              $list[$k]['child'] = $this->four($v['category_id']);
          }
//        halt($list);
          return $this->renderSuccess(compact('templet', 'list'));
      }
      else{
          return $this->renderError('系统发生错误请联系管理员');
      }
    }

    /*四级分类*/
    public  function four($category_id)
    {
        $list=Db::name('category')->where('parent_id',$category_id)->order('sort asc')->select()->toArray();
        if(!empty($list)) {
            foreach ($list as $k => $v) {
             if(isset($v['image_id']))   $list[$k]['image'] = DB::name('upload_file')->where('file_id', $v['image_id'])->find();
            }

        }
        return $list;
    }
}
