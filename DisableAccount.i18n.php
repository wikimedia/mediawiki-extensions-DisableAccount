<?php

// Internationalisation file for the DisableAccount extension

$messages['en'] = array(
	'disableaccount-desc' => 'Allows administrators to disable individual accounts.',
	'right-disableaccount' => 'Disable accounts',
	'disableaccount' => 'Disable a user account',
	'disableaccount-user' => 'User name:',
	'disableaccount-confirm' => "Disable this user account.
The user will not be able to log in, reset their password, or receive email notifications.
If the user is currently logged in anywhere, they will be immediately logged out.
''Note that disabling an account is not reversible without system administrator intervention.''",
	'disableaccount-mustconfirm' => 'You must confirm that you wish to disable this account.',
	'disableaccount-nosuchuser' => 'The user account "$1" does not exist.',
	'disableaccount-success' => 'The user account "$1" has been permanently disabled.',
);