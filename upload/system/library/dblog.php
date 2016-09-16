<?php
class DBLog {

    /**
     * Log file handler
     *
     * @var resource
     */
    protected $handle;

    /**
     * Local configs for log
     *
     * @var Config
     */
    protected $config;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * DBLog constructor.
     *
     * @param string $filename Name of log file
     */
    public function __construct($filename = null) {
        /**
         * Getting global registry instance.
         *
         * @var Registry $registry
         */
        global $registry;
            
        $this->registry = $registry;

        $this->config = new Config();
        $this->config->load('dblog');

        $dir = DIR_LOGS . trim($this->getConfig('dblog_dir', 'db'), '/\\') . '/';

        if (!file_exists($dir) || !is_dir($dir))
            mkdir($dir, 0777, true);

        if (null === $filename)
            $filename = $this->getLogFilename($dir);

        $this->handle = fopen($dir . $filename, 'a');
    }

    /**
     * Add record for SQL query
     *
     * @param string $query SQL query to log
     */
    public function write($query) {
        $record_data = array(
            '%d' => $this->getDate(),
            '%q' => $query,
            '%u' => $this->getUser(),
            '%b' => $this->getBackTrace(),
            '%sb' => $this->getSingleBackTrace()
        );

        $record_data = array_merge($record_data, $this->getSingleBackTraceInfo());
        $record_data = array_merge($record_data, $this->getUserInfo());

        fwrite($this->handle, $this->fillTemplate($this->getConfig('dblog_format', "%d - %q\n"), $record_data));
    }

    public function __destruct() {
        fclose($this->handle);
    }

    /**
     * Getting config value from global config
     * or from local one. If there is no key neither
     * in global nor in local config return default
     * value that passed by second argument
     *
     * @param string $key Name of config
     * @param mixed $default Default value to return if config was not found. Optional. Default: null
     * @return mixed Value of config or default value
     */
    protected function getConfig($key, $default = null) {
        if ($this->registry->has($key))
            return $this->registry->get($key);

        if ($this->config->has($key))
            return $this->config->get($key);

        return $default;
    }
    
    protected function getLogFilename($dir) {
        $filename = $this->getConfig('dblog_filename', 'db.log');
        $split = $this->getConfig('dblog_split', 'by_week');

        if ($split === false)
            return $filename;

        if (is_callable($split)) {
            return call_user_func($split, $filename, $dir);
        }

        if (is_string($split)) {
            if ($split === 'by_day')
                return date('Y-m-d', time()) . '_' . $filename;

            if ($split === 'by_week')
                return date('Y-\w\e\e\k-W', time()) . '_' . $filename;

            if ($split === 'by_month')
                return date('Y-m', time()) . '_' . $filename;
            
            if ($split === 'by_year')
                return date('Y', time()) . '_' . $filename;
        }

        if (is_int($split)) {
            $name = pathinfo($filename, PATHINFO_FILENAME);
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            $extension = ($extension ? '.' . $extension : '');

            $logs = glob($dir . $name . '-*' . $extension);

            if (!$logs)
                return $name . '-1' . $extension;

            $last = 0;

            foreach ($logs as $log) {
                $log_filename = pathinfo($log, PATHINFO_FILENAME);
                $parts = explode('-', $log_filename);
                $num = array_pop($parts);

                if (!is_numeric($num))
                    continue;

                if ((int)$num > $last)
                    $last = (int)$num;
            }

            $last_log_filename = $name . '-' . $last . $extension;

            if (filesize($dir . $last_log_filename) >= $split)
                return $name . '-' . ($last + 1) . $extension;

            return $last_log_filename;
        }

        return $filename;
    }

    /**
     * Returns date for log record
     * @return string Date string
     */
    protected function getDate() {
        return date($this->getConfig('dblog_date_format', 'Y-m-d H:i:s'), time());
    }

    /**
     * Returns user info for log record
     */
    protected function getUser() {
        /** @var User|null $user */
        $user = $this->registry->has('user') ? $this->registry->get('user') : null;

        $data = $this->getUserInfo(false);

        $default_user_template = $this->getConfig('dblog_user_format', 'User ID: %id, User Group ID: %gid');

        $template = $user && $user->isLogged() ?
            $this->getConfig('dblog_logged_user_format', $default_user_template) :
            $this->getConfig('dblog_not_logged_user_format', $default_user_template);

        return $this->fillTemplate(
            $template,
            $data
        );
    }

    protected function getUserInfo($single = true) {
        /** @var User|null $user */
        $user = $this->registry->has('user') ? $this->registry->get('user') : null;

        $prefix = $single ? 'u' : '';

        $user_id = $user ?
            $user->getId() :
            $this->getConfig('dblog_unknown_user_id', $this->getConfig('dblog_unknown', 'Unknown'));

        $user_name = $user ?
            $user->getUserName() :
            $this->getConfig('dblog_unknown_user_name', $this->getConfig('dblog_unknown', 'Unknown'));

        $user_group_id = $user ?
            $user->getGroupId() :
            $this->getConfig('dblog_unknown_user_group_id', $this->getConfig('dblog_unknown', 'Unknown'));

        return array(
            '%' . $prefix . 'id' => $user_id,
            '%' . $prefix . 'n' => $user_name,
            '%' . $prefix . 'gid' => $user_group_id
        );
    }

    /**
     * @return string
     */
    protected function getBackTrace() {

        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        $info = array();
        $skipped = false;
        $count = 0;
        $limit = $this->getConfig('dblog_backtrace_limit', 0);

        foreach ($backtrace as $record) {
            if ($this->getConfig('dblog_skip_dblog_class_in_backtrace', true) && !$skipped) {
                if ($this->getClassName($record) === __CLASS__)
                    continue;
                else
                    $skipped = true;
            }

            $data = array(
                '%c' => $this->getClassName($record),
                '%t' => $this->getFunctionType($record),
                '%fn' => $this->getFunctionName($record),
                '%f' => $this->getFilename($record),
                '%l' => $this->getLine($record)
            );

            $info[] = $this->fillTemplate($this->getConfig('dblog_trace_format', '%c%t%fn() in %f on line %l'), $data);

            $count++;

            if ($limit > 0 && $count >= $limit)
                break;
        }

        return implode($this->getConfig('dblog_backtrace_glue', "\nat "), $info);
    }

    /**
     *
     */
    protected function getSingleBackTraceInfo() {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        $last = array();

        for ($i = 0, $total = count($backtrace), $found_db = false; $i < $total; $i++) {
            if ($found_db) {
                $last = $backtrace[$i];
                break;
            }

            if ($this->getClassName($backtrace[$i]) === 'DB' && $this->getFunctionName($backtrace[$i]) === 'query')
                $found_db = true;
        }

        return array(
            '%c' => $this->getClassName($last),
            '%t' => $this->getFunctionType($last),
            '%fn' => $this->getFunctionName($last),
            '%f' => $this->getFilename($last),
            '%l' => $this->getLine($last)
        );
    }

    protected function getSingleBackTrace() {
        return $this->fillTemplate($this->getConfig('dblog_trace_format', '%c%t%fn() in %f on line %l'), $this->getSingleBackTraceInfo());
    }

    /**
     * @param array $trace
     * @return string
     */
    protected function getClassName($trace) {
        return isset($trace['class']) ?
            $trace['class'] :
            $this->getConfig('dblog_unknown_class_name', $this->getConfig('dblog_unknown', 'Unknown'));
    }

    /**
     * @param array $trace
     * @return string
     */
    protected function getFunctionName($trace) {
        return isset($trace['function']) ?
            $trace['function'] :
            $this->getConfig('dblog_unknown_function_name', $this->getConfig('dblog_unknown', 'Unknown'));
    }

    /**
     * @param array $trace
     * @return string
     */
    protected function getFunctionType($trace) {
        return isset($trace['type']) ?
            $trace['type'] :
            $this->getConfig('dblog_unknown_function_type', $this->getConfig('dblog_unknown', 'Unknown'));
    }

    /**
     * @param $trace
     * @return string
     */
    protected function getFilename($trace) {
        return isset($trace['file']) ?
            $trace['file'] :
            $this->getConfig('dblog_unknown_filename', $this->getConfig('dblog_unknown', 'Unknown'));
    }

    /**
     * @param $trace
     * @return string
     */
    protected function getLine($trace) {
        return isset($trace['line']) ?
            $trace['line'] :
            $this->getConfig('dblog_unknown_line', $this->getConfig('dblog_unknown', 'Unknown'));
    }

    /**
     * @param string $templateString
     * @param array $data
     * @return mixed
     */
    protected function fillTemplate($templateString, $data) {
        uksort($data, array($this, 'templateDataSort'));
        return str_replace(array_keys($data), array_values($data), $templateString);
    }

    protected function templateDataSort($a, $b) {
        return strcasecmp($b, $a);
    }
}