<?php

namespace App\Security;

namespace App\Security;

use App\Entity\Participant as AppUser;
use Symfony\Component\Security\Core\Exception\AccountExpiredException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof AppUser) {
            return;
        }


    }

    public function checkPostAuth(UserInterface $user): void
    {
        if (in_array('ROLE_INACTIF',$user->getRoles())) {
            throw new AccountExpiredException('Compte désactivé. Contacter un administrateur');
        }
        return;

    }
}