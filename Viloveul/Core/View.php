<?php

namespace Viloveul\Core;

/*
 * @author      Fajrul Akbar Zuhdi <fajrulaz@gmail.com>
 * @package     Viloveul
 * @subpackage  Core
 */

use Exception;

class View extends Object
{
    private static $data = array();

    protected $filename = false;

    protected $vars = array();

    protected $directory = null;

    /**
     * Constructor.
     *
     * @param   string filename
     * @param   array vars
     */
    public function __construct($filename, array $vars = array())
    {
        $this->filename = $filename;

        is_array($vars) && $this->set($vars);
    }

    /**
     * To String.
     *
     * @return string rendered output
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * make.
     *
     * @param   [mixed] data paramters
     *
     * @return string rendered output
     */
    public static function make($name, array $data = array())
    {
        self::globalDataSet($data, null);

        return self::createInstance($name);
    }

    /**
     * globalDataSet.
     *
     * @param   string|array var(s) name
     * @param   [mixed]
     */
    public static function globalDataSet($data, $value = null)
    {
        if (is_string($data)) {
            return self::globalDataSet(array($data => $value));
        }

        foreach ((array) $data as $var => $val) {
            if (is_null($val) && array_key_exists($var, self::$data)) {
                unset(self::$data[$var]);
            } else {
                self::$data[$var] = $val;
            }
        }
    }

    /**
     * globalDataGet.
     *
     * @param   string var
     * @param   Any default value(s)
     *
     * @return Any
     */
    public static function globalDataGet($name, $defaultValue = null)
    {
        return array_key_exists($name, self::$data) ? self::$data[$name] : $defaultValue;
    }

    /**
     * render.
     *
     * @param   callable Callback
     *
     * @return string rendered output
     */
    public function render($__local281291callbackFilter = null)
    {
        $this->directory = Application::realpath().'/Views';

        $__local281291vars = array_merge(self::$data, $this->vars);
        $__local281291fileparts = array_filter(explode('/', $this->filename), 'trim');
        $__local281291filename = $this->directory.'/'.implode('/', $__local281291fileparts).'.php';

        if (!is_file($__local281291filename)) {
            throw new Exception('Unable to locate view : '.$__local281291filename);
        }

        $__local281291contentFile = $this->loadContentFile($__local281291filename);

        extract($__local281291vars);

        ob_start();

        eval('?>'.$__local281291contentFile);

        $__local281291outputRendering = ob_get_clean();

        $__local281291trimOutputRendering = trim($__local281291outputRendering);

        return is_callable($__local281291callbackFilter) ?
            call_user_func($__local281291callbackFilter, $__local281291trimOutputRendering, $this) :
                $__local281291trimOutputRendering;
    }

    /**
     * get.
     *
     * @param   string variable name
     * @param   Any default value
     *
     * @return Any value
     */
    public function get($var, $defaultValue = null)
    {
        if (array_key_exists($name, $this->vars)) {
            return $this->vars[$var];
        } elseif (array_key_exists($name, self::$data)) {
            return self::$data[$var];
        }

        return $defaultValue;
    }

    /**
     * set.
     *
     * @param   string variable name
     * @param   Any value
     */
    public function set($var, $value = null)
    {
        if (is_string($var)) {
            return $this->set(array($var => $value));
        }

        foreach ((array) $var as $key => $val) {
            $this->vars[$key] = $val;
        }

        return $this;
    }

    /**
     * filterLoadedContents.
     *
     * @param   string content file
     *
     * @return string
     */
    protected function filterLoadedContents($contents = '')
    {
        if (strpos($contents, '{{@') !== false && false !== strpos($contents, '}}')) {
            $contents = preg_replace_callback(
                '#\{\{\@(.+)\}\}#U',
                array($this, 'handleContentFiltered'),
                $contents
            );
        }

        return $contents;
    }

    /**
     * handleContentFiltered.
     *
     * @param   array matches
     *
     * @return string
     */
    protected function handleContentFiltered($matches)
    {
        $filename = trim($matches[1]);
        $path = "{$this->directory}/{$filename}.php";

        return is_file($path) ?
            $this->loadContentFile($path) :
                $matches[0];
    }

    /**
     * loadContentFile.
     *
     * @param   string filename
     *
     * @return string
     */
    protected function loadContentFile($filename)
    {
        return $this->filterLoadedContents(
            file_get_contents($filename)
        );
    }
}
