<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\EventCategory;
use App\Entity\Report;
use App\Entity\ReportNotification;
use App\Entity\session;
use App\Entity\Recipe;
use App\Entity\RecipeCategory;
use App\Entity\sessionCategory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface ;
use Symfony\Component\HttpFoundation\RedirectResponse;
class AdminPageController extends AbstractController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function index(): Response
    {
        return $this->render('BackOffice/dashboard.html.twig',[
            'controller_name' => 'AdminPageController',
        ]);
   
    }

    
    /**
     * @Route("/dashboard", name="dashboard")
     */
    public function dashboard(){

        $categories = $this->getDoctrine()->getRepository(sessionCategory::class)->createQueryBuilder(' tc')
        ->addSelect('tc.catId, tc.name, tc.imgUrl,count(tc) as totalsessions')
        ->andWhere('tc.isDeleted=0')
        ->getQuery()->getResult();

        $categoriesrecipe = $this->getDoctrine()->getRepository(RecipeCategory::class)->createQueryBuilder(' tc')
        ->addSelect('tc.catId, tc.name, tc.imgUrl,count(tc) as TotalRecipes')
        ->andWhere('tc.isDeleted=0')
        ->getQuery()->getResult();

        $categoriesevent = $this->getDoctrine()->getRepository(EventCategory::class)->createQueryBuilder(' tc')
        ->addSelect('tc.catId, tc.name, tc.imgUrl,count(tc) as TotalEvents')
        ->andWhere('tc.isDeleted=0')
        ->getQuery()->getResult();
        return $this->render('BackOffice/dashboard.html.twig', ['categoriesrecipe' => $categoriesrecipe ,'categories' =>$categories,  'categoriesevent'=>$categoriesevent]);
    }

    /**
     * @Route("/tables", name="tables")
     */
    public function tables(){
        return $this->render('BackOffice/tables.html.twig');
    }

    /**
     * @Route("/moderation", name="moderation")
     */
    public function moderation(){
        $reports=$this->getDoctrine()->getRepository(Report::class)->findBy([],['createdAt'=>'DESC']);
        $unreadNotification=$this->getDoctrine()->getRepository(ReportNotification::class)->findBy(['seenByAdmin'=>'0','closed'=>'0'], ['createdAt'=>'desc']);
        $count=count($unreadNotification);
        return $this->render('BackOffice/moderation.html.twig', ['allReports'=>$reports,'count'=>$count]);
    }

    /**
     * @Route("/moderation/closereport/{reportId}", name="close_report")
     */
    public function closeReport($reportId){
        $report=$this->getDoctrine()->getRepository(Report::class)->find($reportId);
        $report->setIsClosed(1);
        $report->setClosedAt(new \DateTime());
        $em=$this->getDoctrine()->getManager();
        $em->persist($report);
        $em->flush();
        $notif=$this->getDoctrine()->getRepository(ReportNotification::class)->findOneBy(['report'=>$report]);
        $notif->setClosed(true);
        $em=$this->getDoctrine()->getManager();
        $em->persist($notif);
        $em->flush();


        return $this->redirectToRoute("moderation");
    }

    /**
     * @Route("/billing", name="billing")
     */
    public function billing(){
        return $this->render('BackOffice/billing.html.twig');
    }

    /**
     * @Route("/profile", name="profile")
     */
    public function profile(){
        return $this->render('BackOffice/profile.html.twig');
    }

    /**
     * @Route("/signin", name="sign_in")
     */
    public function signIn(){
        return $this->render('BackOffice/sign_in.html.twig');
    }

    /**
     * @Route("/signup", name="sign_up")
     */
    public function signUp(){
        return $this->render('BackOffice/sign_up.html.twig');
    }

    /**
     * @Route("/sessions", name="sessions")
     */
    public function sessions(){
        //$sessions=$this->getDoctrine()->getRepository(session::class)->createQueryBuilder('t')->where('t.isDeleted=0')->orderBy('t.createdAt','desc')->getQuery()->getResult();
        //$categories=$this->getDoctrine()->getRepository(sessionCategory::class)->createQueryBuilder('tc')->where('tc.isDeleted=0')->orderBy('tc.createdAt','desc')->getQuery()->getResult();
        $categories = $this->getDoctrine()->getRepository(session::class)->createQueryBuilder(' t ')
            ->addSelect('tc.catId, tc.name, tc.imgUrl,count(t) as totalsessions')->join('t.cat','tc')
            ->where('t.cat=tc.catId')
            ->andWhere('tc.isDeleted=0')->andWhere('t.isDeleted=0')->groupBy('tc.catId ')->orderBy('tc.createdAt', 'desc')
            ->getQuery()->getResult();

        $sessions = $this->getDoctrine()->getRepository(session::class)->createQueryBuilder(' t ')
            ->join('t.cat','tc')->where('t.cat=tc.catId')
            ->andWhere('tc.isDeleted=0')->andWhere('t.isDeleted=0')->orderBy('t.createdAt', 'desc')
            ->getQuery()->getResult();

        return $this->render('BackOffice/sessions.html.twig', ['sessions'=>$sessions,'categories'=>$categories]);
        // return $this->render('admin_page/sessions.html.twig');
    }
   

    /**
     * @Route("/Events", name="events")
     */
    public function events(){
        $events=$this->getDoctrine()->getRepository(Event::class)->createQueryBuilder('e')->where('e.isDeleted=0')->orderBy('e.createdAt','desc')->getQuery()->getResult();
        $categories=$this->getDoctrine()->getRepository(EventCategory::class)->createQueryBuilder('ec')->where('ec.isDeleted=0')->orderBy('ec.createdAt','desc')->getQuery()->getResult();

        return $this->render('BackOffice/events.html.twig', ['events'=>$events,'categories'=>$categories]);
        // return $this->render('BackOffice/events.html.twig');
    }

    /**
     * @Route("/recipesPage", name="recipesPage")
     */
    public function recipes(){
        $recipes=$this->getDoctrine()->getRepository(Recipe::class)->createQueryBuilder('r')->where('r.isDeleted=0')->orderBy('r.createdAt','desc')->getQuery()->getResult();
        $categories=$this->getDoctrine()->getRepository(RecipeCategory::class)->createQueryBuilder('rc')->where('rc.isDeleted=0')->orderBy('rc.createdAt','desc')->getQuery()->getResult();
        return $this->render('BackOffice/recipesPage.html.twig', ['recipes'=>$recipes,'categories'=>$categories]);
    }

    /**
     * @Route("/deletesesssionadmin/{id}", name="delete_Sessionadmin")
     */
    public function deleteadmin(int $id)
    {
        $Session = $this->getDoctrine()->getRepository(session::class)->find($id);
        $Session->setIsDeleted(1);
        $em = $this->getDoctrine()->getManager();
        $em->persist($Session);
        $em->flush();
        return $this->redirectToRoute("sessions");
    }
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        $user = $token->getUser();
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return new RedirectResponse($this->urlGenerator->generate('admin'));
        }
    
        return new RedirectResponse($this->urlGenerator->generate('home'));
    }
}
