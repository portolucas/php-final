<?php

namespace App\MessageHandler;

use App\Entity\User;
use App\Entity\Telephone;
use App\Message\ListUserMessage;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\ConstraintViolationInterface;

final class ListUserMessageHandler implements MessageHandlerInterface
{
    private EntityManagerInterface $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    public function __invoke(ListUserMessage $message)
    {

        $users = $this->manager->getRepository(User::class)->findAll();

        $data = [];
        foreach ($users as $user) {
            $data[] = $this->userToArray($user);
        }

        return $data;
    }

    private function userToArray(User $user): array
    {
        return [
            'id' => $user->getId(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'telephones' => array_map(fn(Telephone $telephone) => [
                'number' => $telephone->getNumber()
            ], $user->getTelephones()->toArray())
        ];
    }
}