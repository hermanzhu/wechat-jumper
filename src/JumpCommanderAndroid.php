<?php
/**
 * Jumper Command Class
 * 
 * @author herman <i@imzsy.com>
 */

namespace WechatJumper;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * JumpCommand
 */
class JumpCommanderAndroid extends Command
{
    /**
     * Configure
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('android')
            ->setDescription('wechat jumper for android, php version');
    }

    /**
     * Execute command
     *
     * @param InputInterface  $input  input
     * @param OutputInterface $output output
     * 
     * @return void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Android');
    }
}
