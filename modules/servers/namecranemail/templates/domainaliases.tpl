<div class="ncm ncm-{$template|escape}">
    {assign var="ncmPage" value="domainaliases"}
    {include file="$ncmPartials/header.tpl"}

    {if $ncmListError}
        <div class="alert alert-info">{$ncmListError|escape}</div>
    {else}
        <div class="{$ncmCard}">
            <div class="{$ncmCardHead}"><h3 class="{$ncmCardTitle}">Add a Domain Alias</h3></div>
            <div class="{$ncmCardBody}">
                <p class="ncm-help ncm-intro">A domain alias gets the same mailboxes under another domain: with <strong>example.net</strong> as an alias, mail to jane@example.net reaches jane@{$ncmDomainName|escape}. Point its MX records here like the main domain's.</p>
                <form method="post" action="{$ncmBaseUrl}&amp;customAction=domainaliases" class="ncm-row-form">
                    <input type="hidden" name="token" value="{$token}">
                    <input type="hidden" name="op" value="create">
                    <label for="ncm-name" class="sr-only">Domain</label>
                    <input id="ncm-name" name="name" class="form-control" value="{$ncmForm.name|escape}" placeholder="example.net" required autocomplete="off">
                    <button type="submit" class="btn btn-primary">Add Domain Alias</button>
                </form>
            </div>
        </div>

        <div class="{$ncmCard}">
            <div class="{$ncmCardHead}">
                <h3 class="{$ncmCardTitle}">Domain Aliases <span class="ncm-count">{count($ncmDomainAliases)} of {if $ncmDomain.domain_alias_limit}{$ncmDomain.domain_alias_limit}{else}unlimited{/if}</span></h3>
            </div>
            {if $ncmDomainAliases}
                <table class="table table-striped ncm-list">
                    <tbody>
                        {foreach $ncmDomainAliases as $ncmAlias}
                            <tr>
                                <td><strong>{$ncmAlias|escape}</strong></td>
                                <td class="ncm-right">
                                    <form method="post" action="{$ncmBaseUrl}&amp;customAction=domainaliases" class="ncm-inline" onsubmit="return confirm('Remove {$ncmAlias|escape:'javascript'|escape}? Mail to it stops arriving.');">
                                        <input type="hidden" name="token" value="{$token}">
                                        <input type="hidden" name="op" value="delete">
                                        <input type="hidden" name="original" value="{$ncmAlias|escape}">
                                        <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        {/foreach}
                    </tbody>
                </table>
            {else}
                <div class="{$ncmCardBody}"><p class="text-muted ncm-muted">No domain aliases yet.</p></div>
            {/if}
        </div>
    {/if}
</div>
