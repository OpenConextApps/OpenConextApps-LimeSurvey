<?php
/*
 * SAML login module for LimeSurvey, integrate with session handling
 * 
 * $Id: login_check_saml.php$
 */


if (!isset($dbprefix) || isset($_REQUEST['dbprefix'])) {die("Cannot run this script directly");}
if (!isset($action)) {$action=returnglobal('action');}


// Sync session/cookie name
setGlobalSetting('SessionName', session_name());
    
// Use the default sp setting as defined in simplesamlphp
$as = new SimpleSAML_Auth_Simple('default-sp');

// force authentication against remote IdP
$as->requireAuth();

// Get attributes provided by the IdP
$attributes = $as->getAttributes();

// Get a unique ID for the user.
// If you are using standard eduPerson attributes, you may want to use ['urn:mace:dir:attribute-def:eduPersonPrincipalName']
$user_id = $attributes['NameID'][0];

// Set request context to include $user_id as REMOTE_USER
$_SERVER['REMOTE_USER'] = $user_id;
    
// Now delegate to procedure:
include("usercontrol.php");
    
if( isset($_POST['user']) && isset($_POST['password']) ||
($action == "forgotpass") || ($action == "login") ||
($action == "logout") ||
($useWebserverAuth === true && !isset($_SESSION['loginID'])) )
{
    include("usercontrol.php");
}




// login form
if(!isset($_SESSION['loginID']) && $action != "forgotpass" && ($action != "logout" || ($action == "logout" && !isset($_SESSION['loginID'])))) // && $action != "login")	// added by Dennis
{
    if($action == "forgotpassword")
    {
        $loginsummary = '

			<form class="form44" name="forgotpassword" id="forgotpassword" method="post" action="'.$homeurl.'/admin.php" >
				<p><strong>'.$clang->gT('You have to enter user name and email.').'</strong></p>

				<ul>
						<li><label for="user">'.$clang->gT('Username').'</label><input name="user" id="user" type="text" size="60" maxlength="60" value="" /></li>
						<li><label for="email">'.$clang->gT('Email').'</label><input name="email" id="email" type="text" size="60" maxlength="60" value="" /></li>
						<p><input type="hidden" name="action" value="forgotpass" />
						<input class="action" type="submit" value="'.$clang->gT('Check Data').'" />
						<p><a href="'.$scriptname.'">'.$clang->gT('Main Admin Screen').'</a>
			</form>
            <p>&nbsp;</p>
';
    }
    elseif (!isset($loginsummary))
    { // could be at login or after logout
        $refererargs=''; // If this is a direct access to admin.php, no args are given
        // If we are called from a link with action and other args set, get them
        if (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'])
        {
            $refererargs = html_escape($_SERVER['QUERY_STRING']);
        }







        //include("database.php");
        $sIp = $_SERVER['REMOTE_ADDR'];
        $query = "SELECT * FROM ".db_table_name('failed_login_attempts'). " WHERE ip='$sIp';";
        $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
        $result = $connect->query($query) or safe_die ($query."<br />".$connect->ErrorMsg());
        $bCannotLogin = false;
        $intNthAttempt = 0;
        if ($result!==false && $result->RecordCount() >= 1)
        {
            $field = $result->FetchRow();
            $intNthAttempt = $field['number_attempts'];
            if ($intNthAttempt>=$maxLoginAttempt){
                $bCannotLogin = true;
            }

            $iLastAttempt = strtotime($field['last_attempt']);

            if (time() > $iLastAttempt + $timeOutTime){
                $bCannotLogin = false;
                $query = "DELETE FROM ".db_table_name('failed_login_attempts'). " WHERE ip='$sIp';";
                $result = $connect->query($query) or safe_die ($query."<br />".$connect->ErrorMsg());
            }

        }
        $loginsummary ="";
        if (!$bCannotLogin)
        {
            if (!isset($logoutsummary))
            {
                $loginsummary = "<form name='loginform' id='loginform' method='post' action='$homeurl/admin.php' ><p><strong>".$clang->gT("You have to login first.")."</strong><br />	<br />";
            }
            else if (isset($logoutsummary)) {
                // Disable login-form.
            }
            else
            {
                $loginsummary = "<form name='loginform' id='loginform' method='post' action='$homeurl/admin.php' ><br /><strong>".$logoutsummary."</strong><br />	<br />";

                $loginsummary .= "
                                                                <ul>
                                                                                <li><label for='user'>".$clang->gT("Username")."</label>
                                                                                <input name='user' id='user' type='text' size='40' maxlength='40' value='' /></li>
                                                                                <li><label for='password'>".$clang->gT("Password")."</label>
                                                                                <input name='password' id='password' type='password' size='40' maxlength='40' /></li>
                                            <li><label for='loginlang'>".$clang->gT("Language")."</label>
                                            <select id='loginlang' name='loginlang' style='width:216px;'>\n";
                $loginsummary .='<option value="default" selected="selected">'.$clang->gT('Default').'</option>';
                $lan=array();
                foreach (getlanguagedata(true) as $langkey=>$languagekind)
                {
                    array_push($lan,$langkey);
                }

                foreach (getlanguagedata(true) as $langkey=>$languagekind)
                {
                    //The following conditional statements select the browser language in the language drop down box and echoes the other options.
                    $loginsummary .= "\t\t\t\t<option value='$langkey'>".$languagekind['nativedescription']." - ".$languagekind['description']."</option>\n";
                }
                $loginsummary .= "\t\t\t</select>\n"
                . "</li>
                                        </ul>
                                                                                <p><input type='hidden' name='action' value='login' />
                                                                                <input type='hidden' name='refererargs' value='".$refererargs."' />
                                                                                <input class='action' type='submit' value='".$clang->gT("Login")."' /><br />&nbsp;\n<br/>";
            }
        }
        else{
            $loginsummary .= "<p>".sprintf($clang->gT("You have exceeded you maximum login attempts. Please wait %d minutes before trying again"),($timeOutTime/60))."<br /></p>";
        }

        if ($display_user_password_in_email === true)
        {
            $loginsummary .= "<p><a href='$scriptname?action=forgotpassword'>".$clang->gT("Forgot Your Password?")."</a><br />&nbsp;\n";
        }
        $loginsummary .= "                                                </form><br /><p>";
        $loginsummary .= "                                                <script type='text/javascript'>\n";
        $loginsummary .= "                                                  document.getElementById('user').focus();\n";
        $loginsummary .= "                                                </script>\n";
    }
}
