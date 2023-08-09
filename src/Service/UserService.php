<?php
declare(strict_types=1);

use App\Entity\User;
use App\Repository\UserRepository;

class UserService{
    public function __construct(private UserRepository $userRepository){
        
    }
    public function createAnon():User
    {
        $anon = new User();
        $anon->setEmail('anonymous@test.com');
        $anon->setPassword('none');
        $anon->setUsername('anonymous');
        $this->userRepository->save($anon,true);
        return $anon;
    }
}
