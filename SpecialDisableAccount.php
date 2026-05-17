<?php

use MediaWiki\HTMLForm\HTMLForm;
use MediaWiki\SpecialPage\SpecialPage;
use MediaWiki\User\User;
use MediaWiki\User\UserGroupManager;

/**
 * @todo This should use FormSpecialPage
 */
class SpecialDisableAccount extends SpecialPage {
	public function __construct(
		private readonly UserGroupManager $userGroupManager,
	) {
		parent::__construct( 'DisableAccount' );
	}

	/** @inheritDoc */
	public function getRestriction(): string {
		return 'disableaccount';
	}

	public function doesWrites() {
		return true;
	}

	public function execute( $par ) {
		$this->setHeaders();
		$this->checkPermissions();

		$formFields = [
			'account' => [
				'type' => 'text',
				'required' => true,
				'label-message' => 'disableaccount-user',
			],
			'confirm' => [
				'type' => 'toggle',
				'validation-callback' => $this->checkConfirmation( ... ),
				'label-message' => 'disableaccount-confirm',
			],
		];

		$htmlForm = HTMLForm::factory( 'ooui', $formFields, $this->getContext(), 'disableaccount' );

		$htmlForm->setSubmitCallback( $this->submit( ... ) );

		$htmlForm->show();
	}

	/**
	 * @param mixed $field
	 * @return string|true
	 */
	private function checkConfirmation( $field ) {
		if ( $field ) {
			return true;
		} else {
			return $this->msg( 'disableaccount-mustconfirm' )->parse();
		}
	}

	/**
	 * @param array $fields
	 * @param HTMLForm $form
	 * @return string|true
	 */
	private function submit( $fields, $form ) {
		// While we're not actually turning the user into a "system" user, it
		// has the same end result: all passwords and other authentication
		// credentials removed or set to something invalid, email blanked,
		// token invalidated, and existing sessions dropped. So let's just use
		// that if possible instead of duplicating all the code.
		if ( is_callable( 'User::newSystemUser' ) ) {
			$user = User::newSystemUser( $fields['account'], [ 'create' => false, 'steal' => true ] );
			if ( !$user ) {
				return $this->msg( 'disableaccount-nosuchuser', $fields['account'] )->text();
			}
		} else {
			$user = User::newFromName( $fields['account'] );

			if ( !$user || $user->getId() === 0 ) {
				return $this->msg( 'disableaccount-nosuchuser', $fields['account'] )->text();
			}

			$user->setPassword( null );
			$user->setEmail( null );
			$user->setToken();
		}

		$this->userGroupManager->addUserToGroup( $user, 'inactive' );

		$user->saveSettings();
		$user->invalidateCache();

		$logEntry = new ManualLogEntry( 'block', 'disableaccount' );
		$logEntry->setPerformer( $form->getUser() );
		$logEntry->setTarget( $user->getUserPage() );
		$logEntry->setParameters( [ '4::targetUsername' => $user->getName() ] );
		$logId = $logEntry->insert();
		$logEntry->publish( $logId );

		$this->getOutput()->addWikiMsg( 'disableaccount-success', $user->getName() );

		return true;
	}
}
