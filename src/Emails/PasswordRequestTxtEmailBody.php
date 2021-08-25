<?php
namespace App\Emails;

use Twig\Environment;

class PasswordRequestTxtEmailBody extends EmailBody
{
	private $renderer;

	public function __construct(Environment $renderer)
	{
		parent::__construct('text/plain', $renderer, 'password-request.txt.twig');
	}

}

