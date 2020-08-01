<?php

namespace App\MessageHandler;

use App\Entity\User;
use App\Message\UpdateUserMessage;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\ConstraintViolationInterface;

final class UpdateUserMessageHandler implements MessageHandlerInterface
{
    private EntityManagerInterface $manager;
    private ValidatorInterface $validator;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
        $this->validator = Validation::createValidator();
    }

    public function __invoke(UpdateUserMessage $message)
    {

        $json = json_decode($message->getUserData(), true);

        $user = $this->manager->getRepository(User::class)->find($message->getUserId());

        if (null === $user) {
            throw new \InvalidArgumentException('User with ID #' . $message->getUserId() . ' not found');
        }

        $user->setName($json['name']);
        $user->setEmail($json['email']);

        $errors = $this->validator->validate($json['name'], [new Length(['min' => 5])]);

        if (count($errors) > 0) {
            $violations = array_map(fn(ConstraintViolationInterface $violation) => [
                'property' => $violation->getPropertyPath(),
                'message' => $violation->getMessage()
            ], iterator_to_array($errors));
            return new JsonResponse($violations, Response::HTTP_BAD_REQUEST);
        }

        $this->manager->persist($user);
        $this->manager->flush();

        
    }
}
