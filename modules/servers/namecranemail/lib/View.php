<?php

namespace WHMCS\Module\Server\Namecranemail;

/**
 * Renders the module's plain-PHP views (the admin tab), escaping with $e().
 */
class View
{
    /**
     * @param string $name
     * @param array $vars
     * @return string
     */
    public static function render($name, array $vars)
    {
        $e = function ($value) {
            return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
        };

        extract($vars, EXTR_SKIP);
        ob_start();
        include dirname(__DIR__) . '/views/' . $name . '.php';

        return (string) ob_get_clean();
    }
}
