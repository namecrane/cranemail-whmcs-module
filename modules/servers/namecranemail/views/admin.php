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
        </table>
    </div>
</div>
<style>
    .ncm-dns > summary { display: inline-block; list-style: none; cursor: pointer; }
    .ncm-dns > summary::-webkit-details-marker { display: none; }
    .ncm-dns > summary .ncm-caret { display: inline-block; margin-right: 4px; transition: transform 0.15s; }
    .ncm-dns[open] > summary .ncm-caret { transform: rotate(90deg); }
    .ncm-dns .ncm-verify td { background: #fcf8e3; }
</style>
<?php /* Open while the domain waits for its verification record, the one thing to do then. */ ?>
<details class="ncm-dns"<?= $domain['verified'] ? '' : ' open' ?>>
    <summary class="btn btn-default btn-sm"><span class="ncm-caret">&#9656;</span> DNS Records</summary>
    <table class="table table-condensed" style="margin:8px 0 0">
        <tr><th>Type</th><th>Name</th><th>Value</th></tr>
        <?php if (! $domain['verified']) { ?>
            <tr class="ncm-verify">
                <td>TXT</td>
                <td><code><?= $e($domain['verification']['host']) ?></code></td>
                <td><code><?= $e($domain['verification']['value']) ?></code><div class="text-muted small">Verifies the domain. Its mailboxes are set up a few minutes after this record shows up.</div></td>
            </tr>
        <?php } ?>
        <?php foreach ($domain['dns_records'] as $record) { ?>
            <tr><td><?= $e($record['type']) ?></td><td><code><?= $e($record['name']) ?></code></td><td><code style="word-break:break-all"><?= $e($record['content']) ?></code></td></tr>
        <?php } ?>
        <tr><td>TXT</td><td><code>@</code></td><td>SPF with <code><?= $e($domain['spf_include']) ?></code></td></tr>
    </table>
</details>
