<?php

namespace App\Controller\Debug;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use App\Emails\EmailTypes;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Emails\EmailFactory;
use Psr\Log\LoggerInterface;


/**
 * @Route("/debug")
 */
class EmailsPreviewController extends AbstractController
{
	/**
	 * @Route("/email", name="web_email_list-type", methods={"GET"})
	 * @IsGranted("ROLE_ADMIN")
	 */
	public function listTypes()
	{
		return $this->render('emails/viewEmailList.html.twig', [
			'emailTypes' => array_keys(EmailTypes::getTypes())
		]);
	}

	/**
	 * @Route("/email/{name}", name="web_email_list-mimetypes", methods={"GET"})
	 * @IsGranted("ROLE_ADMIN")
	 */
	public function listMimeTypes($name)
	{
		$parameters = EmailTypes::getTypes()[$name];
		if($parameters == null) {
			return new Response('Name not found: '.$name, Response::HTTP_NOT_FOUND,
				['Content-Type' => 'text/plain']);
		}
		$bodies = $parameters->getBodies($this->container->get('twig'));
		$mts = [];
		foreach ($bodies as $body) {
			array_push($mts, $this->formatMimeType($body->getMimeType()));
		}

		return $this->render('emails/viewEmailMimeTypeList.html.twig', [
			'emailType' => $name,
			'mimeTypes' => $mts
		]);
	}


	/**
	 * @Route("/email/{name}", name="web_email_send", methods={"POST"})
	 * @IsGranted("ROLE_ADMIN")
	 */
	public function sendMail(Request $request, \Swift_Mailer $mailer, TranslatorInterface $translator, LoggerInterface $logger, $name)
	{
		$parameters = EmailTypes::getTypes()[$name];
		if($parameters == null) {
			return new Response('Name not found: '.$name, Response::HTTP_NOT_FOUND,
				['Content-Type' => 'text/plain']);
		}
		$to = $request->request->get('to');
		$logger->warning($to);
		$mailFactory = new EmailFactory($mailer, $translator, $this->container->get('twig'), $logger);

		$mailFactory->buildAndSend(
			$parameters,
			$to,
			$parameters->getFakeValues());

		return $this->listMimeTypes($name);
	}


	/**
 	 * @Route("/email/{name}/{mt}", name="web_email_name_mimetype", methods={"GET"})
 	 * @IsGranted("ROLE_ADMIN")
	 */
	public function viewEmailMimeType($name, $mt)
	{
		$parameters = EmailTypes::getTypes()[$name];
		if($parameters == null) {
			return new Response('Name not found: '.$name, Response::HTTP_NOT_FOUND,
				['Content-Type' => 'text/plain']);
		}
		$bodies = $parameters->getBodies($this->container->get('twig'));

		$inputmt = $this->formatMimeType($mt);

		$mts = [];
		foreach($bodies as $body) {
			$nmt = $this->formatMimeType($body->getMimeType());
			if($inputmt === $nmt) {
				return new Response(
					$body->getContent($parameters->getFakeValues()),
					Response::HTTP_OK,
					['Content-Type' => $body->getMimeType()]
				);
			}
			array_push($mts, $nmt);
		}
		return new Response(
			'MimeType not found: '.$mt.'.  Available MimeTypes: '.implode(', ', $mts),
			Response::HTTP_NOT_FOUND,
			['Content-Type' => 'text/plain']
		);
	}

	private function formatMimeType($mimeType) {
		return str_replace('/', '', $mimeType);
	}

}
