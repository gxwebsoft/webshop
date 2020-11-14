<?php

namespace app\store\model;

use app\common\model\Goods as GoodsModel;
use app\store\service\Goods as GoodsService;
use think\Db;
//use PHPExcel_IOFactory;
//use PHPExcel as PHP;
use think\Loader;

/**
 * 商品模型
 * Class Goods
 * @package app\store\model
 */
class Goods extends GoodsModel
{
    /**
     * 添加商品
     * @param array $data
     * @return bool
     * @throws \think\exception\PDOException
     */
    public function add(array $data)
    {
        if (!isset($data['images']) || empty($data['images'])) {
            $this->error = '请上传商品图片';
            return false;
        }
        $data['content'] = isset($data['content']) ? $data['content'] : '';
        $data['wxapp_id'] = $data['sku']['wxapp_id'] = self::$wxapp_id;

        // 开启事务
        $this->startTrans();
        try {
            // 添加商品
            $this->allowField(true)->save($data);
            // 商品规格
            $this->addGoodsSpec($data);
            // 商品图片
            $this->addGoodsImages($data['images']);
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

    /**
     * 添加商品图片
     * @param $images
     * @return int
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    private function addGoodsImages($images)
    {
        $this->image()->delete();
        $data = array_map(function ($image_id) {
            return [
                'image_id' => $image_id,
                'wxapp_id' => self::$wxapp_id
            ];
        }, $images);
        return $this->image()->saveAll($data);
    }

    /**
     * 编辑商品
     * @param $data
     * @return bool|mixed
     */
    public function edit($data)
    {
        if (!isset($data['images']) || empty($data['images'])) {
            $this->error = '请上传商品图片';
            return false;
        }
        $data['spec_type'] = isset($data['spec_type']) ? $data['spec_type'] : $this['spec_type'];
        $data['content'] = isset($data['content']) ? $data['content'] : '';
        $data['wxapp_id'] = $data['sku']['wxapp_id'] = self::$wxapp_id;
        return $this->transaction(function () use ($data) {
            // 保存商品
            $this->allowField(true)->save($data);
            // 商品规格
            $this->addGoodsSpec($data, true);
            // 商品图片
            $this->addGoodsImages($data['images']);
            return true;
        });
    }

    /**
     * 添加商品规格
     * @param $data
     * @param $isUpdate
     * @throws \Exception
     */
    private function addGoodsSpec($data, $isUpdate = false)
    {
        // 更新模式: 先删除所有规格
        $model = new GoodsSku;
        $isUpdate && $model->removeAll($this['goods_id']);
        // 添加规格数据
        if ($data['spec_type'] == '10') {
            // 单规格
            $this->sku()->save($data['sku']);
        } else if ($data['spec_type'] == '20') {
            // 添加商品与规格关系记录
            $model->addGoodsSpecRel($this['goods_id'], $data['spec_many']['spec_attr']);
            // 添加商品sku
            $model->addSkuList($this['goods_id'], $data['spec_many']['spec_list']);
        }
    }

    /**
     * 修改商品状态
     * @param $state
     * @return false|int
     */
    public function setStatus($state)
    {
        return $this->allowField(true)->save(['goods_status' => $state ? 10 : 20]) !== false;
    }

    /**
     * 软删除
     * @return false|int
     */
    public function setDelete()
    {
        if (!GoodsService::checkIsAllowDelete($this['goods_id'])) {
            $this->error = '当前商品正在参与其他活动，不允许删除';
            return false;
        }
        return $this->allowField(true)->save(['is_delete' => 1]);
    }

    /**
     * 获取当前商品总数
     * @param array $where
     * @return int|string
     * @throws \think\Exception
     */
    public function getGoodsTotal($where = [])
    {
        return $this->where('is_delete', '=', 0)->where($where)->count();
    }


    public function copy($data)
    {
//        halt($data);
//        $list=Db::name('goods')->where(['goods_id'=>$data])->select();
//halt($list);
        foreach ($data['goods_id'] as $k => $v)
        {
//            $goods1= GoodsModel::detail($v);


                $goods = Db::name('goods')->where('goods_id', $v)->find();
                unset($goods['goods_id']);
                $goods_id= Db::name('goods')->insertGetId($goods);


                $goods_img = Db::name('goods_image')->field('id', true)->where('goods_id', $v)->find();
                $goods_img['goods_id'] = $goods_id;
                $goods_img['create_time'] = time();
                $goods_img['wxapp_id'] = 10216;
                $res = Db::name('goods_image')->insert($goods_img);


            $goods_sku=Db::name('goods_sku')->field('goods_sku_id',true)->where('goods_id',$v)->find();
//            unset($goods_sku['goods_no']);

            $goods_sku['goods_id']=$goods_id;
            $goods_sku['create_time']=time();
            $goods_sku['wxapp_id']=10216;
            $list=Db::name('goods_sku')->insert($goods_sku);
            }


        return ['status'=>1,'msg'=>'添加成功'];

    }

    public function getGoods()
    {
        $rs=Db::name('goods')->order('goods_id desc')->limit(1)->find();
        var_dump($rs['goods_id']);
        return $rs['goods_id'];
    }
    /**
     * 导出订单
     */
    public function toExport(){

        $name='全部商品';


        /*halt(input('post.page'));*/
        $page = $this->alias('g')
            ->join("category c",'g.category_id=c.category_id')->
                join("goods_sku s","g.goods_id=s.goods_id")->
            select()->toArray();
  foreach ($page as $k=>$v)
  {
      $page[$k]['goods_status'] =$v['goods_status']!=10?'上架':'下架';
      $page[$k]['goods_price']=$v['goods_price'].'元';

//      $page[$k]['name']=$v['category_id'];
      $page[$k]['name']=$this->getParent($v['category_id']);

      $page[$k]['content']=htmlspecialchars_decode($v['content']);
  }
//        halt($page);
//        $path = dirname(__FILE__);
//
//        Loader::import('phpexcel.PHPExcel');
//        Loader::import('phpexcel.PHPExcel.IOFactory');
        vendor("phpexcel.PHPExcel");
        $objPHPExcel = new \PHPExcel();
        // 设置excel文档的属性
        $objPHPExcel->getProperties()->setCreator("WSTShop")//创建人
        ->setLastModifiedBy("WSTShop")//最后修改人
        ->setTitle($name)//标题
        ->setSubject($name)//题目
        ->setDescription($name)//描述
        ->setKeywords("商品")//关键字
        ->setCategory("Test result file");//种类

        // 开始操作excel表
        $objPHPExcel->setActiveSheetIndex(0);
        // 设置工作薄名称
        $objPHPExcel->getActiveSheet()->setTitle(iconv('gbk', 'utf-8', 'Sheet'));
        // 设置默认字体和大小
        $objPHPExcel->getDefaultStyle()->getFont()->setName(iconv('gbk', 'utf-8', ''));
        $objPHPExcel->getDefaultStyle()->getFont()->setSize(11);
        $styleArray = array(
            'font' => array(
                'bold' => true,
                'color'=>array(
                    'argb' => 'ffffffff',
                )
            ),
            'borders' => array (
                'outline' => array (
                    'style' => \PHPExcel_Style_Border::BORDER_THIN,  //设置border样式
                    'color' => array ('argb' => 'FF000000'),     //设置border颜色
                )
            )
        );
        //设置宽
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(55);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(80);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('T')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('U')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getStyle('A1:Z1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A1:Z1')->getFill()->getStartColor()->setARGB('333399');

        $objPHPExcel->getActiveSheet()->setCellValue('A1', '商品ID')->setCellValue('B1', '商品分类')->setCellValue('C1', '商品数量')->setCellValue('D1', '商品价格')->setCellValue('E1', '商品名称')->setCellValue('F1','商品编码')->
        setCellValue('G1', '商品状态')->setCellValue('H1','商品重量')->setCellValue('I1','商品简介')->setCellValue('J1','添加时间');
        $objPHPExcel->getActiveSheet()->getStyle('A1:R1')->applyFromArray($styleArray);




        foreach ($page as $item => $value){
            $i = $item+2;
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $value['goods_id'])->setCellValue('B'.$i, $value['name'])->setCellValue('C'.$i, $value['stock_num'])->setCellValue('D'.$i, $value['goods_price'])->setCellValue('E'.$i, $value['goods_name'])->setCellValue('F'.$i, $value['goods_no'])->setCellValue('G'.$i, $value['goods_status'])
            ->setCellValue('H'.$i, $value['goods_weight'])->setCellValue('I'.$i, $value['content'])->setCellValue('J'.$i, $value['create_time']);
        }

        //输出EXCEL格式
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        // 从浏览器直接输出$filename
        header('Content-Type:application/atom+xml;charset=UTF-8');
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-excel;");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition: attachment;filename="'.$name.'.xls"');
        header("Content-Transfer-Encoding:binary");
        $objWriter->save('php://output');
    }

    public  function getParent( $pid = 0 )
    {

//        static $level = 1;
        $is_parent = Db::name('category')->where(["category_id" => $pid])->find();
        $is_parent['a']=Db::name('category')->where(["category_id" => $is_parent['parent_id']])->find();
        $is_parent['b']=Db::name('category')->where(["category_id" => $is_parent['a']['parent_id']])->find();
        $is_parent['c']=Db::name('category')->where(["category_id" => $is_parent['b']['parent_id']])->find();
//        $array[] = $is_parent;
//        if ($is_parent["parent_id"]>0) {
//           $tree[]=$is_parent;
//            return $this->getParent($is_parent['parent_id'], $tree);
//        }
        if(empty($is_parent['b']['name']))
        {
            return $is_parent['name'].'/'.$is_parent['a']['name'];
        }
        if(empty($is_parent['c']['name']))
        {
            return $is_parent['name'].'/'.$is_parent['a']['name'].'/'.$is_parent['b']['name'];
        }

        return $is_parent['name'].'/'.$is_parent['a']['name'].'/'.$is_parent['b']['name'].'/'.$is_parent['c']['name'];


    }


}
