<?php

namespace WHMCS\Module\Server\Namecranemail;

/**
 * A service's package settings: the product's module options, overridden by configurable
 * options named after them (see the README), in the shape the API takes.
 *
 * The first ten options keep the positions the 1.x module gave them (WHMCS stores them by
 * position), so updating the module leaves existing products as they were.
 */
class Package
{
    const WEBMAIL_LOGIN = 11;

    const MXFILTER_LOGIN = 12;

    const CLIENT_MANAGEMENT = 13;

    /**
     * The module options a product sets, in WHMCS's order.
     *
     * @return array
     */
    public static function configOptions()
    {
        $onOff = array('0' => 'Disabled', '1' => 'Enabled');
        $years = array('0' => 'Disabled', '1' => '1 Year');
        foreach (array(2, 3, 4, 5, 6, 7, 8, 9, 10, 15, 20) as $year) {
            $years[(string) $year] = $year . ' Years';
        }

        return array(
            'Disk Space (GB)' => array('Type' => 'text', 'Size' => '8', 'Default' => '1', 'SimpleMode' => true),
            'Max Email Users' => array('Type' => 'text', 'Size' => '8', 'Default' => '0', 'Description' => '0 = Unlimited', 'SimpleMode' => true),
            'MXFilter Spam Filtering' => array('Type' => 'dropdown', 'Options' => $onOff, 'SimpleMode' => true),
            'Max User Aliases' => array('Type' => 'text', 'Size' => '8', 'Default' => '0', 'Description' => '0 = Unlimited', 'SimpleMode' => true),
            'MXFilter Access' => array('Type' => 'dropdown', 'Options' => array('primary' => 'Primary Administrator Only', 'all' => 'All Domain Administrators'), 'SimpleMode' => true),
            'Max Domain Aliases' => array('Type' => 'text', 'Size' => '8', 'Default' => '0', 'Description' => '0 = Unlimited', 'SimpleMode' => true),
            'Email Archiving (Years)' => array('Type' => 'dropdown', 'Options' => $years, 'SimpleMode' => true),
            'Email Archiving Direction' => array('Type' => 'dropdown', 'Options' => array('in' => 'Incoming', 'out' => 'Outgoing', 'inout' => 'Both'), 'SimpleMode' => true),
            'File Storage' => array('Type' => 'dropdown', 'Options' => $onOff, 'SimpleMode' => true),
            'Office Suite' => array('Type' => 'dropdown', 'Options' => $onOff, 'SimpleMode' => true),
            'Webmail Login' => array(
                'Type' => 'dropdown',
                'Options' => array('on' => 'Allowed', 'off' => 'Turned off'),
                'Description' => 'Lets the client open any mailbox\'s webmail without its password. Turn off where privacy rules (GDPR) forbid it.',
                'SimpleMode' => true,
            ),
            'MXFilter Login' => array(
                'Type' => 'dropdown',
                'Options' => array('on' => 'Allowed', 'off' => 'Turned off'),
                'Description' => 'Lets the client and your staff open the spam filter panel without a password.',
                'SimpleMode' => true,
            ),
            'Client Mailbox Management' => array(
                'Type' => 'dropdown',
                'Options' => array('on' => 'Clients manage mailboxes and aliases', 'off' => 'Hidden from clients'),
                'SimpleMode' => true,
            ),
        );
    }

    /**
     * The domain settings for the API's create and settings calls.
     *
     * @param array $params
     * @return array
     */
    public static function settings(array $params)
    {
        $years = (int) self::value($params, 'archive_years', 7);
        $direction = (string) self::value($params, 'archive_direction', 8);
        $access = (string) self::value($params, 'spamexperts_adminaccess', 5);

        return array(
            'disk_limit' => max(0, (int) self::value($params, 'disklimit', 1)),
            'user_limit' => max(0, (int) self::value($params, 'userlimit', 2)),
            'mxfilter_enabled' => (bool) self::value($params, 'spamexperts', 3),
            'user_alias_limit' => max(0, (int) self::value($params, 'useraliaslimit', 4)),
            'mxfilter_admin_access' => in_array($access, array('primary', 'all'), true) ? $access : 'primary',
            'domain_alias_limit' => max(0, (int) self::value($params, 'domainaliaslimit', 6)),
            'archive_years' => min(20, max(0, $years)),
            'archive_direction' => $years > 0 ? (in_array($direction, array('in', 'out', 'inout'), true) ? $direction : 'inout') : 'disabled',
            'file_storage' => (bool) self::value($params, 'filestorage', 9),
            'office' => (bool) self::value($params, 'office', 10),
            'webmail_sso' => self::webmailLoginAllowed($params),
            'mxfilter_sso' => self::mxfilterLoginAllowed($params),
        );
    }

    /**
     * @param array $params
     * @return bool
     */
    public static function webmailLoginAllowed(array $params)
    {
        return self::option($params, self::WEBMAIL_LOGIN) !== 'off';
    }

    /**
     * @param array $params
     * @return bool
     */
    public static function mxfilterLoginAllowed(array $params)
    {
        return self::option($params, self::MXFILTER_LOGIN) !== 'off';
    }

    /**
     * @param array $params
     * @return bool
     */
    public static function clientManagesMailboxes(array $params)
    {
        return self::option($params, self::CLIENT_MANAGEMENT) !== 'off';
    }

    /**
     * The reseller service the server's key should use, from the server's Username field
     * (needed only when the key covers more than one service).
     *
     * @param array $params
     * @return int|null
     */
    public static function serviceId(array $params)
    {
        $id = isset($params['serverusername']) ? trim((string) $params['serverusername']) : '';

        return ctype_digit($id) && (int) $id > 0 ? (int) $id : null;
    }

    /**
     * A configurable option named $name when the order set one, else module option $index.
     *
     * @param array $params
     * @param string $name
     * @param int $index
     * @return mixed
     */
    private static function value(array $params, $name, $index)
    {
        if (isset($params['configoptions'][$name]) && $params['configoptions'][$name] !== '') {
            return $params['configoptions'][$name];
        }

        return self::option($params, $index);
    }

    /**
     * @param array $params
     * @param int $index
     * @return string
     */
    private static function option(array $params, $index)
    {
        return isset($params['configoption' . $index]) ? trim((string) $params['configoption' . $index]) : '';
    }
}
