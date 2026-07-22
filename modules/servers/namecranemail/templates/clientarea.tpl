{if $error}
<div class="alert alert-danger alert-block">
  {$error}
</div>
{else}  

{if $verified['status']}
<div class="row">
  <div class="col-12 text-right">
      {if $info['spamexperts']}
      <a href="clientarea.php?action=productdetails&id={$serviceid}&modop=custom&a=ssoMXFilter" class="btn btn-success" target="_blank">Login to MXFilter</a>
      {/if}
      <a href="https://workspace.org" class="btn btn-primary" target="_blank">Open Login Page</a>
  </div>
</div>
{/if}

<ul class="nav nav-tabs">
  {if $verified['status']}
  <li class="nav-item">
    <a class="nav-link active" href="#" data-toggle="tab" data-target="#nav-resources" role="tab" aria-controls="nav-home" {if $verified['status']}aria-selected="true"{/if}>Resource Usage</a>
  </li>
  {/if}
  <li class="nav-item">
    <a class="nav-link {if !$verified['status']}active{/if}" href="#" data-toggle="tab" data-target="#nav-dns" role="tab" aria-controls="nav-home" {if !$verified['status']}aria-selected="true"{/if}>DNS Records</a>
  </li>
  {if $dkim && $verified['status']}
  <li class="nav-item">
    <a class="nav-link" href="#" data-toggle="tab" data-target="#nav-dkim" role="tab" aria-controls="nav-home">DKIM Records</a>
  </li>
  {/if}
</ul>

<div class="tab-content" id="nav-tabContent">
  {if $verified['status']}
  <div class="tab-pane fade show active" id="nav-resources" role="tabpanel" style="padding-top: 10px;">
    <div class="row">
      <div class="col-6">
        <table cellspacing="0" cellpadding="3">
          <thead>
            <th>Resource</th>
            <th>Usage / Limit</th>
          </thead>
          <tbody>
            <tr>
              <td class="fieldarea">Disk Space</td>
              <td>{$info['diskusage'] / 1024} GB / <strong>{$info['disklimit'] / 1024} GB</strong></td>
            </tr>
            <tr>
              <td class="fieldarea">Email Users</td>
              <td>{$info['usercount']} / <strong>{if $info['userlimit']}{$info['userlimit']}{else}&infin;{/if}</strong></td>
            </tr>
            <tr>
              <td class="fieldarea">Email Aliases</td>
              <td>{$info['useraliascount']} / <strong>{if $info['useraliaslimit']}{$info['useraliaslimit']}{else}&infin;{/if}</strong></td>
            </tr>
            <tr>
              <td class="fieldarea">Domain Aliases</td>
              <td>{$info['domainaliascount']} / <strong>{if $info['domainaliaslimit']}{$info['domainaliaslimit']}{else}&infin;{/if}</strong></td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="col-6">
        <table cellspacing="0" cellpadding="3">
          <thead>
            <th>Feature</th>
            <th>Status</th>
          </thead>
          <tbody>
            <tr>
              <td class="fieldarea">MXFilter Anti Spam</td>
              <td><strong>{if $info['spamexperts']}Enabled{else}Disabled{/if}</strong></td>
            </tr>
            <tr>
              <td class="fieldarea">File Storage</td>
              <td><strong>{if $info['filestorage']}Enabled{else}Disabled{/if}</strong></td>
            </tr>
            <tr>
              <td class="fieldarea">Office Suite</td>
              <td><strong>{if $info['office']}Enabled{else}Disabled{/if}</strong></td>
            </tr>
            <tr>
              <td class="fieldarea">Email Archiving</td>
              <td><strong>{if $info['archive_years']}Enabled{else}Disabled{/if}</strong></td>
            </tr>
            <tr>
              <td class="fieldarea">Archive Direction</td>
              <td>
                <strong>
                {if $info['archive_direction'] == 'in'}
                  Incoming Only
                {elseif $info['archive_direction'] == 'out'}
                  Outgoing Only
                {elseif $info['archive_direction'] == 'inout'}
                  Incoming + Outgoing
                {/if}
                </strong>
              </td>              
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  {/if}
  <div class="tab-pane fade {if !$verified['status']}show active{/if}" id="nav-dns" role="tabpanel" style="padding-top: 10px;">

    {if !$verified['status']}

    <br />

    <div class="alert alert-danger">
      You must verify you own this domain before it is fully functional.
    </div>
    {/if}

    <table cellspacing="0" cellpadding="3">
      <thead>
        <th>Type</th>
        <th>Name / Host</th>
        <th>Value</th>
      </thead>
      <tbody>
      {if !$verified['status']}
      <tr>
        <td>TXT</td>
        <td>workspace-verification.{$vars['domain']}.</td>
        <td>
          <code>{$verified['txt']}</code>
        </td>
      </tr>
      {/if}
      {foreach from=$dns item=$record}
        <tr>
          <td>{$record['type']}</td>
          <td>{$record['record']}{if $record['record'] == '@'} or {$vars['domain']}.{/if}</td>
          <td>
            <code>{$record['value']}</code>
            {if $record['prio']} (priority: <code>{$record['prio']})</code>{/if}    
          </td>
        </tr>
      {/foreach}
      </tbody>
    </table>
    
  </div>
  {if $dkim && $verified['status']}
  <div class="tab-pane fade" id="nav-dkim" role="tabpanel" style="padding-top: 10px;">
    <table cellspacing="0" cellpadding="3">
      <thead>
        <th>Type</th>
        <th>Name / Host</th>
        <th>Value</th>
      </thead>
      <tbody>
        <tr>
          <td>TXT</td>
          <td>{$dkim['selector']}</td>
          <td>
            <textarea class="form-control" rows="12" disabled>v=DKIM1;k=rsa;h={$dkim['algo']};p={$dkim['key']}</textarea>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
  {/if}
</div>

{/if}
