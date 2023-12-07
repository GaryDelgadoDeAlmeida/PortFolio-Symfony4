<?php

namespace App\Controller\Backoffice;

use App\Entity\Education;
use App\Form\EducationAdminType;
use App\Repository\SkillsRepository;
use App\Repository\EducationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/admin/education", name="admin_education_")
 */
class EducationController extends AbstractController
{
    private SkillsRepository $skillsRepository;
    private EducationRepository $educationRepository;

    function __construct(EducationRepository $educationRepository, SkillsRepository $skillsRepository) {
        $this->skillsRepository = $skillsRepository;
        $this->educationRepository = $educationRepository;
    }
    /**
     * @Route("/", name="index")
     * @Route("/page/{offset}", requirements={"id" = "^\d+(?:\d+)?$"}, name="index_by_page")
     */
    public function index(int $offset = 1)
    {
        $limit = 10;
        $offset = $offset > 0 ? (int)$offset : 1;

        return $this->render('admin/education/index.html.twig', [
            "offset" => $offset,
            "educations" => $this->educationRepository->getEducations($offset, $limit),
            "nbrOffsets" => ceil($this->educationRepository->countEducations() / $limit)
        ]);
    }

    /**
     * @Route("/new", name="new")
     */
    public function new_education(Request $request)
    {
        $response = [];
        $form = $this->createForm(EducationAdminType::class, $education = new Education());
        $form->handleRequest($request);

        // If form is submitted
        if($form->isSubmitted()) {
            
            // If all fields is valid
            if($form->isValid()) {
                try {
                    if(!is_null($education->getEndDate()) && $education->getInProgress()) {
                        $education->setEndDate(null);
                    } elseif(is_null($education->getEndDate()) && !$education->getInProgress()) {
                        $education->setInProgress(true);
                    }
                    
                    $this->educationRepository->add($education, true);
        
                    $response = [
                        "class" => "success",
                        "message" => "L'ajout a été faite."
                    ];
                } catch(\Exception $e) {
                    $response = [
                        "class" => "danger",
                        "message" => "Une erreur a été rencontrée : {$e->getMessage()}"
                    ];
                } finally {}
            } else {
                $response = [
                    "class" => "danger",
                    "message" => $form->getErrors(true, false)
                    // "message" => "Une erreur a été rencontrée avec un ou plusieurs champs."
                ];
            }
        }

        return $this->render('admin/education/form.html.twig', [
            'form' => $form->createView(),
            'response' => $response
        ]);
    }

    /**
     * @Route("/{id}", requirements={"id" = "^\d+(?:\d+)?$"}, name="edit")
     */
    public function edit_education(Education $education, Request $request)
    {
        $response = [];
        $form = $this->createForm(EducationAdminType::class, $education);
        $form->handleRequest($request);

        // If form is submitted
        if($form->isSubmitted()) {

            // If all fields is valid
            if($form->isValid()) {
                try {
                    foreach($form["skills"]->getData() as $skill) {
                        $skill->addEducation($education);
                        $this->skillsRepository->add($skill, true);
                    }
                    $this->educationRepository->add($education, true);
                    
                    $response = [
                        "class" => "success", 
                        "message" => "La mise à jout s'est correctement effectuée"
                    ];
                } catch(\Exception $e) {
                    $response = [
                        "class" => "danger",
                        "message" => $e->getMessage()
                    ];
                } finally {}
            } else {
                $response = [
                    "class" => "warning", 
                    "message" => "Une erreur a été rencontrée avec un ou plusieurs champs du formulaire."
                ];
            }
        }

        return $this->render('admin/education/form.html.twig', [
            "form" => $form->createView(),
            "education_id" => $education->getId(),
            "response" => $response
        ]);
    }

    /**
     * @Route("/{id}/remove", requirements={"id" = "^\d+(?:\d+)?$"}, name="remove", methods={"DELETE"})
     */
    public function delete_education(Education $education)
    {
        try {
            $this->educationRepository->remove($education, true);
        } catch(\Exception $e) {
            die($e->getMessage());
        }

        return $this->redirectToRoute("admin_education_index");
    }
}
