<?php
/**
 * @todo This should use FormSpecialPage
 */
class SpecialDisableAccount extends SpecialPage {
	function __construct() {
		parent::__construct( 'DisableAccount', 'disableaccount' );
	}

	public function doesWrites() {
		return true;
	}

	public function execute( $par ) {
		$this->setHeaders();
		$this->checkPermissions();

		$formFields = array(
			'account' => array(
				'type' => 'text',
				'required' => true,
				'label-message' => 'disableaccount-user',
			),
			'confirm' => array(
				'type' => 'toggle',
				'validation-callback' => array( __CLASS__, 'checkConfirmation' ),
				'label-message' => 'disableaccount-confirm',
			),
		);

		$htmlForm = new HTMLForm( $formFields, $this->getContext(), 'disableaccount' );

		$htmlForm->setSubmitCallback( array( __CLASS__, 'submit' ) );

		$htmlForm->show();
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

		if ( !$user || $user->getId() === 0 ) {
			return wfMessage( 'disableaccount-nosuchuser', $fields['account'] )->text();
		}

		$user->setPassword( null );
		$user->setEmail( null );
		$user->setToken();
		$user->addGroup( 'inactive' );

		$user->saveSettings();
		$user->invalidateCache();

		$logEntry = new ManualLogEntry( 'block', 'disableaccount' );
		$logEntry->setPerformer( $wgUser );
		$logEntry->setTarget( $user->getUserPage() );
		$logEntry->setParameters( array( '4::targetUsername' => $user->getName() ) );
		$logId = $logEntry->insert();
		$logEntry->publish( $logId );

		$wgOut->addWikiMsg( 'disableaccount-success', $user->getName() );

		return true;
	}
}
