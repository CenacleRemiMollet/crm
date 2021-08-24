<?php

namespace App\Controller\Api;

use Hateoas\HateoasBuilder;
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
use App\Entity\ConfigurationProperty;

class ConfigController extends AbstractController
{
	private $logger;

	public function __construct(LoggerInterface $logger)
	{
		$this->logger = $logger;
	}

	/**
	 * @Route("/api/config/properties", methods={"GET"}, name="api_configuration_properties-get")
	 * @IsGranted("ROLE_ADMIN")
	 * @OA\Get(
	 *     tags={"Configuration"},
	 *     path="/api/config/properties",
	 *     summary="List all configuration properties",
	 *     security = {{"basicAuth": {}}},
	 *     @OA\Response(response="200", description="Successful"),
	 *     @OA\Response(response="401", description="You are not authorized")
	 * )
	 */
	public function getAllProperties(Request $request)
	{
		$properties = $this->getDoctrine()->getManager()
			->getRepository(ConfigurationProperty::class)
			->findAll();

		$hateoas = HateoasBuilder::create()->build();
		$json = json_decode($hateoas->serialize($properties, 'json'));

		return new Response(json_encode($json), 200, array(
			'Content-Type' => 'application/json'
		));
	}

}
