<?php

use WHMCS\Database\Capsule;
use GuzzleHttp\Client;

function namecranemail_MetaData() {

  return [
    'DisplayName'     => 'Namecrane Email',
    'APIVersion'      => '1.1',
    'RequiresServer'  => true
  ];

}

function namecranemail_ConfigOptions() {

  return [
    'Disk Space (GB)' => [ 
      'Type'          => 'text',
      'Size'          => '25',
      'Default'       => 1,
      'SimpleMode'    => true
    ],
    'Max Email Users' => [ 
      'Type'        => 'text',
      'Size'        => '8',
      'Description' => '0 = Unlimited',
      'Default'     => 0,      
      'SimpleMode'  => true
    ],
    'SpamExperts' => [ 
      'Type'    => 'dropdown',
      'Options' => [
        '0' => 'Disabled',
        '1' => 'Enabled'
      ],
      'SimpleMode' => true
    ],
    'Max User Aliases' => [ 
      'Type'        => 'text',
      'Size'        => '8',
      'Description' => '0 = Unlimited',
      'Default'     => 0,      
      'SimpleMode'  => true
    ],
    'SpamExperts Access' => [ 
      'Type'    => 'dropdown',
      'Options' => [
        'primary' => 'Primary Administrator Only',
        'all'     => 'All Domain Administrators'
      ],
      'SimpleMode' => true
    ],
    'Max Domain Aliases' => [ 
      'Type'        => 'text',
      'Size'        => '8',
      'Description' => '0 = Unlimited',
      'Default'     => 0,
      'SimpleMode'  => true,
    ],
    'Email Archiving (Years)' => [
      'Type'    => 'dropdown',
      'Options' => [
        '0' => 'Disabled',
        '1' => '1 Year',
        '2' => '2 Years',
        '3' => '3 Years',
        '4' => '4 Years',
        '5' => '5 Years',
        '6' => '6 Years',
        '7' => '7 Years',
        '8' => '8 Years',
        '9' => '9 Years',
        '10' => '10 Years',
        '15' => '15 Years',
        '20' => '20 Years'
      ],
      'SimpleMode' => true,
    ],    
    'Email Archiving Direction' => [
      'Type'    => 'dropdown',
      'Options' => [
        'in'    => 'Incoming',
        'out'   => 'Outgoing',
        'inout' => 'Both'
      ],
      'SimpleMode' => true,
    ],
    'File Storage' => [ 
      'Type'    => 'dropdown',
      'Options' => [
        '0' => 'Disabled',
        '1' => 'Enabled'
      ],
      'SimpleMode' => true
    ],
    'Office Suite' => [ 
      'Type'    => 'dropdown',
      'Options' => [
        '0' => 'Disabled',
        '1' => 'Enabled'
      ],
      'SimpleMode' => true
    ]
  ];

}

function namecranemail_CreateAccount($vars) {
  $post = [
    'domain'                  => $vars['domain'],
    'disklimit'               => (isset($vars['configoptions']['disklimit']) ? $vars['configoptions']['disklimit'] : $vars['configoption1']),
    'userlimit'               => (isset($vars['configoptions']['userlimit']) ? $vars['configoptions']['userlimit'] : $vars['configoption2']),
    'useraliaslimit'          => (isset($vars['configoptions']['useraliaslimit']) ? $vars['configoptions']['useraliaslimit'] : $vars['configoption4']),
    'spamexperts'             => (isset($vars['configoptions']['spamexperts']) ? $vars['configoptions']['spamexperts'] : $vars['configoption3']),
    'spamexperts_adminaccess' => $vars['configoption5'],
    'domainaliaslimit'        => (isset($vars['configoptions']['domainaliaslimit']) ? $vars['configoptions']['domainaliaslimit'] : $vars['configoption6']),
    'archive_years'           => (isset($vars['configoptions']['archive_years']) ? $vars['configoptions']['archive_years'] : $vars['configoption7']),
    'archive_direction'       => (isset($vars['configoptions']['archive_direction']) ? $vars['configoptions']['archive_direction'] : $vars['configoption8']),
    'filestorage'             => (isset($vars['configoptions']['filestorage']) ? $vars['configoptions']['filestorage'] : $vars['configoption9']),
    'office'                  => (isset($vars['configoptions']['office']) ? $vars['configoptions']['office'] : $vars['configoption10']),
  ];

  $return = namecranemail_execute('POST', 'domain/create', $vars, $post);
  
  if(!$return['status']) {
    return $return['message'];
  }
  
  localAPI('UpdateClientProduct', [
    'serviceid'         => $vars['serviceid'],
    'serviceusername'   => $return['data']['username'],
    'servicepassword'   => $return['data']['password'] 
  ]);

  return 'success';

}

function namecranemail_TerminateAccount($vars) {

  $post = [
    'domain'  => $vars['domain'],
  ];

  $return = namecranemail_execute('POST', 'domain/delete', $vars, $post);
  
  if(!$return['status']) {
    return $return['message'];
  }

  return 'success';

}

function namecranemail_SuspendAccount($vars) {

  $post = [
    'domain'  => $vars['domain'],
  ];

  $return = namecranemail_execute('POST', 'domain/suspend', $vars, $post);
  
  if(!$return['status']) {
    return $return['message'];
  }

  return 'success';

}

function namecranemail_UnsuspendAccount($vars) {

  $post = [
    'domain'  => $vars['domain'],
  ];

  $return = namecranemail_execute('POST', 'domain/unsuspend', $vars, $post);
  
  if(!$return['status']) {
    return $return['message'];
  }

  return 'success';

}

function namecranemail_ChangePackage($vars) {

  $post = [
    'domain'                  => $vars['domain'],
    'disklimit'               => (isset($vars['configoptions']['disklimit']) ? $vars['configoptions']['disklimit'] : $vars['configoption1']),
    'userlimit'               => (isset($vars['configoptions']['userlimit']) ? $vars['configoptions']['userlimit'] : $vars['configoption2']),
    'useraliaslimit'          => (isset($vars['configoptions']['useraliaslimit']) ? $vars['configoptions']['useraliaslimit'] : $vars['configoption4']),
    'spamexperts'             => (isset($vars['configoptions']['spamexperts']) ? $vars['configoptions']['spamexperts'] : $vars['configoption3']),
    'spamexperts_adminaccess' => $vars['configoption5'],
    'domainaliaslimit'        => (isset($vars['configoptions']['domainaliaslimit']) ? $vars['configoptions']['domainaliaslimit'] : $vars['configoption6']),
    'archive_years'           => (isset($vars['configoptions']['archive_years']) ? $vars['configoptions']['archive_years'] : $vars['configoption7']),
    'archive_direction'       => (isset($vars['configoptions']['archive_direction']) ? $vars['configoptions']['archive_direction'] : $vars['configoption8']),
    'filestorage'             => (isset($vars['configoptions']['filestorage']) ? $vars['configoptions']['filestorage'] : $vars['configoption9']),
    'office'                  => (isset($vars['configoptions']['office']) ? $vars['configoptions']['office'] : $vars['configoption10'])
  ];

  $return = namecranemail_execute('POST', 'domain/modify', $vars, $post);
  
  if(!$return['status']) {
    return $return['message'];
  }
  
  return 'success';

}

function namecranemail_AdminServicesTabFields($vars) {

  $post = [
    'domain' => $vars['domain']
  ];

  $stats = namecranemail_execute('POST', 'domain/info', $vars, $post);

  if(!$stats['status']) {
    $html = 'Couldn\'t get domain statistics.';
  } else {

    $smarty = new Smarty();
    $smarty->assign('info', $stats['data']['data']);

    $html = $smarty->fetch(__DIR__ . '/templates/adminoutput.tpl');

  }

  return [
    'Statistics' => $html
  ];
  
}

function namecranemail_ClientArea($vars) {

  $post = [
    'domain' => $vars['domain']
  ];

  $stats = namecranemail_execute('POST', 'domain/info', $vars, $post);

  if(!$stats['status']) {
    $error = 'Couldn\'t get domain statistics.';
  }
  
  return [
    'templatefile' => 'templates/clientarea',
    'vars' => [
      'vars'  => $vars,
      'error' => $error,
      'info'  => $stats['data']['data'],
      'dns'   => $stats['data']['data']['dns'],
      'dkim'  => $stats['data']['data']['dkim']
    ]
  ];

}

function namecranemail_ClientAreaCustomButtonArray(array $vars) {

  $return = [];

  if($vars['configoptions']['spamexperts'] || $vars['configoption3']) {
    $return['Login to SpamExperts'] = 'ssoSpamExperts';
  }

  return $return;

}

function namecranemail_AdminCustomButtonArray(array $vars) {

  $return = [];

  if($vars['configoptions']['spamexperts'] || $vars['configoption3']) {
    $return['Login to SpamExperts'] = 'adminSpamExpertsSSO';
  }

  return $return;

}

function namecranemail_getSpamExpertsSSO(array $vars) {

  $post = [
    'domain' => $vars['domain']
  ];

  return namecranemail_execute('POST', 'spamexperts/login', $vars, $post);

}

function namecranemail_adminSpamExpertsSSO(array $vars) {

  $sso = namecranemail_getSpamExpertsSSO($vars);

  if(!$sso['status']) {
    return 'Couldn\'t get authentication token.';
  }

  return 'window|' . $sso['data']['url'];

}

function namecranemail_ssoSpamExperts(array $vars) {

  $sso = namecranemail_getSpamExpertsSSO($vars);

  if(!$sso['status']) {
    return 'Couldn\'t get authentication token.';
  }

  header('Location: ' . $sso['data']['url']);

  exit();

}

function namecranemail_execute($method, $action, $vars, $post = []) {

  $guzzle = new Client();

  try {

    $return = $guzzle->request($method, 'https://namecrane.com/index.php?m=cranemail&action=api/' . $action, [
      'headers'     => [ 'X-API-KEY' => $vars['serveraccesshash'] ],
      'form_params' => ($method == 'POST' ? $post : [])
    ])->getBody();

    $return = json_decode($return, true);

    if(json_last_error() !== JSON_ERROR_NONE) {
      return ['status' => false, 'message' => 'Invalid JSON response from Namecrane. Ticket support (and blame Fran)' ];
    }

    logModuleCall('namecranemail', $action, $post, $return);
    return [ 'status' => $return['status'], 'message' => $return['message'], 'data' => $return ];

  } catch (\GuzzleHttp\Exception\RequestException $e) {    
    logModuleCall('namecranemail', $action, $post, $e);
    return [ 'status' => false, 'message' => $e->getMessage() ];
  } catch(\GuzzleHttp\Exception\GuzzleException $e) {
    logModuleCall('namecranemail', $action, $post, $e);
    return [ 'status' => false, 'message' => $e->getMessage() ];
  }

}

?>
