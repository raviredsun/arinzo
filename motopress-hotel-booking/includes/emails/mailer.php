<?php



namespace MPHB\Emails;



class Mailer {



	/**

	 * Send an email.

	 * @param string|array $to Array or comma-separated list of email addresses to send message.

	 * @param string $subject

	 * @param string $message

	 * @param array|string $headers Optional. Additional headers.

	 * @param array|string $attachments Optional. Files to attach.

	 * @return bool success

     *

     * @since 3.7.1 added new action - "mphb_before_send_mail".

     * @since 3.7.1 added new action - "mphb_after_send_mail".

	 */

	public function send( $to, $subject, $message, $headers = null, $attachments = null ){



		add_filter( 'wp_mail_from', array( $this, 'filterFromEmail' ) );

		add_filter( 'wp_mail_from_name', array( $this, 'filterFromName' ) );

		add_filter( 'wp_mail_content_type', array( $this, 'filterContentType' ) );



        do_action('mphb_before_send_mail');



		$result = wp_mail( $to, $subject, $message, $headers, $attachments );



        do_action('mphb_after_send_mail');



		remove_filter( 'wp_mail_from', array( $this, 'filterFromEmail' ) );

		remove_filter( 'wp_mail_from_name', array( $this, 'filterFromName' ) );

		remove_filter( 'wp_mail_content_type', array( $this, 'filterContentType' ) );



		return $result;

	}



	/**

	 * Filter the from name for outgoing emails.

	 *

	 * @param string $fromName

	 *

	 * @return string

	 */

	public function filterFromName( $fromName ){

		return wp_specialchars_decode( esc_html( MPHB()->settings()->emails()->getFromName() ), ENT_QUOTES );

	}



	/**

	 * Filter the from address for outgoing emails.

	 *

	 * @param string $fromAddress

	 *

	 * @return string

	 */

	public function filterFromEmail( $fromAddress ){

		return sanitize_email( MPHB()->settings()->emails()->getFromEmail() );

	}



	/**

	 * Filter email content type.

	 *

	 * @param string $contentType

	 *

	 * @return string

	 */

	public function filterContentType( $contentType ){

		return 'text/html';

	}



}

