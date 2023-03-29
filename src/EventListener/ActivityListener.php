<?php

namespace App\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use App\Entity\Participant;

class ActivityListener
{
    private Security $security;
    public function __construct(Security $security){
        $this->security=$security;
    }
    public function onKernelRequest(RequestEvent $event, EntityManagerInterface $entityManager){
        if ($event->getRequestType()!==HttpKernelInterface::MAIN_REQUEST){
            return;
        }
        if ($this->security->getUser()) {
            $user = $this->security->getUser();
            $user->setLastActivityAt(new \DateTimeImmutable());
            $entityManager->persist($user);
            $entityManager->flush();
            dump('test');
        }
    }
}