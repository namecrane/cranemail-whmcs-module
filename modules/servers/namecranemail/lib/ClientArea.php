<?php

namespace WHMCS\Module\Server\Namecranemail;

/**
 * The client's pages for one email domain: the overview (status, DNS, usage), and when the
 * package lets clients manage them, its mailboxes, aliases and domain aliases.
 *
 * Pages are picked with ?customAction= on the product details page. A form posts back to
 * its page; a change that worked redirects there with a notice, one that didn't shows the
 * form again with the API's reason.
 */
class ClientArea
{
    const PAGES = array('overview', 'mailboxes', 'aliases', 'domainaliases');

    /** Alias options as the API names them, and how the page labels them. */
    const ALIAS_FEATURES = array(
        'catch_all' => 'Catch-all (gets mail for addresses that don\'t exist)',
        'show_in_gal' => 'Show in the address book',
        'internal_only' => 'Only accept mail from this domain',
        'send_from' => 'Targets can send as this alias',
        'all_users' => 'Deliver to every mailbox',
    );

    /** @var array */
    private $params;

    /** @var ApiClient */
    private $api;

    /** @var string */
    private $domainPath;

    /** @var string|null */
    private $error;

    /** @var array */
    private $old = array();

    /**
     * @param array $params
     */
    public function __construct(array $params)
    {
        $this->params = $params;
        $this->api = ApiClient::fromParams($params);
        $this->domainPath = 'cranemail/domains/' . ApiClient::segment($params['domain']);
    }

    /**
     * @return array
     */
    public function handle()
    {
        $page = isset($_REQUEST['customAction']) ? (string) $_REQUEST['customAction'] : 'overview';

        if ($page === 'webmail') {
            return $this->openWebmail();
        }

        if (! in_array($page, self::PAGES, true) || ($page !== 'overview' && ! Package::clientManagesMailboxes($this->params))) {
            $page = 'overview';
        }

        if ($page !== 'overview' && strtoupper((string) $_SERVER['REQUEST_METHOD']) === 'POST') {
            $this->checkToken();
            $this->act($page);
        }

        try {
            $domain = $this->api->get($this->domainPath);
        } catch (ApiException $e) {
            return $this->view('error', array('ncmError' => $e->getMessage()));
        }

        $method = 'page' . ucfirst($page);

        return $this->view($page, array('ncmDomain' => $domain) + $this->$method($domain));
    }

    /**
     * @param array $domain
     * @return array
     */
    private function pageOverview(array $domain)
    {
        $loginHost = 'workspace.org';

        try {
            foreach ($this->api->get('cranemail/services') as $service) {
                if ((int) $service['id'] === (int) $domain['service_id'] && ! empty($service['login_hostnames'])) {
                    $loginHost = $service['login_hostnames'][0];
                }
            }
        } catch (ApiException $e) {
            // The sign-in address falls back to workspace.org.
        }

        return array(
            'ncmLoginUrl' => 'https://' . $loginHost,
            'ncmUsage' => array(
                array('label' => 'Storage', 'used' => round($domain['disk_usage'] / 1024, 2), 'limit' => $domain['disk_limit'], 'unit' => 'GB'),
                array('label' => 'Mailboxes', 'used' => $domain['user_count'], 'limit' => $domain['user_limit'], 'unit' => ''),
                array('label' => 'Aliases', 'used' => $domain['user_alias_count'], 'limit' => $domain['user_alias_limit'], 'unit' => ''),
                array('label' => 'Domain Aliases', 'used' => $domain['domain_alias_count'], 'limit' => $domain['domain_alias_limit'], 'unit' => ''),
            ),
            'ncmMxfilterLogin' => $this->mxfilterLoginOffered($domain),
        );
    }

    /**
     * @param array $domain
     * @return array
     */
    private function pageMailboxes(array $domain)
    {
        $users = $this->listing('users', $domain);
        $editing = null;
        $wanted = isset($_GET['edit']) ? strtolower((string) $_GET['edit']) : '';

        foreach ($users['users'] as $user) {
            if (strtolower($user['username']) === $wanted) {
                $editing = $user;
            }
        }

        $posted = $this->old !== array();

        return array(
            'ncmUsers' => $users['users'],
            'ncmListError' => $users['error'],
            'ncmEditing' => $editing,
            'ncmForm' => array(
                'name' => $editing ? $editing['username'] : ($posted ? self::input('name') : ''),
                'display_name' => $posted ? self::input('display_name') : ($editing ? $editing['display_name'] : ''),
                'password' => $posted && ! $editing ? self::input('password') : '',
                'disk_limit' => $posted ? (int) self::input('disk_limit') : ($editing ? (int) $editing['disk_limit'] : 0),
                'admin' => $posted ? self::checked('admin') : ($editing ? (bool) $editing['admin'] : false),
                'enabled' => $posted ? self::checked('enabled') : ($editing ? (bool) $editing['enabled'] : true),
            ),
            'ncmWebmailLogin' => Package::webmailLoginAllowed($this->params) && ! empty($domain['webmail_sso']),
        );
    }

    /**
     * @param array $domain
     * @return array
     */
    private function pageAliases(array $domain)
    {
        $aliases = $this->listing('aliases', $domain);
        $editing = null;
        $wanted = isset($_GET['edit']) ? strtolower((string) $_GET['edit']) : '';

        foreach ($aliases['aliases'] as $alias) {
            if (strtolower($alias['username']) === $wanted) {
                $editing = $alias;
            }
        }

        $posted = $this->old !== array();
        $features = array();
        foreach (array_keys(self::ALIAS_FEATURES) as $feature) {
            $features[$feature] = $posted ? self::checked('feature_' . $feature) : ($editing ? ! empty($editing['features'][$feature]) : $feature === 'show_in_gal');
        }

        return array(
            'ncmAliases' => $aliases['aliases'],
            'ncmListError' => $aliases['error'],
            'ncmEditing' => $editing,
            'ncmForm' => array(
                'name' => $posted ? self::input('name') : ($editing ? $editing['username'] : ''),
                'display_name' => $posted ? self::input('display_name') : ($editing ? $editing['display_name'] : ''),
                'targets' => $posted ? self::input('targets') : ($editing ? implode("\n", $editing['targets']) : ''),
                'features' => $features,
            ),
            'ncmFeatures' => self::ALIAS_FEATURES,
        );
    }

    /**
     * @param array $domain
     * @return array
     */
    private function pageDomainaliases(array $domain)
    {
        $aliases = $this->listing('domain-aliases', $domain);

        return array(
            'ncmDomainAliases' => $aliases['domain-aliases'],
            'ncmListError' => $aliases['error'],
            'ncmForm' => array('name' => $this->old !== array() ? self::input('name') : ''),
        );
    }

    /**
     * One of the domain's lists, or why it can't be shown yet.
     *
     * @param string $what
     * @param array $domain
     * @return array
     */
    private function listing($what, array $domain)
    {
        $key = $what === 'users' ? 'users' : $what;
        $empty = array($key => array(), 'error' => null);

        if (empty($domain['verified']) || $domain['status'] !== 'active') {
            $empty['error'] = 'This can be managed once the domain is verified and active.';

            return $empty;
        }

        try {
            $result = $this->api->get($this->domainPath . '/' . $what);
        } catch (ApiException $e) {
            $empty['error'] = $e->getMessage();

            return $empty;
        }

        return array($key => $what === 'users' ? $result['users'] : $result, 'error' => null);
    }

    /**
     * Runs the posted change. On success it goes back to the page with a notice; otherwise the
     * page shows the reason and the form keeps what was typed.
     *
     * @param string $page
     */
    private function act($page)
    {
        $op = isset($_POST['op']) ? (string) $_POST['op'] : '';

        try {
            $notice = $this->change($page, $op);
        } catch (ApiException $e) {
            $this->error = $e->getMessage();
            // A failed add or save shows its form again with what was typed.
            $this->old = in_array($op, array('create', 'update'), true) ? $_POST : array();

            return;
        }

        if ($notice !== null) {
            $_SESSION['namecranemail_notice'] = $notice;
            header('Location: ' . $this->pageUrl($page));
            exit;
        }
    }

    /**
     * @param string $page
     * @param string $op
     * @return string|null
     * @throws ApiException
     */
    private function change($page, $op)
    {
        $name = self::input('name');
        $original = ApiClient::segment(self::input('original'));

        switch ($page . '.' . $op) {
            case 'mailboxes.create':
                $this->api->post($this->domainPath . '/users', array(
                    'username' => $name,
                    'password' => self::input('password'),
                    'display_name' => self::input('display_name'),
                    'disk_limit' => (int) self::input('disk_limit'),
                    'admin' => self::checked('admin'),
                ));

                return 'Mailbox ' . $name . '@' . $this->params['domain'] . ' added.';

            case 'mailboxes.update':
                $changes = array(
                    'display_name' => self::input('display_name'),
                    'disk_limit' => (int) self::input('disk_limit'),
                    'admin' => self::checked('admin'),
                    'enabled' => self::checked('enabled'),
                );
                if (self::input('password') !== '') {
                    $changes['password'] = self::input('password');
                }
                $this->api->put($this->domainPath . '/users/' . $original, $changes);

                return 'Mailbox saved.';

            case 'mailboxes.delete':
                $this->api->delete($this->domainPath . '/users/' . $original);

                return 'Mailbox deleted.';

            case 'aliases.create':
            case 'aliases.update':
                $alias = array(
                    'username' => $name,
                    'display_name' => self::input('display_name'),
                    'targets' => self::addresses(self::input('targets')),
                    'features' => $this->features(),
                );
                if ($op === 'create') {
                    $this->api->post($this->domainPath . '/aliases', $alias);

                    return 'Alias ' . $name . '@' . $this->params['domain'] . ' added.';
                }
                $this->api->put($this->domainPath . '/aliases/' . $original, $alias);

                return 'Alias saved.';

            case 'aliases.delete':
                $this->api->delete($this->domainPath . '/aliases/' . $original);

                return 'Alias deleted.';

            case 'domainaliases.create':
                $this->api->post($this->domainPath . '/domain-aliases', array('alias' => strtolower($name)));

                return 'Domain alias ' . strtolower($name) . ' added. Point its MX records here like the main domain\'s.';

            case 'domainaliases.delete':
                $this->api->delete($this->domainPath . '/domain-aliases/' . $original);

                return 'Domain alias removed.';
        }

        return null;
    }

    /**
     * Opens a mailbox's webmail in the client's browser, without its password.
     *
     * @return array
     */
    private function openWebmail()
    {
        if (! Package::webmailLoginAllowed($this->params)) {
            return $this->view('error', array('ncmError' => 'Signing in to webmail without a password is turned off for this product.'));
        }

        try {
            $login = $this->api->post($this->domainPath . '/users/' . ApiClient::segment(self::input('mailbox')) . '/login');
        } catch (ApiException $e) {
            return $this->view('error', array('ncmError' => $e->getMessage()));
        }

        header('Location: ' . $login['url']);
        exit;
    }

    /**
     * @param array $domain
     * @return bool
     */
    private function mxfilterLoginOffered(array $domain)
    {
        return Package::mxfilterLoginAllowed($this->params) && ! empty($domain['mxfilter_enabled']) && ! empty($domain['mxfilter_sso'])
            && ! empty($domain['verified']) && $domain['status'] === 'active';
    }

    /**
     * @param string $template
     * @param array $vars
     * @return array
     */
    private function view($template, array $vars)
    {
        $notice = isset($_SESSION['namecranemail_notice']) ? $_SESSION['namecranemail_notice'] : null;
        unset($_SESSION['namecranemail_notice']);

        return array(
            'tabOverviewReplacementTemplate' => 'templates/' . $template . '.tpl',
            'templateVariables' => $vars + array(
                'ncmVersion' => NAMECRANEMAIL_VERSION,
                'ncmPartials' => dirname(__DIR__) . '/templates/partials',
                'ncmServiceId' => (int) $this->params['serviceid'],
                'ncmDomainName' => $this->params['domain'],
                'ncmBaseUrl' => $this->pageUrl(''),
                'ncmManage' => Package::clientManagesMailboxes($this->params),
                'ncmNotice' => $notice,
                'ncmFormError' => $this->error,
            ),
        );
    }

    /**
     * @param string $page
     * @return string
     */
    private function pageUrl($page)
    {
        return 'clientarea.php?action=productdetails&id=' . (int) $this->params['serviceid'] . ($page !== '' && $page !== 'overview' ? '&customAction=' . $page : '');
    }

    /**
     * WHMCS's CSRF check on a posted form.
     */
    private function checkToken()
    {
        if (function_exists('check_token')) {
            check_token('WHMCS.default');
        }
    }

    /**
     * @return array
     */
    private function features()
    {
        $features = array();

        foreach (array_keys(self::ALIAS_FEATURES) as $feature) {
            $features[$feature] = self::checked('feature_' . $feature);
        }

        return $features;
    }

    /**
     * @param string $name
     * @return string
     */
    private static function input($name)
    {
        return isset($_REQUEST[$name]) && is_string($_REQUEST[$name]) ? trim($_REQUEST[$name]) : '';
    }

    /**
     * @param string $name
     * @return bool
     */
    private static function checked($name)
    {
        return ! empty($_POST[$name]);
    }

    /**
     * Addresses typed one per line or separated by commas.
     *
     * @param string $text
     * @return array
     */
    private static function addresses($text)
    {
        return array_values(array_filter(array_map('trim', preg_split('/[\s,;]+/', $text))));
    }
}
