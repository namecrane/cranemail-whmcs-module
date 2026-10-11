<div class="ncm ncm-{$template|escape}">
    {assign var="ncmPage" value="aliases"}
    {include file="$ncmPartials/header.tpl"}

    {if $ncmListError}
        <div class="alert alert-info">{$ncmListError|escape}</div>
    {else}
        <div class="{$ncmCard}">
            <div class="{$ncmCardHead}">
                <h3 class="{$ncmCardTitle}">{if $ncmEditing}Edit {$ncmEditing.username|escape}@{$ncmDomainName|escape}{else}Add an Alias{/if}</h3>
            </div>
            <div class="{$ncmCardBody}">
                <p class="ncm-help ncm-intro">An alias is an address with no mailbox of its own: mail sent to it goes on to the addresses you list.</p>
                <form method="post" action="{$ncmBaseUrl}&amp;customAction=aliases{if $ncmEditing}&amp;edit={$ncmEditing.username|escape:'url'}{/if}">
                    <input type="hidden" name="token" value="{$token}">
                    <input type="hidden" name="op" value="{if $ncmEditing}update{else}create{/if}">
                    {if $ncmEditing}<input type="hidden" name="original" value="{$ncmEditing.username|escape}">{/if}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="ncm-name">Alias address</label>
                                <div class="ncm-address">
                                    <input id="ncm-name" name="name" class="form-control" value="{$ncmForm.name|escape}" required autocomplete="off">
                                    <span>@{$ncmDomainName|escape}</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="ncm-display-name">Display name</label>
                                <input id="ncm-display-name" name="display_name" class="form-control" value="{$ncmForm.display_name|escape}" maxlength="255">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="ncm-targets">Deliver to</label>
                                <textarea id="ncm-targets" name="targets" class="form-control ncm-mono" rows="4" placeholder="jane@{$ncmDomainName|escape}&#10;sales@example.com">{$ncmForm.targets|escape}</textarea>
                                <p class="ncm-help">One address per line.</p>
                            </div>
                        </div>
                    </div>
                    {foreach $ncmFeatures as $ncmFeature => $ncmFeatureLabel}
                        <label class="ncm-check"><input type="checkbox" name="feature_{$ncmFeature}" value="1"{if $ncmForm.features[$ncmFeature]} checked{/if}> {$ncmFeatureLabel|escape}</label>
                    {/foreach}
                    <div class="ncm-form-actions">
                        <button type="submit" class="btn btn-primary">{if $ncmEditing}Save Alias{else}Add Alias{/if}</button>
                        {if $ncmEditing}<a href="{$ncmBaseUrl}&amp;customAction=aliases" class="btn btn-default">Cancel</a>{/if}
                    </div>
                </form>
            </div>
        </div>

        <div class="{$ncmCard}">
            <div class="{$ncmCardHead}">
                <h3 class="{$ncmCardTitle}">Aliases <span class="ncm-count">{count($ncmAliases)} of {if $ncmDomain.user_alias_limit}{$ncmDomain.user_alias_limit}{else}unlimited{/if}</span></h3>
            </div>
            {if $ncmAliases}
                <div class="ncm-scroll">
                    <table class="table table-striped ncm-list">
                        <thead><tr><th>Address</th><th>Delivers to</th><th></th></tr></thead>
                        <tbody>
                            {foreach $ncmAliases as $ncmAlias}
                                <tr>
                                    <td>
                                        <strong>{$ncmAlias.username|escape}@{$ncmDomainName|escape}</strong>
                                        {if $ncmAlias.features.catch_all}<span class="{$ncmBadge}info">Catch-all</span>{/if}
                                        {if $ncmAlias.display_name}<div class="text-muted ncm-muted">{$ncmAlias.display_name|escape}</div>{/if}
                                    </td>
                                    <td class="ncm-targets">{foreach $ncmAlias.targets as $ncmTarget}<div>{$ncmTarget|escape}</div>{/foreach}</td>
                                    <td class="ncm-right ncm-nowrap">
                                        <a href="{$ncmBaseUrl}&amp;customAction=aliases&amp;edit={$ncmAlias.username|escape:'url'}" class="btn btn-default btn-sm">Edit</a>
                                        <form method="post" action="{$ncmBaseUrl}&amp;customAction=aliases" class="ncm-inline" onsubmit="return confirm('Delete the alias {$ncmAlias.username|escape:'javascript'|escape}@{$ncmDomainName|escape:'javascript'|escape}?');">
                                            <input type="hidden" name="token" value="{$token}">
                                            <input type="hidden" name="op" value="delete">
                                            <input type="hidden" name="original" value="{$ncmAlias.username|escape}">
                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            {/foreach}
                        </tbody>
                    </table>
                </div>
            {else}
                <div class="{$ncmCardBody}"><p class="text-muted ncm-muted">No aliases yet.</p></div>
            {/if}
        </div>
    {/if}
</div>
