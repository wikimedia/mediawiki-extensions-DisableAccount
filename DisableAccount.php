<?php
// DisableAccount extension: quick extension to disable an account.
// Written by Andrew Garrett, 2010-12-02

$wgExtensionCredits['other'][] = array(
	'path' => __FILE__,
	'name' => 'Disable Account',
	'author' => array( 'Andrew Garrett' ),
	'url' => 'https://www.mediawiki.org/wiki/Extension:DisableAccount',
	'descriptionmsg' => 'disableaccount-desc',
);

$dir = __DIR__ . '/';

$wgMessagesDirs['DisableAccount'] = __DIR__ . '/i18n';
$wgExtensionMessagesFiles['DisableAccountAliases'] = $dir . 'DisableAccount.alias.php';

// Special page classes
$wgAutoloadClasses['SpecialDisableAccount'] = $dir . 'DisableAccount_body.php';
$wgSpecialPages['DisableAccount'] = 'SpecialDisableAccount';

// Add permission required to use Special:DisableAccount
$wgAvailableRights[] = 'disableaccount';

// Log
$wgLogActionsHandlers['block/disableaccount'] = 'DisableAccountLogFormatter';

class DisableAccountLogFormatter extends LogFormatter {
	protected function getMessageParameters() {
		$params = parent::getMessageParameters();
		if ( count( $params ) == 3 ) {
			// Deal with old log entries which don't have this set (needed for GENDER support)
			$params[3] = $this->entry->getTarget()->getRootText();
		}
		return $params;
	}
}
