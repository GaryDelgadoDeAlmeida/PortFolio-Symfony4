<?php

namespace App\Controller\Backoffice;

use App\Entity\Witness;
use App\Form\WitnessType;
use App\Repository\WitnessRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/admn/witness", name="admin_witness_")
 */
class WitnessController extends AbstractController
{
    private WitnessRepository $witnessRepository;

    function __construct(
        WitnessRepository $witnessRepository
    ) {
        $this->witnessRepository = $witnessRepository;
    }
    /**
     * @Route("/", name="index")
     * @Route("/page/{page}", requirements={"page" = "^\d+(?:\d+)?$"}, name="index_by_page")
     */
    public function index(Request $request, int $page = 0): Response
    {
        $limit = 10;
        $page = is_numeric($page) && $page >= 1 ? $page : 1;

        return $this->render("admin/witness/list.html.twig", [
            "title" => "Témoignage",
            "offset" => $page,
            "nbrOffsets" => ceil( $this->witnessRepository->countWitness() / $limit),
            "witnesses" => $this->witnessRepository->findBy([], ["id" => "DESC"], $limit, ($page - 1) * $limit),
        ]);
    }

    /**
     * @Route("/new", name="new")
     * @Route("/{id}/edit", requirements={"id" = "^\d+(?:\d+)?$"}, name="edit")
     */
    public function form_witness(Request $request, Witness $witness = null)
    {
        $response = [];
        $form = $this->createForm(WitnessType::class, $witness ?? (new Witness())
            ->setCreatedAt(new \DateTimeImmutable())
        );
        $form->handleRequest($request);

        // If form is submitted and all fields valid
        if($form->isSubmitted() && $form->isValid()) {
            
            // Save into database the new witness / user comment
            $this->witnessRepository->add($witness, true);

            // Return a response to the user
            $response = [
                "class" => "success",
                "message" => "Le témoignage a bien été enregistrer"
            ];
        }

        return $this->render("admin/witness/form.html.twig", [
            "witnessForm" => $form->createView(),
            "response" => $response
        ]);
    }

    /**
     * @Route("/{id}/remove", requirements={"id" = "^\d+(?:\d+)?$"}, name="remove", methods={"DELETE"})
     */
    public function remove_witness(Request $request, int $id): JsonResponse
    {
        // Search a witness
        $witness = $this->witnessRepository->find($id);
        if(empty($witness)) {
            return $this->json(null, Response::HTTP_NOT_FOUND);
        }

        try {
            // Remove the founded witness
            $this->witnessRepository->remove($witness, true);
        } catch(\Exception $e) {
            return $this->json([
                "message" => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json(null, Response::HTTP_ACCEPTED);
    }
}
