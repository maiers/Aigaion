<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php
/*
BY OYVIND HAUGE

Creates and send an email with the attachments.
*/
class Email_Export {
  function Email_Export()
  {
  }

	function sendEmail($email_address, $messageBody, $publications, $subject='Export from Aigaion')
	{
		$CI = &get_instance();
		$userlogin  = getUserLogin();
		$user       = $CI->user_db->getByID($userlogin->userID());

		$userMail = $user->email;
		if($userMail == '')
			$userMail = EXPORT_REPLY_ADDRESS;

		$userFirst = $user->firstname;
		if($userFirst == '')
			$userFirst = 'Aigion';

		$userSur = $user->surname;
		if($userSur == '')
			$userFirst = __('Export');

		$CI->load->library('email');

		$CI->email->from($userMail, ereg_replace("[^A-Za-z0-9]", "?", $userFirst).' '.ereg_replace("[^A-Za-z0-9]", "?", $userSur));
		$CI->email->to($email_address);
		$CI->email->subject($subject);
		$CI->email->message($messageBody);

		/*
			Adds the attachments
		*/
		foreach ($publications as $publication)
		{
			$aigaion_attachments = $publication->getAttachments();
			if(count($aigaion_attachments) > 0 && file_exists(AIGAION_ATTACHMENT_DIR."/".$aigaion_attachments[0]->location))
			{
				$CI->email->attach(AIGAION_ATTACHMENT_DIR."/".$aigaion_attachments[0]->location);
			}
		}
		return $CI->email->send();
	}




	/*
	Gets publications and calculates the size of the attachments.
	*/
	function attachmentSize($publications)
	{
		$attachmentSize = 0;
		foreach ($publications as $publication) {
			$aigaion_attachments = $publication->getAttachments();
			if(count($aigaion_attachments) > 0 && file_exists(AIGAION_ATTACHMENT_DIR."/".$aigaion_attachments[0]->location))
			{
				$attachmentSize += round(filesize(AIGAION_ATTACHMENT_DIR."/".$aigaion_attachments[0]->location)/1024);
			}
		}
		return $attachmentSize;
	}
}
?>