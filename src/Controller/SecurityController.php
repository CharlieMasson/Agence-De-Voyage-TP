<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\entity\User;

class SecurityController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path:'/register', name:'register')]
        public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        if ($request->isMethod('POST')) {
            $email = $request->get('username');
            $password = $request->get('password');
            $passwordCheck = $request->get('password-confirm');
            
            if ($password !== $passwordCheck) {
                $this->addFlash('error', 'Les mots de passe ne correspondent pas.');
            } elseif (strlen($password) < 8) {
                $this->addFlash('error', 'Le mot de passe doit contenir au moins 8 caractères.');
            } else {
                $user = New User();
                $hashedPassword = $passwordHasher->hashPassword($user, $password);
                $user->setEmail($email)->setPassword($hashedPassword);

                $this->entityManager->persist($user);
                $this->entityManager->flush();
    
                $this->addFlash('success', 'VVotre compte a été créé avec succès.');
                return $this->redirectToRoute('login');
            }
        }
        return $this->render('security/register.html.twig', []);
    }
}
