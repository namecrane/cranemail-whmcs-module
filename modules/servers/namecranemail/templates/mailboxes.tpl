<div class="ncm ncm-{$template|escape}">
    {assign var="ncmPage" value="mailboxes"}
    {include file="$ncmPartials/header.tpl"}

    {if $ncmListError}
        <div class="alert alert-info">{$ncmListError|escape}</div>
    {else}
        <div class="{$ncmCard}">
            <div class="{$ncmCardHead}">
                <h3 class="{$ncmCardTitle}">{if $ncmEditing}Edit {$ncmEditing.username|escape}@{$ncmDomainName|escape}{else}Add a Mailbox{/if}</h3>
            </div>
            <div class="{$ncmCardBody}">
                <form method="post" action="{$ncmBaseUrl}&amp;customAction=mailboxes{if $ncmEditing}&amp;edit={$ncmEditing.username|escape:'url'}{/if}">
                    <input type="hidden" name="token" value="{$token}">
                    <input type="hidden" name="op" value="{if $ncmEditing}update{else}create{/if}">
                    {if $ncmEditing}<input type="hidden" name="original" value="{$ncmEditing.username|escape}">{/if}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="ncm-name">Email address</label>
                                <div class="ncm-address">
                                    <input id="ncm-name" name="name" class="form-control" value="{$ncmForm.name|escape}" {if $ncmEditing}disabled{else}required{/if} autocomplete="off">
                                    <span>@{$ncmDomainName|escape}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="ncm-display-name">Display name</label>
                                <input id="ncm-display-name" name="display_name" class="form-control" value="{$ncmForm.display_name|escape}" maxlength="255">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="ncm-password">{if $ncmEditing}New password{else}Password{/if}</label>
                                <div class="input-group" data-ncm-password>
                                    <input id="ncm-password" name="password" type="password" class="form-control ncm-mono" value="{$ncmForm.password|escape}" {if !$ncmEditing}required{/if} minlength="8" autocomplete="new-password">
                                    <span class="{$ncmInputButtons}">
                                        <button type="button" class="btn btn-default" data-ncm-toggle title="Show password" aria-label="Show password" aria-pressed="false">
                                            <svg class="ncm-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                                        </button>
                                        <button type="button" class="btn btn-default" data-ncm-generate title="Generate a password" aria-label="Generate a password">
                                            <svg class="ncm-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                                        </button>
                                    </span>
                                </div>
                                <p class="ncm-help">{if $ncmEditing}Leave empty to keep the current password.{else}At least 8 characters. Share it with the mailbox's owner.{/if}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="ncm-quota">Quota (MB)</label>
                                <input id="ncm-quota" name="disk_limit" type="number" min="0" class="form-control" value="{$ncmForm.disk_limit|escape}">
                                <p class="ncm-help">0 uses the domain's default.</p>
                            </div>
                        </div>
                    </div>
                    <label class="ncm-check"><input type="checkbox" name="admin" value="1"{if $ncmForm.admin} checked{/if}> Domain administrator (can manage the domain from webmail)</label>
                    {if $ncmEditing}
                        <label class="ncm-check"><input type="checkbox" name="enabled" value="1"{if $ncmForm.enabled} checked{/if}> Enabled</label>
                    {/if}
                    <div class="ncm-form-actions">
                        <button type="submit" class="btn btn-primary">{if $ncmEditing}Save Mailbox{else}Add Mailbox{/if}</button>
                        {if $ncmEditing}<a href="{$ncmBaseUrl}&amp;customAction=mailboxes" class="btn btn-default">Cancel</a>{/if}
                    </div>
                </form>
            </div>
        </div>

        <div class="{$ncmCard}">
            <div class="{$ncmCardHead}">
                <h3 class="{$ncmCardTitle}">Mailboxes <span class="ncm-count">{count($ncmUsers)} of {if $ncmDomain.user_limit}{$ncmDomain.user_limit}{else}unlimited{/if}</span></h3>
            </div>
            {if $ncmUsers}
                <div class="ncm-scroll">
                    <table class="table table-striped ncm-list">
                        <thead><tr><th>Address</th><th>Name</th><th>Usage</th><th></th></tr></thead>
                        <tbody>
                            {foreach $ncmUsers as $ncmUser}
                                <tr>
                                    <td>
                                        <strong>{$ncmUser.username|escape}@{$ncmDomainName|escape}</strong>
                                        {if $ncmUser.primary_admin}<span class="{$ncmBadge}info">Primary admin</span>{elseif $ncmUser.admin}<span class="{$ncmBadge}info">Admin</span>{/if}
                                        {if !$ncmUser.enabled}<span class="{$ncmBadge}warning">Disabled</span>{/if}
                                    </td>
                                    <td>{$ncmUser.display_name|escape}</td>
                                    <td class="ncm-nowrap">{if $ncmUser.disk_limit}{$ncmUser.disk_usage} MB of {$ncmUser.disk_limit} MB{else}<span class="text-muted">Domain's limit</span>{/if}</td>
                                    <td class="ncm-right ncm-nowrap">
                                        {if $ncmWebmailLogin && $ncmUser.enabled}
                                            <a href="{$ncmBaseUrl}&amp;customAction=webmail&amp;mailbox={$ncmUser.username|escape:'url'}" target="_blank" rel="noopener" class="btn btn-default btn-sm">Webmail</a>
                                        {/if}
                                        <a href="{$ncmBaseUrl}&amp;customAction=mailboxes&amp;edit={$ncmUser.username|escape:'url'}" class="btn btn-default btn-sm">Edit</a>
                                        {if !$ncmUser.primary_admin}
                                            <form method="post" action="{$ncmBaseUrl}&amp;customAction=mailboxes" class="ncm-inline" onsubmit="return confirm('Delete {$ncmUser.username|escape:'javascript'|escape}@{$ncmDomainName|escape:'javascript'|escape} and all of its mail? This can\'t be undone.');">
                                                <input type="hidden" name="token" value="{$token}">
                                                <input type="hidden" name="op" value="delete">
                                                <input type="hidden" name="original" value="{$ncmUser.username|escape}">
                                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                            </form>
                                        {/if}
                                    </td>
                                </tr>
                            {/foreach}
                        </tbody>
                    </table>
                </div>
            {else}
                <div class="{$ncmCardBody}"><p class="text-muted ncm-muted">No mailboxes yet.</p></div>
            {/if}
        </div>
    {/if}
</div>
