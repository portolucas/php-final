<?php

namespace App\MessageHandler;

use App\Entity\User;
use App\Message\CreateUserMessage;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\ConstraintViolationInterface;

final class CreateUserMessageHandler implements MessageHandlerInterface
{
    private EntityManagerInterface $manager;
    private ValidatorInterface $validator;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
        $this->validator = Validation::createValidator();
    }

    public function __invoke(CreateUserMessage $message)
    {
        $json = json_decode($message->getUserData(), true);

        $user = new User($json['name'], $json['email']);
        foreach ($json['telephones'] as $telephone) {
            $user->addTelephone($telephone['number']);
        }

        $errors = $this->validator->validate($json['name'], [new Length(['min' => 5])]);

        if (count($errors) > 0) {
            $violations = array_map(fn(ConstraintViolationInterface $violation) => [
                'property' => $violation->getPropertyPath(),
                'message' => $violation->getMessage()
            ], iterator_to_array($errors));
            throw new \InvalidArgumentException('Invalid data' . violations);
        }

        $this->manager->persist($user);
        $this->manager->flush();
    }
}
