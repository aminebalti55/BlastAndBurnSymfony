<?php
namespace App\Controller;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface ;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class accessController extends AbstractController
{
 /**
 * Redirect users after login based on the granted ROLE
 * @Route("/login/redirect", name="_login_redirect")
 */
public function loginRedirectAction(Request $request)
{
    if($this->isGranted('ROLE_ADMIN'))
    {
        return $this->redirectToRoute('admin');
    }
    else if($this->isGranted('ROLE_USER'))
    {
        return $this->redirectToRoute('home');
    }
    else
    {
        return $this->redirectToRoute('home');
    }
}
}