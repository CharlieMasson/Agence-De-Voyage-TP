<?php

namespace App\Controller\Admin;

use App\Entity\Travel;
use App\Form\TravelType;
use App\Repository\TravelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/admin/travel')]
final class TravelController extends AbstractController
{
    #[Route(name: 'app_travel_index', methods: ['GET'])]
    public function index(TravelRepository $travelRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $queryBuilder = $travelRepository->createQueryBuilder('a')
        ->orderBy('a.id', 'ASC');

        $pagination = $paginator->paginate(
            $queryBuilder, 
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('admin/travel/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/new', name: 'app_travel_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $travel = new Travel();
        $form = $this->createForm(TravelType::class, $travel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($travel);
            $entityManager->flush();

            return $this->redirectToRoute('app_travel_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/travel/new.html.twig', [
            'travel' => $travel,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_travel_show', methods: ['GET'])]
    public function show(Travel $travel): Response
    {
        return $this->render('admin/travel/show.html.twig', [
            'travel' => $travel,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_travel_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Travel $travel, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TravelType::class, $travel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_travel_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/travel/edit.html.twig', [
            'travel' => $travel,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_travel_delete', methods: ['POST'])]
    public function delete(Request $request, Travel $travel, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$travel->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($travel);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_travel_index', [], Response::HTTP_SEE_OTHER);
    }
}
