<?php
namespace App\Controller\Api;

use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Model\ApiHome;
use Hateoas\HateoasBuilder;

class ApiController extends AbstractController
{

    /**
     * @Route("/api", name="api_root_get", methods={"GET"})
     * @OA\Get(
     *     operationId="getApi",
     *     tags={"API"},
     *     path="/api",
     *     summary="API Home",
     *     @OA\Response(
     *         response="200",
     *         description="Successful",
     *         @OA\MediaType(
     *             mediaType="application/hal+json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/ApiHome")
     *             )
     *         )
     *     )
     * )
     */
    public function getApiHome()
    {
        $hateoas = HateoasBuilder::create()->build();
        return new Response(
            $hateoas->serialize(new ApiHome(), 'json'),
            Response::HTTP_OK,
            array('Content-Type' => 'application/hal+json'));
    }
}

