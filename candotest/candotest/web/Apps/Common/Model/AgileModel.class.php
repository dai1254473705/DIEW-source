<?php

/**
 * 数据库敏捷操作类 敏捷开发(Agile development)
 * 
 * 目的：统一数据库表的设计模式，为 增、删、改 提供便捷
 * 
 * 注意：此model为可选继承，也可以直接继承ThinkPHP的Model类
 */

namespace Common\Model;

use Think\Model;

class AgileModel extends Model {

    /**
     * 根据主键ID获取记录，默认主键是id，如果不一样的主键，在model中自定义即可
     * @param type $id
     * @return type
     */
    public function getByPk($id, $fields = array()) {
        if (!$id) {
            return array();
        }
        if (!empty($fields)) {
            $fieldsStr = join(',', $fields);
            $data = $this->field($fieldsStr)->where(array($this->pk => $id))->find();
        } else {
            $data = $this->where(array($this->pk => $id))->find();
        }
        return $data;
    }

    /**
     * 根据主键获取一批记录
     * @param type $ids
     * @return type
     */
    public function getsByPk($ids, $isIndexPk = false) {
        if (!is_array($ids)) {
            $ids = array($ids);
        }
        
        # 是否以pk作为index
        if ($isIndexPk) {
            $datas = $this->index($this->pk)->where(
                            array(
                                $this->pk => array("in", join(',', $ids))
                            )
                    )->select();
        } else {
            $datas = $this->where(
                            array(
                                $this->pk => array("in", join(',', $ids))
                            )
                    )->select();
        }


        return $datas;
    }

    /**
     * 获取数据的所有字段名称
     * @return type
     */
    public function getAllFields() {
        return $this->fields['_type'];
    }

    /**
     * 字段过多时，过滤添加或修改时需要的字段信息 
     * @param type $data
     * @return type
     */
    public function _filterField($data) {
        $fields = $this->getAllFields();
        $res = array();
        foreach ($fields as $key => $val) {
            if (isset($data[$key])) {
                $res[$key] = $data[$key];
            }
        }
        return $res;
    }

    /**
     * 重载ThinkPHP的原生setDec，当递减的字段值为0时，不做操作
     * @access public
     * @param string $field  字段名
     * @param integer $step  减少值
     * @param integer $lazyTime  延时时间(s)
     * @return boolean
     */
    public function setDec($field, $step = 1, $lazyTime = 0) {
        $condition = $this->options['where'];
        $record = $this->where($condition)->find();
        if (empty($record)) {
            return false;
        }
        if ($record[$field] <= 0) {
            return true;
        }
        if ($lazyTime > 0) {// 延迟写入
            $guid = md5($this->name . '_' . $field . '_' . serialize($condition));
            $step = $this->lazyWrite($guid, $step, $lazyTime);
            if (false === $step)
                return true; // 等待下次写入
        }
        $this->options['where'] = $condition;
        return $this->setField($field, array('exp', $field . '-' . $step));
    }
    
    
    /**
     * 内部结果传递类--失败
     * @param type $msg
     * @return boolean
     */
    static protected function transFail($msg) {
        $res = array(
            'status' => false,
            'msg' => $msg
        );
        return $res;
    }

    /**
     * 内部结果传递类--成功
     * @param type $msg
     * @return type
     */
    static protected function transSuccess($msg) {
        $res = array(
            'status' => true,
            'msg' => $msg
        );
        return $res;
    }
    

}
