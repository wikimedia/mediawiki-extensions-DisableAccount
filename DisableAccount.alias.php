<?php
/**
 * Aliases for Special:DisableAccount
 *
 * @file
 */

$specialPageAliases = array();

/** English
 * @author Andrew Garrett
 */
$specialPageAliases['en'] = array(
	'DisableAccount' => array( 'DisableAccount' ),
);

/** German (Deutsch)
 * @author SVG
 */
$specialPageAliases['de'] = array(
	'DisableAccount' => array( 'Benutzerkonto_deaktivieren' ),
);

/**
 * For backwards compatibility with MediaWiki 1.15 and earlier.
 */
$aliases =& $specialPageAliases;
