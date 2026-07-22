<table style="margin: 20px" cellspacing="0" cellpadding="3" >
    <tbody>
        <tr>
        <td class="fieldarea">Domain Verified:</td>
            <td align="left">{if $info['verified']['status']}Yes{else}<strong>No</strong>{/if}</strong></td>
        </tr>
        <tr>
            <td class="fieldarea">Disk Space:</td>
            <td align="left">{$info['diskusage'] / 1024} GB / <strong>{$info['disklimit'] / 1024} GB</strong></td>
        <tr>
            <td class="fieldarea">Email Users:</td>
            <td align="left">{$info['usercount']} / <strong>{if $info['userlimit']}{$info['userlimit']}{else}&infin;{/if}</strong></td>
        </tr>
        <tr>
            <td class="fieldarea">Email Aliases:</td>
            <td align="left">{$info['useraliascount']} / <strong>{if $info['useraliaslimit']}{$info['useraliaslimit']}{else}&infin;{/if}</strong></td>
        </tr>
        <tr>
            <td class="fieldarea">Domain Aliases:</td>
            <td align="left">{$info['domainaliascount']} / <strong>{if $info['domainaliaslimit']}{$info['domainaliaslimit']}{else}&infin;{/if}</strong></td>
        </tr>
        <tr>
            <td class="fieldarea">File Storage:</td>
            <td align="left"><strong>{if $info['filestorage']}Enabled{else}Disabled{/if}</strong></td>
        </tr>
        <tr>
            <td class="fieldarea">Office Suite:</td>
            <td align="left"><strong>{if $info['office']}Enabled{else}Disabled{/if}</strong></td>
        </tr>
        <tr>
            <td class="fieldarea">MXFilter Status:</td>
            <td align="left"><strong>{if $info['spamexperts']}Enabled{else}Disabled{/if}</strong></td>
        </tr>
        <tr>
            <td class="fieldarea">Email Archives:</td>
            <td align="left"><strong>{if $info['archive_years']}Enabled{else}Disabled{/if}</strong></td>
        </tr>
        <tr>
            <td class="fieldarea">Archive Direction:</td>
            <td align="left"><strong>{if $info['archive_years']}{$info['archive_direction']}{else}N/A{/if}</strong></td>
        </tr>
        <tr>
            <td class="fieldarea">Last Updated:</td>
            <td align="left"><strong>{$info['lastupdated']}</strong></td>
        </tr>
    </tbody>
</table>
