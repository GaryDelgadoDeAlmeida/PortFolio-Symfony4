<?php

namespace App\Controller;

use App\Manager\ContactManager;
use App\Repository\SkillsRepository;
use App\Repository\ProjectRepository;
use App\Repository\EducationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use App\Form\{ContactUserType, LoginAdminType};
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\{Contact, Education, Project, Skills, User};
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{
    private ContactManager $contactManager;

    private EntityManagerInterface $em;
    private SkillsRepository $skillsRepository;
    private ProjectRepository $projectRepository;
    private EducationRepository $educationRepository;

    function __construct(
        EntityManagerInterface $em, 
        ContactManager $contactManager,
        ProjectRepository $projectRepository,
        SkillsRepository $skillsRepository,
        EducationRepository $educationRepository
    ) {
        $this->em = $em;
        $this->contactManager = $contactManager;
        $this->skillsRepository = $skillsRepository;
        $this->projectRepository = $projectRepository;
        $this->educationRepository = $educationRepository;
    }

    /**
     * @Route("/", name="home")
     */
    public function home(Request $request)
    {
        $formContact = $this->createForm(ContactUserType::class, $contact = new Contact());
        $formContact->handleRequest($request);
        $captchat = [
            "question" => "Combien fait 2 x 2 ?",
            "answer" => 4
        ];

        $skills = $this->skillsRepository->getSkillsOrderedByCategory();
        $orderedSkills = [];
        foreach($skills as $skill) {
            $orderedSkills[$skill->getType()][] = $skill;
        }

        if($formContact->isSubmitted() && $formContact->isValid()) {
            dd(
                $request,
                $request->request,
                $request->get("captcha"),
                $formContact,
                $captchat
            );
            ["answer" => $answer, "response" => $response] = $this->contactManager->sendMail($newSend->getSenderFullName(), $newSend->getSenderEmail(), $newSend->getEmailSubject(), $newSend->getEmailContent());
            
            if($answer) {
                $newSend->setEmailContent($newSend->getEmailContent());
                $newSend->setIsRead(false);
                $newSend->setCreatedAt(new \DateTimeImmutable());
                $this->em->persist($newSend);
                $this->em->flush();
            }
        }

        return $this->render("User/home.html.twig", [
            "user" => $this->em->getRepository(User::class)->getUserByID(),
            "skillCategories" => $orderedSkills,
            "portfolios" => $this->projectRepository->getLastestProject(6),
            "educations" => $this->educationRepository->getLatestEducationFromCategory("experience", 5),
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

        return $this->render("User/portFolio.html.twig", [
            "offset" => $page,
            "portfolio" => $this->projectRepository->getProject($page - 1, $limit),
            "total_page" => ceil($this->projectRepository->countProject() / $limit)
        ]);
    }

    /**
     * @Route("/portfolio/{portfolioID}", requirements={"portfolioID" = "^\d+(?:\d+)?$"}, name="single_portfolio")
     */
    public function single_portfolio(int $portfolioID)
    {
        $portfolio = $this->projectRepository->find($portfolioID);
        if(empty($portfolio)) {
            return $this->redirectToRoute("portfolio");
        }

        return $this->render("User/portfolioDetail.html.twig", [
            "portfolio" => $portfolio
        ]);
    }

    /**
     * @Route("/login", name="login")
     */
    public function login(Security $security)
    {
        if($security->getUser()) {
            return $this->redirectToRoute("adminHome");
        }

        return $this->render("User/login.html.twig", [
            "formLogin" => $this->createForm(LoginAdminType::class)->createView()
        ]);
    }
}
