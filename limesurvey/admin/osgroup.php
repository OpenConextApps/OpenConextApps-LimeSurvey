<?php

/**
 * OpenSocial Group support for LimeSurvey
 * Group Form presentation broker
 * included by admin.php broker, handler for action=osgroup
 *
 * mdobrinic@cozmanova.com
 */
include_once("login_check.php");
include_once("database.php");

if (!isset($surveyid)) {$surveyid=returnglobal('sid');}


if ($subaction == 'form') {
  global $templatesoutput, $grouprel_config, $as, $user_id;
  if (isset($as)) {
    $auth = array($grouprel_config['userIdAttribute'] => $user_id);
  } else {
    // developer:
    $auth = array($grouprel_config['userIdAttribute'] => 'urn:collab:person:test.surfguest.nl:mdobrinic');
  }

  require_once('classes/GroupTokens.php');

  $o = new GroupTokens($auth, $surveyid, $grouprel_config);
  $relations = GroupTokens::getFreshFromSession("relations", array("userId" => $o->getSubjectUser()));

  $data = array(
			"groups" => $relations,
			"form" => array(
				"action" => "$rooturl/admin/admin.php?action=tokens&sid=$surveyid&subaction=uploados",
				"method" => "POST",
			),
			"hiddenfields" => array("share"=>"invite", "checksessionbypost" => $_SESSION['checksessionpost']),
			"showsubmit" => true,
			"root" => "$rooturl/images/GroupRel",		// base-path for URLs in template
		);

  $filename = './classes/'.'GroupRel/View/group_form.php';
  ob_start();
  include $filename;
  $output .= ob_get_contents();
  ob_end_clean();
  
  $templatesoutput .= $output;

  print $templatesoutput;
  exit();
}

?>