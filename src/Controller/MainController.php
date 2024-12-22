<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Knp\Component\Pager\PaginatorInterface;
use App\Repository\TravelRepository;
use App\Entity\Travel;
use App\Entity\Activity;

class MainController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route(path: '/', name: 'index')]
    public function index(TravelRepository $travelRepository): Response
    {
        $travels = $travelRepository->createQueryBuilder('t')
        ->setMaxResults(3) 
        ->getQuery()
        ->getResult();

    return $this->render('index.html.twig', [
        'travels' => $travels,
    ]);
    }

    #[Route(path: '/view-travel/{id}', name: 'travel_details')]
    public function details(int $id, TravelRepository $travelRepository): Response
    {
        $travel = $travelRepository->find($id);

        if (!$travel) {
            throw $this->createNotFoundException('Voyage non trouvé.');
        }

        $sortedComments = $travel->getComments()->toArray();

        usort($sortedComments, function ($a, $b) {
            return $b->getPostedAt() <=> $a->getPostedAt(); 
        });

        return $this->render('travel/details.html.twig', [
            'travel' => $travel,
            'sortedComments' => $sortedComments,
        ]);
    }

    #[Route('/travels', name: 'travel_list')]
    public function list(Request $request, PaginatorInterface $paginator, TravelRepository $travelRepository): Response
    {
        $queryBuilder = $travelRepository->createQueryBuilder('t')
            ->orderBy('t.startAt', 'ASC'); 

        $pagination = $paginator->paginate(
            $queryBuilder, 
            $request->query->getInt('page', 1), 
            12 
        );

        return $this->render('travel/list.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/profile', name: 'user_profile')]
    public function profile(): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('user/profile.html.twig', [
            'user' => $user
        ]);
    }
}
