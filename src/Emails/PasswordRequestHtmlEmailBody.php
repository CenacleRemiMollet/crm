<?php
namespace App\Emails;

use Twig\Environment;

class PasswordRequestHtmlEmailBody extends EmailBody
{
	private $renderer;

	public function __construct(Environment $renderer)
	{
		parent::__construct('text/html', $renderer, 'password-request.html.twig');
	}

}

