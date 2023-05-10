<?php

namespace App\Controller;

use App\Entity\{Contact, Education, Project, Skills, User};
use App\Form\{ContactUserType, LoginAdminType};
use App\Manager\ContactManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{
    private $em;
    private $contactManager;

    function __construct(EntityManagerInterface $manager) {
        $this->em = $manager;
        $this->contactManager = new ContactManager();
    }

    /**
     * @Route("/", name="home")
     */
    public function home(Request $request)
    {
        $formContact = $this->createForm(ContactUserType::class, $contact = new Contact());
        $formContact->handleRequest($request);
        $captchat = [
            "question" => "Combien fait 3 x 1.5 ?",
            "answer" => 4.5
        ];

        $skills = $this->em->getRepository(Skills::class)->getSkillsOrderedByCategory();
        $orderedSkills = [];
        foreach($skills as $skill) {
            $orderedSkills[$skill->getType()][] = $skill;
        }

        if($formContact->isSubmitted() && $formContact->isValid()) {
            ["answer" => $answer, "response" => $response] = $this->contactManager->sendMail($newSend->getSenderFullName(), $newSend->getSenderEmail(), $newSend->getEmailSubject(), $newSend->getEmailContent());
            
            if($answer) {
                $newSend->setEmailContent(json_encode($newSend->getEmailContent()));
                $newSend->setIsRead(false);
                $newSend->setCreatedAt(new \DateTimeImmutable());
                $this->em->persist($newSend);
                $this->em->flush();
            }
        }

        return $this->render("User/home.html.twig", [
            "user" => $this->em->getRepository(User::class)->getUserByID(),
            "skillCategories" => $orderedSkills,
            "portfolios" => $this->em->getRepository(Project::class)->getLastestProject(6),
            "educations" => $this->em->getRepository(Education::class)->getLatestEducationFromCategory("experience", 5),
            "contactForm" => $formContact->createView(),
            "response" => !empty($response) ? $response : [],
            "captchat" => $captchat
        ]);
    }

    /**
     * @Route("/portfolio", name="portfolio")
     * @Route("/portfolio/page/{page}", requirements={"id" = "^\d+(?:\d+)?$"}, name="portfolioPage")
     */
    public function portfolio(int $page = 1)
    {
        $limit = 15;
        $page = ($page >= 1 ? $page : 1);
        $projectRepo = $this->em->getRepository(Project::class);

        return $this->render("User/portFolio.html.twig", [
            "offset" => $page,
            "portfolio" => $projectRepo->getProject($page - 1, $limit),
            "total_page" => ceil($projectRepo->countProject() / $limit)
        ]);
    }

    /**
     * @Route("/portfolio/{portfolioID}", requirements={"portfolioID" = "^\d+(?:\d+)?$"}, name="single_portfolio")
     */
    public function single_portfolio(int $portfolioID)
    {
        $portfolio = $this->em->getRepository(Project::class)->find($portfolioID);
        if(empty($portfolio)) {
            return $this->redirectToRoute("portfolio");
        }

        return $this->render("User/portfolioDetail.html.twig", [
            "portfolio" => []
        ]);
    }

    /**
     * @Route("/login", name="login")
     */
    public function login()
    {
        return $this->render("User/login.html.twig", [
            "formLogin" => $this->createForm(LoginAdminType::class)->createView()
        ]);
    }
}
