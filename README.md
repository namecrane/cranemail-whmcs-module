WHMCS module for selling NameCrane Mail email domains from your NameCrane reseller plan.

Works with WHMCS 7 and 8 (PHP 7.2 and up) and the `six`, `twenty-one` and Lagom client themes.

## Updating from 1.x

Upload the new files over the old ones, then empty WHMCS's template cache (**Utilities >
System > System Cleanup > Empty Template Cache**). Your products, servers and API key carry
over as they are. The 1.x module stops working at the end of November 30, 2026.

## Setup

1. In the NameCrane client portal, create an API key under **My Profile > API Keys**. Give it
   Full Access, or limit it to your CraneMail reseller service with read and write.
2. Upload `modules/servers/namecranemail` to your WHMCS install.
3. Add a **Server**, picking **NameCrane Mail** as the module:
   - **Hostname**: leave empty (it uses `namecrane.com`).
   - **Access Hash**: your API key.
   - **Username**: leave empty, unless the key covers more than one reseller plan. Then put
     the service ID of the plan this server is for (the number in its portal address).
   - Click **Test Connection**.
4. Create your products with **NameCrane Mail** as the module and set their options.

The API reference is at https://namecrane.com/docs/api.

## What clients get

On the product page, clients see their domain's status, the DNS records to set, and usage.
Unless you hide it (the **Client Mailbox Management** option), they can also manage:

- mailboxes: add, edit, change passwords, delete;
- aliases (forwarding addresses);
- domain aliases.

The webmail sign-in address is your own login hostname when you've set one up under Login
Portal in the NameCrane portal, otherwise workspace.org.

## Product options

| Option | Description |
| ------ | ----------- |
| Disk Space (GB) | Storage for the domain |
| Max Email Users | Mailboxes (0 = unlimited) |
| MXFilter Spam Filtering | Filter the domain's mail through MXFilter |
| Max User Aliases | Aliases (0 = unlimited) |
| MXFilter Access | Which domain administrators can open MXFilter from workspace.org |
| Max Domain Aliases | Domain aliases (0 = unlimited) |
| Email Archiving (Years) | How long to archive mail |
| Email Archiving Direction | Incoming, outgoing, or both |
| File Storage | Online file storage |
| Office Suite | Online document editing |
| Webmail Login | Lets the client open any mailbox's webmail without its password |
| MXFilter Login | Lets the client and your staff open MXFilter without a password |
| Client Mailbox Management | Shows or hides the Mailboxes, Aliases and Domain Aliases pages |

**Privacy (GDPR):** set **Webmail Login** and **MXFilter Login** to **Turned off** where only
the mailbox owner may open their mail. NameCrane then refuses those sign-ins for the domain
everywhere, including in your NameCrane portal.

## Order-time options (à la carte)

Configurable options override the product's settings. Name each option with the key below,
a pipe, and your own label, e.g. `userlimit|Email Accounts`. If you use them, set the matching
product options to 0 to avoid confusion.

| Option name | Type | Description |
| ----------- | ---- | ----------- |
| `disklimit` | quantity | Storage (GB) |
| `userlimit` | quantity | Mailboxes |
| `useraliaslimit` | quantity | Aliases |
| `domainaliaslimit` | quantity | Domain aliases |
| `spamexperts` | yesno | MXFilter spam filtering |
| `archive_years` | dropdown | Archiving years: 0 (off), 1 to 10, 15 or 20 |
| `archive_direction` | dropdown | `in`, `out` or `inout` |
| `filestorage` | yesno | Online file storage |
| `office` | yesno | Online document editing |

## Usage

WHMCS's daily usage update fills in each service's disk usage and limit.
