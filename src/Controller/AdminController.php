<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Contact;
use App\Entity\Project;
use App\Form\SearchType;
use App\Form\AboutAdminType;
use App\Form\ProjectAdminType;
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
     * @Route("/admin/about", name="adminAbout")
     * @IsGranted("ROLE_ADMIN")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function admin_about(Secu $security, Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder)
    {
        $user = $security->getUser();
        $form = $this->createForm(AboutAdminType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            if(
                preg_match('/[\'\/~`\!@#\$%\^&\*\(\)_\-\+=\{\}\[\]\|;:"\<\>,\.\?\\\]/', $user->getPassword()) && 
                preg_match('/[0-9]/i', $user->getPassword())
            ) {
                $user->setPassword($encoder->encodePassword($user, $user->getPassword()));
                $manager->persist($user);
                $manager->flush();
                $message = [
                    "class" => 'alert alert-success',
                    "message" => 'La mise à jour s\'est correctement éffectuée.'
                ];
            } else {
                $message = [
                    "class" => 'alert alert-warning',
                    "message" => 'Le mot de passe doit contenir des caractères spéciaux et des nombres en plus de sa longue minimale'
                ];
            }
        }

        return $this->render('Admin/About/index.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'message' => isset($message) ? $message : "",
            "title" => "Profile"
        ]);
    }

    /**
     * @Route("/admin/work", name="adminProject")
     * @IsGranted("ROLE_ADMIN")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function admin_project(Request $request)
    {
        $form = $this->createForm(SearchType::class);
        $form->handleRequest($request);

        if($form->isSubmitted()) {
            $searchItem = trim(strip_tags($request->get('search')["search_input"]));
            $projects = $this->getDoctrine()->getRepository(Project::class)->getProjectByName($searchItem);
        } else {
            $projects = $this->getDoctrine()->getRepository(Project::class)->getProject();
        }

        return $this->render('Admin/Portfolio/index.html.twig', [
            "projects" => $projects,
            "search" => $form->createView(),
            "title" => "Work"
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
                    if(!array_search('./content/img/portfolio/'.$newFilename, glob("./content/img/portfolio/*.".$imageFile->guessExtension()))) {
                        $imageFile->move(
                            $this->getParameter('project_img_dir'),
                            $newFilename
                        );
                    } else {
                        unlink('./content/img/portfolio/'.$newFilename);
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
                    if(!array_search('./content/img/portfolio/'.$newFilename, glob("./content/img/portfolio/*.".$imageFile->guessExtension()))) {
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
        if(in_array("./content/img/portfolio/".$project->getImgPath(), glob("./content/img/portfolio/*.*"))) {
            unlink("./content/img/portfolio/".$project->getImgPath());
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
     * @IsGranted("ROLE_ADMIN")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function admin_contact()
    {
        return $this->render("Admin/Contact/index.html.twig", [
            "title" => "Contact"
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
