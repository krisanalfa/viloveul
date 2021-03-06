<?php

namespace Viloveul\Core;

/*
 * @author      Fajrul Akbar Zuhdi <fajrulaz@gmail.com>
 * @package     Viloveul
 * @subpackage  Core
 */

use Exception;

class Debugger
{
    /**
     * printMessage.
     *
     * @param   string content message
     * @param   array backtrace
     * @param   bool
     */
    public static function printMessage($content, array $backtrace = array(), $exit = true)
    {
        $output = '<div style="border: 1px solid #993300; padding: 15px; margin: 0 0 15px 0;">';
        $output .= $content;
        if ($backtrace) {
            $output .= self::calculateBacktrace($backtrace);
        }
        $output .= '</div>';

        if (ob_get_level() > 0) {
            ob_end_flush();
        }

        echo $output;

        empty($exit) or exit(1);
    }

    /**
     * calculateBacktrace.
     *
     * @param   array backtrace
     *
     * @return string
     */
    public static function calculateBacktrace(array $backtrace)
    {
        $output = '';

        foreach ($backtrace as $error) :
            if (isset($error['file'])) {
                $output .= '<p style="padding-left: 10px; border-left: 2px dashed #CCCCCC">';
                $output .= sprintf('%s -> %s : %d', $error['function'], $error['file'], $error['line']);
                $output .= '</p>';
            }
        endforeach;

        return $output ? sprintf('<div style="margin-left: 15px;">Backtrace : %s</div>', $output) : '';
    }

    /**
     * handleError.
     *
     * @param   string severity message
     * @param   string data message
     * @param   string filename
     * @param   int line number
     */
    public static function handleError($severity, $message, $file, $line)
    {
        $data = '<h4>A PHP Error was encountered</h4>';

        $reallyError = (((E_ERROR | E_COMPILE_ERROR | E_CORE_ERROR | E_USER_ERROR) & $severity) === $severity);

        if (($severity & error_reporting()) !== $severity) {
            return;
        }

        $data .= sprintf('<p>Severity : %s</p>', $severity);
        $data .= sprintf('<p>Message : %s</p>', $message);
        $data .= sprintf('<p>Filename : %s</p>', $file);
        $data .= sprintf('<p>Line Number : %s</p>', $line);

        self::printMessage($data, debug_backtrace(), (boolean) $reallyError);

        return true;
    }

    /**
     * handleException.
     *
     * @param   object Exception
     */
    public static function handleException(Exception $e)
    {
        $data = '<h4>An uncaught Exception was encountered</h4>';

        $data .= sprintf('<p>Type : %s</p>', get_class($e));
        $data .= sprintf('<p>Message : %s</p>', $e->getMessage());
        $data .= sprintf('<p>Filename : %s</p>', $e->getFile());
        $data .= sprintf('<p>Line Number : %s</p>', $e->getLine());

        self::printMessage($data, $e->getTrace(), true);

        return true;
    }

    /**
     * registerErrorHandler.
     */
    public static function registerErrorHandler()
    {
        set_error_handler(array(__CLASS__, 'handleError'), E_ALL);
    }

    /**
     * registerExceptionHandler.
     */
    public static function registerExceptionHandler()
    {
        set_exception_handler(array(__CLASS__, 'handleException'));
    }
}
