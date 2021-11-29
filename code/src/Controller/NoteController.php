<?php

namespace App\Controller;

use App\Entity\Note;
use App\Form\NoteType;
use App\Repository\NoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\DateTime;

#[Route('/notes')]
class NoteController extends AbstractController
{

    //The index page for the api. Will be used as the "show all" page.

    #[Route('/', name: 'note_index', methods: ['GET'])]
    public function index(Request $request, NoteRepository $noteRepository, EntityManagerInterface $entityManager): Response
    {
        
        // Creating the query commands for the limit of notes shown, and the reversing of the order.
        // Executed when the api gets called with options like /notes/?limit&reversed
        $limit =  $request->query->get('limit');
        $sortingOrder = $request->query->get('reversed');

        //Checking if the limit or reverse have been given as parameter
        if (!$limit){
            $limit = 10;
        }

        if (!$sortingOrder){
            $sortingOrder = 'DESC' ;
        } else { 
            $sortingOrder = 'ASC'; 
        }
        
        // Look in the repository, return the result of $notes as a json object
        $notes = $noteRepository->findBy(
            array(),
            array('created_time' => $sortingOrder),
            $limit,
            0
        );

        return $this->json($notes);

        
    }
    
    //The end point for adding new notes.

    #[Route('/add', name: 'note_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Add a new note with a given title and text. Created-time comes from the object itself being sent.
        $note = new Note();
        $note->setTitle($request->get('title'));
        $note->setText($request->get('text'));
        $note->setCreatedTime(new \DateTime("now"));
        
        // Making the changes in the db.
        $entityManager->persist($note);
        $entityManager->flush();

        return $this->json($note);
    }


    //Looking up specific notes by id. For example ../notes/13

    #[Route('/{id}', name: 'note_show', methods: ['GET'])]
    public function show(Note $note, NoteRepository $noteRepository): Response
    {
        return $this->json($noteRepository->find($note));
    }

    //Editing a note. Takes a title and text and updates the note with the note that has the matching id.

    #[Route('/{id}/edit', name: 'note_edit', methods: ['PUT'])]
    public function edit(Request $request, Note $note, NoteRepository $noteRepository, EntityManagerInterface $entityManager): Response
    {
        $note->setTitle($request->get('title'));
        $note->setText($request->get('text'));

        $entityManager->persist($note);
        $entityManager->flush();

        return $this->json($noteRepository->find($note));
    }

    //Deleting the note that corresponds to the id.

    #[Route('/{id}/delete', name: 'note_delete', methods: ['DELETE'])]
    public function delete(Request $request, Note $note, NoteRepository $noteRepository, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($note);
        $entityManager->flush();
    
        return $this->json($noteRepository->findAll());
    }
}
