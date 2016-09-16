<?php
$_ = array(
    /**
     * Name of log file
     *
     * @var string
     */
    'dblog_filename' => 'db.log',

    /**
     * Directory in opencart log folder
     * where database queries logs will be stored
     *
     * @var string
     */
    'dblog_dir' => 'db',

    /**
     * How to split log files
     *
     * Possible values
     * (bool)
     * false - for write data into one file
     * (string)
     * 'by_day' - for create new file every day
     * 'by_week' - for create new file every monday
     * 'by_month' - for create new file every first day of month
     * 'by_year' - for create new file every new year
     * (int) - max file size of log in bytes, if it's reached the new file will be created
     * (callable) - custom callback that accepts default file name as first argument and directory as second one
     *              should return name of file where to write logs
     *
     * @var bool|string|int|callable
     */
    'dblog_split' => 'by_week',

    /**
     * Commands that should be logged
     * If array is empty then, all queries will be logged
     *
     * @var array
     */
    'dblog_commands' => array(),

    /**
     * Format of single log record
     *
     * Available variables:
     * %a - Affected rows
     * %b - Backtrace
     *      @see dblog_backtrace_limit option
     *      @see dblog_skip_dblog_class_in_backtrace option
     *      @see dblog_trace_format option
     *      @see dblog_backtrace_glue option
     * %c - Class Name where DB->query() was called
     * %d - Date of record
     *      @see: dblog_date_format option
     * %f - Filename where DB->query() was called
     * %fn - Function name where DB->query() was called
     * %l - Line number where DB->query() was called
     * %lid - Last auto incremented ID
     * %nr - Selected number of rows
     * %q - SQL query
     * %sb - Single backtrace record of where DB->query() was called
     *       @see dblog_trace_format option
     * %t - Type of function where DB->query() was called. 
     *      Can be '->' for non-static class, '::' for static class and empty string for single function call
     * %u - User info
     *      @see: dblog_user_format option
     * %ugid - User group id
     * %uid - User id
     * %un - User name
     *
     * @var string
     */
    'dblog_format' => "%d - %q\n",

    /**
     * Date format
     *
     * @var string
     */
    'dblog_date_format' => 'Y-m-d H:i:s',

    /**
     * Format of current user info
     *
     * Available variables:
     * %gid - User group id
     * %id - User id
     * %n - User name
     *
     * @var string
     */
    'dblog_user_format' => 'User ID: %id, User Group ID: %gid',

    /**
     * Format of current user info
     * if he is logged in. By default
     * dblog_user_format option using
     *
     * @var string
     */
    // 'dblog_logged_user_format' => '',

    /**
     * Format of current user info
     * if he is not logged in. By default
     * dblog_user_format option using
     *
     * @var string
     */
    // 'dblog_not_logged_user_format' => ''

    /**
     * Limit of backtrace records
     *
     * @var int
     */
    'dblog_backtrace_limit' => 0,

    /**
     * Should we skip DBLog class records
     * from backtrace?
     *
     * @var bool
     */
    'dblog_skip_dblog_class_in_backtrace' => true,

    /**
     * Format of single backtrace row
     *
     * Available variables:
     * %c - Class name
     * %f - Filename
     * %fn - Function name
     * %l - Line number
     * %t - Type of function
     *      Can be '->' for non-static class, '::' for static class and empty string for single function call
     *
     * @var string
     */
    'dblog_trace_format' => '%c%t%fn() in %f on line %l',

    /**
     * How to join backtrace records with each other
     *
     * @var string
     */
    'dblog_backtrace_glue' => "\nat ",

    /**
     * Text to show if user id is unknown
     * By default using dblog_unknown option
     *
     * @var string
     */
    // 'dblog_unknown_user_id' => '',

    /**
     * Text to show if user name is unknown
     * By default using dblog_unknown option
     *
     * @var string
     */
    // 'dblog_unknown_user_name' => '',

    /**
     * Text to show if user group id is unknown
     * By default using dblog_unknown option
     *
     * @var string
     */
    // 'dblog_unknown_user_group_id' => '',

    /**
     * Text to show if class name is unknown
     * or single function using
     * By default using dblog_unknown option
     *
     * @var string
     */
    'dblog_unknown_class_name' => '',

    /**
     * Text to show if function name is unknown
     * By default using dblog_unknown option
     *
     * @var string
     */
    // 'dblog_unknown_function_name',

    /**
     * Text to show if function type is unknown
     * or single function using
     * By default using dblog_unknown option
     *
     * @var string
     */
    'dblog_unknown_function_type' => '',

    /**
     * Text to show if filename is unknown
     * By default using dblog_unknown option
     *
     * @var string
     */
    'dblog_unknown_filename' => 'Unknown file',

    /**
     * Text to show if line number is unknown
     * By default using dblog_unknown option
     *
     * @var string
     */
    // 'dblog_unknown_line' => '',

    /**
     * Common text for unknown item
     *
     * @var string
     */
    'dblog_unknown' => 'Unknown'
);