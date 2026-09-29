<?php

namespace App\Controller\Api;

use Hateoas\HateoasBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use App\Util\RequestUtil;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use App\Model\LocaleModel;
use App\Exception\ViolationException;
use primus852\ShortResponse\ShortResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\ConfigurationProperty;
use App\Model\ConfigurationPropertyUpdate;
use App\Model\ConfigurationPropertyView;
use App\Service\ConfigurationPropertyService;
use App\Entity\Events;
use App\Exception\CRMException;
use App\Service\PlanningColors;

class ConfigController extends AbstractController
{
    use \App\Controller\DoctrineSubscriberTrait;

	private $logger;

	public function __construct(LoggerInterface $logger)
	{
		$this->logger = $logger;
	}

	#[OA\Get(
	    path: '/api/config/properties',
	    operationId: 'getAllProperties',
	    summary: 'List all configuration properties',
	    security: [['basicAuth' => []]],
	    tags: ['Configuration'],
	    responses: [
	        new OA\Response(
	            response: '200',
	            description: 'Successful',
	            content: [
	                new OA\MediaType(
	                    mediaType: 'application/hal+json',
	                    schema: new OA\Schema(
	                        type: 'array',
	                        items: new OA\Items(ref: '#/components/schemas/ConfigurationProperty')
	                    )
	                )
	            ]
	        ),
	        new OA\Response(
	            response: '401',
	            description: 'You are not authorized',
	            content: [
	                new OA\MediaType(
	                    mediaType: 'application/hal+json',
	                    schema: new OA\Schema(ref: '#/components/schemas/Error')
	                )
	            ]
	        )
	    ]
	)]
    #[Route(path: '/api/config/properties', methods: ['GET'], name: 'api_configuration_properties-get')]
    #[IsGranted('ROLE_ADMIN')]
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


	#[OA\Patch(
	    path: '/api/config/properties',
	    operationId: 'updateProperties',
	    summary: 'Update some properties',
	    security: [['basicAuth' => []]],
	    requestBody: new OA\RequestBody(
	        content: [
	            new OA\MediaType(
	                mediaType: 'application/json',
	                schema: new OA\Schema(
	                    type: 'array',
	                    items: new OA\Items(ref: '#/components/schemas/ConfigurationPropertyUpdate')
	                )
	            )
	        ]
	    ),
	    tags: ['Configuration'],
	    parameters: [
	        new OA\Parameter(
	            name: 'X-ClientId',
	            in: 'header',
	            required: true,
	            schema: new OA\Schema(type: 'string', format: 'string', pattern: '[a-z0-9_]{2,64}'),
	            example: 'my-client-name'
	        )
	    ],
	    responses: [
	        new OA\Response(response: '204', description: 'Successful'),
	        new OA\Response(
	            response: '400',
	            description: 'Request contains not valid field',
	            content: [
	                new OA\MediaType(
	                    mediaType: 'application/hal+json',
	                    schema: new OA\Schema(ref: '#/components/schemas/Error')
	                )
	            ]
	        ),
	        new OA\Response(
	            response: '401',
	            description: 'You are not authorized',
	            content: [
	                new OA\MediaType(
	                    mediaType: 'application/hal+json',
	                    schema: new OA\Schema(ref: '#/components/schemas/Error')
	                )
	            ]
	        )
	    ]
	)]
    #[Route(path: '/api/config/properties', methods: ['PATCH'], name: 'api_configuration_properties-update')]
    #[IsGranted('ROLE_ADMIN')]
    public function updateProperties(Request $request, SerializerInterface $serializer, TranslatorInterface $translator)
	{
		$requestUtil = new RequestUtil($serializer, $translator);
    	//$propertiesToUpdate = $requestUtil->validate($request, ConfigurationPropertyUpdate::class);
		$propertiesToUpdate = $requestUtil->validate($request, 'App\Model\ConfigurationPropertyUpdate[]');

		// validate everything before writing anything
		foreach($propertiesToUpdate as $propertyToUpdate) {
			if(strpos($propertyToUpdate->getKey(), PlanningColors::PREFIX) === 0) {
				$error = PlanningColors::validate($propertyToUpdate->getKey(), $propertyToUpdate->getValue());
				if($error !== null) {
					throw new CRMException(Response::HTTP_BAD_REQUEST, $error, [$propertyToUpdate->getKey() => $error]); // 400
				}
			}
		}

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
