<?php

namespace App\Controller;

use App\Entity\Telephone;
use App\Entity\User;
use App\Message\RemoveUserMessage;
use App\Message\CreateUserMessage;
use App\Message\UpdateUserMessage;
use App\Message\ListUserMessage;
use App\Message\DetailUserMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;


class UserController extends AbstractController
{
    private MessageBusInterface $bus;

    private ValidatorInterface $validator;
    private EntityManagerInterface $manager;

    public function __construct(EntityManagerInterface $manager, ValidatorInterface $validator, \Symfony\Component\Messenger\MessageBusInterface $bus)
    {
        $this->manager = $manager;
        $this->validator = $validator;
        $this->bus = $bus;
    }

    /**
     * @Route("/users", methods={"GET"})
     */
    public function listAction(): Response
    {
        $envelope = $this->bus->dispatch(new ListUserMessage());
        $handledStamp = $envelope->last(HandledStamp::class);

        return new JsonResponse($handledStamp->getResult());
    }

    /**
     * @Route("/users/{id}", methods={"GET"})
     */
    public function detailAction(int $id): Response
    {
        $envelope = $this->bus->dispatch(new DetailUserMessage($id));
        $handledStamp = $envelope->last(HandledStamp::class);

        return new JsonResponse($handledStamp->getResult());
    }

    /**
     * @Route("/users", methods={"POST"})
     */
    public function createAction(Request $request): Response
    {
        $this->bus->dispatch(new CreateUserMessage($request->getContent()));
        return new Response('', Response::HTTP_OK);
    }

    /**
     * @Route("/users/{id}", methods={"PUT"})
     */
    public function updateAction(Request $request, int $id): Response
    {
        $this->bus->dispatch(new UpdateUserMessage($id, $request->getContent()));
        return new Response('', Response::HTTP_OK);
    }

    /**
     * @Route("/users/{id}", methods={"DELETE"})
     */
    public function removeAction(int $id): Response
    {
        $this->bus->dispatch(new RemoveUserMessage($id));
        return new Response('', Response::HTTP_OK);
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
