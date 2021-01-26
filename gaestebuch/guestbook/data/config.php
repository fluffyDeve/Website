<?php
#########################################################################
#	Gaestebuch Script - YellaBook					                                #
#	http://www.YellaBook.de               						                    #
#	All rights by KnotheMedia.de                                    			#
#-----------------------------------------------------------------------#
#	I-Net: http://www.knothemedia.de                            					#
#########################################################################
/**
 * Configuration for the guestbook
 *
 * @date 2012-07-29
 * @version 1.0
 *
 */
 
define("ADMIN_NAME", "gbadmin");							// the login-name for the admin-area
define("ADMIN_PASS", "gbpass");								// the login-password for the admin-area
define("ADMIN_MAIL", "");									// the email where infomails should be send
define("NEW_ENTRY_MAIL", "0");								// should a mail be send when there is a new entry?
define("LANGUAGE", "german");								// language of the guestbook
define("ENTRIES_PER_PAGE", "10");							// entries shown per page
define("ENABLE_ENTRIES", "0");								// should the entries have to be enabled before they will be displayed?
define("AUTO_INCREMENT", "17");								// the next value for the autoincrement
define("MAX_NAME_CHARACTERS", "30");						// the maximum name-chars
define("MAX_CITY_CHARACTERS", "30");						// the maximum city-chars
define("MAX_EMAIL_CHARACTERS", "30");						// the maximum email-chars
define("MAX_WEB_CHARACTERS", "120");						// the maximum web-chars
define("MAX_MESSAGE_CHARACTERS", "1000");					// the maximum message-chars
define("ENABLE_SECUREQUESTION", "1");						// should a security-question be displayed?
					
	
?>