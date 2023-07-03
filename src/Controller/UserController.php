<?php

namespace App\Controller;

use App\Manager\ContactManager;
use App\Repository\SkillsRepository;
use App\Repository\ProjectRepository;
use App\Repository\ServiceRepository;
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
    private EntityManagerInterface $em;
    private ContactManager $contactManager;
    private SkillsRepository $skillsRepository;
    private ProjectRepository $projectRepository;
    private ServiceRepository $serviceRepository;
    private EducationRepository $educationRepository;

    function __construct(
        EntityManagerInterface $em, 
        ContactManager $contactManager,
        SkillsRepository $skillsRepository,
        ProjectRepository $projectRepository,
        ServiceRepository $serviceRepository,
        EducationRepository $educationRepository
    ) {
        $this->em = $em;
        $this->contactManager = $contactManager;
        $this->skillsRepository = $skillsRepository;
        $this->projectRepository = $projectRepository;
        $this->serviceRepository = $serviceRepository;
        $this->educationRepository = $educationRepository;
    }

    /**
     * @Route("/", name="home")
     */
    public function home(Request $request)
    {
        $formContact = $this->createForm(ContactUserType::class, $contact = new Contact());
        $formContact->handleRequest($request);
        $response = [];
        $captchat = [
            "question" => "Combien fait 2 x 2 ?",
            "answer" => 4
        ];

        $skills = $this->skillsRepository->getSkillsOrderedByCategory();
        $orderedSkills = [];
        foreach($skills as $skill) {
            $orderedSkills[$skill->getType()][] = $skill;
        }

        // If form is submitted
        if($formContact->isSubmitted()) {

            // If all field is valid
            if($formContact->isValid()) {
                
                // If captchat has been filled and the answer if correct => Prevent mail spamming
                if($request->request->get("captchat") == $captchat["answer"]) {
                    
                    // Send an email to the admin
                    ["answer" => $answer, "response" => $response] = $this->contactManager->sendMail(
                        $contact->getSenderEmail(), // Sender fullname
                        $contact->getSenderEmail(), // Sender email
                        $contact->getEmailSubject(), // Email subject
                        $contact->getEmailContent() // Email content
                    );
                    
                    // If the email has been send then, save the data into database
                    if($answer) {
                        $contact->setSenderFullName($contact->getEmailContent());
                        $contact->setEmailContent($contact->getEmailContent());
                        $contact->setIsRead(false);
                        $contact->setCreatedAt(new \DateTimeImmutable());
                        $this->em->persist($contact);
                        $this->em->flush();
                    }
                } else {
                    $response = [
                        "class" => "danger",
                        "message" => "Le captchat est incorrect."
                    ];
                }
            } else {
                $response = [
                    "class" => "danger",
                    "message" => "Une erreur a été rencontré avec l'un des champs"
                ];
            }
        }

        return $this->render("User/home.html.twig", [
            "response" => $response,
            "user" => $this->em->getRepository(User::class)->getUserByID(),
            "services" => $this->serviceRepository->findAll(),
            "skillCategories" => $orderedSkills,
            "portfolios" => $this->projectRepository->getLastestProject(6),
            "educations" => $this->educationRepository->getLatestEducationFromCategory("experience", 5),
            "contactForm" => $formContact->createView(),
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
