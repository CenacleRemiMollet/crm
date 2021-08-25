<?php
namespace App\Emails;

use Twig\Environment;
use Symfony\Contracts\Translation\TranslatorInterface;
use Psr\Log\LoggerInterface;

/**
 *
 * @author f.agu
 *
 */
class EmailFactory
{
	const DEFAULT_FROM = ['remi.mollet@cenaclerm.fr' => 'Me Rémi Mollet'];

	private $mailer;
	private $translator;
	private $renderer;
	private $logger;

	public function __construct(\Swift_Mailer $mailer, TranslatorInterface $translator, Environment $renderer, LoggerInterface $logger)
	{
		$this->mailer = $mailer;
		$this->translator = $translator;
		$this->renderer = $renderer;
		$this->logger = $logger;
	}

	public function buildAndSend(EmailParameters $params, $to, $vars, $from = self::DEFAULT_FROM) {
		$title = $this->translator->trans($params->getTitle());
		$bodies = $params->getBodies($this->renderer);

		$totxt = is_array($to) ? implode(', ', $to) : $to;
		$this->logger->debug('Sending email "'.$params->getTitle().'" to: '.$totxt);

		$message = (new \Swift_Message($title))
		->setFrom($from)
		->setTo($to)
		->setBody($bodies[0]->getContent($vars), $bodies[0]->getMimeType());

		foreach (array_slice($bodies, 1) as $body) {
			$message->addPart($body->getContent($vars), $body->getMimeType());
		}

		$numSent = $this->mailer->send($message);
		$this->logger->debug('Email sent "'.$params->getTitle().'" to "'.$totxt.'": '.$numSent);
	}
}

