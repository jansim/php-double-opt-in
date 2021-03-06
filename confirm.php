<?
require_once('vendor/autoload.php');

// let's define some outcomes for this page
define('STATUS_DEFAULT', 0);
define('STATUS_SUCCESS', 1);
define('STATUS_ERROR', 2);
$status = STATUS_DEFAULT;

$confirmationCode = $_GET['confirmationCode'];

$Registration = new Registration();
try {
	$Registration->fetchByConfirmationCode($confirmationCode);
	$Registration->confirm();
	
	$Email = new Email();
	$Email->subject = $settings['email']['confirmed_subject'];
	$Email->recipient = $Registration->email;

	$mailData = array(
		'link' => $settings['link_url_root'] . 'unsubscribe.php?email=' . urlencode($Registration->email)
	);
	$mailData = array_merge($mailData, $Registration->fields);
	$Email->message_text = Renderer::renderMail('mail_confirmed_txt', $mailData);
	$Email->message_html = Renderer::renderMail('mail_confirmed', $mailData);

	$Email->send();
	
	$status = STATUS_SUCCESS;
} catch(Exception $e) {
	$status = STATUS_ERROR;
}

switch($status) {
	case STATUS_SUCCESS:
		Renderer::page('confirmation_success', array(
			'email' => $Registration->email
		));
		break;
	
	case STATUS_ERROR:
		Renderer::page('confirmation_error', array());
		break;
	
	default:
		Renderer::error();
}

?>
