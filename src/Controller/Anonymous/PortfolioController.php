<?php

namespace App\Controller\Anonymous;

use App\Repository\ProjectRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PortfolioController extends AbstractController
{
    private ProjectRepository $projectRepository;

    function __construct(ProjectRepository $projectRepository) {
        $this->projectRepository = $projectRepository;
    }
    /**
     * @Route("/portfolio", name="portfolio")
     * @Route("/portfolio/page/{page}", requirements={"id" = "^\d+(?:\d+)?$"}, name="portfolioPage")
     */
    public function portfolio(Request $request, int $page = 1)
    {
        $limit = 15;
        $page = ($page > 0 ? $page : 1);

        return $this->render("User/portFolio.html.twig", [
            "offset" => $page,
            "portfolio" => $this->projectRepository->getProject($page - 1, $limit),
            "total_page" => ceil($this->projectRepository->countProject() / $limit)
        ]);
    }

    /**
     * @Route("/portfolio/{portfolioID}", requirements={"portfolioID" = "^\d+(?:\d+)?$"}, name="single_portfolio")
     */
    public function single_portfolio(Request $request, int $portfolioID)
    {
        $portfolio = $this->projectRepository->find($portfolioID);
        if(empty($portfolio)) {
            return $this->redirectToRoute("portfolio");
        }

        return $this->render("User/portfolioDetail.html.twig", [
            "portfolio" => $portfolio
        ]);
    }
}
