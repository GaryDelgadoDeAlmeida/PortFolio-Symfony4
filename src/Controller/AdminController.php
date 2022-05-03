<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\About;
use App\Entity\Contact;
use App\Entity\Project;
use App\Form\SearchType;
use App\Entity\Education;
use App\Form\AboutAdminType;
use App\Form\ProfileAdminType;
use App\Form\ProjectAdminType;
use App\Form\EducationAdminType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security as Secu;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin", name="adminHome")
     * @IsGranted("ROLE_ADMIN")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function admin()
    {
        return $this->render('Admin/home.html.twig', [
            "title" => "Home",
            "nbrProjects" => $this->getDoctrine()->getRepository(Project::class)->getNbrProject()[1],
            "nbrNewMail" => $this->getDoctrine()->getRepository(Contact::class)->getNbrContact()[1]
        ]);
    }

    /**
     * @Route("/admin/profile", name="adminProfile")
     * @IsGranted("ROLE_ADMIN")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function admin_profile(Secu $security, Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder)
    {
        $user = $security->getUser();
        $formProfile = $this->createForm(ProfileAdminType::class, $user);
        $formProfile->handleRequest($request);

        if($formProfile->isSubmitted() && $formProfile->isValid()) {
            if(
                preg_match('/[\'\/~`\!@#\$%\^&\*\(\)_\-\+=\{\}\[\]\|;:"\<\>,\.\?\\\]/', $user->getPassword()) && 
                preg_match('/[0-9]/i', $user->getPassword())
            ) {
                $user->setPassword($encoder->encodePassword($user, $user->getPassword()));
                $manager->persist($user);
                $manager->flush();
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
            'user' => $user,
            'userAboutImg' => $user->getAbout()->getImgPath(),
            'form' => $formProfile->createView(),
            'message' => isset($message) ? $message : null,
            "title" => "Profile"
        ]);
    }

    /**
     * @Route("/admin/profile/about", name="adminAbout")
     * @IsGranted("ROLE_ADMIN")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function admin_about(Secu $security, Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder)
    {
        $user = $security->getUser();
        $about = $user->getAbout() != null ? $user->getAbout() : new About();
        $formAbout = $this->createForm(AboutAdminType::class, $about);
        $formAbout->handleRequest($request);

        if($formAbout->isSubmitted() && $formAbout->isValid()) {
            $imageFile = $formAbout['img']->getData();
            
            if($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
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
            $about->setIdUSer($user);
            $manager->persist($about);
            $manager->flush();
            $message = [
                "class" => 'alert alert-success',
                "content" => 'La mise à jour s\'est correctement éffectuée.'
            ];
        }

        return $this->render('Admin/Profile/about.html.twig', [
            'user' => $user,
            'form' => $formAbout->createView(),
            'message' => isset($message) ? $message : null,
            "title" => "Profile - about"
        ]);
    }

    /**
     * @Route("/admin/education", name="adminEducation")
     * @IsGranted("ROLE_ADMIN")
     * @Security("is_granted('ROLE_ADMIN')")
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
     * @IsGranted("ROLE_ADMIN")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function admin_education_add(Request $request, EntityManagerInterface $manager)
    {
        $education = new Education();
        $form = $this->createForm(EducationAdminType::class, $education);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            if(!is_null($education->getEndDate()) && $education->getInProgress()) {
                $education->setEndDate(null);
            } elseif(is_null($education->getEndDate()) && !$education->getInProgress()) {
                $education->setInProgress(true);
            }
            
            $manager->persist($education);
            $manager->flush();

            $message = [
                "class" => "alert-success text-center",
                "content" => "L'ajout a été faite."
            ];
        }

        return $this->render('Admin/Education/add.html.twig', [
            "title" => "Education",
            'form' => $form->createView(),
            'message' => isset($message) ? $message : null
        ]);
    }

    /**
     * @Route("/admin/education/{id}", requirements={"id" = "^\d+(?:\d+)?$"}, name="adminEditEducation")
     * @IsGranted("ROLE_ADMIN")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function admin_edit_education(Education $education, Request $request, EntityManagerInterface $manager)
    {
        $form = $this->createForm(EducationAdminType::class, $education);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $manager->persist($education);
            $manager->flush();

            $message = [
                "class" => "alert-success text-center w-20px",
                "content" => "La mise à jout s'est correctement effectuée"
            ];
        }

        return $this->render('Admin/Education/edit.html.twig', [
            "form_edit_education" => $form->createView(),
            "education_id" => $education->getId(),
            "title" => "Edit Education",
            "message" => isset($message) ? $message : null
        ]);
    }

    /**
     * @Route("/admin/education/{id}", requirements={"id" = "^\d+(?:\d+)?$"}, name="adminDeleteEducation")
     * @IsGranted("ROLE_ADMIN")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function admin_delete_education(Education $education, EntityManagerInterface $manager)
    {
        $manager->remove($education);
        $manager->flush();

        return $this->redirectToRoute("adminProject");
    }

    /**
     * @Route("/admin/work", name="adminProject")
     * @Route("/admin/work/page/{page}", requirements={"id" = "^\d+(?:\d+)?$"}, name="adminProjectPage")
     * @IsGranted("ROLE_ADMIN")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function admin_project(Request $request, $page = 1)
    {
        $limit = 20;
        $form = $this->createForm(SearchType::class);
        $form->handleRequest($request);

        if($form->isSubmitted()) {
            $searchItem = trim(strip_tags($request->get('search')["search_input"]));
            $projects = $this->getDoctrine()->getRepository(Project::class)->getProjectByName($searchItem);
        } else {
            $projects = $this->getDoctrine()->getRepository(Project::class)->getProject($page - 1, $limit);
        }

        return $this->render('Admin/Portfolio/index.html.twig', [
            "projects" => $projects,
            "search" => $form->createView(),
            "title" => "Work",
            "page" => $page,
            "total_page" => ceil($this->getDoctrine()->getRepository(Project::class)->getNbrProject()[1] / $limit)
        ]);
    }

    /**
     * @Route("/admin/work/{id}", requirements={"id" = "^\d+(?:\d+)?$"}, name="adminEditProject")
     * @IsGranted("ROLE_ADMIN")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function admin_edit_project(Project $project, Request $request, EntityManagerInterface $manager)
    {
        $form = $this->createForm(ProjectAdminType::class, $project);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form['imgPath']->getData();
            
            if($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
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
                $manager->persist($project);
                $manager->flush();

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
     * @Route("/admin/work/add", name="adminAddProject")
     * @IsGranted("ROLE_ADMIN")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function admin_add_project(Request $request, EntityManagerInterface $manager)
    {
        $project = new Project();
        $form = $this->createForm(ProjectAdminType::class, $project);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form['imgPath']->getData();
            
            if($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $newFilename = 'portFolio-'.str_replace(" ", "_", $project->getName()) . "-" . $project->getVersion() .'.'.$imageFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    if(!array_search('./content/portfolio/'.$newFilename, glob("./content/portfolio/*.".$imageFile->guessExtension()))) {
                        $imageFile->move(
                            $this->getParameter('project_img_dir'),
                            $newFilename
                        );
                    }
                } catch (FileException $e) {
                    dd($e->getMessage());
                }

                $project->setImgPath($newFilename);
                $project->setCreatedAt(new \Datetime());
                $manager->persist($project);
                $manager->flush();

                return $this->redirectToRoute('adminProject');
            }
        }

        return $this->render('Admin/Portfolio/add.html.twig', [
            "form_add_project" => $form->createView(),
            "title" => "Add Work"
        ]);
    }

    /**
     * @Route("/admin/work/search", name="adminSearchProject", methods="POST")
     * @IsGranted("ROLE_ADMIN")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function admin_search_project(Request $request)
    {
        return $this->render("Admin/Portfolio/index.html.twig");
    }

    /**
     * @Route("/admin/work/delete/{id}", requirements={"id" = "^\d+(?:\d+)?$"}, name="adminDeleteProject")
     * @IsGranted("ROLE_ADMIN")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function admin_delete_project(Project $project, EntityManagerInterface $manager)
    {
        if(in_array("./content/portfolio/".$project->getImgPath(), glob("./content/portfolio/*.*"))) {
            unlink("./content/portfolio/".$project->getImgPath());
            $manager->remove($project);
            $manager->flush();
        }

        return $this->redirectToRoute("adminProject");
    }

    /**
     * @Route("/admin/news", name="adminNews")
     * @IsGranted("ROLE_ADMIN")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function admin_news()
    {
        $rss_link = "https://www.developpez.com/index/rss";
        $rss_load = simplexml_load_file($rss_link);
        
        if($rss_load == false) {
            $rss_load = [];
        }
        
        return $this->render('Admin/news.html.twig', [
            'rss_load' => $rss_load,
            "title" => "News"
        ]);
    }

    /**
     * @Route("/admin/contact", name="adminContact")
     * @Route("/admin/contact/page/{page}", requirements={"id" = "^\d+(?:\d+)?$"}, name="adminContactByPage")
     * @IsGranted("ROLE_ADMIN")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function admin_contact($offset = 1)
    {
        $limit = 10;

        return $this->render("Admin/Contact/index.html.twig", [
            "title" => "Contact",
            "list_mail" => $this->getDoctrine()->getRepository(Contact::class)->getMail($offset, $limit),
            "offset" => $offset,
            "total_page" => ceil($this->getDoctrine()->getRepository(Contact::class)->getNbrContact()[1] / $limit)
        ]);
    }

    /**
     * @Route("/admin/contact/{id}", requirements={"id" = "^\d+(?:\d+)?$"}, name="adminReadMail")
     * @IsGranted("ROLE_ADMIN")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function admin_read_mail(Contact $contact, EntityManagerInterface $manager)
    {
        if(!$contact->getIsRead()) {
            $contact->setIsRead(true);
            $manager->persist($contact);
            $manager->flush();
        }

        $contact->setEmailContent(json_decode($contact->getEmailContent()));

        return $this->render("Admin/Contact/read.html.twig", [
            "title" => "Contact",
            "mail" => $contact
        ]);
    }

    /**
     * @Route("/admin/logout", name="adminLogout")
     * @IsGranted("ROLE_ADMIN")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function admin_logout()
    {
        return $this->render('User/home.html.twig');
    }
}
