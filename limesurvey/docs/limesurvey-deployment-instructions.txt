LimeSurvey - Deployment instructions
--------------------------------------------------------
2011/10/24; mdobrinic

LimeSurvey is installed on an Ubuntu 11.04 virtual machine.
The following installation instructions are the guideline of the LimeSurvey installation procedure: http://docs.limesurvey.org/Installation&structure=English+Instructions+for+LimeSurvey
LimeSurvey will be installed in /var/www/limesurvey.conext.surfnetlabs.nl

* Installing pre-requisites
$ sudo apt-get install php5-gd php5-imap sqlite php5-sqlite php5-curl

* Configuration of SSL host
- create ssl site definition in /etc/apache2/sites-available/limesurvey.conext.surfnetlabs.nl
  (the 'limesurvey.conext.surfnetlabs.nl file is made available)
- copy key-, certificate- and certificate-chain files to /usr/local/etc/ssl
  /usr/local/etc/ssl/star.conext.surfnetlabs.nl.CHAINED.pem
  /usr/local/etc/ssl/star.conext.surfnetlabs.nl.key
  
  (where the CHAINED.pem file is created like: cat server-cert.pem cert-chain.pem >> CHAINED.pem)
  
- add "Redirect permanent / https://limesurvey.conext.surfnetlabs.nl/" statement in /etc/apache2/sites-available/default
  (the 'default' site-definition file is available)
- enable this site with s2ensite limesurvey.conext.surfnetlabs.nl
- restart apache with “/etc/init.d/apache2 reload”
- check logs for correct startup with "tail /var/log/apache2/error.log"


* Create database user and database for LimeSurvey application
$ mysql -u root -p
mysql> create database limesurvey;
mysql> create user 'ls_user'@'localhost' identified by 'veryverysecret';
mysql> grant all privileges on limesurvey.* to 'ls_user'@'localhost' with grant option;
mysql> flush privileges;
mysql> exit


* Install SimpleSAML
Get latest version from www.simplesamlphp, and install in
/var/www/simplesamlphp

SimpleSAML configuration is done by:
1. Update config/authsources.php :
   In 'default-sp' configure 
   - the SP's 'privatekey' and 'certificate' files (read from cert/ directory)
   - the 'idp', to match the entityId of the SURFfederatie IDP (makes it default ==> no WAYF)
   
2. Updating config.php :
   - secretsalt
   - technical contact info
   - Updating authproc: 
Enable NameID-to-Attribute filter in authproc.sp:
.....
        'authproc.sp' => array(
                ...
                /* append NameID to available attributes */
                20  => array(
                  'class' => 'saml:NameIDAttribute',
                  'attribute' => 'NameID',
                  'format' => '%V',
                ),
.....

4. Add SURFfederatie IDP-metadata in metadata/saml20-idp-remote.php
Note: must match in authsources.php:default-sp['idp']
   

* Install LimeSurvey from source package to /var/www/limesurvey.conext.surfnetlabs.nl
# tar zxvf limesurvey-conext-x.y.z.tgz

Ensure that the 'tmp' and 'upload' directories are writable for the webserver;
Transfer ownership to the user, and doublecheck whether group/user-write permissions are set:
$ sudo chown -R www-data:www-data /var/www/limesurvey.conext.surfnetlabs.nl/limesurvey/tmp
$ sudo chown -R www-data:www-data /var/www/limesurvey.conext.surfnetlabs.nl/limesurvey/upload


5. Configuration of LimeSurvey - SET INITIAL ADMIN ACCOUNT!
LimeSurvey is configured from config.php, which overrides the settings of config-defaults.php

Review config.php for 
- its database connection settings:
  $databaselocation, $databasename, $databaseuser, $databasepass
- its $rooturl setting, make sure it starts with 'https://' for forced SSL root url


* SAML configuration
SAML-configuration is included from (the end of) config-defaults.php, and configures how SAML behaves.
Must change:
- $userArrayMap to define an Array with the SURFfederatie NameID's that must be authorized as admin,
  for example:
  $userArrayMap = Array ('urn:collab:person:test.surfguest.nl:mdobrinic' => 'admin');
- the path to SimpleSAMLphp, for example:
  define('SIMPLESAML_PATH', '/var/www/simplesaml');
  
Note: all attribute-related configuration is made here as well, as in which attributes are 
  used as userId, email-address or username.
  
* OpenSocial configuration
OpenSocial-configuration is included from (the end of) config-defaults.php, and configures how 
OpenSocial behaves.
Review this configuration, to configure the appropriate consumerkey/-secret definitions
as well as the appropriate OAuth- and service endpoints.

Note: OAuth-tokens are stored in /tmp/osapi-ls



6. Finalizing application install
Visit url https://server-url/limesurvey/admin/install/index.php to have a fresh database created.
After populating the database with fresh tables, remove the limesurvey/admin/install-directory.


