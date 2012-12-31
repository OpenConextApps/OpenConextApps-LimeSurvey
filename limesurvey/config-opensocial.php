<?php
/**
 * Configuration instance to specify how openSocial group support
 * is performed
 *
 * mdobrinic@cozmanova.com
 *
 **/

    $grouprel_config = array(
		/* cache_ttl: defines how many seconds a fetched instance is cached */
		'cache_ttl' => 2,

		/* userIdAttribute: the userId-attribute to use as (external) userId in openSocial calls */
		'userIdAttribute' => 'NameID',	// set by NameIDAttribute-module in SSP	-- OpenSocial UserID, Grouper UserID

	/* impl: defines a configuration for the actual fetching code */
    	'impl' => array(
			/* class: Worker class instance of IGroupRelations, used to retrieve Group relations */
			'class' => 'OpenSocial_GroupRelationsImpl',
			
			/* configuration for the worker class; see documentation below */
			'consumerkey' => '-insert-key-here-',
			'consumersecret' => '-insert-secret-here-',
			'provider' => array(
				'providerName' => 'conext',
				'class' => 'osapiGroupRelProvider',
				'requestTokenUrl' => 'https://api.surfconext.nl/v1/oauth1/requestToken',
				'authorizeUrl' => 'https://api.surfconext.nl/v1/oauth1/confirm_access',
				'accessTokenUrl' => 'https://api.surfconext.nl/v1/oauth1/accessToken', 
				'restEndpoint' => 'https://api.surfconext.nl/v1/social/rest',
				'rpcEndpoint' => '',
				),
			'strictMode' => FALSE,
    	),
    );

/*
Worker class configuration:
The OpenSocial_GroupRelationsImpl uses the OpenSocial API to retrieve group information.
This depends on OAuth for authorizing the client calls. To configure the OpenSocial_GroupRelationsImpl
implementation, the following options need to be configured:
'consumerkey' : consumerkey that was agreed with OpenSocial provider
'consumersecret' : consunersecret that was agreed with OpenSocial provider
'provider' : array of configuation options of the OpenSocial provider:
  'providerName' : (internal) name to identify the provider
  'class' : class that implements the provider; set value to 'osapiGroupRelProvider'
  'requestTokenUrl',
  'authorizeUrl', 
  'AccessTokenUrl' : the OAuth URL's to establish a user access token
  'restEndpoint',
  'rpcEndpoint' : the endpoints to make the actual calls to
'strictMode' : whether or not the OpenSocial API operates in strict mode (default: false)
*/
?>
