<?php

namespace App\Controller;

use App\Entity\Author;
use App\Repository\AuthorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AuthorController extends AbstractController
{
    #[Route('/api/author', name: 'author', methods: ['GET'])]
    public function getAllAuthor(AuthorRepository $authorRepository, SerializerInterface $serializer): JsonResponse
    {
        $authorList = $authorRepository->findAll();
        $jsonBookList = $serializer->serialize($authorList, 'json',
            ['groups' => 'getAuthor']);
        return new JsonResponse($jsonBookList, Response::HTTP_OK,
            [], true);
    }
    #[Route('/api/author/{id}', name: 'detailAuthor', methods:
        ['GET'])]
    public function getDetailAuthor(Author $author, SerializerInterface $serializer): JsonResponse
    {
        $jsonBook = $serializer->serialize($author, 'json', ['groups'
            => 'getAuthor']);
        return new JsonResponse($jsonBook, Response::HTTP_OK, [],
            true);
    }



    //CRUD AUTEUR
    #[Route('/api/author/{id}', name: 'deleteAuthor', methods:
        ['DELETE'])]
    public function deleteAuthor(Author $author, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($author);
        $em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/author', name: "createAuthor", methods: ['POST'])]
    public function createAuthor(Request $request,
        SerializerInterface $serializer, EntityManagerInterface $em,
        UrlGeneratorInterface $urlGenerator, AuthorRepository $authorRepository, ValidatorInterface $validator): JsonResponse
    {
        $author = $serializer->deserialize($request->getContent(),
            Author::class, 'json');

        $errors = $validator->validate($author);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors,
                'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $request->toArray();
        $em->persist($author);
        $em->flush();
        $jsonBook = $serializer->serialize($author, 'json', ['groups'
            => 'getAuthor']);
        $location = $urlGenerator->generate('detailAuthor', ['id' =>
            $author->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        return new JsonResponse($jsonBook, Response::HTTP_CREATED,
            ["Location" => $location], true);
    }

    #[Route('/api/author/{id}', name: "updateAuthor", methods: ['PUT'])]
    public function updateBook(Request $request,
        SerializerInterface $serializer, Author $currentAuthor,
        EntityManagerInterface $em, AuthorRepository $authorRepository): JsonResponse
    {
        $updatedBook = $serializer->deserialize($request->getContent(),
            Author::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentAuthor]);
        $request->toArray();
        $em->persist($updatedBook);
        $em->flush();
        return new JsonResponse(null,
            JsonResponse::HTTP_NO_CONTENT);
    }
}
