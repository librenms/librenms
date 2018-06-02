<?php namespace snmptrapmanager;

/**
 * https://github.com/gilbitron/PIP/blob/master/system/view.php
 *
 * https://github.com/gilbitron/PIP
 */
class MIBUpView
{

    private $pageVars = array();
    private $template;

    public function __construct($template)
    {
        $this->template = MIBUpUtils::bfp(
            array(
            dirname(__FILE__), '..', 'views', $template . '.php'
            )
        );
    }

    public static function load($sName)
    {
        $res = new MIBUpView($sName);
        return $res;
    }

    /**
     * In a PHP file, sets a var named $$var to the value $val.
     *
     * @return MIBUpView
     */
    public function set($var, $val)
    {
        $this->pageVars[$var] = $val;
        return $this;
    }

    /**
     * Renders the view.
     *
     * @return string
     */
    public function render()
    {
        extract($this->pageVars);

        ob_start();
        include $this->template;
        return ob_get_clean();
    }
}
