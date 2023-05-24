<?php

namespace App\Controller;

use App\Service\RegexService;
use App\Manager\{ExperienceManager, FileManager, NotificationManager};
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security as Secu;
use App\Entity\{About, Contact, Education, Project, Skills};
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Form\{ProfileAdminType, ProjectAdminType, EducationAdminType, SkillsType, UpdatePasswordType};

/**
 * 
 * @IsGranted("ROLE_ADMIN")
 * @Security("is_granted('ROLE_ADMIN')")
 */
class AdminController extends AbstractController
{
    private $em;
    private $user;
    private $notificationManager;
    private $fileManager;

    function __construct(Secu $security, EntityManagerInterface $manager) 
    {
        $this->em = $manager;
        $this->user = $security->getUser();
        $this->fileManager = new FileManager();
        $this->notificationManager = new NotificationManager();
    }
    /**
     * @Route("/admin", name="adminHome")
     */
    public function admin()
    {
        $projectRepo = $this->em->getRepository(Project::class);
        $contactRepo = $this->em->getRepository(Contact::class);
        $educationRepo = $this->em->getRepository(Education::class);
        $experienceManager = new ExperienceManager();

        return $this->render('Admin/home.html.twig', [
            "nbrVisitors" => 1,
            "nbrProjects" => $projectRepo->countProject(),
            "workExp" => $experienceManager->countYearEXP($educationRepo->getIntervalEducationFromCategory("experience")),
            "latestWork" => $projectRepo->getLastestProject(),
            "latestEmail" => $contactRepo->getLatestMails(),
            "latestExp" => $educationRepo->getLatestEducationFromCategory("experience")
        ]);
    }

    /**
     * @Route("/admin/profile", name="adminProfile")
     */
    public function admin_profile(Request $request)
    {
        // ProfileAdminType
        $formProfile = $this->createForm(ProfileAdminType::class, $this->user);
        $formProfile->handleRequest($request);

        // UpdatePasswordType
        $formUpdatePwd = $this->createForm(UpdatePasswordType::class);
        $formUpdatePwd->handleRequest($request);

        // ProfileAdminType
        if($formProfile->isSubmitted()) {
            if($formProfile->isValid()) {
                try {
                    // Check if a file image need to be treated
                    $fileImage = $formProfile["imgPath"]->getData();
                    if(!empty($fileImage)) {
                        $responseTreatImg = $this->fileManager->moveFileToDestinationRepository($fileImage, "me.{$fileImage->guessExtension()}", "{$this->getParameter("public_dir")}content/profile/", "./content/profile/");
                        if($responseTreatImg["response"]) {
                            $this->user->setImgPath($responseTreatImg["path"]);
                        }
                    }
                    
                    // Persist & save all changes
                    $this->em->persist($this->user);
                    $this->em->flush();
    
                    // Return a message to the user
                    $message = $this->notificationManager->returnNotification("success", "La mise à jour s'est correctement éffectuée.");
                } catch(\Exeception $e) {
                    $message = $this->notificationManager->returnNotification("danger", $e->getMessage());
                } finally {}
            } else {
                $message = $this->notificationManager->returnNotification("warning", "Une erreur a été rencontrée avec un ou plusieurs champs.");
                // dd($formProfile->getData(), $message);
            }
        }

        // UpdatePasswordType
        if($formUpdatePwd->isSubmitted() && $formUpdatePwd->isValid()) {
            $formUpdatePwdData = $formUpdatePwd-getData();

            // Check if the password is the same of the old one
            if($this->user->getPassword() == $formUpdatePwdData["oldPassword"]) {

                // Check if the old password and the new one have diffirent value
                if($this->user->getPassword() != $formUpdatePwdData["newPassword"]) {
                    
                    // Check if the new password and the confirmation field have the exact same value
                    if($formUpdatePwdData["newPassword"] === $formUpdatePwdData["confirmNewPassword"]) {

                        // Check if the new password have the minimum requierement
                        if(
                            preg_match(RegexService::SECURE_PASSWORD, $formUpdatePwdData["newPassword"]) && 
                            preg_match(RegexService::ONLY_NUMERIC, $formUpdatePwdData["newPassword"])
                        ) {
                            try {
                                $this->em->persist($this->user);
                                $this->em->flush();
                                
                                $updatePwdMessage = $this->notificationManager->returnNotification("success", "La mise à jour s'est correctement éffectuée.");
                            } catch(\Exeception $e) {
                                $updatePwdMessage = $this->notificationManager->returnNotification("danger", $e->getMessage());
                            } finally {}
                        } else {
                            $updatePwdMessage = $this->notificationManager->returnNotification("danger", "Le mot de passe doit contenir des caractères spéciaux et des nombres en plus de sa longue minimale");
                        }
                    }
                }
            }
        }

        return $this->render('Admin/Profile/index.html.twig', [
            "user" => $this->user,
            "form" => $formProfile->createView(),
            "pwdForm" => $formUpdatePwd->createView(),
            "message" => isset($message) ? $message : null,
            "updatePwdMessage" => isset($updatePwdMessage) ? $updatePwdMessage : null,
        ]);
    }

    /**
     * @Route("/admin/skills", name="adminSkills")
     */
    public function admin_skills(Request $request)
    {
        $print = !empty($request->get("print")) ? $request->get("print") : "frontend";
        $skill = new Skills();
        $skill->setType($print);
        $formSkills = $this->createForm(SkillsType::class, $skill);
        $formSkills->handleRequest($request);
        $response = !empty($request->get("response")) ? json_decode(urldecode($request->get("response")), true) : [];

        if($formSkills->isSubmitted() && $formSkills->isValid()) {

            // If the skill to add already still no exist in the category, then add it. Else do nothing
            if(empty($this->em->getRepository(Skills::class)->searchSkill($skill->getSkill(), $skill->getType()))) {
                try {
                    $skill->setCreatedAt(new \DateTimeImmutable());
                    $this->em->getRepository(Skills::class)->add($skill, true);

                    $response = $this->notificationManager->returnNotification("success", "The skill '{$skill->getSkill()}' has been successfully added to the '{$skill->getType()}' category");
                } catch(\Exception $e) {
                    $response = $this->notificationManager->returnNotification("danger", "An error has been encountered : {$e->getMessage()}");
                } finally {}
            } else {
                $response = $this->notificationManager->returnNotification("warning", "The skill '{$skill->getSkill()}' already exist in the '{$skill->getType()}' category.");
            }

            // Re-instenciate the form to clear all fields
            $skill = new Skills();
            $skill->setType($print);
            $formSkills = $this->createForm(SkillsType::class, $skill);
        }

        return $this->render("Admin/Profile/skill.html.twig", [
            "print" => $print,
            "skills" => $this->em->getRepository(Skills::class)->getSkillsByCategory($print),
            "formSkills" => $formSkills->createView(),
            "response" => $response,
        ]);
    }

    /**
     * @Route("/admin/skills/{id}/remove", requirements={"id" = "^\d+(?:\d+)?$"}, name="adminRemoveSkill")
     */
    public function admin_delete_skill(int $id)
    {
        $skill = $this->em->getRepository(Skills::class)->find($id);
        
        if(!empty($skill)) {
            try {

                // Remove all associations with any projects
                foreach($skill->getProjects() as $project) {
                    $skill->removeProject($project);
                }
                
                // Remove the skill from the database
                $this->em->remove($skill);
                $this->em->flush();
    
                // Return a message to the user
                return $this->redirectToRoute("adminSkills", [
                    "response" => urlencode(
                        json_encode(
                            $this->notificationManager->returnNotification(
                                "success", 
                                "The skill {$skill->getSkill()} has been successfully deleted."
                            ), 
                        JSON_UNESCAPED_UNICODE)
                    )
                ]);
            } catch(Exception $e) {
                return $this->redirectToRoute("adminSkills", [
                    "response" => urlencode(
                        json_encode(
                            $this->notificationManager->returnNotification(
                                "danger", 
                                "An error has been encountered : {$e->getMessage()}"
                            ), 
                        JSON_UNESCAPED_UNICODE)
                    )
                ]);
            }
        }

        return $this->redirectToRoute("adminSkills", [
            "response" => urlencode(
                json_encode(
                    $this->notificationManager->returnNotification(
                        "warning", 
                        "The skill hasn't been found."
                    ), 
                JSON_UNESCAPED_UNICODE)
            )
        ]);
    }

    /**
     * @Route("/admin/education", name="adminEducation")
     * @Route("/admin/education/page/{offset}", requirements={"id" = "^\d+(?:\d+)?$"}, name="adminEducationByPage")
     */
    public function admin_education(int $offset = 1)
    {
        $limit = 10;
        $offset = $offset > 0 ? (int)$offset : 1;
        $educationRepo = $this->getDoctrine()->getRepository(Education::class);

        return $this->render('Admin/Education/index.html.twig', [
            "offset" => $offset,
            "educations" => $educationRepo->getEducations($offset, $limit),
            "nbrOffsets" => ceil($educationRepo->countEducations() / $limit)
        ]);
    }

    /**
     * @Route("/admin/education/add", name="adminEducationAdd")
     */
    public function admin_education_add(Request $request)
    {
        $education = new Education();
        $form = $this->createForm(EducationAdminType::class, $education);
        $form->handleRequest($request);
        $message = [];

        if($form->isSubmitted()) {
            if($form->isValid()) {
                try {
                    if(!is_null($education->getEndDate()) && $education->getInProgress()) {
                        $education->setEndDate(null);
                    } elseif(is_null($education->getEndDate()) && !$education->getInProgress()) {
                        $education->setInProgress(true);
                    }
                    
                    $this->em->persist($education);
                    $this->em->flush();
        
                    $message = [
                        "class" => "success",
                        "icon" => "/content/images/svg/checkmark-green.svg",
                        "content" => "L'ajout a été faite."
                    ];
                } catch(\Exception $e) {
                    $message = [
                        "class" => "danger",
                        "icon" => "/content/images/svg/closemark-red.svg",
                        "content" => "Une erreur a été rencontrée : {$e->getMessage()}"
                    ];
                } finally {}
            } else {
                $message = [
                    "class" => "danger",
                    "icon" => "/content/images/svg/closemark-red.svg",
                    "content" => "Une erreur a été rencontrée avec un ou plusieurs champs."
                ];
            }
        }

        return $this->render('Admin/Education/form.html.twig', [
            'form' => $form->createView(),
            'message' => !empty($message) ? $message : null
        ]);
    }

    /**
     * @Route("/admin/education/{id}", requirements={"id" = "^\d+(?:\d+)?$"}, name="adminEditEducation")
     */
    public function admin_edit_education(Education $education, Request $request)
    {
        $form = $this->createForm(EducationAdminType::class, $education);
        $form->handleRequest($request);
        $message = [];

        if($form->isSubmitted()) {
            if($form->isValid()) {
                try {
                    foreach($form["skills"]->getData() as $skill) {
                        $skill->addEducation($education);
                        $this->em->persist($skill);
                    }
                    $this->em->persist($education);
                    $this->em->flush();
                    
                    $message = $this->notificationManager->returnNotification("success", "La mise à jout s'est correctement effectuée");
                } catch(\Exception $e) {
                    $message = $this->notificationManager->returnNotification("danger", $e->getMessage());
                } finally {}
            } else {
                $message = $this->notificationManager->returnNotification("warning", "Une erreur a été rencontrée avec un ou plusieurs champs du formulaire.");
            }
        }

        return $this->render('Admin/Education/form.html.twig', [
            "form" => $form->createView(),
            "education_id" => $education->getId(),
            "message" => !empty($message) ? $message : null
        ]);
    }

    /**
     * @Route("/admin/education/{id}/remove", requirements={"id" = "^\d+(?:\d+)?$"}, name="adminDeleteEducation")
     */
    public function admin_delete_education(Education $education)
    {
        try {
            $this->em->remove($education);
            $this->em->flush();
        } catch(Exception $e) {
            die($e->getMessage());
        }

        return $this->redirectToRoute("adminEducation");
    }

    /**
     * @Route("/admin/portfolio", name="adminProject")
     * @Route("/admin/portfolio/page/{page}", requirements={"id" = "^\d+(?:\d+)?$"}, name="adminProjectPage")
     */
    public function admin_project(int $page = 1)
    {
        $limit = 20;
        $page = $page > 0 ? $page : 1;

        return $this->render('Admin/Portfolio/index.html.twig', [
            "page" => $page,
            "projects" => $this->getDoctrine()->getRepository(Project::class)->getProject($page - 1, $limit),
            "total_page" => ceil($this->getDoctrine()->getRepository(Project::class)->countProject() / $limit)
        ]);
    }

    /**
     * @Route("/admin/portfolio/add", name="adminAddProject")
     */
    public function admin_add_project(Request $request)
    {
        $response = [];
        $project = new Project();
        $form = $this->createForm(ProjectAdminType::class, $project);
        $form->handleRequest($request);

        // If the form is submitted and valid (the validaty of the form include configuration set in the Entitys)
        if($form->isSubmitted() && $form->isValid()) {

            // Check if a project with the same name and version to avoid double
            if( empty($this->em->getRepository(Project::class)->getProjectByNameAndVersion($project->getName(), $project->getVersion())) ) {
                
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
                    $project->setCreatedAt(new \Datetime());
                    $this->em->persist($project);
                    $this->em->flush();

                    // Return an answer of the process to the user
                    $response = $this->notificationManager->returnNotification("success", "The project {$project->getName()} has been added to the realization.");
                } catch (\Exception $e) {
                    // Return an answer if an error has been found during the process
                    $response = $this->notificationManager->returnNotification("danger", "Une erreur a été rencontrée : {$e->getMessage()}");
                } finally {}
            } else {
                // Return a response to the user if a project with the exact same name and version already exist
                $response = $this->notificationManager->returnNotification("warning", "A project with the same name and version already exist.");
            }
        }

        return $this->render('Admin/Portfolio/form.html.twig', [
            "projectForm" => $form->createView(),
            "response" => $response
        ]);
    }

    /**
     * @Route("/admin/portfolio/{id}", requirements={"id" = "^\d+(?:\d+)?$"}, name="adminSingleProject")
     */
    public function admin_single_project(int $id)
    {
        $project = $this->em->getRepository(Project::class)->find($id);
        if(empty($project)) {
            return $this->redirectToRoute("adminProject");
        }

        return $this->render("Admin/Portfolio/single.html.twig", [
            "project" => $project,
            "skills" => array_map(function($item) {
                return $item->getSkill();
            }, $project ? $project->getSkills()->toArray() : [])
        ]);
    }

    /**
     * @Route("/admin/portfolio/{id}/edit", requirements={"id" = "^\d+(?:\d+)?$"}, name="adminEditProject")
     */
    public function admin_edit_project(Project $project, Request $request)
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
                    $this->em->persist($project);
                    $this->em->flush();

                    // Return a success response to the user
                    $response = $this->notificationManager->returnNotification("success", "La mise à jour du projet a bien été prise en compte.");
                } catch(\Exeption $e) {
                    $response = $this->notificationManager->returnNotification("danger", "Une erreur a été rencontrée, voici le message rencontré : {$e->getMessage()}");
                } finally {}
            } else {
                $response = $this->notificationManager->returnNotification("warning", "Une erreur a été rencontrée avec l'un des champs renseignés.");
            }
        }

        return $this->render('Admin/Portfolio/form.html.twig', [
            "projectId" => $project->getId(),
            "projectForm" => $form->createView(),
            "response" => $response
        ]);
    }

    /**
     * @Route("/admin/portfolio/{id}/delete", requirements={"id" = "^\d+(?:\d+)?$"}, name="adminDeleteProject")
     */
    public function admin_delete_project(Project $project)
    {
        if(in_array("./content/portfolio/".$project->getImgPath(), glob("./content/portfolio/*.*"))) {
            unlink("./content/portfolio/".$project->getImgPath());
            $this->em->remove($project);
            $this->em->flush();
        }

        return $this->redirectToRoute("adminProject");
    }

    /**
     * @Route("/admin/contact", name="adminContact")
     * @Route("/admin/contact/page/{page}", requirements={"id" = "^\d+(?:\d+)?$"}, name="adminContactByPage")
     */
    public function admin_contact(int $page = 1)
    {
        $limit = 10;

        return $this->render("Admin/Contact/index.html.twig", [
            "title" => "Contact",
            "list_mail" => $this->getDoctrine()->getRepository(Contact::class)->getMails($page, $limit),
            "offset" => $page,
            "total_page" => ceil($this->getDoctrine()->getRepository(Contact::class)->countContact() / $limit)
        ]);
    }

    /**
     * @Route("/admin/contact/{id}", requirements={"id" = "^\d+(?:\d+)?$"}, name="adminReadMail")
     */
    public function admin_read_mail(Contact $contact)
    {
        if(!$contact->getIsRead()) {
            $contact->setIsRead(true);
            $this->em->persist($contact);
            $this->em->flush();
        }

        $contact->setEmailContent(json_decode($contact->getEmailContent()));

        return $this->render("Admin/Contact/read.html.twig", [
            "title" => "Contact",
            "mail" => $contact
        ]);
    }

    /**
     * @Route("/admin/contact/{id}/remove", requirements={"id" = "^\d+(?:\d+)?$"}, name="adminRemoveMail")
     */
    public function admin_remove_mail(int $id)
    {
        // Check the email id is a positive integer
        if($id < 1) {
            return $this->redirectToRoute("adminContact", [
                "response" => [
                    "class" => "danger",
                    "message" => "An error has been found with the email id. The removal process has been cancelled."
                ]
            ]);
        }

        // Get the mail in the database
        $email = $this->em->getRepository(Contact::class)->find($id);

        // Check if an sended email has been found
        if(!empty($email)) {
            try {
                // Remove the email and save the changes in the database
                $this->em->remove($email);
                $this->em->flush();

                // Return a response to the user
                return $this->redirectToRoute("adminContact", [
                    "response" => [
                        "class" => "success",
                        "message" => "The email sended by {$email->getSenderEmail()} has been successfully removed."
                    ]
                ]);
            } catch(Exception $e) {
                return $this->redirectToRoute("adminContact", [
                    "response" => [
                        "class" => "danger",
                        "message" => "An error has been found with the email id. The removal process has been cancelled."
                    ]
                ]);
            }
        }

        return $this->redirectToRoute("adminContact", [
            "response" => [
                "class" => "danger",
                "message" => "An error has been found with the email id. The removal process has been cancelled."
            ]
        ]);
    }

    /**
     * @Route("/admin/witnesses", name="adminWitnesses")
     * @Route("/admin/witnesses/page/{page}", requirements={"id" = "^\d+(?:\d+)?$"}, name="adminWitnessesByPage")
     */
    public function admin_witnesses(int $page = 1)
    {
        $limit = 10;
        $page = $page < 1 ? 1 : $page;

        return $this->render("Admin/Witness/list.html.twig", [
            "offset" => $page,
            "nbrOffsets" => ceil( 0 / $limit),
            "witnesses" => [],
        ]);
    }

    /**
     * @Route("/admin/witnesses/add", name="adminAddWitness")
     */
    public function admin_add_witness(Request $request)
    {
        return $this->render("Admin/Witness/form.html.twig", [
            "addWitnessForm" => []
        ]);
    }

    /**
     * @Route("/admin/prices", name="adminServicePrice")
     */
    public function admin_service_price(Request $request)
    {
        return $this->render("Admin/Service/list-prices.html.twig", [
            "prices" => []
        ]);
    }

    /**
     * @Route("/admin/logout", name="adminLogout")
     */
    public function admin_logout()
    {
        return $this->render('User/home.html.twig');
    }
}
