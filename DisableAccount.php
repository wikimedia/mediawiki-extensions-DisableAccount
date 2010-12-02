<?php
// DisableAccount extension: quick extension to disable an account.
// Written by Andrew Garrett, 2010-12-02

$wgExtensionCredits['other'][] = array(
	'path' => __FILE__,
	'name' => 'Disable Account',
	'author' => array( 'Andrew Garrett' ),
	'descriptionmsg' => 'disableaccount-desc',
);

$wgExtensionMessagesFiles['DisableAccount'] = dirname(__FILE__)."/DisableAccount.i18n.php";

$wgAutoloadClasses['SpecialDisableAccount'] = dirname(__FILE__)."/SpecialDisableAccount.php";
$wgSpecialPages['DisableAccount'] = 'SpecialDisableAccount';

$wgAvailableRights[] = 'disableaccount';
