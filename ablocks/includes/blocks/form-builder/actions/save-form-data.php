<?php
namespace ABlocks\Blocks\FormBuilder\Actions;

use ABlocks\Blocks\FormBuilder\Actions\Interfaces\FormSubmissionAction;
use ABlocks\Blocks\FormBuilder\ValidateFormData;
use ABlocks\Blocks\FormBuilder\Query;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Save Form data to db
 */
final class SaveFormData implements FormSubmissionAction {

	/** @var $validateformdata */
	private ValidateFormData $validateformdata;

	/**
	 * Contructor
	 *
	 * @param ValidateFormData $obj
	 */
	public function __construct( ValidateFormData $obj ) {
		$this->validateformdata = $obj;
	}

	public function action() {
		if (
			$this->validateformdata->form_info['info']['type'] === 'contact' &&
			! in_array( 'submission', $this->validateformdata->form_info['info']['actions'], true )
		) {
			return;
		}

		// detemine this request coming from subscription form & email is not exists
		if (
			$this->validateformdata->form_info['info']['type'] === 'subscription' &&
			Query::is_email_exists( $this->validateformdata->form_info['info']['email'], 'subscription' )
		) {
			$this->validateformdata->set_error_message( __( 'Already subscribed.', 'ablocks' ) );
			$this->validateformdata->state_data['dont_send_subs_email'] = true;
			return;
		}

		/**
		 * By default both userIp, userAgent are in submissionMetaData, but here it is not available, an empty array.
		 * when a single element is selected, the single element became available
		 * but when both are selected it is not available
		 */
		$settings = $this->validateformdata->form_info['info']['config']['submissionMetaData'] ?? [];

		$ip = '';
		if ( in_array( 'userIp', $settings, true ) ) {
			$ip = sanitize_text_field(
				$_SERVER['HTTP_CF_CONNECTING_IP'] ??
				$_SERVER['HTTP_CLIENT_IP'] ??
				$_SERVER['HTTP_X_FORWARDED_FOR'] ??
				$_SERVER['REMOTE_ADDR'] ?? ''
			);
		}

		$user_agents = '';
		if ( in_array( 'userAgent', $settings, true ) ) {
			$user_agents = sanitize_text_field(
				$_SERVER['HTTP_USER_AGENT'] ?? ''
			);
		}

		// insert data to entry
		$id = Query::add_new_entry(
			$this->validateformdata->form_info['info']['type'],
			$this->validateformdata->form_info['info']['postId'],
			$this->validateformdata->form_info['info']['email'],
			$ip,
			$user_agents,
			$this->validateformdata->form_info['data'],
			$this->validateformdata->form_info['info']['config']['block_id'],
			boolval( $this->validateformdata->form_info['info']['config']['emailVerification'] ?? false )
		);

		if ( $id ) {
			$this->validateformdata->state_data['submission_id'] = $id;
			$this->validateformdata->set_message( __( 'Success!', 'ablocks' ) );
			return;
		}
		$this->validateformdata->set_error_message( __( 'Failed to save data', 'ablocks' ) );
	}
}
