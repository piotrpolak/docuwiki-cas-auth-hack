# Dokuwiki CAS autentication hack

Docuwiki alternative CAS autentication driver

cas-auth-hack.php works by overwriting (login, logout) or blocking (admin, profile, usermanager) default Dokuwiki actions.

## Instalation

1. Copy folder /cas-auth-hack/ to wiki root directory
2. Edit doku.php and include ./cas-auth-hack/cas-auth-hack.php in doku.php just after require_once(DOKU_INC.'inc/init.php'); (see code below)
3. Make sure to delete install.php
4. Configure CAS host, port and path inside conf/dokuwiki.php (see Configuration section)

```php
// File docu.php
// CAS hack - important - should be included before act_dispatch
//require_once(__DIR__.'/cas-auth-hack/cas-auth-hack.php');
```

### Example

```php
// File doku.php
// (...)

// load and initialize the core system
require_once(DOKU_INC.'inc/init.php');

// CAS hack - important - should be included before act_dispatch
require_once(__DIR__.'/cas-auth-hack/cas-auth-hack.php');
// (...)
```

## Configuration

Edit conf/dokuwiki.php and specify the following values:

```php
// CAS driver settings
$conf['cas']['host'] = 'cas.example.com';
$conf['cas']['port'] = 443;
$conf['cas']['path'] = '/cas';
```