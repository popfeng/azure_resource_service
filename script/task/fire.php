<?PHP
/**
 * 任务启动脚本
 */
include realpath(__DIR__ . '/../../') . '/index.php';
$call_path = SCRIPT_PATH . 'task/call.php';

while (true) {
    // 获取待执行操作列表
    $result = Model\ResOp::single()->getDatasByStatus(Model\ResOp::STATUS_ACCEPT);
    // 打印日志
    echo sprintf(
        "%s\t%s\n",
        date('Y-m-d H:i:s'),
        $result ? implode(', ', array_column($result, 'id')) : 'wait 1s'
    );

    // 多进程异步调用任务处理模块
    foreach ($result as $item) {
        exec(sprintf(
            '/bin/php %s %d >/dev/null 2>&1 &',
            $call_path,
            $item['id']
        ));
        // 更新操作状态为处理中
        Model\ResOp::single()->updateStatus($item['id'], Model\ResOp::STATUS_PROCESS);
    }
    // 小憩1秒
    sleep(1);
}
