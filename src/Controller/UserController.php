<?php
// src/Controller/UserController.php

namespace App\Controller;

use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class UserController extends AbstractController
{
    private $entityManager;
    private $userRepository;

    public function __construct(EntityManagerInterface $entityManager, UserRepository $userRepository)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/api/users", name="api_users", methods={"GET"})
     */
    public function getUsers(): JsonResponse
    {
        $users = $this->userRepository->findAll();

        $data = [];
        foreach ($users as $user) {
            $data[] = $this->serializeUser($user);
        }

        return $this->json($data);
    }

    /**
     * @Route("/api/users", name="api_user_add", methods={"POST"})
     */
    public function addUser(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $user = new User();
        $this->deserializeUser($user, $data);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->json(['message' => 'User added successfully'], 201);
    }

    /**
     * @Route("/api/users/{id}", name="api_user_update", methods={"PUT"})
     */
    public function updateUser(int $id, Request $request): JsonResponse
    {
        $user = $this->userRepository->find($id);

        if (!$user) {
            return $this->json(['message' => 'User not found'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $this->deserializeUser($user, $data);

        $this->entityManager->flush();

        return $this->json(['message' => 'User updated successfully']);
    }

    /**
     * @Route("/api/users/{id}", name="api_user_delete", methods={"DELETE"})
     */
    public function deleteUser(int $id): JsonResponse
    {
        $user = $this->userRepository->find($id);

        if (!$user) {
            return $this->json(['message' => 'User not found'], 404);
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return $this->json(['message' => 'User deleted successfully']);
    }

    /**
     * Serialize a user entity to an array.
     */
    private function serializeUser(User $user): array
    {
        return [
            'id' => $user->getId(),
            'name' => $user->getName(),
            'surname' => $user->getSurname(),
            'email' => $user->getEmail(),
            'phone' => $user->getPhone(),
            'birthDate' => $user->getBirthDate()->format('Y-m-d'),
        ];
    }

    /**
     * Deserialize data to a user entity.
     */
    private function deserializeUser(User $user, array $data): void
    {
        $user->setName($data['name']);
        $user->setSurname($data['surname']);
        $user->setEmail($data['email']);
        $user->setPhone($data['phone']);
        $user->setBirthDate(new \DateTime($data['birthDate'])); // Adjust the date format as needed
    }
}