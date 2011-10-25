<?php
/**
 * form-view of groups
 * 
 * @author dopey (mdobrinic@cozmanova.com)
 * for SURFnet bv (www.surfnet.nl)
 * 
 * 
 * Extremely simple layout abstraction from data
 * 
 * Data in:
 * $data["groups"] containing cGroup instances
 * $data["form"] contains an array with formdata:
 *   ["action"] the action to post to
 *   ["method"] the post method
 *   ["selected"] array with cSelectable instances that contain defaults
 * $data["hiddenfields"] the fields that should be hidden in the form but posted
 * $data["showsubmit"] boolean that indicates whether to show the submit-button (default:true) 
 *   
 * $data["root"] contains web-path to document-root
 * ================================================================================
 * This is part of the LimeSurvey integration, so make use of LimeSurvey context
 * for internationalization and style
 */
 
global $clang, $baseurl;
?>

<style type="text/css" media="screen">
<!--
  div.coinGroupSelector {
    font-family: sans-serif;
    
    width: 400px;
    background: #F1F2F3;
    //border: dotted;
    padding: 2px;
  }

  span.spGroup {
    font-weight: bold;
    text-align: left;
  }  

  span.spContact {
    font-weight: normal;
    text-align: left;
    font-size: smaller;
  }
  
  span.cpActionRow {
    width: 100%;
    text-align: right;
  }
-->
</style>

<div class="coinGroupSelector">
<? if ($data["form"] && $data["form"] != "embed") {
	$aForm = $data["form"];
	echo "<form" . ($aForm["action"] ? " action=\"" . $aForm["action"] . "\"" : "" )
	 . ($aForm["method"] ? " method=\"" . $aForm["method"] . "\"" : "" )
	 . ">\n";
  }
  if (is_array($data["hiddenfields"])) {
  	foreach ($data["hiddenfields"] as $name => $value) {
	   	echo "<input type=\"hidden\" name=\"" . $name . "\" value=\"" . $value . "\" />\n";
    }
  }
?>
<table width="100%" border="0">
  <thead>
    <tr>
      <td colspan="3" align="center">
        <?php echo $clang->gT("Select group(s) or person(s)"); ?><hr />
      </td>
    </tr>
  </thead>
  <tbody>
<?
foreach ($data["groups"] as $aGroup) { 
	$sDisplayName = (array_key_exists("title", $aGroup->_aAttributes) ? 
			$aGroup->_aAttributes["title"] : 
			$aGroup->getIdentifier());
	?>
    <tr>
      <td colspan="1">
        <? if (sizeof($aGroup->getContacts()) > 0) { ?>
        <img src="<?=$data["root"]?>/resources/Person-group-add.png" height="16" width="16" />
        <? } else { ?>
        <img src="<?=$data["root"]?>/resources/Person-group.png" height="16" width="16" />
        <? }?>
      </td>
      <td>
        <input type="checkbox" name="<?=$aGroup->getInputName();?>" <?=(array_key_exists($aGroup->getIdentifier(), is_array($aForm["selected"]) ? $aForm["selected"] : array()) ? " checked" : "");?> />
      </td>
      <td><span class="spGroup"><?=$sDisplayName?></span></td>
    </tr>
<?
  foreach ($aGroup->getContacts() as $aContact) { ?>
    <tr>
      <td></td>
      <td>
        <img src="<?=$data["root"]?>/resources/Person-white.png" height="16" width="16" />
        <input type="checkbox" name="<?=$aContact->getInputName();?>" <?=(array_key_exists($aContact->getIdentifier(), is_array($aForm["selected"]) ? $aForm["selected"] : array()) ? " checked" : "");?> />
      </td>
      <td><span class="spContact" title="<?=$aContact->_aAttributes["eduPersonPrincipalName"][0];?>"><?=$aContact->_aAttributes["cn"][0];?></span></td>
    </tr>
<?
  } // foreach (contact)
} // foreach (group)
?>
<?php if ($data["showsubmit"] != false) { ?>
    <tr>
      <td colspan="3" align="right">
        <span class="cpActionRow">
          <input type="submit" name="submit" value="<?php echo $clang->gT("Submit"); ?>" />
        </span>
      </td>
    </tr>
<?php } // showsubmit ?>    
  </tbody>

</table>
<? if ($data["form"] && $data["form"] != "embed") {
  echo "</form>";
}
?>
</div>
<!-- LimeSurvey-integration-scripts -->
<?php
print "<script type=\"text/javascript\">\n"
        . "<!--\n"
        . "\tfor(i=0; i<document.forms.length; i++)\n"
        . "\t{\n"
        . "var el = document.createElement('input');\n"
        . "el.type = 'hidden';\n"
        . "el.name = 'checksessionbypost';\n"
        . "el.value = '".$_SESSION['checksessionpost']."';\n"
        . "document.forms[i].appendChild(el);\n"
        . "\t}\n"
        . "\n"
        . "\tfunction addHiddenElement(theform,thename,thevalue)\n"
        . "\t{\n"
        . "var myel = document.createElement('input');\n"
        . "myel.type = 'hidden';\n"
        . "myel.name = thename;\n"
        . "theform.appendChild(myel);\n"
        . "myel.value = thevalue;\n"
        . "return myel;\n"
        . "\t}\n"
        . "\n"
        . "\tfunction sendPost(myaction,checkcode,arrayparam,arrayval)\n"
        . "\t{\n"
        . "var myform = document.createElement('form');\n"
        . "document.body.appendChild(myform);\n"
        . "myform.action =myaction;\n"
        . "myform.method = 'POST';\n"
        . "for (i=0;i<arrayparam.length;i++)\n"
        . "{\n"
        . "\taddHiddenElement(myform,arrayparam[i],arrayval[i])\n"
        . "}\n"
        . "addHiddenElement(myform,'checksessionbypost',checkcode)\n"
        . "myform.submit();\n"
        . "\t}\n"
        . "\n"
        . "//-->\n"
        . "</script>\n";
?>
<!-- coinGroupSelector -->