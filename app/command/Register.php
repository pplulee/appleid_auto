<?php
declare (strict_types=1);

namespace app\command;

use app\model\User;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;

class Register extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('register')
            ->addArgument('username', Argument::REQUIRED, '用户名')
            ->addArgument('password', Argument::REQUIRED, '密码')
            ->setDescription('注册管理员用户');
    }

    protected function execute(Input $input, Output $output)
    {
        // 获取命令参数
        $username = $input->getArgument('username');
        $password = $input->getArgument('password');
        // 检测用户名是否已存在
        $user = User::where('username', $username)->find();
        if ($user) {
            $output->writeln('用户名已存在');
            return;
        } else {
            $user = new User();
            $user->username = $username;
            $user->password = password_hash($password, PASSWORD_DEFAULT);
            $user->is_admin = 1;
            $user->save();
            $output->writeln('注册成功');
        }
    }
}
