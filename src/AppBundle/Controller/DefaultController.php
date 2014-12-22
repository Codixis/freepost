<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use AppBundle\Entity\Community;

class DefaultController extends Controller
{
    public function homeAction()
    {
        $user = $this->getUser();
        
        if (!is_null($user))
            return $this->redirect($this->generateUrl('freepost_user', array('userName' => $user->getUsername())));
        
        $em = $this->getDoctrine()->getManager();
        
        $communities = $em->getRepository('AppBundle:Community')->findAll();
        
        return $this->render(
            'AppBundle:Default:Home/cards.html.twig',
            array('communities' => $communities)
        );
    }
    
}
