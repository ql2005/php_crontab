<?php

/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/10/12
 * Time: 15:34
 */
class CrontabTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $log_file = "/tmp/crontab_test.log";

    protected $err_file = "/tmp/crontab_err.log";


    public function testStart()
    {
        if (file_exists($this->log_file)) {
            unlink($this->log_file);
        }
        $logger = new \Monolog\Logger(\Jenner\Crontab\Crontab::NAME);
        $logger->pushHandler(new \Monolog\Handler\StreamHandler($this->log_file));
        $mission = new \Jenner\Crontab\Mission("mission_test", "ls /", "* * * * *", $logger);
        $crontab = new \Jenner\Crontab\Crontab(null, array($mission));

        $crontab->start(time());
        $out = file_get_contents($this->log_file);
        $except = shell_exec("ls /");
        $this->assertEquals($out, $except);
    }

    public function testError(){
        if (file_exists($this->log_file)) {
            unlink($this->log_file);
        }
        if(file_exists($this->err_file)){
            unlink($this->err_file);
        }
        $out = new \Monolog\Logger(\Jenner\Crontab\Crontab::NAME);
        $out->pushHandler(new \Monolog\Handler\StreamHandler($this->log_file));
        $err = new \Monolog\Logger(Jenner\Crontab\Crontab::NAME);
        $err->pushHandler(new \Monolog\Handler\StreamHandler($this->err_file));
        $mission = new \Jenner\Crontab\Mission("mission_test", "ls / && ddddeee", "* * * * *", $out, $err);
        $crontab = new \Jenner\Crontab\Crontab(null, array($mission));

        $crontab->start(time());
        $stdout = file_get_contents($this->log_file);
        $except = shell_exec("ls /");
        $this->assertEquals($stdout, $except);
        $stderr = file_get_contents($this->err_file);
        $except = shell_exec('ddddeeee');
        $this->assertEquals($stderr, $except);
    }

    public function testNotStart()
    {
        if (file_exists($this->log_file)) {
            unlink($this->log_file);
        }
        $mission = new \Jenner\Crontab\Mission("mission_test", "ls /", "3 * * * *", $this->log_file);
        $crontab = new \Jenner\Crontab\Crontab(null, array($mission));

        $crontab->start(time());
        $this->assertFalse(file_exists($this->log_file));
    }

}