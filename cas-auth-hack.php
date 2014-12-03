<?php
/**
 * PHP CAS Authentication hack for Dokuwiki
 *
 * @author piotr@polak.ro
 * @version 0.1
 * @date 2014.12.03
 * @url https://github.com/piotrpolak/docuwiki-cas-auth-hack
 *
 * Installation
 * 1. Copy folder /cas-auth-hack/ to wiki root directory
 * 2. Edit doku.php and include ./cas-auth-hack/cas-auth-hack.php in doku.php just before act_dispatch (see code below)
 * 3. Make sure to delete install.php
 * 4. Configure CAS host, port and path inside conf/dokuwiki.php (see Configuration section inside README.txt)
 *
 */

// CAS hack - important - should be included just after require_once(DOKU_INC.'inc/init.php');
//require_once(__DIR__.'/cas-auth-hack/cas-auth-hack.php');

// Checking if doku.php initialized
if( !defined('DOKU_INC') )
{
    exit("This script should be installed in doku.php only");
}

// Checking configuration
if( !isset($conf['cas']) || !isset($conf['cas']['host'])  || !isset($conf['cas']['port']) || !isset($conf['cas']['path']) )
{
    exit("CAS driver not configured. See ./cas-auth-hack/README.md");
}

// Doing the CAS job
require_once(__DIR__.'/CAS/CAS.php');
phpCAS::client(CAS_VERSION_2_0, $conf['cas']['host'], $conf['cas']['port'], $conf['cas']['path']);
phpCAS::setNoCasServerValidation();
phpCAS::forceAuthentication(); // This call prevents from the code below from being executed if the user is not authenticated

// Overwrite usermanager and profile action
if( ($_GET['do'] == 'admin' && $_GET['page'] == 'usermanager') || $_GET['do'] == 'profile' )
{
    exit('This page is blocked by CAS driver hack. <a href="./">Go back</a>'); // just in case
}

// Overwrite logout action
if( $_GET['do'] == 'logout' )
{
    auth_logoff();
    session_destroy();
    phpCAS::logout();

    exit(); // just in case
}

// Checking session state
$casHackForceLogin = !isset($_SESSION[DOKU_COOKIE]['auth']) || !$_SERVER['REMOTE_USER'] || $_GET['do'] == 'login';

// Only execute if the user is not yet logged in
if( $casHackForceLogin )
{
    $pass = md5(time().'-sand-sdfsdfgs9854-pseudo-random'.__DIR__);
    $casUser = phpCAS::getUser();
    $username = explode('@', $casUser);
    $username = $username[0];
    $fullname = ucwords(str_replace('.', ' ', $username));

    // TODO Check if the user already registered

    // Attempt to register the user locally
    if(!$auth->triggerUserMod('create', array($username, $pass, $fullname, $casUser))) {
        // ALREADY REGISTERED
        $auth->triggerUserMod('modify', array($username, array('pass' => $pass)));// Lets give it a try
    }

    $success = auth_login($username, $pass, false, true);

    if( !$success )
    {
        auth_logoff();
        session_destroy();
        phpCAS::logout();
        exit('Login failed. <a href="./">Try againk</a>');
    }

    // Redirect to apply all the changes
    $redirectUrl = 'doku.php';
    if( isset($_SERVER['REQUEST_URI']) )
    {
        $redirectUrl = $_SERVER['REQUEST_URI'];
    }
    elseif( $conf['userewrite'] )
    {
        $redirectUrl = './';
    }
    header('Location: '.$redirectUrl);
}

// Disabling actions
$disabledActions = 'profile';
if( $conf['disableactions'] )
{
    $conf['disableactions'] .= ',';
}
$conf['disableactions'] .= $disabledActions;
