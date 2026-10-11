{* Every page's top: the theme's class names (six and Lagom are Bootstrap 3, twenty-one is
   Bootstrap 4), the module's stylesheet, the page tabs and any notice. *}
{if $template eq 'twenty-one'}
    {assign var="ncmCard" value="card ncm-card" scope="parent"}
    {assign var="ncmCardHead" value="card-header" scope="parent"}
    {assign var="ncmCardTitle" value="card-title" scope="parent"}
    {assign var="ncmCardBody" value="card-body" scope="parent"}
    {assign var="ncmBadge" value="badge badge-" scope="parent"}
{else}
    {assign var="ncmCard" value="panel panel-default ncm-card" scope="parent"}
    {assign var="ncmCardHead" value="panel-heading" scope="parent"}
    {assign var="ncmCardTitle" value="panel-title" scope="parent"}
    {assign var="ncmCardBody" value="panel-body" scope="parent"}
    {assign var="ncmBadge" value="label label-" scope="parent"}
{/if}
<link rel="stylesheet" href="{$WEB_ROOT}/modules/servers/namecranemail/assets/css/namecranemail.css?v={$ncmVersion}">

{if $ncmManage}
    <ul class="nav nav-tabs ncm-tabs">
        {foreach from=['overview' => 'Overview', 'mailboxes' => 'Mailboxes', 'aliases' => 'Aliases', 'domainaliases' => 'Domain Aliases'] key=ncmTab item=ncmTabLabel}
            <li class="nav-item{if $ncmPage eq $ncmTab} active{/if}">
                <a class="nav-link{if $ncmPage eq $ncmTab} active{/if}" href="{$ncmBaseUrl}{if $ncmTab neq 'overview'}&amp;customAction={$ncmTab}{/if}">{$ncmTabLabel}</a>
            </li>
        {/foreach}
    </ul>
{/if}

{if $ncmNotice}
    <div class="alert alert-success">{$ncmNotice|escape}</div>
{/if}
{if $ncmFormError}
    <div class="alert alert-danger">{$ncmFormError|escape}</div>
{/if}
