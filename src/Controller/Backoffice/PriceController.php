<?php

namespace App\Controller\Backoffice;

use App\Entity\Price;
use App\Form\PriceType;
use App\Repository\PriceRepository;
use App\Repository\PriceDetailRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/admin/price", name="admin_price_")
 */
class PriceController extends AbstractController
{
    private PriceRepository $priceRepository;
    private PriceDetailRepository $priceDetailRepository;

    function __construct(PriceRepository $priceRepository, PriceDetailRepository $priceDetailRepository) {
        $this->priceRepository = $priceRepository;
        $this->priceDetailRepository = $priceDetailRepository;
    }
    
    /**
     * @Route("/", name="index")
     */
    public function index(Request $request): Response
    {
        return $this->render("admin/price/list.html.twig", [
            "prices" => $this->priceRepository->findAll()
        ]);
    }

    /**
     * @Route("/new", name="new")
     * @Route("/{id}/edit", name="edit")
     */
    public function new_price(Request $request, Price $price = null) {
        $response = [];
        $price = $price ?? (new Price())->setCreatedAt(new \DateTimeImmutable());

        $priceForm = $this->createForm(PriceType::class, $price);
        $priceForm->handleRequest($request);

        // if form is submitted and all fields valid
        if($priceForm->isSubmitted() && $priceForm->isValid()) {
            
            // If the price is null then we'll check if the number of price is inferior to 6
            if($price->getId() == null) {
                
                // Allow to 4 prices (TODO :: can be changed but I'll see it more later)
                if($this->priceRepository->countPrices() < 4) {

                    foreach($price->getPriceDetails() as $priceDetail) {
                        $this->priceDetailRepository->add($priceDetail, true);
                    }
                    
                    // Save database
                    $this->priceRepository->add($price, true);
                    
                    // Return a response to the user
                    $response = [
                        "class" => "success",
                        "message" => "The price service has been added"
                    ];

                    // Empty the object
                    $priceForm = $this->createForm(PriceType::class, $price);
                    $priceForm->handleRequest($request);
                } else {
                    $response = [
                        "class" => "danger",
                        "message" => "The number of autorised price services has been already attained"
                    ];
                }
            } else {
                foreach($price->getPriceDetails() as $priceDetail) {
                    $priceDetail->setPrice($price);

                    $this->priceDetailRepository->add($priceDetail, true);
                }

                // Save database
                $this->priceRepository->add($price, true);
                    
                // Return a response to the user
                $response = [
                    "class" => "success",
                    "message" => "The price service has been added"
                ];
            }
        }

        return $this->render("admin/price/form.html.twig", [
            "priceForm" => $priceForm->createView(),
            "response" => $response
        ]);
    }

    /**
     * @Route("/{id}/remove", name="remove", requirements={"id" = "^\d+(?:\d+)?$"}, methods={"DELETE"})
     */
    public function remove_price(Request $request, Price $price): JsonResponse {
        
        try {
            // remove all details
            foreach($price->getPriceDetails() as $priceDetail) {
                $this->priceDetailRepository->remove($priceDetail);
            }

            // At the end, remove the object Price in the database
            $this->priceRepository->remove($price, true);
        } catch(\Exception $e) {
            return $this->json($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Return a response to the client
        return $this->json(null, Response::HTTP_ACCEPTED);
    }
}
