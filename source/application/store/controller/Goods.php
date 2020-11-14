<?php

namespace app\store\controller;
use think\Db;
use app\store\model\Goods as GoodsModel;
use app\store\model\Category as CategoryModel;
use app\store\service\Goods as GoodsService;

/**
 * 商品管理控制器
 * Class Goods
 * @package app\store\controller
 */
class Goods extends Controller
{
    /**
     * 商品列表(出售中)
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index()
    {
        // 获取全部商品列表
        $model = new GoodsModel;
        $list = $model->getList(array_merge(['status' => -1], $this->request->param()));
//        halt($list->toArray());
        // 商品分类
        $model1 = new CategoryModel;
//        $catgory = CategoryModel::getCacheTree();
        $catgory = $model1->catTree();
        return $this->fetch('index', compact('list', 'catgory'));
    }

    /**
     * 添加商品
     * @return array|mixed
     * @throws \think\exception\PDOException
     */
    public function add()
    {
        if (!$this->request->isAjax()) {
            $model = new CategoryModel;
            $list = $model->catTree();
            return $this->fetch(
                'add',
                array_merge(GoodsService::getEditData(null, 'add'), [],compact('list'))
            );
        }
        $model = new GoodsModel;
        if ($model->add($this->postData('goods'))) {
            return $this->renderSuccess('添加成功', url('goods/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 一键复制
     * @param $goods_id
     * @return array|mixed
     * @throws \think\exception\PDOException
     */
    public function copy($goods_id)
    {
        // 商品详情
        $model1 = new CategoryModel;
        $model = GoodsModel::detail($goods_id);
        $list = $model1->catTree();
        if (!$this->request->isAjax()) {
            return $this->fetch(
                'edit',
                array_merge(GoodsService::getEditData($model, 'edit'), [],compact('model','list'))
            );
        }


        $model = new GoodsModel;
        if ($model->add($this->postData('goods'))) {
            return $this->renderSuccess('添加成功', url('goods/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 商品编辑
     * @param $goods_id
     * @return array|bool|mixed
     */
    public function edit($goods_id)
    {
        // 商品详情
        $model = GoodsModel::detail($goods_id);
        $model1 = new CategoryModel;
        $list = $model1->catTree();
        if (!$this->request->isAjax()) {
            return $this->fetch(
                'edit',
                array_merge(GoodsService::getEditData($model), compact('model','list'))
            );
        }
        // 更新记录
        if ($model->edit($this->postData('goods'))) {
            return $this->renderSuccess('更新成功', url('goods/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

    /**
     * 修改商品状态
     * @param $goods_id
     * @param boolean $state
     * @return array
     */
    public function state($goods_id, $state)
    {
        // 商品详情
        $model = GoodsModel::detail($goods_id);
        if (!$model->setStatus($state)) {
            return $this->renderError('操作失败');
        }
        return $this->renderSuccess('操作成功');
    }

    /**
     * 删除商品
     * @param $goods_id
     * @return array
     */
    public function delete($goods_id)
    {
        // 商品详情
        $model = GoodsModel::detail($goods_id);
        if (!$model->setDelete()) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }
/*
 * 导出*/
  public function toexport()
  {
      $model=new GoodsModel();
      $rs=$model->toExport();
//      halt($rs);
  }

    public function copymore()
    {
        $data=input('post.');
//        $new= explode(',',$data['goods_id']);


        $model=new GoodsModel();
       $rs= $model->copy($data);
       return $rs;
    }

    public function import()
    {
        if(!$_FILES){
            return $this->fetch();
        }
        vendor("phpexcel.PHPExcel");
        $objPHPExcel = new \PHPExcel();

        if (! empty ( $_FILES ['excel'] ['name'] ))
        {
            $tmp_file = $_FILES ['excel'] ['tmp_name'];
            $file_types = explode ( ".", $_FILES ['excel'] ['name'] );
            $file_type = $file_types [count ( $file_types ) - 1];
            /*判别是不是.xls文件，判别是不是excel文件*/
            if (strtolower ( $file_type ) != "xls"&&strtolower ( $file_type ) != "xlsx")
            {
                $this->error ( '不是Excel文件，重新上传' );
            }
            /*设置上传路径*/
            $savePath = 'uploads/';
            /*以时间来命名上传的文件*/
            $str = date ( 'Ymdhis' );
            $file_name = 'inser' . "." . $file_type;
            /*是否上传成功*/
            if (! copy ( $tmp_file, $savePath . $file_name ))
            {
                $this->error ( '上传失败','goods/index');
            }
        }else{
            $this->error('上传失败','goods/index');
        }

        $extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));//判断导入表格后缀格式
        if($extension == 'xlsx') {
            return 'xlsx';
            $objReader =\PHPExcel_IOFactory::createReader('Excel2007');
            $objPHPExcel =$objReader->load($savePath.$file_name, $encode = 'utf-8');
        }else if($extension == 'xls'){
//            return 'xls';
            $objReader =\PHPExcel_IOFactory::createReader('Excel5');
            $objPHPExcel =$objReader->load($savePath.$file_name, $encode = 'utf-8');
        }

        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();//取得总行数
        $highestColumn = $sheet->getHighestColumn(); //取得总列数
        $score=array();
//        halt($highestRow);
        for($i = 2; $i <= $highestRow; $i++) {
            $cell = $sheet->getCellByColumnAndRow(0, $i);
            $value = $cell->getValue();
//            $score[$i]['user_name']=$objPHPExcel->getActiveSheet()->getCell("A".$i)->getValue();
            $score[$i]['category_id'] = $objPHPExcel->getActiveSheet()->getCell("B" . $i)->getValue();
            $score[$i]['stock_num'] = $objPHPExcel->getActiveSheet()->getCell("C" . $i)->getValue();
            $score[$i]['line_price'] = $objPHPExcel->getActiveSheet()->getCell("D" . $i)->getValue();
            $score[$i]['goods_price'] = $objPHPExcel->getActiveSheet()->getCell("E" . $i)->getValue();
            $score[$i]['file_name'] = $objPHPExcel->getActiveSheet()->getCell("F" . $i)->getValue();
            $score[$i]['goods_name'] = $objPHPExcel->getActiveSheet()->getCell("G" . $i)->getValue();
            $score[$i]['goods_status'] = $objPHPExcel->getActiveSheet()->getCell("I" . $i)->getValue();
            $score[$i]['goods_no'] = $objPHPExcel->getActiveSheet()->getCell("H" . $i)->getValue();
            $score[$i]['goods_weight'] = $objPHPExcel->getActiveSheet()->getCell("J" . $i)->getValue();
            $score[$i]['content'] = $objPHPExcel->getActiveSheet()->getCell("K" . $i)->getValue();
//            $score[$i]['mobile'] =$objPHPExcel->getActiveSheet()->getCell("I".$i)->getValue();
//            $score[$i]['status'] =$objPHPExcel->getActiveSheet()->getCell("J".$i)->getValue();
//            $score[$i]['report_mark'] =$objPHPExcel->getActiveSheet()->getCell("K".$i)->getValue();
//            halt($score[$i]['goods_weight']);
//            if($score[$i]['goods_weight']==null)
//            {
//                return  $this->error ( '请填写产品重量' );
//            }
            $rs=$this->checkimg( $score[$i]['file_name']);
//            var_dump($score[$i]['file_name']);
            
            if (!empty($rs))
            {
              return  $this->error ( '图片名重复' );
            }
//halt('a');
           $data=time();
            Db::name('upload_file')->insert(['file_name'=> $score[$i]['file_name'],'create_time'=>$data
                ,'wxapp_id'=>10216,'file_type'=>'image','extension'=>'jpg','file_url'=>'http://developer.wsdns.cn','storage'=>'local']);
            Db::name('goods')->insert(['category_id'=> $score[$i]['category_id'],'create_time'=>$data,'goods_name'
            =>$score[$i]['goods_name'],'content'=>$score[$i]['content'],'goods_status'=>$score[$i]['goods_status']
            ,'spec_type'=>10,'wxapp_id'=>10216]);
            Db::name('goods_sku')->insert(['goods_id'=>$this->getgoodsid(),'goods_price'=>$score[$i]['goods_price'],
                'stock_num'=>$score[$i]['stock_num'],'create_time'=>$data,'wxapp_id'=>10216,'goods_no'=>$score[$i]['goods_no']
                ,'goods_weight'=>$score[$i]['goods_weight'],'line_price'=>$score[$i]['line_price']
            ]);
            Db::name('goods_image')->insert(['goods_id'=>$this->getgoodsid(),'image_id'=>$this->getfileid(),'create_time'=>$data,'wxapp_id'=>10216]);
//            $this->saveData($score[$i]);


        }

        return $this->success('导入成功','goods/index');

    }

    public  function  getgoodsid()
    {
      $rs= Db::name('goods')->order('goods_id desc')->limit(1)->select();
      return $rs[0]['goods_id'];
    }
    public  function  getfileid()
    {
        $rs= Db::name('upload_file')->order('file_id desc')->limit(1)->select();
        return $rs[0]['file_id'];
    }
    public function checkimg($file_name)
    {
        $rs = Db::name('upload_file')->where('file_name', $file_name)->where('is_delete', 0)->find();
        return $rs;
    }
}
