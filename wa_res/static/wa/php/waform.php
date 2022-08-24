<?php

	$path_mailer=__DIR__.'/phpmailer/';



	include_once(__DIR__.'/../../../generated/.global.php');


	include_once($path_mailer.'src/POP3.php');

	include_once($path_mailer.'src/PHPMailer.php');
	include_once($path_mailer.'src/SMTP.php');
	include_once($path_mailer.'src/Exception.php');	
	include_once($path_mailer.'src/OAuth.php');	
	
	use PHPMailer\PHPMailer\PHPMailer;

	/*





//test PHP
	 $b_success = true;
$error_string="";

$mail = new PHPMailer(false);
$mail->addAddress('geustache@intuisphere.com'); 
$mail->Subject = 'Here is the subject';
$mail->Body = 'This is the HTML message body';
$b_success = $mail->send();
$error_string = $mail->ErrorInfo;


$json_response=[
"success"=>$b_success,
"message"=>$error_string
];
echo json_encode($json_response);





	
	$conf = [

		"smtp"=>... ,
		"main_to"=>
		"from"=>"",
		"extend_to"=>"",
		"reply_to",
		subject
		body
	]
	*/

	class WaMailFormatter
	{ 
		private $m_message="";
		private $m_attachments=[];
		private $m_form=null;

		private $m_force_email_to_reply = "";


	    function __construct($forms,$current_uuid)
	    {
	    	$this->m_form = $this->getForm($forms,$current_uuid);

	    	$this->format();
	    }

	    private function getForm($forms,$current_uuid)
	    {
	    	foreach ($forms as $form)
        	{
        		$uuid = $form['uuid'];
        		if ($uuid == $current_uuid)
        		{
        			return $form;
        		}
            	
        	}
        	return null;
	    }
	   	function text()
	    {
			return $this->m_message;
	    }
	    function getFieldValue($k)
	    {
			$val='';
			if (isset($_POST)&&array_key_exists($k,$_POST)) $val= $_POST[$k];
			return stripslashes( $val );
	    }
	   	function append($str)
	    {
	    	$this->m_message.=$str."\n";
	    }
	    function format()
	    {
	    	$this->m_message = "";
	    	foreach ($this->m_form['inputs'] as $input)
        	{

	
        		if (array_key_exists('is_file' , $input) && ($input['is_file'] ==true))
        		{
        			$name = $input['name'];
                    if (array_key_exists($name , $_FILES))
                    {
                    	$uploadfile = tempnam(sys_get_temp_dir(), hash('sha256', $_FILES[$name]['name']));
                        if ($uploadfile===FALSE)
                        {
                            $warning_upload = "Warning FileUpload ( tempnam return FALSE)\n";
                        }

                        if ($uploadfile===FALSE)
                        {
                            $dossier = sys_get_temp_dir().'/';
                            $fichier = basename($_FILES[$name]['name']);

                            $uploadfile = $dossier . $fichier;
                        }
                        if (move_uploaded_file($_FILES[$name]['tmp_name'], $uploadfile))
                        {
                            $warning_upload = "";
                            // Attach the uploaded file
                            //$mail->addAttachment($uploadfile,$_FILES[$name]['name']);

                            $attachment= [
                            	"filepath"=>$uploadfile,
                            	"name"=>$_FILES[$name]['name']
                            ];
                            array_push($this->m_attachments, $attachment);
                        }
                        else
                        {
                            $error_upload = "Error FileUpload (move_uploaded_file return FALSE) : ".$_FILES[$name]['error']."\n";
                        }

                    }
        		}
        		else
        		{

        			$name = $input['name'];
	        		$label = $input['label'];
	        		$value = $this->getFieldValue($name);

	        		$this->append($label);
	        		$this->append("");
	        		$this->append($value);
	        		$this->append("-------------------------------");
        			if (array_key_exists('use_email_to_reply' , $input) && ($input['use_email_to_reply'] ==true))
	        		{
	        			$this->m_force_email_to_reply = $value;
	        		}  	
        		}

        	}
/*
			if (strlen($this->m_force_email_to_reply )>0))
    		{
    			$this->append("--------  AUTO REPLY TO ------------");
    			$this->append($this->m_force_email_to_reply);
    		} 
    		*/ 	

	    }	 
	    function forceEmailToReply()
	    {
	    	return $this->m_force_email_to_reply;
	    }

	    function attachments()
	    {
	    	return $this->m_attachments;
	    }

	    function config()
	    {
	    	return $this->m_form['conf'];
	    }
	   
	}

	class WaMailWrapper 
	{ 
	    private $m_error_string="";
	    private $m_attachments=[];
	    private $force_email_reply="";

	    function __construct($conf)
	    {
	    	$param_conf = $conf['config'];
	    	$text = $conf['text'];
	    	$this->m_attachments= $conf['attachments'];
	    	$this->force_email_reply= $conf['force_email_reply'];
	    	//

	    	$default_conf = $GLOBALS['wa_global_mailer_conf']['default_conf'] ;

	    	$this->m_conf = $this->mixConfig($param_conf,$default_conf);

	    	$this->m_mailer = new PHPMailer(false);
	        $this->m_mailer->CharSet = 'UTF-8';
	        $this->m_mailer->ClearReplyTos();


	        $main_to = $this->m_conf['main_to'];


	        //$pieces = explode(",", $main_to);


	        $from = $this->m_conf['from'];
	        if ($from == null)
	        {
	        	$from = $main_to;
	        }
			$this->m_mailer->SetFrom($from);


			$reply_to = $this->m_conf['reply_to'];
			if ($reply_to != null)
	        {
	        	$this->m_mailer->AddReplyTo($reply_to);
	        }


	        if (strlen($this->force_email_reply)>0)
	        {
	        	$this->m_mailer->AddReplyTo($this->force_email_reply);
	        }

			
			$this->m_mailer->AddAddress($main_to);
			if ($this->m_conf['extend_to'] !=null)
			{
				foreach ($this->m_conf['extend_to'] as $to)
	        	{
	            	$this->m_mailer->AddAddress($to);
	        	}
			}

        	$this->m_mailer->Subject = $this->m_conf['subject'];
        	$this->m_mailer->Body    = $text;


			foreach ($this->m_attachments as $attachment)
        	{
        		$this->m_mailer->addAttachment($attachment['filepath'],$attachment['name']);
        	}
        	$smtp = $GLOBALS['wa_global_mailer_conf'] ['smtp'] ;
        	if (($smtp != null)&&($smtp != ''))
        	{
        		        			//PHP
		        $this->m_mailer->isSMTP();// Set mailer to use SMTP
		        $this->m_mailer->Host = $smtp['host'];  // Specify main and backup SMTP servers
		        $this->m_mailer->SMTPSecure = $smtp['protocol']; // Enable TLS encryption, `ssl` also accepted
		        $this->m_mailer->Port = $smtp['port'];

		        $this->m_mailer->SMTPAuth = ($smtp['b_auth']===1)?true:false;    // Enable SMTP authentication
		        $this->m_mailer->Username = $smtp['user']; // SMTP username
		        $this->m_mailer->Password = $smtp['pwd'];           // SMTP password

        	}

        	$this->m_mailer->UseSendmailOptions = $GLOBALS['wa_global_mailer_conf'] ['UseSendmailOptions'];

	    }

      function mixConfig($conf,$default_conf)
      {
        foreach ($conf as $key => $value)
        {
          if (($value ==null))
          {
                   if (array_key_exists($key,$default_conf))
                   {
                     if ($default_conf[$key]!=null)
                     {
                      $conf[$key] = $default_conf[$key];
                     }
                   }
          }
        }
        ///////
        foreach ($default_conf as $key => $value)
        {
          //if (($value !=null)||(strlen($value)>0))
          {
               if (array_key_exists($key,$conf) == false)
               {
                  $conf[$key] = $default_conf[$key];
               }
          }

        }
        return $conf;
      }

	    function errorString()
	    {
	    	return $this->m_error_string;
	    }

	    function send()
	    {
	    	if ($this->m_mailer->send()==true)
	    	{
	    		return true;
	    	}
	    	$this->m_error_string = $this->m_mailer->ErrorInfo;
	    	return false;
	    }

} 