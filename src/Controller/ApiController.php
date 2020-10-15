<?php


namespace App\Controller;

use App\Repository\CityRepository;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/api/v1", name="api_v1_")
 */
class ApiController extends AbstractController
{
    /**
     * retourne les lieux présents dans une ville
     * @Route("/city/{id}/locations", name="get_city_locations")
     */
    public function getCityLocations($id, CityRepository $cityRepository, SerializerInterface $serializer)
    {
        $city = $cityRepository->find($id);
        $json = $serializer->serialize($city, 'json');
        $response = new JsonResponse();
        $response->setJson($json);
        return $response;
    }
}