<?php

class SpecialDisableAccount extends SpecialPage {
	function __construct() {
		parent::__construct( 'DisableAccount', 'disableaccount',
					true, array( $this, 'show' ) );
	}

	public function show( $par ) {
		$formFields = array(
			'account' => array(
				'type' => 'text',
				'validation-callback' => array( __CLASS__, 'validateUser' ),
				'label-message' => 'disableaccount-user',
			),
			'confirm' => array(
				'type' => 'toggle',
				'validation-callback' => array( __CLASS__, 'checkConfirmation' ),
				'label-message' => 'disableaccount-confirm',
			),
		);

		$htmlForm = new HTMLForm( $formFields, 'disableaccount' );

		$htmlForm->setSubmitCallback( array( __CLASS__, 'submit' ) );
		$htmlForm->setTitle( $this->getTitle() );

		$htmlForm->show();
	}

	static function validateUser( $field, $allFields ) {
		$u = User::newFromName( $field );

		if ( $u && $u->getID() != 0 ) {
			return true;
		} else {
			return wfMessage( 'disableaccount-nosuchuser', array( $field ) )->parse();
		}
	}

	static function checkConfirmation( $field, $allFields ) {
		if ( $field ) {
			return true;
		} else {
			return wfMessage( 'disableaccount-mustconfirm' )->parse();
		}
	}

	static function submit( $fields ) {
		global $wgOut, $wgUser;
		$user = User::newFromName( $fields['account'] );

		$user->setPassword( null );
		$user->setEmail( null );
		$user->setToken();
		$user->addGroup( 'inactive' );

		$user->saveSettings();
		$user->invalidateCache();

		$logEntry = new ManualLogEntry( 'block', 'disableaccount' );
		$logEntry->setPerformer( $wgUser );
		$logEntry->setTarget( $user->getUserPage() );
		$logId = $logEntry->insert();
		$logEntry->publish( $logId );

		$wgOut->addWikiMsg( 'disableaccount-success', $user->getName() );

		return true;
	}
}
