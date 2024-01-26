<?php

namespace App\Controller;


use App\Entity\Event;
use App\Entity\EventRate;
use App\Entity\Rate;
use App\Entity\Recipe;
use App\Entity\RecipeRate;
use App\Entity\session;
use App\Entity\sessionRate;
use App\Form\RateType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RateController extends AbstractController
{

    /**
     * @Route("/rate/{ratedId}", name="rate", methods={"GET","POST"})
     */
    public function new(Request $request, $ratedId): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();
        $type=$request->query->get('type');
        $rate=new Rate();
        $rate->setType($type);
        $rate->setCreatedAt(new \DateTime());
        $rate->setUser($user);

        switch ($type){
           
            case 'event':$rated = $this->getDoctrine()->getRepository(Event::class)->find($ratedId);
                $itemRate=new EventRate();
                $itemRate->setEvent($rated);
                break;
            case 'session':$rated = $this->getDoctrine()->getRepository(session::class)->find($ratedId);
                $itemRate=new sessionRate();
                $itemRate->setsession($rated);
                break;
         
            case 'recipe':$rated = $this->getDoctrine()->getRepository(Recipe::class)->find($ratedId);
                $itemRate=new RecipeRate();
                $itemRate->setRecipe($rated);
                break;
        }

        $form = $this->createForm(RateType::class, $rate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($rate);
            $entityManager->flush();
            $itemRate->setRate($rate);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($itemRate);
            $entityManager->flush();

            return $this->redirectToRoute('all');
        }

        return $this->render('FrontOffice/rate.html.twig', [
            'rate' => $rate,
            'form' => $form->createView(),
        ]);
    }
}
