<?php

namespace App\Controller\Backoffice;

use App\Entity\Project;
use App\Manager\FileManager;
use App\Form\ProjectAdminType;
use App\Repository\ProjectRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/admin/portfolio", name="admin_project_")
 */
class PortfolioController extends AbstractController
{
    private FileManager $fileManager;
    private ProjectRepository $projectRepository;
    
    function __construct(
        FileManager $fileManager,
        ProjectRepository $projectRepository
    ) {
        $this->fileManager = $fileManager;
        $this->projectRepository = $projectRepository;
    }

    /**
     * @Route("/", name="index")
     * @Route("/page/{page}", requirements={"id" = "^\d+(?:\d+)?$"}, name="index_by_page")
     */
    public function index(int $page = 1)
    {
        $limit = 20;
        $page = $page > 0 ? $page : 1;

        return $this->render('admin/portfolio/index.html.twig', [
            "page" => $page,
            "projects" => $this->projectRepository->getProject($page - 1, $limit),
            "total_page" => ceil($this->projectRepository->countProject() / $limit)
        ]);
    }

    /**
     * @Route("/new", name="new")
     */
    public function new_project(Request $request)
    {
        $response = [];
        $form = $this->createForm(ProjectAdminType::class, $project = (new Project())->setCreatedAt(new \Datetime()));
        $form->handleRequest($request);

        // If the form is submitted and valid (the validaty of the form include configuration set in the Entitys)
        if($form->isSubmitted() && $form->isValid()) {

            // Check if a project with the same name and version to avoid double
            if( empty($this->projectRepository->getProjectByNameAndVersion($project->getName(), $project->getVersion())) ) {
                
                $imageFile = $form['imgPath']->getData();
                if(!empty($imageFile)) {
                    $responseTreatImg = $this->fileManager->moveFileToDestinationRepository(
                        $imageFile,
                        "portFolio-" . str_replace(" ", "_", $project->getName()) . "-{$project->getVersion()}.{$imageFile->guessExtension()}",
                        $this->getParameter('project_img_dir'),
                        "./content/portfolio/"
                    );

                    if($responseTreatImg["response"]) {
                        $project->setImgPath($responseTreatImg["path"]);
                    }
                }

                try {
                    $this->projectRepository->save($project, true);

                    // Return an answer of the process to the user
                    $response = [
                        "class" => "success",
                        "message" => "The project {$project->getName()} has been added to the realization."
                    ];
                } catch (\Exception $e) {
                    // Return an answer if an error has been found during the process
                    $response = [
                        "class" => "danger",
                        "message" => "Une erreur a été rencontrée : {$e->getMessage()}"
                    ];
                } finally {}
            } else {
                // Return a response to the user if a project with the exact same name and version already exist
                $response = [
                    "class" => "warning",
                    "message" => "A project with the same name and version already exist."
                ];
            }
        }

        return $this->render('admin/portfolio/form.html.twig', [
            "projectForm" => $form->createView(),
            "response" => $response
        ]);
    }

    /**
     * @Route("/{id}", requirements={"id" = "^\d+(?:\d+)?$"}, name="show")
     */
    public function show_project(int $id)
    {
        $project = $this->projectRepository->find($id);
        if(empty($project)) {
            return $this->redirectToRoute("admin_project_index");
        }

        return $this->render("admin/portfolio/single.html.twig", [
            "project" => $project,
            "skills" => array_map(function($item) {
                return $item->getSkill();
            }, $project ? $project->getSkills()->toArray() : [])
        ]);
    }

    /**
     * @Route("/{id}/edit", requirements={"id" = "^\d+(?:\d+)?$"}, name="edit")
     */
    public function edit_project(Project $project, Request $request)
    {
        $response = [];
        $form = $this->createForm(ProjectAdminType::class, $project);
        $form->handleRequest($request);

        if($form->isSubmitted()) {
            if($form->isValid()) {
                $imageFile = $form['imgPath']->getData();
            
                if($imageFile) {
                    $imageFile = $form['imgPath']->getData();
                    if(!empty($imageFile)) {
                        $responseTreatImg = $this->fileManager->moveFileToDestinationRepository(
                            $imageFile,
                            "portFolio-" . str_replace(" ", "_", $project->getName()) . "-{$project->getVersion()}.{$imageFile->guessExtension()}",
                            $this->getParameter('project_img_dir'),
                            "./content/portfolio/"
                        );

                        if($responseTreatImg["response"]) {
                            $project->setImgPath($responseTreatImg["path"]);
                        }
                    }
                }
                
                try {
                    $this->projectRepository->save($project, true);

                    // Return a success response to the user
                    $response = [
                        "class" => "success",
                        "message" => "La mise à jour du projet a bien été prise en compte."
                    ];
                } catch(\Exeption $e) {
                    $response = [
                        "class" => "danger",
                        "message" => "Une erreur a été rencontrée, voici le message rencontré : {$e->getMessage()}"
                    ];
                } finally {}
            } else {
                $response = [
                    "class" => "danger",
                    "message" => "Une erreur a été rencontrée avec l'un des champs renseignés."
                ];
            }
        }

        return $this->render('admin/portfolio/form.html.twig', [
            "projectID" => $project->getId(),
            "projectForm" => $form->createView(),
            "response" => $response
        ]);
    }

    /**
     * @Route("/{id}/delete", requirements={"id" = "^\d+(?:\d+)?$"}, name="remove", methods={"DELETE"})
     */
    public function delete_project(Project $project)
    {
        if(in_array("./content/portfolio/".$project->getImgPath(), glob("./content/portfolio/*.*"))) {
            unlink("./content/portfolio/".$project->getImgPath());
            $this->projectRepository->remove($project, true);
        }

        return $this->redirectToRoute("admin_project_index");
    }
}
