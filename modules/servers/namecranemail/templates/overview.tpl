<div class="ncm ncm-{$template|escape}">
    {assign var="ncmPage" value="overview"}
    {include file="$ncmPartials/header.tpl"}

    <div class="{$ncmCard}">
        <div class="{$ncmCardHead}">
            <h3 class="{$ncmCardTitle}">
                {$ncmDomain.domain|escape}
                {if $ncmDomain.status eq 'active'}
                    <span class="{$ncmBadge}success ncm-status">Active</span>
                {elseif $ncmDomain.status eq 'pending'}
                    <span class="{$ncmBadge}warning ncm-status">Waiting for DNS</span>
                {else}
                    <span class="{$ncmBadge}danger ncm-status">Suspended</span>
                {/if}
            </h3>
        </div>
        <div class="{$ncmCardBody}">
            {if $ncmDomain.status eq 'pending'}
                <p>Add this TXT record to <strong>{$ncmDomain.domain|escape}</strong>'s DNS to prove it's yours. The mailboxes are set up a few minutes after it shows up.</p>
                <table class="table ncm-records">
                    <thead><tr><th>Type</th><th>Name</th><th>Value</th></tr></thead>
                    <tbody>
                        <tr><td>TXT</td><td><code class="ncm-copy">{$ncmDomain.verification.host|escape}</code></td><td><code class="ncm-copy">{$ncmDomain.verification.value|escape}</code></td></tr>
                    </tbody>
                </table>
            {elseif $ncmDomain.status eq 'active'}
                <p class="ncm-actions">
                    <a href="{$ncmLoginUrl|escape}" target="_blank" rel="noopener" class="btn btn-primary">Open Webmail</a>
                    {if $ncmMxfilterLogin}
                        <a href="clientarea.php?action=productdetails&amp;id={$ncmServiceId}&amp;dosinglesignon=1" target="_blank" class="btn btn-default">Login to MXFilter</a>
                    {/if}
                </p>
                <p class="text-muted ncm-muted">Mailboxes sign in at <a href="{$ncmLoginUrl|escape}" target="_blank" rel="noopener">{$ncmLoginHost|escape}</a> with their full email address and password.</p>
            {else}
                <p>This domain is suspended, so its mail is stopped. Contact us to have it turned back on.</p>
            {/if}
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="{$ncmCard}">
                <div class="{$ncmCardHead}"><h3 class="{$ncmCardTitle}">Usage</h3></div>
                <table class="table ncm-usage">
                    <tbody>
                        {foreach from=$ncmUsage item=ncmItem}
                            <tr>
                                <td>{$ncmItem.label}</td>
                                <td class="ncm-right">
                                    {$ncmItem.used}{if $ncmItem.unit} {$ncmItem.unit}{/if}
                                    <span class="text-muted">of {if $ncmItem.limit}{$ncmItem.limit}{if $ncmItem.unit} {$ncmItem.unit}{/if}{else}unlimited{/if}</span>
                                </td>
                            </tr>
                        {/foreach}
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-md-6">
            <div class="{$ncmCard}">
                <div class="{$ncmCardHead}"><h3 class="{$ncmCardTitle}">Features</h3></div>
                <table class="table ncm-usage">
                    <tbody>
                        <tr><td>MXFilter spam filtering</td><td class="ncm-right">{if $ncmDomain.mxfilter_enabled}On{else}<span class="text-muted">Off</span>{/if}</td></tr>
                        <tr><td>File storage</td><td class="ncm-right">{if $ncmDomain.file_storage}On{else}<span class="text-muted">Off</span>{/if}</td></tr>
                        <tr><td>Office suite</td><td class="ncm-right">{if $ncmDomain.office}On{else}<span class="text-muted">Off</span>{/if}</td></tr>
                        <tr><td>Email archiving</td><td class="ncm-right">{if $ncmDomain.archive_years}{$ncmDomain.archive_years} {if $ncmDomain.archive_years eq 1}year{else}years{/if}{else}<span class="text-muted">Off</span>{/if}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="{$ncmCard}">
        <div class="{$ncmCardHead}"><h3 class="{$ncmCardTitle}">DNS Records</h3></div>
        <div class="{$ncmCardBody}">
            <p class="ncm-muted">Set these at the domain's DNS host. For SPF, add <code>{$ncmDomain.spf_include|escape}</code> to the domain's SPF record, or create <code>v=spf1 {$ncmDomain.spf_include|escape} ~all</code> if it has none.</p>
        </div>
        <div class="ncm-scroll">
            <table class="table table-striped ncm-records">
                <thead><tr><th>Type</th><th>Name</th><th>Value</th></tr></thead>
                <tbody>
                    {foreach from=$ncmDomain.dns_records item=ncmRecord}
                        <tr>
                            <td>{$ncmRecord.type|escape}</td>
                            <td><code class="ncm-copy">{$ncmRecord.name|escape}</code></td>
                            <td><code class="ncm-copy ncm-wrap">{$ncmRecord.content|escape}</code></td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>
    </div>
</div>
