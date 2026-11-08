<?php

/**
 * NameCrane Mail for WHMCS: sells CraneMail email domains from your NameCrane reseller plan.
 *
 * Set up a server with your NameCrane API key in its Access Hash field. Leave Hostname empty
 * (it defaults to namecrane.com). Fill in Username only if the key covers more than one
 * reseller plan: put the plan's service ID there.
 *
 * Runs on PHP 7.2 and up, so WHMCS 7 installs can use it too.
 */

use WHMCS\Database\Capsule;
use WHMCS\Module\Server\Namecranemail\ApiClient;
use WHMCS\Module\Server\Namecranemail\ApiException;
use WHMCS\Module\Server\Namecranemail\ClientArea;
use WHMCS\Module\Server\Namecranemail\Package;
use WHMCS\Module\Server\Namecranemail\View;

if (! defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

if (! defined('NAMECRANEMAIL_VERSION')) {
    define('NAMECRANEMAIL_VERSION', '2.0.0');
}

require_once __DIR__ . '/lib/ApiException.php';
require_once __DIR__ . '/lib/ApiClient.php';
require_once __DIR__ . '/lib/Package.php';
require_once __DIR__ . '/lib/View.php';
require_once __DIR__ . '/lib/ClientArea.php';

function namecranemail_MetaData()
{
    return array(
        'DisplayName' => 'NameCrane Mail',
        'APIVersion' => '1.1',
        'RequiresServer' => true,
        'ServiceSingleSignOnLabel' => 'Login to MXFilter',
        'AdminSingleSignOnLabel' => 'Login to MXFilter',
    );
}

function namecranemail_ConfigOptions()
{
    return Package::configOptions();
}

/**
 * The server's "Test Connection": the key works, and it's clear which reseller plan it uses.
 */
function namecranemail_TestConnection(array $params)
{
    try {
        $services = ApiClient::fromParams($params)->get('cranemail/services');
    } catch (ApiException $e) {
        return array('success' => false, 'error' => namecranemail_explain($e));
    }

    $wanted = Package::serviceId($params);

    if ($services === array()) {
        return array('success' => false, 'error' => 'This API key can\'t reach any NameCrane Mail reseller plan. Check the key\'s access in your NameCrane account.');
    }

    if ($wanted !== null) {
        foreach ($services as $service) {
            if ((int) $service['id'] === $wanted) {
                return array('success' => true, 'error' => '');
            }
        }

        return array('success' => false, 'error' => 'This API key can\'t reach service #' . $wanted . '. It can reach: ' . namecranemail_describeServices($services) . '.');
    }

    if (count($services) > 1) {
        return array('success' => false, 'error' => 'This API key covers more than one reseller plan (' . namecranemail_describeServices($services) . '). Put the service ID of the one this server is for in the Username field.');
    }

    return array('success' => true, 'error' => '');
}

function namecranemail_CreateAccount(array $params)
{
    $domain = strtolower(trim((string) $params['domain']));

    if ($domain === '') {
        return 'This service has no domain. Set the email domain on the service first.';
    }

    $request = array('domain' => $domain) + Package::settings($params);

    if (($serviceId = Package::serviceId($params)) !== null) {
        $request['service_id'] = $serviceId;
    }

    try {
        $created = ApiClient::fromParams($params)->post('cranemail/domains', $request);
    } catch (ApiException $e) {
        return namecranemail_explain($e);
    }

    if (! empty($created['admin']['password'])) {
        localAPI('UpdateClientProduct', array(
            'serviceid' => $params['serviceid'],
            'serviceusername' => $created['admin']['username'],
            'servicepassword' => $created['admin']['password'],
        ));
    }

    return 'success';
}

function namecranemail_SuspendAccount(array $params)
{
    try {
        ApiClient::fromParams($params)->post('cranemail/domains/' . ApiClient::segment($params['domain']) . '/suspend');
    } catch (ApiException $e) {
        return namecranemail_explain($e);
    }

    return 'success';
}

function namecranemail_UnsuspendAccount(array $params)
{
    try {
        ApiClient::fromParams($params)->post('cranemail/domains/' . ApiClient::segment($params['domain']) . '/unsuspend');
    } catch (ApiException $e) {
        // Unsuspending a domain that isn't suspended leaves it as it should be.
        return $e->status() === 400 && $e->getMessage() === 'This domain is not suspended.' ? 'success' : namecranemail_explain($e);
    }

    return 'success';
}

function namecranemail_TerminateAccount(array $params)
{
    try {
        ApiClient::fromParams($params)->delete('cranemail/domains/' . ApiClient::segment($params['domain']));
    } catch (ApiException $e) {
        // Already gone is what terminating wants.
        return $e->status() === 404 ? 'success' : namecranemail_explain($e);
    }

    return 'success';
}

function namecranemail_ChangePackage(array $params)
{
    try {
        ApiClient::fromParams($params)->put('cranemail/domains/' . ApiClient::segment($params['domain']) . '/settings', Package::settings($params));
    } catch (ApiException $e) {
        return namecranemail_explain($e);
    }

    return 'success';
}

/**
 * Disk usage and limits for every domain on this server, in MB as WHMCS keeps them.
 */
function namecranemail_UsageUpdate(array $params)
{
    try {
        $services = ApiClient::fromParams($params)->get('cranemail/services');
    } catch (ApiException $e) {
        return $e->getMessage();
    }

    $wanted = Package::serviceId($params);
    $now = date('Y-m-d H:i:s');

    foreach ($services as $service) {
        if ($wanted !== null && (int) $service['id'] !== $wanted) {
            continue;
        }

        foreach ($service['domains'] as $domain) {
            Capsule::table('tblhosting')
                ->where('server', $params['serverid'])
                ->where('domain', $domain['domain'])
                ->update(array(
                    'diskusage' => (int) round($domain['disk_usage']),
                    'disklimit' => (int) $domain['disk_limit'] * 1024,
                    'lastupdate' => $now,
                ));
        }
    }

    return 'success';
}

/**
 * The MXFilter panel, for the client (from their product page) and for staff (from the
 * service in the admin area), when the package allows it.
 */
function namecranemail_ServiceSingleSignOn(array $params)
{
    if (! Package::mxfilterLoginAllowed($params)) {
        return array('success' => false, 'errorMsg' => 'Signing in to MXFilter without a password is turned off for this product.');
    }

    try {
        $login = ApiClient::fromParams($params)->post('cranemail/domains/' . ApiClient::segment($params['domain']) . '/mxfilter/login');
    } catch (ApiException $e) {
        return array('success' => false, 'errorMsg' => $e->getMessage());
    }

    return array('success' => true, 'redirectTo' => $login['url']);
}

function namecranemail_AdminServicesTabFields(array $params)
{
    if (trim((string) $params['domain']) === '') {
        return array();
    }

    try {
        $domain = ApiClient::fromParams($params)->get('cranemail/domains/' . ApiClient::segment($params['domain']));
    } catch (ApiException $e) {
        return array('NameCrane Mail' => '<div class="alert alert-warning" style="margin:0">' . htmlspecialchars($e->getMessage()) . '</div>');
    }

    return array('NameCrane Mail' => View::render('admin', array('domain' => $domain)));
}

function namecranemail_ClientArea(array $params)
{
    $page = new ClientArea($params);

    return $page->handle();
}

/**
 * The API's refusal, with what to do about it when the server setup is the cause.
 *
 * @return string
 */
function namecranemail_explain(ApiException $e)
{
    $body = $e->body();

    if ($e->status() === 422 && ! empty($body['services'])) {
        return 'This API key covers more than one reseller plan (' . namecranemail_describeServices($body['services']) . '). Put the service ID of the one this server is for in the server\'s Username field.';
    }

    if ($e->status() === 401) {
        return 'NameCrane didn\'t accept the API key: ' . $e->getMessage() . ' Check the server\'s Access Hash field.';
    }

    return $e->getMessage();
}

/**
 * @return string
 */
function namecranemail_describeServices(array $services)
{
    return implode(', ', array_map(function ($service) {
        return '#' . $service['id'] . ' ' . $service['name'];
    }, $services));
}
