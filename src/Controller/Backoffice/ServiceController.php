<?php

namespace App\Controller\Backoffice;

use App\Entity\Service;
use App\Form\ServiceType;
use App\Repository\ServiceRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/admin/service", name="admin_service_")
 */
class ServiceController extends AbstractController
{
    private ServiceRepository $serviceRepository;
    
    function __construct(ServiceRepository $serviceRepository) {
        $this->serviceRepository = $serviceRepository;
    }
    /**
     * @Route("/", name="index")
     */
    public function index(Request $request): Response
    {
        return $this->render("admin/service/list.html.twig", [
            "services" => $this->serviceRepository->findAll()
        ]);
    }

    /**
     * @Route("/new", name="new")
     */
    function new_service(Request $request)
    {
        $formService = $this->createForm(ServiceType::class, $service = new Service());
        $formService->handleRequest($request);
        $response = [];

        if($formService->isSubmitted() && $formService->isValid()) {
            try {
                $service->setCreatedAt(new \DateTimeImmutable());
                
                // Save into database the new Service object
                $this->serviceRepository->add($service, true);

                $response = [
                    "class" => "success",
                    "message" => "Le nouveau service a bien été ajouté"
                ];
            } catch(\Exception $e) {
                $response = [
                    "class" => "danger",
                    "message" => $e->getMessage()
                ];
            } finally {}
        }

        return $this->render("admin/service/form.html.twig", [
            "formService" => $formService->createView(),
            "response" => $response
        ]);
    }

    /**
     * @Route("/{serviceID}/edit", requirements={"serviceID" = "^\d+(?:\d+)?$"}, name="edit")
     */
    public function edit_service(Request $request, int $serviceID) 
    {
        $service = $this->serviceRepository->find($serviceID);
        if(!$service) {
            return $this->redirectToRoute("admin_service_index");
        }

        $formService = $this->createForm(ServiceType::class, $service);
        $formService->handleRequest($request);
        $response = [];

        if($formService->isSubmitted() && $formService->isValid()) {
            try {
                // Save all changes into bdd
                $this->serviceRepository->add($service, true);

                // Return a response to the user
                $response = [
                    "class" => "success",
                    "message" => "Le service a bien été mise à jour"
                ];
            } catch(\Exception $e) {
                $response = [
                    "class" => "danger",
                    "message" => $e->getMessage()
                ];
            } finally {}
        }

        return $this->render("admin/service/form.html.twig", [
            "formService" => $formService->createView(),
            "response" => $response
        ]);
    }

    /**
     * @Route("/{serviceID}/remove", requirements={"serviceID" = "^\d+(?:\d+)?$"}, name="remove")
     */
    public function remove_service(Request $request, int $serviceID) {
        $service = $this->serviceRepository->find($serviceID);
        if(!$service) {
            return $this->redirectToRoute("admin_service_index");
        }

        try {
            $this->serviceRepository->remove($service, true);
        } catch(\Exception $e) {
            return $this->json($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json(null, Response::HTTP_OK);
    }
}
