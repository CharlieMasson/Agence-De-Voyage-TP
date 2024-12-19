<?php

namespace App\Security;

use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{

    public function checkPreAuth(UserInterface $user) : void
    {
        if (in_array('ROLE_BANNED', $user->getRoles())) {
            throw new CustomUserMessageAuthenticationException("Vous êtes est banni.");
        }
    }

    public function checkPostAuth(UserInterface $user) : void
    {

    }
}