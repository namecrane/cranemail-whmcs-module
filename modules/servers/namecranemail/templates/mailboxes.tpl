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
                                <input id="ncm-password" name="password" type="text" class="form-control ncm-mono" value="{$ncmForm.password|escape}" {if !$ncmEditing}required{/if} minlength="8" autocomplete="new-password">
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
                                    <td class="ncm-nowrap">{$ncmUser.disk_usage} MB{if $ncmUser.disk_limit} of {$ncmUser.disk_limit} MB{/if}</td>
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
