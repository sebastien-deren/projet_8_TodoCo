<?php
declare(strict_types=1);
namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SecurityService
{
    public function __construct(
        private UserPasswordHasherInterface $userPasswordHasherInterface,
        private UserRepository $userRepository)
    {
        
    }
    public function setPassword(User $user,string $password)
    {
        $user->setPassword($this->userPasswordHasherInterface->hashPassword($user,$password));
    }
    public function saveUser(User $user)
    {
        $this->userRepository->save($user,true);
    }
}
