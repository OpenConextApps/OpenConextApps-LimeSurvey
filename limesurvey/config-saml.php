<?php
/*
 * Created on Jan 4, 2010
 * LimeSurvey SimpleSAMLphp autentication
 * Copyright (C) 2009 - SURFnet - www.surfnet.nl
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * 
 * This config implements the use of simplesamlphp (http://rnd.feide.no/simplesamlphp/) 
 * for authentication of users using SAML
 * 
 * SAML authentication is implemented using the already available '$useWebserverAuth' variable.
 * Current configuration assumes simplesamlphp is installed in '/var/simplesamlphp'
 * 
 */


// This section is copied from config-defaults.php
 
// Enable delegation of authentication to the webserver.
// If you set this parameter to true and set your webserver to authenticate
// users accessing the /admin subdirectory, then the username returned by
// the webserver will be trusted by LimeSurvey and used for authentication
// unless a username mapping is used see $userArrayMap below
//
// The user still needs to be defined in the limesurvey database in order to
// login and get his permissions (unless $WebserverAuth_autocreateUser is set to true)
$useWebserverAuth = true;
    
    
// Configure authmethod to be SAML
$samlEnabled = true;
    
    
//
// $userArrayMap
// Enable username mapping
// This parameter is an array mapping username from the webserver to username
// defined in LimeSurvey
// Can be usefull if you have no way to add an 'admin' user to the database
// used by the webserver, then you could map your true loginame to admin with
$userArrayMap = Array ('urn:collab:person:test.surfguest.nl:mdobrinic' => 'admin');
//
// $WebserverAuth_autocreateUser
// Enable this if you want to automatically create users authenticated by the 
// webserver in LS
// Default is false (commenting this options also means false)
$WebserverAuth_autocreateUser = true;
//
// $WebserverAuth_autouserprofile
// This parameter MUST be defined if you set $WebserverAuth_autocreateUser to true
// otherwise autocreateUser will be disabled.
// This is an array describing the default profile to use for auto-created users
// This profile will be the same for all users (unless you define the optionnal 
// 'hook_get_autouserprofile' function).
// 
//$WebserverAuth_autouserprofile = Array(
//					'full_name' => 'autouser',
//					'email' => $siteadminemail,
//					'lang' => 'en',
//					'htmleditormode' => $defaulthtmleditormode,
//					'templatelist' => 'default,basic',
//					'create_survey' => 1,
//					'create_user' => 0,
//					'delete_user' => 0,
//					'superadmin' => 0,
//					'configurator' =>0,
//					'manage_template' => 0,
//					'manage_label' => 0);
//
//
// The optionnal 'hook_get_autouserprofile' function
// is for advanced user usage only.
// It is used to customize the profile of the imported user
// If set, the this function will overwrite the $WebserverAuth_autouserprofile
// defined above by its return value
// 
// You can use any external DB in order to fill the profile for the user_name
// passed as the first parameter
// A dummy example for the 'hook_get_autouserprofile' function is given
// below:
//function hook_get_autouserprofile($user_name)
//{
//	return Array(
//			'full_name' => '$user_name',
//			'email' => "$user_name@localdomain.org",
//			'lang' => 'en',
//			'htmleditormode' => 'inline',
//			'templatelist' => 'default,basic,MyOrgTemplate',
//			'create_survey' => 1,
//			'create_user' => 0,
//			'delete_user' => 0,
//			'superadmin' => 0,
//			'configurator' =>0,
//			'manage_template' => 0,
//			'manage_label' => 0);
//}			



// Handle SAML authentication stuff using simplesamlphp

// Include simpleSAMLcodebase
define('SIMPLESAML_PATH', '/var/www/simplesaml');
// define('SIMPLESAML_PATH', '/Users/dopey/Projects/SURFnet/LimeSurvey/Workspace/simplesaml');
require_once(SIMPLESAML_PATH . '/lib/_autoload.php');

$a = $_SERVER['REQUEST_URI'];
$inadmin = ( strstr($a, "/admin") !== FALSE ? true : false);

if ($inadmin && $samlEnabled) {
    // Use the default sp setting as defined in simplesamlphp
    $as = new SimpleSAML_Auth_Simple('default-sp');

    // force authentication against remote IdP
    $as->requireAuth();

    // Get attributes provided by the IdP
    $attributes = $as->getAttributes();

    // Get a unique ID for the user.
    // If you are using standard eduPerson attributes, you may want to use ['urn:mace:dir:attribute-def:eduPersonPrincipalName']
    $user_id = $attributes['NameID'][0];

    // Set the $user_id in the server scope; this allows limesurvey to detect the user is already authenticated
    $_SERVER['REMOTE_USER'] = $user_id;
}

// Use the hook_get_autouserprofile funciton to provision users on the fly
// 
function hook_get_autouserprofile($user_id)
{

  // Define some layout and formatting defaults for the user
  $layoutDefaults = Array(
			'lang' => 'en',
			'htmleditormode' => 'inline',
			'templatelist' => 'default,basic,MyOrgTemplate');

  // Get roles from the groupmanager to determine roles & rights
  $userRights = getGroupRoles($user_id);

  
  // Get the federation attributes and map these to limesurvey attributes
  global $as;
  
  $attributes = $as->getAttributes();
  
  // Use 'urn:mace:dir:attribute-def:displayName' if this is not available, use ['urn:mace:dir:attribute-def:eduPersonPrincipalName']
  if (strlen($attributes['urn:mace:dir:attribute-def:displayName'][0]) != 0) {
  	$user_name = $attributes['urn:mace:dir:attribute-def:displayName'][0];
  } else {
  	$user_name = $attributes['urn:mace:dir:attribute-def:eduPersonPrincipalName'][0];
  }
  
  // Use email attribute to fill email settings; note that an email adress is required for limesurvey
  $user_email = $attributes['urn:mace:dir:attribute-def:mail'][0];
  
  // Use instellingafkorting (SURFfederatie) to provide an organisation
  $org = $attributes['urn:mace:terena.org:attribute-def:schacHomeOrganization'][0];

  $userAttributes =  Array(
			'full_name' => $user_name . " (".$org.")",
			'email' => $user_email);
			
  return array_merge($userAttributes, $userRights, $layoutDefaults)	;
}

// This is a placeholder function that can query a groupmanager such as Grouper (http://www.internet2.edu/grouper/)
// to determine the roles and set the rights of the user accordingly 
function getGroupRoles($user_name) {
	
	return Array('create_survey' => 1,
			     'create_user' => 0,
			     'delete_user' => 0,
			     'superadmin' => 0,
			     'configurator' =>0,
			     'manage_template' => 0,
			     'manage_label' => 0);

}
 
?>
