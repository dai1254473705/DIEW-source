<?php

namespace Common\Model;

use Think\Model;
use Common\Model\AgileModel;

class ArticleCatModel extends AgileModel {
    
    protected $pk = 'acat_id';

    /**
     * 定义是否显示
     */
    const IS_SHOW_TRUE = 1;
    const IS_SHOW_FALSE = 0;

    static public $is_show_status = array(
        self::IS_SHOW_TRUE => array(
            'kw' => "IS_SHOW_TRUE",
            'desc' => "显示"
        ),
        self::IS_SHOW_FALSE => array(
            'kw' => "IS_SHOW_FALSE",
            'desc' => "隐藏"
        ),
    );
    

    /**
     * 格式化分类数据，方便列表展示
     * @param type $cateData  分类原始数据
     * @param type $parent_id  父类ID
     * @return type
     */
    public function formatCategory($parent_id = 0) {
        if (!is_numeric($parent_id)) {
            return array();
        }

        //获取分类信息
        $parentData = $this->where('parent_id=' . $parent_id)->order('sort_order asc, acat_id asc')->select();

        $return = array();
        foreach ($parentData as $key => $data) {
            if ($data['parent_id'] == $parent_id) {
                $data['child'] = $this->formatCategory($data['acat_id']);
                $return[$data['acat_id']] = $data;
            }
        }

        return $return;
    }

    /**
     * 获取分类信息，用于前台显示
     * @param type $formatData
     * @return type
     */
    public function checkLevel($formatData, &$levelData = array()) {
        if (!$formatData) {
            return array();
        }

        foreach ($formatData as $key => $val) {
            $tmp = $val['child'];
            unset($val['child']);
            // 最上级分类（一次循环结束）
            $levelData[$key] = $val;
            if (!$val['parent_id']) {
                $levelData[$key]['level'] = 0;
                $levelData[$key]['level_px'] = $levelData[$key]['level'] * 15 + 5;
            }
            
            // 如果一个分类下的子分类同时存在同级子分类和下级子分类，则同级子分类的level值是上级分类的level加1
            $parentData = $levelData[$val['parent_id']];
            if ($val['parent_id']) {
                $levelData[$key]['level'] = $parentData['level'] + 1;
            }
            $levelData[$key]['level_px'] = $levelData[$key]['level'] * 15 + 5;
            
            # 如果没有下级分类，则不递归检测
            if (empty($tmp)) {
                continue;
            }
            
            $this->checkLevel($tmp, $levelData);
        }

        return $levelData;
    }

    /**
     * 获取格式化分类数据之后的所有子分类
     * @param type $formatData
     * @return type
     */
    public function getAllChilds($formatData, &$childs = array()) {
        if (!$formatData) {
            return array();
        }
        foreach ($formatData as $key => $data) {
            if (!empty($data['child'])) {
                $childs[$key] = $data['child'];
                $this->getAllChilds($data['child'], $childs);
            }
        }

        return $childs;
    }
    
}
