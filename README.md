# Egoi Email Marketing for Prestashop 1.7.X (V.2.0.0)

Keep your contacts sync with E-goi and increase your sales! Easily design email marketing or SMS campaigns, create the segmentation you want for your contacts or build intuitive automation processes for all channels available on E-goi.

## Getting Started

Clone and code

### Prerequisites

Php enabled server / Mysql enabled server

- SoapClient
- Curl lib

### Installing and Deploy

For install, clone the repo, rename folder to smartmarketingps 


And compress

```
$ zip -r smart-marketing-ps.1.7.vx.x.x.zip /home/you/smartmarketingps/
```

Upload to deploy server

### Track&Engage theme hook
If your theme is not calling the hook 'displayTop' the script injection will not work.
For a workaround you can add this hook call to your **header.tpl**
```
{hook e='egoiDisplayTE'}
```

### Changelog
```

-- V.2.0.0
   -- Deprecate old forms. iframe and popup forms are now created an shown by using connected sites.
   -- Contact sync (Newsletter subscriber fix)
   -- Deprecate Remarketing option

-- V.1.6.15
   -- Improve Connected Sites flow
   -- Team Permissions
   -- New version notify

-- V.1.6.14
   -- Connected Sites Integration

-- V.1.6.13
   -- Fixed installation errors
   -- Minor fix on Ifthenpay sms

-- V.1.6.12
   -- EuPago sms fix
   -- Senders minor fix

-- V.1.6.11
   -- Synchronization cron performance improvements

-- V.1.6.10
   -- Select conversion state Track&Engage

-- V.1.6.9
   -- Customer info sync with execution window

-- V.1.6.8
   -- Backend order conversion (fix 1.7.7.x prestashop issue #18789)

-- V.1.6.7
   -- Add cellphone field to subscriber

-- V.1.6.6
   -- Better plugin permission flow 

-- V.1.6.5
   -- Failsafe on TE order conversion (modified thankyou page)

-- V.1.6.4
   -- Fix disable/enable menu bug

-- V.1.6.3
   -- Fix WebserviceRequest compatibility

-- V.1.6.2
   -- IfThenPay SMS upgrade

-- V.1.6.1
   -- Perform TE flow optimizations

-- V.1.6.0
   -- Added push notifications

-- V.1.5.6
   -- Bumped readme file
   -- Bumped plugin version
   -- Temporary fix on price and sale price

-- V.1.5.5
   -- Add readme file
   -- Add Changelog to readme file
   -- Bumped plugin version

```