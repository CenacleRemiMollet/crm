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
use App\Model\ConfigurationPropertyUpdate;
use App\Model\ConfigurationPropertyView;
use App\Service\ConfigurationPropertyService;
use App\Entity\Events;

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
	 *     operationId="getAllProperties",
	 *     tags={"Configuration"},
	 *     path="/api/config/properties",
	 *     summary="List all configuration properties",
	 *     security = {{"basicAuth": {}}},
	 *     @OA\Response(
	 *         response="200",
	 *         description="Successful",
	 *         @OA\MediaType(
	 *             mediaType="application/hal+json",
	 *             @OA\Schema(
	 *                 type="array",
	 *                 @OA\Items(ref="#/components/schemas/ConfigurationProperty")
	 *             )
	 *         )
	 *     ),
	 *     @OA\Response(response="401", description="You are not authorized", @OA\MediaType(mediaType="application/hal+json", @OA\Schema(ref="#/components/schemas/Error")))
	 * )
	 */
	public function getAllProperties(Request $request)
	{
		$properties = $this->container->get('doctrine')->getManager()
			->getRepository(ConfigurationProperty::class)
			->findAll();
		$propModels = array();
		foreach ($properties as &$property) {
			array_push($propModels, new ConfigurationPropertyView($property));
		}

		$hateoas = HateoasBuilder::create()->build();
		return new Response(
		    $hateoas->serialize($propModels, 'json'),
		    Response::HTTP_OK,
		    array('Content-Type' => 'application/json'));
	}


	/**
	 * @Route("/api/config/properties", methods={"PATCH"}, name="api_configuration_properties-update")
	 * @IsGranted("ROLE_ADMIN")
	 * @OA\Patch(
	 *     operationId="updateProperties",
	 *     tags={"Configuration"},
	 *     path="/api/config/properties",
	 *     summary="Update some properties",
	 *     @OA\Parameter(name="X-ClientId", in="header", required=true, example="my-client-name", @OA\Schema(format="string", type="string", pattern="[a-z0-9_]{2,64}")),
	 *     security = {{"basicAuth": {}}},
	 *     @OA\RequestBody(
	 *         @OA\MediaType(
	 *            mediaType="application/json",
	 *            @OA\Schema(
	 *               type="array",
	 *               @OA\Items(ref="#/components/schemas/ConfigurationPropertyUpdate")
	 *            )
	 *        )
	 *     ),
	 *     @OA\Response(response="204", description="Successful"),
	 *     @OA\Response(response="400", description="Request contains not valid field", @OA\MediaType(mediaType="application/hal+json", @OA\Schema(ref="#/components/schemas/Error"))),
	 *     @OA\Response(response="401", description="You are not authorized", @OA\MediaType(mediaType="application/hal+json", @OA\Schema(ref="#/components/schemas/Error")))
	 * )
	 */
	public function updateProperties(Request $request, SerializerInterface $serializer, TranslatorInterface $translator)
	{
		$requestUtil = new RequestUtil($serializer, $translator);
    	//$propertiesToUpdate = $requestUtil->validate($request, ConfigurationPropertyUpdate::class);
		$propertiesToUpdate = $requestUtil->validate($request, 'App\Model\ConfigurationPropertyUpdate[]');

		$account = $this->getUser();
		$doctrine = $this->container->get('doctrine');
        $propService = new ConfigurationPropertyService($doctrine->getManager());
		$data = array();
		foreach($propertiesToUpdate as &$propertyToUpdate) {
			$propService->update($account, $propertyToUpdate);
			$data[$propertyToUpdate->getKey()] = $propertyToUpdate->getValue();
		}
		
		Events::add($doctrine, Events::CONFIG_PROPERTIES_SAVED, $this->getUser(), $request, $data);

		return new Response('', Response::HTTP_NO_CONTENT);
	}


}
