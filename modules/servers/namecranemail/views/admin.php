<?php
/**
 * The NameCrane Mail tab on a service in the WHMCS admin area.
 *
 * @var array $domain the API's domain details
 * @var callable $e escapes for HTML
 */
$statuses = array(
    'active' => array('Active', 'success'),
    'pending' => array('Waiting for DNS', 'warning'),
    'suspended' => array('Suspended (your NameCrane plan)', 'danger'),
    'client_suspended' => array('Suspended', 'danger'),
    'admin_suspended' => array('Suspended by NameCrane', 'danger'),
);
list($statusLabel, $statusColor) = isset($statuses[$domain['status']]) ? $statuses[$domain['status']] : array($domain['status'], 'default');
$limit = function ($used, $limit, $unit = '') {
    return $used . $unit . ' of ' . ($limit ? $limit . $unit : 'unlimited');
};
?>
<div class="row">
    <div class="col-sm-6">
        <table class="table table-condensed" style="margin-bottom:10px">
            <tr><td style="width:40%">Status</td><td><span class="label label-<?= $e($statusColor) ?>"><?= $e($statusLabel) ?></span></td></tr>
            <tr><td>Storage</td><td><?= $e($limit(round($domain['disk_usage'] / 1024, 2), $domain['disk_limit'], ' GB')) ?></td></tr>
            <tr><td>Mailboxes</td><td><?= $e($limit($domain['user_count'], $domain['user_limit'])) ?></td></tr>
            <tr><td>Aliases</td><td><?= $e($limit($domain['user_alias_count'], $domain['user_alias_limit'])) ?></td></tr>
            <tr><td>Domain aliases</td><td><?= $e($limit($domain['domain_alias_count'], $domain['domain_alias_limit'])) ?></td></tr>
        </table>
    </div>
    <div class="col-sm-6">
        <table class="table table-condensed" style="margin-bottom:10px">
            <tr><td style="width:40%">MXFilter</td><td><?= $domain['mxfilter_enabled'] ? 'On' : 'Off' ?></td></tr>
            <tr><td>File storage / Office</td><td><?= $domain['file_storage'] ? 'On' : 'Off' ?> / <?= $domain['office'] ? 'On' : 'Off' ?></td></tr>
            <tr><td>Archiving</td><td><?= $domain['archive_years'] ? $e($domain['archive_years'] . ' years, ' . $domain['archive_direction']) : 'Off' ?></td></tr>
            <tr><td>Password-free sign-in</td><td>Webmail <?= $domain['webmail_sso'] ? 'allowed' : 'off' ?>, MXFilter <?= $domain['mxfilter_sso'] ? 'allowed' : 'off' ?></td></tr>
            <?php if (! $domain['verified']) { ?>
                <tr><td>Verification TXT</td><td><code><?= $e($domain['verification']['host']) ?></code> = <code><?= $e($domain['verification']['value']) ?></code></td></tr>
            <?php } ?>
        </table>
    </div>
</div>
<details>
    <summary style="cursor:pointer">DNS records the domain needs</summary>
    <table class="table table-condensed" style="margin:8px 0 0">
        <tr><th>Type</th><th>Name</th><th>Value</th></tr>
        <?php foreach ($domain['dns_records'] as $record) { ?>
            <tr><td><?= $e($record['type']) ?></td><td><code><?= $e($record['name']) ?></code></td><td><code style="word-break:break-all"><?= $e($record['content']) ?></code></td></tr>
        <?php } ?>
        <tr><td>TXT</td><td><code>@</code></td><td>SPF with <code><?= $e($domain['spf_include']) ?></code></td></tr>
    </table>
</details>
