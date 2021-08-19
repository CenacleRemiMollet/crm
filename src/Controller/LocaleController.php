<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Annotations as OA;
use Psr\Log\LoggerInterface;
use App\Util\RequestUtil;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use App\Model\LocaleModel;
use App\Exception\ViolationException;
use primus852\ShortResponse\ShortResponse;

class LocaleController extends AbstractController
{
	private $logger;

	public function __construct(LoggerInterface $logger)
	{
		$this->logger = $logger;
	}

	/**
	 * @Route("/locale", methods={"GET"}, name="web_locale-get")
	 * @OA\Get(
	 *     tags={"Web"},
	 *     path="/locale",
	 *     summary="Get the current locale",
	 *     @OA\Response(response="200", description="Successful")
	 * )
	 */
	public function getLocale(Request $request)
	{
		$locale = $request->getLocale();
		return new Response('{"locale": "'.$locale.'"}', 200, array(
			'Content-Type' => 'application/json'
		));
	}

	/**
 	 * @Route("/locale", methods={"PUT"}, name="web_locale-put")
	 */
	public function putLocale(Request $request, SerializerInterface $serializer, TranslatorInterface $translator)
	{
		$requestUtil = new RequestUtil($serializer, $translator);
		try {
			$localeModel = $requestUtil->validate($request, LocaleModel::class);
		} catch (ViolationException $e) {
			return ShortResponse::error("data", $e->getErrors())
				->setStatusCode(Response::HTTP_BAD_REQUEST);
		}
		$locale = $localeModel->getLocale();
		//$this->logger->debug('Set locale to "'.$locale.'"');
		$request->setLocale($locale);
		$request->getSession()->set('_locale', $locale);
		$request->getSession()->set('_locale2', $locale);
		//$this->logger->debug('Setted locale to "'.$request->getLocale().'"');
		$json = $serializer->serialize($localeModel, 'json');
		return new Response($json, 200, array(
			'Content-Type' => 'application/json'
		));
	}


}
