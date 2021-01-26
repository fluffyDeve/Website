<?php
#########################################################################
#	Gaestebuch Script - YellaBook					                                #
#	http://www.YellaBook.de               				                        #
#	All rights by KnotheMedia.de                                          #
#-----------------------------------------------------------------------#
#	I-Net: http://www.knothemedia.de                                    	#
#########################################################################
/**
 * admin-area for the guestbook
 * 
 * @date 2013-01-13
 * @version 1.1
 * 
 */

 
 
session_start();
header('Content-type: text/html; charset=utf-8');


/********************************** define constants *******************************/

define("PATH_TO_LANG", "lang/");
define("PATH_TO_RES", "res/");
define("PATH_TO_DATA", "data/");
define("PATH_TO_TEMPLATES", "templates/");




/********************************* include needed data ******************************/

require_once(PATH_TO_DATA."config.php");				// include config
require_once(PATH_TO_RES."class.template.php");			// include template-class
require_once(PATH_TO_RES."class.entries.php");			// include extries-class
require_once(PATH_TO_RES."class.config.php");			// include configuration-class
require_once(PATH_TO_RES."class.language.php");			// include language-class
require_once(PATH_TO_RES."class.securequestions.php");	// include securequestions-class





/************************************ handle language ********************************/

$config = new Config(PATH_TO_DATA);

// set language an get texts
$_SESSION['gbook']['language'] = $config->get_config('LANGUAGE');
$language = new Language(PATH_TO_LANG, $config->get_config('LANGUAGE'));
if(!$language->get_texts()){
	echo $language->error[0];
	exit;
}
 
 
 
 
/************************************** set action ***********************************/

$action = "";

// the user ins't logged in
if(!isset($_SESSION['gbook']['logged_id']) || 1!=$_SESSION['gbook']['logged_id']){

	// user want to log in -> set this in action
	if(isset($_POST['login'])){
		$action = "check_login";
	}
}

// the user is logged in
else{
	// set the action from get-vars
	if(!isset($_POST['action']) && isset($_GET['action'])){
		$action = $_GET['action'];
	}
	// set the action from post-vars
	else if(isset($_POST['action']) && !isset($_GET['action'])){
		$action = $_POST['action'];
	}
	
	if(""==$action){
		$action = "get_config";
	}
}
 
 
 

 
 
/*********************************** action-handling ********************************/

$content = "";

switch($action){
	
	// config
	case "get_config":				$content = get_config(); break;
	case "save_config":				$content = save_config(); break;
	
	// entries
	case "get_entries":				$content = get_entries(); break;
	case "edit_entry":				$content = get_entries(); break;
	case "save_entry":				$content = save_entry(); break;
	
	// backup
	case "get_backup":				$content = get_backup(); break;
	case "download_backup":			download_backup(); break;
	
	// subquestions
	case "get_securequestions":		$content = get_securequestions(); break;
	case "save_securequestions":	$content = save_securequestions(); break;
	
	// login/logout
	case "check_login":				$content = check_login(); break;
	case "logout":					do_logout(); $content = show_login(); break;
	default:						$content = show_login(); break;
}





/********************************* translate content *******************************/

foreach($_SESSION['gbook']['texts'] as $key=>$value){
	$content = str_replace('###'.$key.'###', $value, $content);
}

echo $content;





/************************************* functions **********************************/

// login/logout

/**
 * display the loginform
 *
 * @param string $message	errormessage
 * @return string 			html-content
 */
function show_login($message=""){
	
	$template = new Template(PATH_TO_RES."loginform.html");
	$template->replace_marker("MESSAGE", $message);
	return $template->get_content();
}


/**
 * checks the login and manages the redirection
 *
 * @param string $message	errormessage
 * @return string 			html-content
 */
function check_login(){
	
	if(isset($_POST['name']) && isset($_POST['password']) && ADMIN_NAME==$_POST['name'] && ADMIN_PASS==$_POST['password']){
		$_SESSION['gbook']['logged_id'] = 1;
		return get_config();
	}
	else{
		return show_login('<p class="error">###error_wrong_login###</p>');
	}
}


/**
 * delets the login-session
 */
function do_logout(){
	$_SESSION['gbook']['logged_id'] = 0;
}




// configuration

/**
 * display the form with the configurations
 *
 * @param string $message	errormessage
 * @return string 			html-content
 */
function get_config($message=""){

	// get templates
	$main_tpl = new Template(PATH_TO_RES."body.html");
	$tpl = new Template(PATH_TO_RES."configuration.html");
	
	// insert config-values
	$config = new Config(PATH_TO_DATA);
	$tpl->replace_marker('ADMIN_NAME_VALUE', $config->get_config('ADMIN_NAME'));
	$tpl->replace_marker('ADMIN_PASS_VALUE', $config->get_config('ADMIN_PASS'));
	$tpl->replace_marker('ADMIN_MAIL_VALUE', $config->get_config('ADMIN_MAIL'));
	$tpl->replace_marker('ENTRIES_PER_PAGE_VALUE', $config->get_config('ENTRIES_PER_PAGE'));
	$tpl->replace_marker('MAX_NAME_CHARACTERS_VALUE', $config->get_config('MAX_NAME_CHARACTERS'));
	$tpl->replace_marker('MAX_CITY_CHARACTERS_VALUE', $config->get_config('MAX_CITY_CHARACTERS'));
	$tpl->replace_marker('MAX_EMAIL_CHARACTERS_VALUE', $config->get_config('MAX_EMAIL_CHARACTERS'));
	$tpl->replace_marker('MAX_WEB_CHARACTERS_VALUE', $config->get_config('MAX_WEB_CHARACTERS'));
	$tpl->replace_marker('MAX_MESSAGE_CHARACTERS_VALUE', $config->get_config('MAX_MESSAGE_CHARACTERS'));

	
	// insert config for new entry mail
	if($config->get_config('NEW_ENTRY_MAIL')){
		$tpl->replace_marker('NEW_ENTRY_MAIL_VALUE_YES', 'checked="checked"');
		$tpl->replace_marker('NEW_ENTRY_MAIL_VALUE_NO', '');
	}
	else{
		$tpl->replace_marker('NEW_ENTRY_MAIL_VALUE_YES', '');
		$tpl->replace_marker('NEW_ENTRY_MAIL_VALUE_NO', 'checked="checked"');
	}
	
	
	// insert config for languages
	$language = new Language(PATH_TO_LANG, $config->get_config('LANGUAGE'));
	if(!$languages = $language->get_languages()){
		$tpl->replace_marker('LANGUAGES', '<p class="error">###'.$language->error[0].'###</p>');
	}
	else{
		$language_string = "";
		foreach($languages as $language){
			if($config->get_config('LANGUAGE')==$language){
				$language_string .= '<label><input class="checkbox" type="radio" name="LANGUAGE" value="'.$language.'" checked="checked" /> '.ucfirst($language).'</label>';
			}
			else{
				$language_string .= '<label><input class="checkbox" type="radio" name="LANGUAGE" value="'.$language.'" /> '.ucfirst($language).'</label>';
			}
		}
		$tpl->replace_marker('LANGUAGES', $language_string);
	}
	
	
	// insert config for enable entries
	if($config->get_config('ENABLE_ENTRIES')){
		$tpl->replace_marker('ENABLE_ENTRIES_VALUE_YES', 'checked="checked"');
		$tpl->replace_marker('ENABLE_ENTRIES_VALUE_NO', '');
	}
	else{
		$tpl->replace_marker('ENABLE_ENTRIES_VALUE_YES', '');
		$tpl->replace_marker('ENABLE_ENTRIES_VALUE_NO', 'checked="checked"');
	}	
	
	// insert config for enable securequestion
	if($config->get_config('ENABLE_SECUREQUESTION')){
		$tpl->replace_marker('ENABLE_SECUREQUESTION_VALUE_YES', 'checked="checked"');
		$tpl->replace_marker('ENABLE_SECUREQUESTION_VALUE_NO', '');
	}
	else{
		$tpl->replace_marker('ENABLE_SECUREQUESTION_VALUE_YES', '');
		$tpl->replace_marker('ENABLE_SECUREQUESTION_VALUE_NO', 'checked="checked"');
	}
	
	
	// insert rest
	if(""==$message && isset($_GET['message'])){
		$tpl->replace_marker('MESSAGE', '<p class="message">###'.$_GET['message'].'###</p>');
	}
	else{
		$tpl->replace_marker('MESSAGE', $message);
	}
	$main_tpl->replace_marker('CONTENT', $tpl->get_content());
	$main_tpl->replace_marker('CONFIGMENU', 'class="active"');
	$main_tpl->replace_marker('SECUREQUESTIONS', '');
	$main_tpl->replace_marker('ENTRIESMENU', '');
	$main_tpl->replace_marker('BACKMENU', '');
	
	return $main_tpl->get_content();
}




/**
 * save the configuration and redirect to the display of the configuration if it was successful
 *
 * @return string|void 			html-content with the configuration and an error or nothing (redirect to the configuration if it was successful)
 */
function save_config(){

	// save config-values
	$config = new Config(PATH_TO_DATA);	
	$config->set_config('ADMIN_NAME', $_POST['ADMIN_NAME']);
	$config->set_config('ADMIN_PASS', $_POST['ADMIN_PASS']);
	$config->set_config('ADMIN_MAIL', $_POST['ADMIN_MAIL']);
	if(isset($_POST['NEW_ENTRY_MAIL']) && 1==$_POST['NEW_ENTRY_MAIL']){
		$config->set_config('NEW_ENTRY_MAIL', '1');
	}
	else{
		$config->set_config('NEW_ENTRY_MAIL', '0');
	}
	$config->set_config('LANGUAGE', $_POST['LANGUAGE']);
	$config->set_config('ENTRIES_PER_PAGE', $_POST['ENTRIES_PER_PAGE']);
	if(isset($_POST['ENABLE_ENTRIES']) && 1==$_POST['ENABLE_ENTRIES']){
		$config->set_config('ENABLE_ENTRIES', '1');
	}
	else{
		$config->set_config('ENABLE_ENTRIES', '0');
	}	
	if(isset($_POST['ENABLE_SECUREQUESTION']) && 1==$_POST['ENABLE_SECUREQUESTION']){
		$config->set_config('ENABLE_SECUREQUESTION', '1');
	}
	else{
		$config->set_config('ENABLE_SECUREQUESTION', '0');
	}
	$config->set_config('MAX_NAME_CHARACTERS', $_POST['MAX_NAME_CHARACTERS']);
	$config->set_config('MAX_CITY_CHARACTERS', $_POST['MAX_CITY_CHARACTERS']);
	$config->set_config('MAX_EMAIL_CHARACTERS', $_POST['MAX_EMAIL_CHARACTERS']);
	$config->set_config('MAX_WEB_CHARACTERS', $_POST['MAX_WEB_CHARACTERS']);
	$config->set_config('MAX_MESSAGE_CHARACTERS', $_POST['MAX_MESSAGE_CHARACTERS']);
	if(!$config->save_config()){
		return get_config('<p class="error">###'.$config->error[0].'###</p>');
	}
	else{
		header("Location: admin.php?action=get_config&message=save_config_successful");
		exit;
	}
}






// entries

/**
 * display a list with the entries
 *
 * @param string $message	errormessage
 * @return string 			html-content
 */
function get_entries($message=""){

	// create needed objects
	require_once(PATH_TO_DATA."smileys.php");			// include smiley-array
	$_SESSION['gbook']['smileys'] = $gb_smileys;
	$main_tpl = new Template(PATH_TO_RES."body.html");
	$template = new Template(PATH_TO_RES."entries.html");
	$entry_obj = new Entries(PATH_TO_DATA, 1);

	
	
	// get last page and current page
	$entry_obj->entries_qty%ENTRIES_PER_PAGE == 0 ?
		$last_page = $entry_obj->entries_qty/ENTRIES_PER_PAGE :
		$last_page = floor($entry_obj->entries_qty/ENTRIES_PER_PAGE) + 1;
	
	if(isset($_POST['page'])){
		$_GET['page'] = $_POST['page'];
	}
	if(isset($_GET['page']) && is_numeric($_GET['page'])){
		$_GET['page'] = floor($_GET['page']);
	}
	if(!isset($_GET['page']) || ""==trim($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page']>$last_page){
		$_GET['page'] = 1;
	}
	


	// create pagebrowser
	if(0<$entry_obj->entries_qty){
		$template_pagebrowser = new Template(PATH_TO_RES."pagebrowser.html");
		1<$_GET['page'] ?
			$link_prev = '<a href="admin.php?action=get_entries&page='.($_GET['page']-1).'">###prev_page###</a>' :
			$link_prev = '###prev_page###';
		($_GET['page']+1) <= $last_page ?
			$link_next = '<a href="admin.php?action=get_entries&page='.($_GET['page']+1).'">###next_page###</a>' :
			$link_next = '###next_page###';	
			
		$template_pagebrowser->replace_marker("ENTRIES_QTY", $entry_obj->entries_qty);
		$template_pagebrowser->replace_marker("PAGES_QTY", $last_page);
		$template_pagebrowser->replace_marker("CURRENT_PAGE", $_GET['page']);
		$template_pagebrowser->replace_marker("PREV_PAGE_LINK", $link_prev);
		$template_pagebrowser->replace_marker("NEXT_PAGE_LINK", $link_next);
		$template->replace_marker("PAGEBROWSER", $template_pagebrowser->get_content());
	}
	else{
		$template->replace_marker("PAGEBROWSER", "");
	}

	
	// get entries
	$entry_arr = $entry_obj->get_entries(($_GET['page']*ENTRIES_PER_PAGE)-ENTRIES_PER_PAGE+1, ENTRIES_PER_PAGE);

	
	
	// no entries -> message
	if(0==count($entry_arr)){
		$template->replace_marker('ENTRIESLIST', '<p>###no_entries###</p>');
	}
	
	
	// there are entries
	else{
	
		// create entry-list
		$entries = "";
		foreach($entry_arr as $entry){
		
		
			// edit entry
			if(isset($_POST['action']) && 'edit_entry'==$_POST['action'] && $entry['id']==$_POST['id']){
				
				// get entry-template
				$template_entry = new Template(PATH_TO_RES."entry_form.html");
				
				
				// insert entry-info
				$template_entry->replace_marker("CITY", $entry['city']);
				$template_entry->replace_marker("DATE", date("d.m.Y", $entry['entry_date']));
				$template_entry->replace_marker("TIME", date("H:i", $entry['entry_date']));
				$template_entry->replace_marker("EMAIL", $entry['email']);		
				$template_entry->replace_marker("WEB", $entry['web']);
				$template_entry->replace_marker("MESSAGE", str_replace(array("<br/>", "<br>", "<br />"), array("\r\n", "\r\n", "\r\n"), $entry['message']));
			
				
				// create-smiley-block
				$smileys_message = "";
				foreach($_SESSION['gbook']['smileys'] as $smiley_text => $smiley_image){
					$smiley_template = new Template(PATH_TO_TEMPLATES."smiley.html");
					$smiley_template->replace_marker("SMILEY_TEXT", $smiley_text);		
					$smiley_template->replace_marker("SMILEY_TEXTAREA_ID", "gb_form_messagebox_".$entry['id']);		
					$smiley_template->replace_marker("SMILEY_IMAGE", 'images/'.$smiley_image);		
					$smileys_message .= $smiley_template->get_content();
				}
				$template_entry->replace_marker("SMILEYS_MESSAGE", $smileys_message);		
			}
			
			// no edit entry
			else{
			
				// get entry-template
				$template_entry = new Template(PATH_TO_RES."entry.html");
				
				
				// insert entry-info
				""==$entry['city'] ?
					$template_entry->remove_area("FROM") :
					$template_entry->replace_marker("CITY", $entry['city']);
				$template_entry->replace_marker("DATE", date("d.m.Y", $entry['entry_date']));
				$template_entry->replace_marker("TIME", date("H:i", $entry['entry_date']));
				if(""==$entry['email']){
					$template_entry->remove_area("EMAIL");
				}
				else{
					$template_entry->replace_marker("EMAIL", htmlentities("mailto:".$entry['email'], ENT_COMPAT, 'UTF-8'));		
					$template_entry->replace_marker("EMAIL_VALUE", htmlentities($entry['email'], ENT_COMPAT, 'UTF-8'));			
				}
				
				$text = $entry['message'];
				foreach($_SESSION['gbook']['smileys'] as $smiley_text => $smiley_image){
					$text = str_replace($smiley_text, '<img class="gb_smiley" src="images/'.$smiley_image.'" width="28" height="19" alt="" />', $text);
				}
				$template_entry->replace_marker("TEXT", $text);
			}
			
			
			// set common data
			$template_entry->replace_marker("ENTRY_ID", $entry['id']);
			$template_entry->replace_marker("NAME", $entry['name']);
			if(""==$entry['comment']){
				$template_entry->replace_marker("COMMENT_DATE", "...");
				$template_entry->replace_marker("COMMENT_TIME", "...");
				$template_entry->replace_marker("COMMENT", "");
			}
			else{
				$template_entry->replace_marker("COMMENT_DATE", date("d.m.Y", $entry['comment_date']));
				$template_entry->replace_marker("COMMENT_TIME", date("H:i", $entry['comment_date']));
				$template_entry->replace_marker("COMMENT", str_replace(array("<br/>", "<br>", "<br />"), array("\r\n", "\r\n", "\r\n"), $entry['comment']));
			}
			
			// create-smiley-block for comment
			$smileys_comment = "";
			foreach($_SESSION['gbook']['smileys'] as $smiley_text => $smiley_image){
				$smiley_template = new Template(PATH_TO_TEMPLATES."smiley.html");
				$smiley_template->replace_marker("SMILEY_TEXT", $smiley_text);		
				$smiley_template->replace_marker("SMILEY_TEXTAREA_ID", "gb_form_commentbox_".$entry['id']);		
				$smiley_template->replace_marker("SMILEY_IMAGE", 'images/'.$smiley_image);		
				$smileys_comment .= $smiley_template->get_content();
			}
			$template_entry->replace_marker("SMILEYS_COMMENT", $smileys_comment);		
			
			// set the active-class and value
			if(0==$entry['active']){
				$template_entry->replace_marker("ACTIVATION", "");
				$template_entry->replace_marker("ACTIVATION_CLASS", "gb_inactive");
			}
			else{
				$template_entry->replace_marker("ACTIVATION", 'checked="checked"');
				$template_entry->replace_marker("ACTIVATION_CLASS", 'gb_active');
			}

				
				
			// store entry in list
			$template_entry->replace_marker("PAGE", $_GET['page']);		
			$template_entry->replace_marker("ID", $entry['id']);		
			$entries .= $template_entry->get_content();
		}
		
		// fill template with entry-information
		$template->replace_marker("ENTRIESLIST", $entries);
	}

	
	// insert rest
	if(""==$message && isset($_GET['message'])){
		$template->replace_marker('MESSAGE', '<p class="message">###'.$_GET['message'].'###</p>');
	}
	else{
		$template->replace_marker('MESSAGE', $message);
	}
	$main_tpl->replace_marker('CONTENT', $template->get_content());
	$main_tpl->replace_marker('CONFIGMENU', '');
	$main_tpl->replace_marker('SECUREQUESTIONS', '');
	$main_tpl->replace_marker('ENTRIESMENU', 'class="active"');
	$main_tpl->replace_marker('BACKMENU', '');
	
	return $main_tpl->get_content();
}




/**
 * save or delete an entry-comment and activation - returns the entry-page with a success-message
 *
 * @return string 			html-content
 */
function save_entry(){

	// create entry-object
	$entry_obj = new Entries(PATH_TO_DATA, 1);
	
	
	// delete entry
	if(isset($_POST['delete_entry'])){
	
		if($entry_obj->delete_entry($_POST['id'])){
			return get_entries('<p class="message">###delete_entry_successful###</p>');
		}
		else{
			return get_entries('<p class="error">'.$entry_obj->error[0].'</p>');
		}
	}
	
	// save the changes
	else{
	
		// get the entry to update
		$update_entry = $entry_obj->get_entry($_POST['id']);	
	
	
		// update all entry-data
		if(isset($_POST['all_data'])){
		
			// set all data
			$update_entry['name'] = $_POST['name'];
			$update_entry['city'] = $_POST['city'];
			$update_entry['email'] = $_POST['email'];
			$update_entry['web'] = $_POST['web'];
			$update_entry['message'] = $_POST['message'];
			if(isset($_POST['comment'])){
				""==$_POST['comment'] ? 
					$update_entry['comment_date'] = "" :
					$update_entry['comment_date'] = time();
				$update_entry['comment'] = $_POST['comment'];
			}
			isset($_POST['active']) && 1==$_POST['active'] ?
				$update_entry['active'] = 1 :
				$update_entry['active'] = 0;
		}
	
		// update only activation and comment
		else{
		
			// set the only activiation
			$update_entry['name'] = html_entity_decode($update_entry['name'], ENT_COMPAT, 'UTF-8');
			$update_entry['city'] = html_entity_decode($update_entry['city'], ENT_COMPAT, 'UTF-8');
			$update_entry['email'] = html_entity_decode($update_entry['email'], ENT_COMPAT, 'UTF-8');
			$update_entry['web'] = html_entity_decode($update_entry['web'], ENT_COMPAT, 'UTF-8');
			$update_entry['message'] = str_replace(array('<br/>', '<br />', '<br>'), array("\r\n", "\r\n", "\r\n"), html_entity_decode($update_entry['message'], ENT_COMPAT, 'UTF-8'));
			if(isset($_POST['comment'])){
				""==$_POST['comment'] ? 
					$update_entry['comment_date'] = "" :
					$update_entry['comment_date'] = time();
				$update_entry['comment'] = $_POST['comment'];
			}
			isset($_POST['active']) && 1==$_POST['active'] ?
				$update_entry['active'] = 1 :
				$update_entry['active'] = 0;
		}
			
		// update the entry
		if($entry_obj->update_entries($update_entry)){
			return get_entries('<p class="message">###save_entry_successful###</p>');
		}
		else{
			return get_entries('<p class="error">'.$entry_obj->error[0].'</p>');
		}
	}
}







// backup

/**
 * returns the page with the backup-download-link
 * 
 * @return string 			html-content
 */
function get_backup(){

	// get templates
	$main_tpl = new Template(PATH_TO_RES."body.html");
	$tpl = new Template(PATH_TO_RES."backup.html");
	$main_tpl->replace_marker('CONTENT', $tpl->get_content());
	$main_tpl->replace_marker('CONFIGMENU', '');
	$main_tpl->replace_marker('SECUREQUESTIONS', '');
	$main_tpl->replace_marker('ENTRIESMENU', '');
	$main_tpl->replace_marker('BACKMENU', 'class="active"');
	
	return $main_tpl->get_content();
}


/**
 * returns the data-file for download
 */
function download_backup(){

	$file = PATH_TO_DATA."data.txt";
	header("Content-Type: application/force-download");
	header("Content-Disposition: attachment; filename=".date("Y-m-d")."_backup.txt");
	header("Content-Length:".filesize($file));
	readfile($file);
	exit;
}










// subquestions

/**
 * display the form with the securequestions
 *
 * @param string $message	errormessage
 * @return string 			html-content
 */
function get_securequestions($message=""){

	$content = "";

	// get templates
	$main_tpl = new Template(PATH_TO_RES."body.html");
	$tpl = new Template(PATH_TO_RES."securequestions.html");
	
	// get subquestions
	$securequestion_obj = new Securequestions(PATH_TO_DATA);
	$securequestions = $securequestion_obj->get_securequestions();
	
	// get config
	$config = new Config(PATH_TO_DATA);

	// get languages
	$language = new Language(PATH_TO_LANG, $config->get_config('LANGUAGE'));
	if(!$languages = $language->get_languages()){
		$message = '<p class="error">###'.$language->error[0].'###</p>';
	}
	
	// insert securequestions
	else{
		
		$securequestions_qty = count($securequestions);
		
		// insert each securequestion and an empty field for a new question
		for($i=0; $i<=$securequestions_qty; $i++){
		
			// get the first securequestion
			if(0<count($securequestions)){
				$securequestion = array_shift($securequestions);
			}
			else{
				$securequestion = array();
			}
			
		
			$content .= "<tr>
							<td>
								<table>";
		
			// insert the questions in different languages
			foreach($languages as $language){
				$content .= '		<tr>
										<td>'.ucfirst($language).'</td>
										<td><input type="text" name="securequestions['.$i.']['.$language.']" value="'.(isset($securequestion[$language]) ? $securequestion[$language] : '').'" /></td>
									</tr>';
			}
			$content .= '		</table>
							</td>
							<td>
								<div class="securequestion_answer_text">###answer###</div>
								<p class="securequestion_answer">
									<input type="text" name="securequestions['.$i.'][answer]" value="'.(isset($securequestion['answer']) ? $securequestion['answer'] : '').'" />
								</p>
								<p class="securequestion_delete">
									<label><input class="checkbox" type="checkbox" name="securequestions['.$i.'][delete]" value="1" /> ###delete###</label>
								</p>
							</td>
						</tr>';

		}
		$tpl->replace_marker('SECUREQUESTIONS', $content);
	}
	
	
	// insert rest
	if(""==$message && isset($_GET['message'])){
		$tpl->replace_marker('MESSAGE', '<p class="message">###'.$_GET['message'].'###</p>');
	}
	else{
		$tpl->replace_marker('MESSAGE', $message);
	}
	$main_tpl->replace_marker('CONTENT', $tpl->get_content());
	$main_tpl->replace_marker('SECUREQUESTIONS', 'class="active"');
	$main_tpl->replace_marker('CONFIGMENU', '');
	$main_tpl->replace_marker('ENTRIESMENU', '');
	$main_tpl->replace_marker('BACKMENU', '');
	
	return $main_tpl->get_content();
}




/**
 * save the securequestions and redirect to the display of the securequestions if it was successful
 *
 * @return string|void 			html-content with the securequestions and an error or nothing (redirect to the securequestions if it was successful)
 */
function save_securequestions(){

	// save securequestions
	$securequestions = new Securequestions(PATH_TO_DATA);
	if(!$securequestions->save_securequestions()){
		return get_securequestions('<p class="error">###'.$securequestions->error[0].'###</p>');
	}
	else{
		header("Location: admin.php?action=get_securequestions&message=save_securequestions_successful");
		exit;
	}
}





?>