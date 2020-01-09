<?php

namespace App\Controller;

use App\Entity\Project;
use App\Form\ProjectType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin", name="admin")
     * @IsGranted("ROLE_ADMIN")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function admin()
    {
        return $this->render('Admin/home.html.twig');
    }

    /**
     * @Route("/admin/about", name="adminAbout")
     * @IsGranted("ROLE_ADMIN")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function admin_about()
    {
        return $this->render('Admin/about.html.twig');
    }

    /**
     * @Route("/admin/work", name="adminProject")
     * @IsGranted("ROLE_ADMIN")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function admin_project()
    {
        return $this->render('Admin/Portfolio/index.html.twig', [
            "projects" => $this->getDoctrine()->getRepository(Project::class)->findAll()
        ]);
    }

    /**
     * @Route("/admin/work/{id}", requirements={"id" = "^\d+(?:\d+)?$"}, name="admin_one_project")
     * @IsGranted("ROLE_ADMIN")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function admin_print_project(Project $project)
    {
        return $this->render('Admin/Portfolio/edit.html.twig', [
            "project" => $project
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
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form['imgPath']->getData();
            
            if($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $newFilename = 'portFolio-'.str_replace(" ", "_", $project->getName()).'.'.$imageFile->guessExtension();

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
            "form_add_project" => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/work/delete/{id}", requirements={"id" = "^\d+(?:\d+)?$"}, name="adminDeleteProject")
     * @IsGranted("ROLE_ADMIN")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function admin_delete_project(int $id)
    {
        return $this->render('Admin/home.html.twig');
    }

    /**
     * @Route("/admin/contact", name="adminContact")
     * @IsGranted("ROLE_ADMIN")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function admin_contact()
    {
        return $this->render('Admin/contact.html.twig');
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
        
        return $this->render('Admin/news.html.twig', [
            'rss_load' => $rss_load
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
