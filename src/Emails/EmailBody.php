<?php
namespace App\Emails;

use Twig\Environment;


abstract class EmailBody
{
	private $mimeType;
	private $renderer;
	private $template;

	public function __construct(string $mimeType, Environment $renderer, string $template)
	{
		$this->mimeType = $mimeType;
		$this->renderer = $renderer;
		$this->template = $template;
	}

	public function  getMimeType(): string
	{
		return $this->mimeType;
	}

	public function getContent($vars) {
		return $this->renderer->render('emails/'.$this->template, $vars);
	}
}

