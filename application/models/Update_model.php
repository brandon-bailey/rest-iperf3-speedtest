<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Update_model extends CI_Model
{
    public $currentTag;
    private $initialized = false;

    public function __construct()
    {
        parent::__construct();
        $this->getCurrentTag();
    }

    public function download($version = null)
    {
        log_message('debug', __CLASS__ . '::' . __FUNCTION__);
    }

    public function getCurrentTag()
    {
        $this->currentTag = exec("git --git-dir=" . FCPATH . "/.git describe --abbrev=0 --tags");
        return $this->currentTag;
    }

    private function checkoutVersion($version)
    {
        exec("git --git-dir=" . FCPATH . "/.git -C " . FCPATH . " checkout -f {$version}", $output, $status);
        
        if (0 !== $status) {
            log_message('error', "Issue completing checkout of new tag [{$status}]: " . json_encode($output));
            return false;
        } else {
            log_message('debug', 'Completed checkout of new tag');
            return true;
        }
    }

    protected function discardChanges()
    {
        log_message('debug', "function: " . __FUNCTION__ . " line: ". __LINE__);
        $output = array();
        if (!is_cli()) {
            exec("git --git-dir=" . FCPATH . "/.git reset --hard", $output, $status);
        } else {
            passthru("git --git-dir=" . FCPATH . "/.git reset --hard", $status);
        }

        if (0 !== $status) {
            log_message('error', 'Error trying to reset --hard repo: ' . json_encode($output));
            return;
        }

        $this->hasDiscardedChanges = true;
    }

    private function getUpdatedTagReference()
    {
        if (!is_cli()) {
            exec("git --git-dir=" . FCPATH . "/.git fetch --tags");
        } else {
            passthru("git --git-dir=" . FCPATH . "/.git fetch --tags");
        }
    }

    protected function normalizePath($path)
    {
        if (self::isWindows() && strlen($path) > 0) {
            $basePath = $path;
            $removed = array();

            while (!is_dir($basePath) && $basePath !== '\\') {
                array_unshift($removed, basename($basePath));
                $basePath = dirname($basePath);
            }

            if ($basePath === '\\') {
                return $path;
            }

            $path = rtrim(realpath($basePath) . '/' . implode('/', $removed), '/');
        }

        return $path;
    }

    /**
     * @return bool Whether the host machine is running a Windows OS
     */
    private static function isWindows()
    {
        return defined('PHP_WINDOWS_VERSION_BUILD');
    }

    /**
     * {@inheritDoc}
     */
    protected function hasMetadataRepository($path)
    {
        log_message('debug', "function: " . __FUNCTION__ . " line: ". __LINE__);
        $path = $this->normalizePath($path);

        return is_dir($path.'/.git');
    }
}
