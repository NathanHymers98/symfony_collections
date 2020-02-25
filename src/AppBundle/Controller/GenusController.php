<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Genus;
use AppBundle\Entity\GenusNote;
use AppBundle\Entity\GenusScientist;
use AppBundle\Service\MarkdownTransformer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class GenusController extends Controller
{
    /**
     * @Route("/genus/new")
     */
    public function newAction()
    {
        $em = $this->getDoctrine()->getManager();

        $subFamily = $em->getRepository('AppBundle:SubFamily')
            ->findAny();

        $genus = new Genus(); // Creating a genus. (There are more genuses in the database thanks to the fixtures.yml file)
        $genus->setName('Octopus'.rand(1, 10000));
        $genus->setSubFamily($subFamily);
        $genus->setSpeciesCount(rand(100, 99999));
        $genus->setFirstDiscoveredAt(new \DateTime('50 years'));

        $genusNote = new GenusNote();
        $genusNote->setUsername('AquaWeaver');
        $genusNote->setUserAvatarFilename('ryan.jpeg');
        $genusNote->setNote('I counted 8 legs... as they wrapped around me');
        $genusNote->setCreatedAt(new \DateTime('-1 month'));
        $genusNote->setGenus($genus);

        $user = $em->getRepository('AppBundle:User') // Finding one user in the database
            ->findOneBy(['email' => 'aquanaut1@example.org']);

        // In order to create a new GenusScientist object, which is the relation between a Genus object and a User object put together, we need to pass both objects.
        $genusScientist = new GenusScientist(); // Creating a new GenusScientist object
        $genusScientist->setGenus($genus); // Setting the genus property to the one created
        $genusScientist->setUser($user); // Setting the user property to the one found
        $genusScientist->setYearsStudied(10); // Setting the years studied property
        $em->persist($genusScientist); // Saving the genusScientist object to the database

        $em->persist($genus);
        $em->persist($genusNote);
        $em->flush();

        return new Response(sprintf(
            '<html><body>Genus created! <a href="%s">%s</a></body></html>',
            $this->generateUrl('genus_show', ['slug' => $genus->getSlug()]),
            $genus->getName()
        ));
    }

    /**
     * @Route("/genus")
     */
    public function listAction() // Lists all the genus objects
    {
        $em = $this->getDoctrine()->getManager();

        $genuses = $em->getRepository('AppBundle:Genus')
            ->findAllPublishedOrderedByRecentlyActive(); // Uses this query to find all the published genuses and order them by their notes

        return $this->render('genus/list.html.twig', [ // Returns the rendered twig view, and passing it the data that the query found so that it can be used in the twig file
            'genuses' => $genuses
        ]);
    }

    // Since the name field has a slug, we can change the URL to use the slug instead so that the URL for each entry is unique and lowercase
    /**
     * @Route("/genus/{slug}", name="genus_show")
     */
    public function showAction(Genus $genus) // Since we are passing a slug as the URL and since slug is a property of the Genus class, we no longer need to pass the genusName as the arguemnt directly, instead we can just pass the class
    {
        $em = $this->getDoctrine()->getManager();

        $markdownTransformer = $this->get('app.markdown_transformer');
        $funFact = $markdownTransformer->parse($genus->getFunFact());

        $this->get('logger')
            ->info('Showing genus: '.$genus->getName());

        $recentNotes = $em->getRepository('AppBundle:GenusNote')
            ->findAllRecentNotesForGenus($genus);

        return $this->render('genus/show.html.twig', array(
            'genus' => $genus,
            'funFact' => $funFact,
            'recentNoteCount' => count($recentNotes)
        ));
    }

    /**
     * @Route("/genus/{slug}/notes", name="genus_show_notes")
     * @Method("GET")
     */
    public function getNotesAction(Genus $genus)
    {
        $notes = [];

        foreach ($genus->getNotes() as $note) {
            $notes[] = [
                'id' => $note->getId(),
                'username' => $note->getUsername(),
                'avatarUri' => '/images/'.$note->getUserAvatarFilename(),
                'note' => $note->getNote(),
                'date' => $note->getCreatedAt()->format('M d, Y')
            ];
        }

        $data = [
            'notes' => $notes
        ];

        return new JsonResponse($data);
    }

    /**
     * @Route("/genus/{genusId}/scientists/{userId}", name="genus_scientists_remove")
     * @Method("DELETE")
     */
    public function removeGenusScientistAction($genusId, $userId) // Passing the genusid and userid because they are both required to remove a scientist. This is an end point for removing a genus scientist
    {
        $em = $this->getDoctrine()->getManager(); // Getting the entity manager so that we get fetch both objects

        $genusScientist = $em->getRepository('AppBundle:GenusScientist') // Finds the genusScientist object using the findOneBy() method with the passed userid and genusid as 'user' and 'genus' so that they can be called in twig
            ->findOneBy([
                'user' => $userId,
                'genus' => $genusId
            ]);

        $em->remove($genusScientist); // Removes the genusScientist object which matches the userid and genusid from the database
        $em->flush();

        return new Response(null, 204); // Gives a 204 content not found page
    }
}
