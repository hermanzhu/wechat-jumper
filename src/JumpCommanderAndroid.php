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
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Input\InputOption;

/**
 * JumpCommand
 */
class JumpCommanderAndroid extends Command
{
    use CalculateTrait;

    const IMAGE = 'image.png';
    const BACKUP_IMAGE = 'backup.png';

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
        $output->writeln('<info>使用跳一跳Android工具前，请先确认adb配置是否正常。</info>'.PHP_EOL);
        $helper = $this->getHelper('question');
        $question = new Question(
            '<question>请配置距离计算系数(默认2.04):</question>'.PHP_EOL.PHP_EOL,
            '2.04'
        );
        $question->setNormalizer(
            function ($value) {
                return $value ? trim($value) : '';
            }
        );
        $col = $helper->ask($input, $output, $question);

        // do it!
        while (true) {
            // screenshot
            $this->screenshot();
            $img = new ImageAnalyzer(TMP_DIR.self::IMAGE);
            $currentPoint = $img->findCurrent();
            $targetPoint = $img->findTarget();
            $time = $this->calculate($currentPoint, $targetPoint, $col);

            $output->writeln('<info>+++++++++++++++++++++++++++++++</info>');
            $output->writeln('<info> ++++++当前[x:'.$currentPoint[0].';y:'.$currentPoint[1].']++++++</info>');
            $output->writeln('<info> ++++++目标[x:'.$targetPoint[0].';y:'.$targetPoint[1].']++++++</info>');
            $output->writeln('<info>   按下时间:'.($time*1000).'ms</info>');
            $output->writeln('<info>+++++++++++++++++++++++++++++++</info>');
            $output->writeln('');
            $output->writeln('');

            $this->touch(round($time*1000), 0);
            // wait 2 seconds for screenshot.
            sleep(1.8+$time);
        }
    }

    /**
     * Screenshot
     *
     * @return void
     */
    protected function screenshot()
    {
        if (file_exists(TMP_DIR.self::IMAGE)) {
            copy(TMP_DIR.self::IMAGE, TMP_DIR.self::BACKUP_IMAGE);
        }
        try {
            exec('adb shell screencap -p /sdcard/jump.tmp.png');
            exec('adb pull /sdcard/jump.tmp.png '.TMP_DIR.self::IMAGE);
        } catch (\Exception $e) {
            dump($e->getMessage());
            exit();
        }
    }

    /**
     * Touch
     *
     * @param float $time time
     * 
     * @return void
     */
    protected function touch($time)
    {
        exec('adb shell input swipe 320 410 320 410  '.$time);
    }

    /**
     * Configure
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('android')
            ->setDescription('微信跳一跳AI，Android工具');
    }
}
