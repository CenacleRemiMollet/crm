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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class ConfigController extends AbstractController
{
	private $logger;

	public function __construct(LoggerInterface $logger)
	{
		$this->logger = $logger;
	}

	/**
	 * @Route("/config", methods={"GET"}, name="web_config-get")
	 * @IsGranted("ROLE_ADMIN")
	 */
	public function getConfig(Request $request)
	{
		return new Response('{}', 200, array(
			'Content-Type' => 'application/json'
		));
	}

}
