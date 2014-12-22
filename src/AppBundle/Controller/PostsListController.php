<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use AppBundle\Entity\Community;

class PostsListController extends Controller
{
    // Retrieve posts
    public function postsAction($communityHashId, $sort = '')
    {
        $em = $this->getDoctrine()->getManager();
        $communityRepo = $em->getRepository('AppBundle:Community');
        $postRepo = $em->getRepository('AppBundle:Post');
        
        $user = $this->getUser();
        $community = $communityRepo->findOneByHashId($communityHashId);
        
        $sort = strtoupper($sort);
        
        switch ($sort)
        {
            case 'NEW':
                $posts = $postRepo->findNew($community, $user);
                break;
            default:
                $sort = 'HOT';
                $posts = $postRepo->findHot($community, $user);
                break;
        }
        
        return new JsonResponse(array(
            'html' =>   $this->renderView(
                            'AppBundle:Default:Etc/PostsList/list.html.twig',
                            array(
                                'community'     => $community,
                                'posts'         => $posts,
                                'postsSorting'  => $sort
                            )
                        )
        ));
    }
    
    // Retrieve HOT posts
    public function hotAction($communityHashId)
    {
        return $this->forward('AppBundle:PostsList:posts', array(
            'communityHashId' => $communityHashId,
            'sort'            => 'HOT',
        ));
    }
    
    // Retrieve NEW posts
    public function newAction($communityHashId)
    {
        return $this->forward('AppBundle:PostsList:posts', array(
            'communityHashId' => $communityHashId,
            'sort'            => 'NEW',
        ));
    }
    
}


