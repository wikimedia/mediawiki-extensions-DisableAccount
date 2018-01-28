<?php

$IP = getenv( 'MW_INSTALL_PATH' );
if ( $IP === false ) {
	$IP = __DIR__ . '/../../..';
}
require_once "$IP/maintenance/Maintenance.php";

/**
 * Script to migrate disabled accounts to blocked accounts. This will also remove these users
 * from the 'inactive' group. Note that these users will still need sysadmin help to restore
 * their account as their email and password is set to null when the account is disabled.
 */
class BlockDisabledAccounts extends Maintenance {
	public function __construct() {
		parent::__construct();
		$this->addOption( 'reason', 'Block reason', false, true );
		$this->setBatchSize( 10 );

		$this->requireExtension( 'Disable Account' );
	}

	public function execute() {
		$dbr = wfGetDB( DB_REPLICA );
		$ids = $dbr->selectFieldValues(
			'user_groups',
			'ug_user',
			[
				'ug_group' => 'inactive',
				'ug_expiry IS NULL OR ug_expiry >= ' . $dbr->addQuotes( $dbr->timestamp() )
			],
			__METHOD__
		);

		$disabledCount = count( $ids );
		if ( $disabledCount === 0 ) {
			$this->output( "No users in 'inactive' group.\n" );
			return;
		}

		$counter = 0;
		$success = 0;
		$users = UserArray::newFromIDs( $ids );

		$this->output( "Starting migration...\n" );
		foreach ( $users as $user ) {
			if ( $counter >= $this->mBatchSize ) {
				$counter = 0;
				wfWaitForSlaves();
			}

			$counter++;
			if ( $this->doBlockAndLog( $user ) ) {
				$user->removeGroup( 'inactive' );
				$success++;
			}
		}

		$this->output( "Total $disabledCount users in 'inactive' group. " .
			"Successfully migrated $success users\n" );
	}

	/*
	 * Attempt to block the given user. If user is already blocked, modify
	 * the existing block. If block was successful, insert log entry as well.
	 *
	 * @param User $user
	 *
	 * @return bool true on success, false on failure
	 */
	private function doBlockAndLog( User $user ) {
		global $wgContLang;
		$block = $user->getBlock();
		$alreadyBlocked = ( $block === null ) ? false : true;

		if ( $block === null ) {
			$block = new Block();
		}
		$reason = $this->getOption( 'reason', 'Convert disabled account to blocked account' );
		$scriptUser = User::newFromName( 'Maintenance script' );

		$block->setTarget( $user );
		$block->setBlocker( $scriptUser );
		if ( class_exists( CommentStore::class ) ) {
			// CommentStore handles truncation
			$block->mReason = $reason;
		} else {
			$block->mReason = $wgContLang->truncate( $reason, 255 );
		}
		$block->mExpiry = 'infinity';
		$block->prevents( 'sendemail', true );
		$block->prevents( 'editownusertalk', true );

		// Try to update block if user is already blocked. Otherwise, attempt to insert a new one.
		$success = $alreadyBlocked ? $block->update() : $block->insert();

		if ( is_array( $success ) ) {
			$logAction = $alreadyBlocked ? 'reblock' : 'block';
			$logParams = [];
			$logParams['5::duration'] = 'infinity';
			$logParams['6::flags'] = 'noemail,nousertalk';

			$logEntry = new ManualLogEntry( 'block', $logAction );
			$logEntry->setTarget( Title::makeTitle( NS_USER, $user->getName() ) );
			$logEntry->setComment( $reason );
			$logEntry->setPerformer( $scriptUser );
			$logEntry->setParameters( $logParams );
			$logEntry->setRelations( [ 'ipb_id' => [ $success['id'] ] ] );
			$logId = $logEntry->insert();
			$logEntry->publish( $logId );
			return true;
		}

		return false;
	}
}

$maintClass = 'BlockDisabledAccounts';
require_once RUN_MAINTENANCE_IF_MAIN;
