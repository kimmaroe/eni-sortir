<?php


namespace App\Controller;

use App\Entity\Location;
use App\Form\LocationType;
use App\Repository\CityRepository;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/api/v1", name="api_v1_")
 */
class ApiController extends AbstractController
{
    /**
     * retourne les lieux présents dans une ville
     * @Route("/city/{id}/locations", name="get_city_locations", methods={"GET"})
     */
    public function getCityLocations($id, CityRepository $cityRepository, SerializerInterface $serializer)
    {
        $city = $cityRepository->find($id);
        $json = $serializer->serialize($city, 'json');
        $response = new JsonResponse();
        $response->setJson($json);
        return $response;
    }

    /**
     * @Route("/location/", name="location_create", methods={"POST"})
     */
    public function createLocation(Request $request, SerializerInterface $serializer)
    {
        $location = new Location();
        $location->setDateCreated(new \DateTime());

        $form = $this->createForm(LocationType::class, $location);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $em = $this->getDoctrine()->getManager();
            $em->persist($location);
            $em->flush();

            $json = $serializer->serialize($location, 'json');
            $response = new JsonResponse();
            $response->setJson($json);
            return $response;
        }
        elseif ($form->isSubmitted()) {
            $formErrors = $form->getErrors(false, true);
            $json = $serializer->serialize($formErrors, 'json');
            $response = new JsonResponse();
            $response->setStatusCode(400);
            $response->setJson($json);
            return $response;
        }
        else {
            throw new BadRequestHttpException('wtf');
        }
    }
}