<?php

namespace App\Controller\Backoffice;

use App\Entity\Price;
use App\Form\PriceType;
use App\Repository\PriceRepository;
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

    function __construct(PriceRepository $priceRepository) {
        $this->priceRepository = $priceRepository;
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
                if($this->priceRepository->countPrices() < 6) {
                    // Save database
                    $this->priceRepository->add($price, true);
                    
                    // Return a response to the user
                    $response = [
                        "class" => "success",
                        "message" => "The price service has been added"
                    ];
                } else {
                    $response = [
                        "class" => "danger",
                        "message" => "The number of autorised price services has been already attained"
                    ];
                }
            }
        }

        return $this->render("admin/price/form.html.twig", [
            "priceForm" => $priceForm->createView(),
            "response" => $response
        ]);
    }

    /**
     * @Route("/{id}/remove")
     */
    public function remove_price(Request $request): JsonResponse {
        return $this->json([
            "message" => "Route under construction"
        ], Response::HTTP_ACCEPTED);
    }
}
