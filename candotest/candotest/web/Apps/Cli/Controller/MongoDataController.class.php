<?php

namespace Cli\Controller;

/**
 * 更新MongoDB中的数据信息
 * 
 * 地址：http://gui.ideathink.com.cn/cli.php/MongoData/update?do=1
 * Cron： /alidata/server/php/bin/php /data/www/turtle/cli.php MongoData/update do=1
 */
use Cli\Controller\CommonController;
use Common\Model\Model;

class MongoDataController extends CommonController {

    private $objUserLogic;
    private $objUserDbModel;


    public function _initialize() {
        parent::_initialize(false);
        
        $this->objUserLogic = new \Common\Logic\UserLogic();
        $this->objUserDbModel = new \Common\Model\UserModel();
        
    }

    /**
     * 全量更新
     */
    public function update() {
        self::output("开始更新，init...");
        self::newline();
        
        $cond = array();
        $list = $this->objUserDbModel->field('id')->where($cond)->select();
        if (empty($list)) {
            self::exit_done("无用户数据...");
        }
        
        foreach ($list as $k => $v) {
            $status = $this->objUserLogic->saveMongo($v['id'], true);
            if ($status['status']) {
                self::output("id".$v['id']." 更新成功, next...");
            }  else {
                self::output("id".$v['id']." 更新失败, next...");
            }
            
        }
        
        self::exit_done();
        exit;
    }
    
    
    /**
     * 更新供求的mongodb信息
     * @param type $param
     */
    public function runSupply() {
        
        self::output("#########Start supply, init...#########");
        self::newline();
        
        # 开始处理
        $start = 0;
        $offset = 200;
        $supplyModel = new \Common\Model\SupplyModel();
        $mgSupplyLogic = new \Common\Logic\SupplyLogic();
        
        
        $cond = array(
            'is_old' => 0 #新的供求
        );
        
        do {
            
            $limit = $start . ',' . $offset;
            self::output('>>>>>>>>>>>>>>>>>> fenyeGET SUPPLY TABLE '.$limit.' <<<<<<<<<<<<<<<<<<<<<<<<');
            
            $supplyIds = $supplyModel->field('id')->where($cond)->order('id DESC')->limit($limit)->select();
            if (empty($supplyIds)) {
                self::exit_done("NNNNNNNNNNNNNNNNNNNNNN=> NO SUPPLY DATA... <=NNNNNNNNNNNNNNNNNNNNNN");
                break;
            }
            
            foreach ($supplyIds as $k => $v) {
                # id
                $id = $v['id'];
                
                $saveSta = $mgSupplyLogic->saveMongo($id, true);
                if (!$saveSta) {
                    self::output('NNNNNNNNNNNN MONGO SUPPLY TABLE ID' . $v['id'] . ' save not ok, continue NNNNNNNNNNNN');
                    continue;
                }
                
                self::output('OKOKOKOKOKOKOKOK MONGO SUPPLY TABLE ID' . $v['id'] . ' save ok, next... OKOKOKOKOKOKOKOK');
            }
            
            self::output('>>>>>>>>>>>>>>>>>> MONGO SUPPLY TABLE '.$limit.' success, NEXT LIMIT ... <<<<<<<<<<<<<<<<<<<<<<<<');
            self::newLine();
            
            $start += $offset;
            
        } while(true);
        
        
        self::exit_done();
        exit;
        
        
    }
    
}
