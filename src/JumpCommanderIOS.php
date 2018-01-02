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

use GuzzleHttp\Client as HttpClient;
use Symfony\Component\Console\Input\InputOption;

/**
 * JumpCommand
 */
class JumpCommanderIOS extends Command
{
    use CalculateTrait;

    const IMAGE = 'image.png';
    const BACKUP_IMAGE = 'backup.png';

    protected $host = 'http://localhost:8100';
    protected $session = '';
    /**
     * Http client.
     *
     * @var HttpClient
     */
    protected $httpClient;

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
        // init http client.
        $this->httpClient = new HttpClient();

        // get and set host
        $helper = $this->getHelper('question');
        $output->writeln('<info>iOS工具依赖WebDriverAgent,配置清参考https://testerhome.com/topics/7220</info>'.PHP_EOL);
        $question = new Question(
            '<question>请输入配置好的WebDriverAgent的host(默认http://localhost:8100):</question>'.PHP_EOL.PHP_EOL,
            'http://localhost:8100'
        );
        $question->setNormalizer(
            function ($value) {
                return $value ? trim($value) : '';
            }
        );
        $host = $helper->ask($input, $output, $question);
        $this->host = $host;

        // set session
        $this->setSession();
        // dump($this->session);

        // do it!
        while (true) {
            // screenshot
            $this->screenshot();
            $img = new ImageAnalyzer(TMP_DIR.self::IMAGE);
            $currentPoint = $img->findCurrent();
            $targetPoint = $img->findTarget();
            $time = $this->calculate($currentPoint, $targetPoint);

            $output->writeln('<info>+++++++++++++++++++++++++++++++</info>');
            $output->writeln('<info> ++++++当前[x:'.$currentPoint[0].';y:'.$currentPoint[1].']++++++</info>');
            $output->writeln('<info> ++++++目标[x:'.$targetPoint[0].';y:'.$targetPoint[1].']++++++</info>');
            $output->writeln('<info> +++按下时间:'.($time*1000).'ms+++</info>');
            $output->writeln('<info>+++++++++++++++++++++++++++++++</info>');
            $output->writeln('');
            $output->writeln('');

            $this->touch($time);
            // wait 2 seconds for screenshot.
            sleep(1.8+$time);
        }
    }

    /**
     * Simulate touch
     *
     * @param float $time duration time
     *
     * @return void
     */
    protected function touch($time)
    {
        $this->httpClient->request(
            'POST',
            $this->host.'/session/'.$this->session.'/wda/touchAndHold',
            [
                'json' => [
                    'x' => 200,
                    'y' => 200,
                    'duration' => $time,
                ],
            ]
        );
    }

    /**
     * Screenshot
     *
     * @return void
     */
    protected function screenshot()
    {
        $res = $this->httpClient->get($this->host.'/screenshot');

        $res = json_decode($res->getBody(), true);
        $data = 'data:image/png;base64,'.$res['value'];
        list($type, $data) = explode(';', $data);
        list(, $data)      = explode(',', $data);
        $data = base64_decode($data);

        if (file_exists(TMP_DIR.self::IMAGE)) {
            copy(TMP_DIR.self::IMAGE, TMP_DIR.self::BACKUP_IMAGE);
        }
        try {
            file_put_contents(TMP_DIR.self::IMAGE, $data);
        } catch (\Exception $e) {
            dump($e->getMessage());
            exit();
        }
    }

    /**
     * Set session
     *
     * @return void
     */
    protected function setSession()
    {
        $res = $this->httpClient->get($this->host.'/status');
        $res = json_decode($res->getBody(), true);

        $this->session = $res['sessionId'];
    }

    /**
     * Configure
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('ios')
            ->setDescription('微信跳一跳AI，iOS工具');
    }
}
