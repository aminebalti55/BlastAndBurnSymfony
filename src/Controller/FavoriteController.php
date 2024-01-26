<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Favorite;
use App\Entity\Recipe;
use App\Entity\session;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class FavoriteController extends AbstractController
{

    /**
     * @Route("/favorite/{type}/{itemId}", name="favorite")
     */
    public function favorite($type, $itemId)
    {
        $favorite=new Favorite();

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();
        $favorite->setUser($user);
        switch ($type){
        
            case 'event':$item=$this->getDoctrine()->getRepository(Event::class)->find($itemId);
                $favorite->setEvent($item);
                break;
            case 'session':$item=$this->getDoctrine()->getRepository(session::class)->find($itemId);
                $favorite->setsession($item);
                break;
          
            case 'recipe':$item=$this->getDoctrine()->getRepository(Recipe::class)->find($itemId);
                $favorite->setRecipe($item);
                break;
        }

        $favorite->setType($type);
        $favorite->setCreatedAt(new \DateTime());
        $em=$this->getDoctrine()->getManager();
        $em->persist($favorite);
        $em->flush();

        $this->addFlash("success", "Added to favorites");

        return new JsonResponse([
            'fav'=>$favorite
        ]);
    }

    /**
     * @Route("/unfavorite/{type}/{itemId}", name="unfavorite")
     */
    public function unfavorite($type, $itemId)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();
        $favorite=$this->getDoctrine()->getRepository(Favorite::class)->findOneBy([
            'user' => $user,
            $type => $itemId,
        ]);
        $em=$this->getDoctrine()->getManager();
        $em->remove($favorite);
        $em->flush();

        $this->addFlash("success", "Removed from favorites");

        return new JsonResponse([
            'fav'=>'done'
        ]);
    }

    /**
     * @Route("/myfavorites", name="myfavorites")
     */
    public function myFavorites()
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();

        $myFavorite=$this->getDoctrine()->getRepository(Favorite::class)->findBy([
            'user' => $user,
        ]);

        return $this->render('FrontOffice/my_favorites.html.twig',['myfav'=>$myFavorite]);
    }
}
