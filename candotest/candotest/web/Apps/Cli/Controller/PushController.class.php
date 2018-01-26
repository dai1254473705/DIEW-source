<?php

namespace Cli\Controller;

/**
 * 推送消息给系统用户
 * 
 * 地址：http://gui.ideathink.com.cn/cli.php/Push/run?do=1
 * Cron： /alidata/server/php/bin/php /data/www/turtle/cli.php Push/run do=1
 */
use Cli\Controller\CommonController;


class PushController extends CommonController {
    
    # 单个推送最大重试次数
    static $maxTryTimes = 3;
    
    private $objNoticeModel;
    private $objUserDbModel;
    private $objJpushBindModel;
    private $objPushQueueModel;


    /**
     * init方法
     */
    public function _initialize() {
        parent::_initialize();
        
        $this->objNoticeModel = new \Common\Model\NoticeModel();
        $this->objUserDbModel = new \Common\Model\UserModel();
        $this->objJpushBindModel = M('bind_jpush');
        $this->objPushQueueModel = new \Common\Model\PushQueueModel();
    }
    
    
    /**
     * 推送开始
     *  1.获取未推送的，生成推送队列；
     *  2.按消息获取推送队列的值，分批推送；
     *  3.记录推送状态，非成功的再次推送
     */
    public function run() {
        self::output("开始获取消息信息，init...");
        self::newline();
        
        
        # 1.获取未推送的
        $noticeCond = array(
            'push_status' => \Common\Model\NoticeModel::PUSH_NOT,
            'status' => \Common\Model\NoticeModel::STATUS_PENDDING
        );
        
        $notice = $this->objNoticeModel->where(array($noticeCond))->select();
        if (!empty($notice)) {
            self::output("存在需要生成推送队列的消息，开始生成推送队列并立即推送...");
            
            foreach ($notice as $k => $v) {
                
                # 调用函数生成推送队列
                $this->createPushQueue($v);
                
                # 插入成功后，开始单独推送，防止推送队列数据过多获取慢
                $status = $this->pushing($v['id']);
                
                self::output("消息ID：".$v['id']."，生成推送队列并立即推送操作 完成，开始生成下一个消息的推动队列...");
            }
            
        } else {
            
            self::output("无需要生成推送队列的消息，开始进入推送...");
            $status = $this->pushing();
            
            self::output("推送操作完成...contine....");
        }


        self::exit_done();
        exit;
    }
    
    
    /**
     * 内部创建推送队列
     * @param type $data
     */
    protected function createPushQueue($data) {
        
        $userModel = $this->objUserDbModel;
        $userCount = $userModel->where(array('status' => \Common\Model\UserModel::STATUS_TRUE))->count();
        
        # 开始处理时，将记录的状态置为处理中
        $saveCond = array( 'id' => $data['id'] );
        $ups = $this->objNoticeModel->where($saveCond)->save(array(
            'status' => \Common\Model\NoticeModel::STATUS_PENDDING,
            'update_time' => time()
        ));
        
        
        # 开始处理
        $start = 0;
        $offset = 100;
        do {
            
            $limit = $start . ',' . $offset;
            self::output('消息ID：' . $data['id'] . '获取数据并开始插入推送队列' . $limit);
            
            $userIds = $userModel->index('id')->field('id')->where(array('status' => \Common\Model\UserModel::STATUS_TRUE))->order('id ASC')->limit($limit)->select();
            if (empty($userIds)) {
                self::output('消息ID：' . $data['id'] . $limit . '无数据， break');
                break;
            }
            
            $addAll = array();
            foreach (array_keys($userIds) as $user_id) {
                $addAll[] = array(
                    'user_id' => $user_id,
                    'title' => $data['title'],
                    'content' => $data['content'],
                    'url' => $data['url'],
                    'url_type' => $data['url_type'],
                    'notice_id' => $data['id'],
                    'try_times' => 0,
                    'create_time' => time()
                );
            }
            
            $listIns = $this->objPushQueueModel->addAll($addAll);
            if ($listIns) {
                self::output('消息ID' . $data['id'] . '插入推送队列' . $limit . '成功，下一阶段插入...');
            }
            
            $start += $offset;
            
        } while(true);
        
        
        $status = $this->objNoticeModel->where($saveCond)->save(array(
            'push_status' => \Common\Model\NoticeModel::PUSH_PENDDING,
            'update_time' => time()
        ));
        
        return true;
    }
    
    
    /**
     * 推送
     * @param type $id
     */
    protected function pushing($id = false) {
        
        $cond = array(
            'push_status' => \Common\Model\NoticeModel::PUSH_PENDDING,
            'status' => \Common\Model\NoticeModel::STATUS_PENDDING
        );
        if ($id) {
            $cond['id'] = $id;
        }
        
        # 获取notice数据
        $penddingNotice = $this->objNoticeModel->where($cond)->select();
        if (empty($penddingNotice)) {
            self::output('无需要推送的消息数据，continue...');
            return false;
        }
        
        # 真实推送
        foreach ($penddingNotice as $k => $notice) {
            self::output('>>>>>>>>>>>>>>>>>>>>>>>>> 开始远端推送.. >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>');
            $this->pushRemote($notice);
        }
        
        return true;
    }
    
    
    
    /**
     * 远端推送
     * 保证数据执行的成功率，还是一条一条的推送，针对未成功推送的数据 延迟重试的操作
     * @param type $notice_id
     */
    protected function pushRemote($notice) {
        
        $cond = array(
            'notice_id' => $notice['id']
        );
        
        # 数据
        $limit = 20;
        $count = $this->objPushQueueModel->where(array('notice_id' => $notice['id']))->count();
        $failNoBind = 0;
        $failCount = 0;
        $okCount = 0;
        
        while (true) {
            
            $push = array();
            $data = $this->objPushQueueModel->getsByNoticeId($notice['id'], $limit);
            if (empty($data)) {
                self::output('消息ID：'.$notice['id']. '无推送队列信息，break...');
                break;
            }
            
            # 获取registration_id信息
            $userIds = i_array_column($data, 'user_id');
            $jpushData = $this->objJpushBindModel->index('uid')->where(array('uid' => array('in', join(',', $userIds))))->select();
            
            # 依次循环
            foreach ($data as $one) {
                $queue_id = $one['id'];
                $oneUserId = $one['user_id'];
                $registration_id = $jpushData[$oneUserId]['registration_id'];
                
                
                # 不存在绑定信息的，删除
                if (!$registration_id) {
                    self::output('用户ID：'.$oneUserId. '无Jpush 绑定信息，delete...');
                    $failNoBind++;
                    $this->objPushQueueModel->del($queue_id);
                    continue;
                }
                
                if ($one['try_times'] > self::$maxTryTimes) {
                    self::output('用户ID：'.$oneUserId. ' 达到最大重试次数，delete...');
                    $failCount++;
                    $this->objPushQueueModel->del($queue_id);
                    continue;
                }
                
                # 推送
                $urlTypes = \Common\Model\UrlTypeModel::parseTypeUrl($notice['url'], $notice['url_type']);
                $pushStatus = \Common\Service\JpushService::pushMessage_by_registration_id($registration_id, $notice['title'], $urlTypes['redirect_type'], $urlTypes['redirect_url']);
                if (!$pushStatus['status']) {
                    self::output('用户ID：' . $oneUserId . '推送失败（' . $pushStatus['msg']['message'] . '）.. delay...');
                    $status = $this->objPushQueueModel->delay($queue_id);
                    continue;
                }
                
                self::output('用户ID：'.$oneUserId. '推送成功.. delete && next...');
                $okCount++;
                $this->objPushQueueModel->del($queue_id);   
            }
        }
        
        
        # 一个消息推送完成，记录log
        $logAdd = array(
            'title' => $notice['title'],
            'content' => $notice['content'],
            'url' => $notice['url'],
            'url_type' => $notice['url_type'],
            'notice_id' => $notice['id'],
            'total' => $count,
            'no_bind_count' => $failNoBind,
            'fail_count' => $failCount,
            'success_count' => $okCount,
            'create_time' => time()
        );
        $addStatus = M('push_log')->add($logAdd);
        
        
        # 更新消息的推送状态为 已推送
        $up = array(
            'id' => $notice['id'],
            'push_status' => \Common\Model\NoticeModel::PUSH_COMPLETE,
            'status' => \Common\Model\NoticeModel::STATUS_COMPLETE,
            'update_time' => time()
        );
        $save = $this->objNoticeModel->save($up);
        
        self::output('消息ID：'. $notice['id'] . '整体推送成功.. sleep(5) 下一个消息...');
        self::setSleep(5);
        
        return true;
    }
    
}
