<?php defined('BASEPATH') or exit('No direct script access allowed');

use Monolog\Logger;
use Monolog\ErrorHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\NewRelicHandler;
use Monolog\Handler\HipChatHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Processor\IntrospectionProcessor;

/**
 * replaces CI's Logger class, use Monolog instead
 *
 * see https://github.com/stevethomas/codeigniter-monolog & https://github.com/Seldaek/monolog
 *
 */
class CI_Log
{
    /**
     * Predefined logging levels
     *
     * @var array
     */
    protected $_levels = array('ERROR' => 1, 'DEBUG' => 2, 'INFO' => 3, 'ALL' => 4);
    // config placeholder
    protected $config = array();
    /**
     * prepare logging environment with configuration variables
     */
    public function __construct()
    {
        // copied functionality from system/core/Common.php, as the whole CI infrastructure is not available yet
        if (!defined('ENVIRONMENT') or !file_exists($file_path = APPPATH . 'config/' . ENVIRONMENT . '/monolog.php')) {
            $file_path = APPPATH . 'config/monolog.php';
        }
        // Fetch the config file
        if (file_exists($file_path)) {
            require($file_path);
        } else {
            exit('monolog.php config does not exist');
        }
        // make $config from config/monolog.php accessible to $this->write_log()
        $this->config = $config;
        $this->log = new Logger($this->config['channel']);
        // detect and register all PHP errors in this log hence forth
        ErrorHandler::register($this->log);
        if ($this->config['introspection_processor']) {
            // add controller and line number info to each log message
            $this->log->pushProcessor(new IntrospectionProcessor(Logger::DEBUG, [], 2));
        }
        // decide which handler(s) to use
        foreach ($this->config['handlers'] as $value) {
            switch ($value) {
                case 'file':
                    $handler = new RotatingFileHandler($this->config['file_logfile'], $this->config['file_number_rotate']);
                    $formatter = new LineFormatter(null, null, $config['file_multiline']);
                    $handler->setFormatter($formatter);
                    break;
                case 'new_relic':
                    $handler = new NewRelicHandler(Logger::ERROR, true, $this->config['new_relic_app_name']);
                    break;
                case 'hipchat':
                    $handler = new HipChatHandler(
                        $config['hipchat_app_token'],
                        $config['hipchat_app_room_id'],
                        $config['hipchat_app_notification_name'],
                        $config['hipchat_app_notify'],
                        $config['hipchat_app_loglevel']
                    );
                    break;
                case 'stderr':
                    $handler = new StreamHandler('php://stderr');
                    break;
                case 'papertrail':
                    $handler = new SyslogUdpHandler($this->config['papertrail_host'], $this->config['papertrail_port']);
                    $formatter = new LineFormatter("%channel%.%level_name%: %message% %extra%", null, $config['papertrail_multiline']);
                    $handler->setFormatter($formatter);
                    break;
                default:
                    exit('log handler not supported: ' . $this->config['handler']);
            }
            $this->log->pushHandler($handler);
        }
        //$this->write_log('DEBUG', 'Monolog replacement logger initialized');
    }


    /**
     * Write Log File
     *
     * Generally this function will be called using the global log_message() function
     *
     * @param   string  $level  The error level: 'error', 'debug' or 'info'
     * @param   string  $msg    The error message
     * @return  bool
     */
    public function write_log($level = 'error', $msg)
    {
        $level = strtoupper($level);
        // verify error level
        if (!isset($this->_levels[$level])) {
            $this->log->addError('unknown error level: ' . $level);
            $level = 'ALL';
        }
        // filter out anything in $this->config['exclusion_list']
        if (!empty($this->config['exclusion_list'])) {
            foreach ($this->config['exclusion_list'] as $findme) {
                $pos = strpos($msg, $findme);
                if ($pos !== false) {
                    // just exit now - we don't want to log this error
                    return true;
                }
            }
        }
        if ($this->_levels[$level] <= $this->config['log_threshold']) {
            switch ($level) {
                case 'ERROR':
                    $this->log->addError($msg);
                    break;
                case 'DEBUG':
                    $this->log->addDebug($msg);
                    break;
                case 'ALL':
                case 'INFO':
                    $this->log->addInfo($msg);
                    break;
            }
        }
        return true;
    }
}
