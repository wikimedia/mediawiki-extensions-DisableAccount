<?php
if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'DisableAccount' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['DisableAccount'] = __DIR__ . '/i18n';
	$wgExtensionMessagesFiles['DisableAccountAliases'] = __DIR__ . '/DisableAccount.alias.php';
	/* wfWarn(
		'Deprecated PHP entry point used for DisableAccount extension. ' .
		'Please use wfLoadExtension instead, ' .
		'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	); */
	return;
} else {
	die( 'This version of the DisableAccount extension requires MediaWiki 1.25+' );
}
