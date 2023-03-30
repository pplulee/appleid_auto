<?php
declare (strict_types=1);

namespace app\command;

use app\model\UnlockRecord;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;
use think\facade\Db;

class DbClean extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('dbclean')
            ->addArgument('action', Argument::OPTIONAL, "清理动作")
            ->setDescription('用于清理数据库中的无用数据');
    }

    protected function execute(Input $input, Output $output)
    {
        $action = $input->getArgument('action');
        switch ($action) {
            case 'ExpireShare':
                // 清理过期分享页
                $output->writeln('开始清理过期分享页……');
                Db::name('share')->where('expire', '<', date('Y-m-d H:i:s'))->delete();
                $output->writeln('清理完成');
                break;
            case 'UnlockRecord':
                // 清空解锁记录
                $output->writeln('开始清空解锁记录……');
                $unlockRecord = new UnlockRecord();
                $unlockRecord->truncate();
                break;
            default:
                $output->writeln('未知的清理动作' . PHP_EOL);
                $output->writeln('可用的清理动作：');
                $output->writeln('ExpireShare - 清理过期分享页');
                $output->writeln('UnlockRecord - 清空解锁记录');
                break;
        }
    }
}
