<?php

namespace App\Controller;

use App\Event\CommentCreatedEvent;
use App\Form\CommentType;
use App\Repository\PostRepository;
use App\Repository\TagRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\NoteRepository;
use App\Entity\Note;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class NoteController extends AbstractController
{
    public function index(Request $request): Response
    {
        
        $notes = $this->getDoctrine()
            ->getRepository(Note::class)
            ->findAll();


        return $this->render('Note\index.html.twig', [
            'notes' => $notes
        ]);
    }

    public function create(Request $request): Response
    {
        
        $entityManager = $this->getDoctrine()->getManager();

        if(!$_POST["title"]){
            return new Response('Pas de titre');
        }
        $note = new Note();
        $note->setTitle($_POST["title"]);
        $note->setText($_POST["text"]);

        // tell Doctrine you want to (eventually) save the note (no queries yet)
        $entityManager->persist($note);

        // actually executes the queries (i.e. the INSERT query)
        $entityManager->flush();

        return new Response('Saved new note with id '.$note->getId());
    }

    public function update(Request $request, NoteRepository $noteRepository): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $id = $_POST["id"];
        $note = $entityManager->getRepository(Note::class)->find($id);

        if (!$note) {
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );
        }

        $note->setTitle($_POST["title"]);
        $note->setText($_POST["text"]);
        $entityManager->flush();

        return new Response('Updated note with id '.$note->getId());
    }

    public function delete(Request $request, NoteRepository $noteRepository): Response
    {
        
        $note = $noteRepository->find($_POST["id"]);
        $id = $_POST["id"];
        $entityManager = $this->getDoctrine()->getManager();

        // tell Doctrine you want to (eventually) save the note (no queries yet)
        $entityManager->remove($note);

        // actually executes the queries (i.e. the INSERT query)
        $entityManager->flush();

        return new Response('Delete new note with id '.$id);
    }

    public function show(Request $request, NoteRepository $noteRepository): JsonResponse
    {
        $note = $noteRepository->find($_POST["id"]);

        if (!$note) {
            throw $this->createNotFoundException(
                'No note found'
            );
        }

        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];

        $serializer = new Serializer($normalizers, $encoders);
                
        $jsonContent = $serializer->serialize($note, 'json');

        return new JsonResponse($jsonContent);
    }

}
