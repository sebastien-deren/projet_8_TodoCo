<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Guard\AuthenticatorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'login')]
    public function loginAction( AuthenticationUtils $authenticationUtils): \Symfony\Component\HttpFoundation\Response
    {


        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', array(
            'last_username' => $lastUsername,
            'error'         => $error,
        ));
    }

    #[Route(path: '/login_check', name: 'login_check')]
    public function loginCheck(): void
    {
        // This code is never executed.
    }

    #[Route(path: '/logout', name: 'logout')]
    public function logoutCheck(): void
    {
        // This code is never executed.
    }
}
