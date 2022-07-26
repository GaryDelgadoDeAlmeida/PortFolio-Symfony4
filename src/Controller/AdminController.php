<?php

namespace App\Controller;

use App\Entity\{About, Contact, Education, Project, Skills};
use App\Form\{AboutAdminType, ProfileAdminType, ProjectAdminType, EducationAdminType, SkillsType};
use App\Manager\ExperienceManager;
use App\Service\RegexService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security as Secu;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * 
 * @IsGranted("ROLE_ADMIN")
 * @Security("is_granted('ROLE_ADMIN')")
 */
class AdminController extends AbstractController
{
    private $em;
    private $user;

    function __construct(Secu $security, EntityManagerInterface $manager) 
    {
        $this->em = $manager;
        $this->user = $security->getUser();
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
        $formProfile = $this->createForm(ProfileAdminType::class, $this->user);
        $formProfile->handleRequest($request);

        if($formProfile->isSubmitted() && $formProfile->isValid()) {
            if(
                preg_match(RegexService::SECURE_PASSWORD, $this->user->getPassword()) && 
                preg_match(RegexService::ONLY_NUMERIC, $this->user->getPassword())
            ) {
                $this->em->persist($this->user);
                $this->em->flush();
                $message = [
                    "class" => 'alert alert-success',
                    "content" => 'La mise à jour s\'est correctement éffectuée.'
                ];
            } else {
                $message = [
                    "class" => 'alert alert-warning',
                    "content" => 'Le mot de passe doit contenir des caractères spéciaux et des nombres en plus de sa longue minimale'
                ];
            }
        }

        return $this->render('Admin/Profile/index.html.twig', [
            'user' => $this->user,
            'form' => $formProfile->createView(),
            'message' => isset($message) ? $message : null,
            "title" => "Profile"
        ]);
    }

    /**
     * @Route("/admin/profile/about", name="adminAbout")
     */
    public function admin_about(Request $request)
    {
        $about = $this->user->getAbout() != null ? $this->user->getAbout() : new About();
        $formAbout = $this->createForm(AboutAdminType::class, $about);
        $formAbout->handleRequest($request);

        if($formAbout->isSubmitted() && $formAbout->isValid()) {
            $imageFile = $formAbout['img']->getData();
            
            if($imageFile) {
                // this is needed to safely include the file name as part of the URL
                $newFilename = 'photo_garry_almeida.'.$imageFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    if(array_search('./content/images/'.$newFilename, glob("./content/images/*.".$imageFile->guessExtension()))) {
                        unlink('./content/images/'.$newFilename);
                    }
                    
                    $imageFile->move(
                        $this->getParameter('photo_img_dir'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    dd($e->getMessage());
                }

                $about->setImgPath($newFilename);
            }

            $about->setIdUSer($this->user);
            $this->em->persist($about);
            $this->em->flush();
            $message = [
                "class" => 'alert alert-success',
                "content" => 'La mise à jour s\'est correctement éffectuée.'
            ];
        }

        return $this->render('Admin/Profile/about.html.twig', [
            'user' => $this->user,
            'form' => $formAbout->createView(),
            'message' => isset($message) ? $message : null,
            "title" => "Profile - about"
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
        $response = [];

        if($formSkills->isSubmitted() && $formSkills->isValid()) {

            // If the skill to add already still no exist in the category, then add it. Else do nothing
            if(empty($this->em->getRepository(Skills::class)->searchSkill($skill->getSkill(), $skill->getType()))) {
                try {
                    $skill->setCreatedAt(new \DateTimeImmutable());
                    $this->em->getRepository(Skills::class)->add($skill, true);

                    $response = [
                        "message" => "The skill '{$skill->getSkill()}' has been successfully added to the '{$skill->getType()}' category"
                    ];
                } catch(\Exception $e) {
                    $response = [
                        "message" => "An error has been encountered : {$e->getMessage()}"
                    ];
                } finally {}
            } else {
                $response = [
                    "message" => "The skill '{$skill->getSkill()}' already exist in the '{$skill->getType()}' category."
                ];
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
        return $this->redirectToRoute("adminSkills");
    }

    /**
     * @Route("/admin/education", name="adminEducation")
     */
    public function admin_education()
    {
        return $this->render('Admin/Education/index.html.twig', [
            "title" => "Education",
            "educations" => $this->getDoctrine()->getRepository(Education::class)->getEducations()
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

        if($form->isSubmitted() && $form->isValid()) {

            if(!is_null($education->getEndDate()) && $education->getInProgress()) {
                $education->setEndDate(null);
            } elseif(is_null($education->getEndDate()) && !$education->getInProgress()) {
                $education->setInProgress(true);
            }
            
            $this->em->persist($education);
            $this->em->flush();

            $message = [
                "class" => "alert-success text-center",
                "content" => "L'ajout a été faite."
            ];
        }

        return $this->render('Admin/Education/add.html.twig', [
            "title" => "Education",
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

        if($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($education);
            $this->em->flush();

            $message = [
                "class" => "alert-success text-center w-20px",
                "content" => "La mise à jout s'est correctement effectuée"
            ];
        }

        return $this->render('Admin/Education/edit.html.twig', [
            "form_edit_education" => $form->createView(),
            "education_id" => $education->getId(),
            "title" => "Edit Education",
            "message" => !empty($message) ? $message : null
        ]);
    }

    /**
     * @Route("/admin/education/{id}", requirements={"id" = "^\d+(?:\d+)?$"}, name="adminDeleteEducation")
     */
    public function admin_delete_education(Education $education)
    {
        $this->em->remove($education);
        $this->em->flush();

        return $this->redirectToRoute("adminProject");
    }

    /**
     * @Route("/admin/portfolio", name="adminProject")
     * @Route("/admin/portfolio/page/{page}", requirements={"id" = "^\d+(?:\d+)?$"}, name="adminProjectPage")
     */
    public function admin_project(int $page = 1)
    {
        $limit = 20;

        return $this->render('Admin/Portfolio/index.html.twig', [
            "page" => $page,
            "projects" => $this->getDoctrine()->getRepository(Project::class)->getProject($page - 1, $limit),
            "total_page" => ceil($this->getDoctrine()->getRepository(Project::class)->countProject() / $limit)
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
            "project" => $project
        ]);
    }

    /**
     * @Route("/admin/portfolio/{id}/edit", requirements={"id" = "^\d+(?:\d+)?$"}, name="adminEditProject")
     */
    public function admin_edit_project(Project $project, Request $request)
    {
        $form = $this->createForm(ProjectAdminType::class, $project);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form['imgPath']->getData();
            
            if($imageFile) {
                
                // this is needed to safely include the file name as part of the URL
                $newFilename = 'portFolio-'.str_replace(" ", "_", $project->getName()) . "-" . $project->getVersion() .'.'.$imageFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    if(!array_search('./content/portfolio/'.$newFilename, glob("./content/portfolio/*.".$imageFile->guessExtension()))) {
                        $imageFile->move(
                            $this->getParameter('project_img_dir'),
                            $newFilename
                        );
                    } else {
                        unlink('./content/portfolio/'.$newFilename);
                        $imageFile->move(
                            $this->getParameter('project_img_dir'),
                            $newFilename
                        );
                    }
                } catch (FileException $e) {
                    dd($e->getMessage());
                }

                $project->setImgPath($newFilename);
                $this->em->persist($project);
                $this->em->flush();

                return $this->redirectToRoute('adminProject');
            }
        }

        return $this->render('Admin/Portfolio/edit.html.twig', [
            "form_edit_project" => $form->createView(),
            "projectId" => $project->getId(),
            "title" => "Edit Work"
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
                    try {
                        // this is needed to safely include the file name as part of the URL
                        $newFilename = 'portFolio-'.str_replace(" ", "_", $project->getName()) . "-" . $project->getVersion() .'.'.$imageFile->guessExtension();
                        
                        // Move the file to the directory where brochures are stored
                        if(!array_search('./content/portfolio/'.$newFilename, glob("./content/portfolio/*.".$imageFile->guessExtension()))) {
                            $imageFile->move(
                                $this->getParameter('project_img_dir'),
                                $newFilename
                            );
                        }

                        $project->setImgPath("/content/portfolio/{$newFilename}");
                    } catch (FileException $e) {
                        dd($e->getMessage());
                    }

                    $project->setCreatedAt(new \Datetime());
                    $this->em->persist($project);
                    $this->em->flush();
                    
                    $response = [
                        "classname" => "success",
                        "icon" => "",
                        "message" => "The project {$project->getName()} has been added to the realization."
                    ];
                }
            } else {
                $response = [
                    "classname" => "warning",
                    "icon" => "",
                    "message" => "A project with the same name and version already exist."
                ];
            }
        }

        return $this->render('Admin/Portfolio/add.html.twig', [
            "form_add_project" => $form->createView(),
            "response" => $response
        ]);
    }

    /**
     * @Route("/admin/portfolio/search", name="adminSearchProject", methods="POST")
     */
    public function admin_search_project(Request $request)
    {
        $offset = $request->get("offset");
        $limit = $request->get("limit");

        return $this->render("Admin/Portfolio/index.html.twig", [
            "offset" => $offset,
            "limit" => $limit,
            "portfolio" => []
        ]);
    }

    /**
     * @Route("/admin/portfolio/delete/{id}", requirements={"id" = "^\d+(?:\d+)?$"}, name="adminDeleteProject")
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
        dd($id);
    }

    /**
     * @Route("/admin/logout", name="adminLogout")
     */
    public function admin_logout()
    {
        return $this->render('User/home.html.twig');
    }
}
