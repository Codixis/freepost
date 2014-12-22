<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use AppBundle\Entity\Community;
use AppBundle\Entity\VotePost;

class PostController extends Controller
{
    // Display a community post
    public function postAction($communityName, $postHashId, $postTitle = '', $commentHashId = NULL)
    {
        $em          = $this->getDoctrine()->getManager();
        $commentRepo = $em->getRepository('AppBundle:Comment');
        
        $post        = $em->getRepository('AppBundle:Post')->findOneByHashId($postHashId);
        $commentsSet = $commentRepo->findHot($post);
        $rootComment = NULL;
        
        // Build a reference parent->children
        $comments = array(
            'root'        => array()       // Comments with no parent go here
            // [parentId] => array(...)    // Comments with parent go here
        );
        
        foreach ($commentsSet as &$aComment)
        {
            if ($aComment->getHashId() === $commentHashId)
                $rootComment = $aComment;
            
            // Comment with no parent (i.e. this is not a comment reply)
            if (!$aComment->hasParent())
            {
                $comments['root'][] = $aComment;
                continue;
            }
            
            // If this comment has a parent, i.e. this is a comment reply
            $parentId = $aComment->getParent()->getId();
            array_key_exists($parentId, $comments) || $comments[$parentId] = array();
            
            $comments[$parentId][] = $aComment;
        }
        
        // Don't show all comments, but use $rootComment as root
        if (!is_null($rootComment))
            $comments['root'] = array($rootComment);
        
        return $this->render(
            'AppBundle:Default:Post/page.html.twig',
            array(
                'post'      => $post,
                'comments'  => $comments
            )
        );
    }
    
    public function upvoteAction($postHashId)
    {
        $user = $this->getUser();
        
        if (is_null($user))
            return new JsonResponse(array(
                'done' => FALSE
            ));
        
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();

        // Bad request data...
        if (is_null($postHashId))
            return new JsonResponse(array(
                'done' => FALSE
            ));
        
        $post = $em->getRepository('AppBundle:Post')->findOneByHashId($postHashId);
        $vote = $em->getRepository('AppBundle:VotePost')->findOneBy(array(
            'user' => $user,
            'post' => $post
        ));
        
        // If user hasn't yet voted this post
        if (is_null($vote))
        {
            $vote = new VotePost();
            $vote->setUser($user);
            $vote->setPost($post);
            $vote->setUpvoted();
            
            $post->upvote();
            
            $em->persist($vote);
        } else
        
        // If user has already voted this post
        {
            // Already voted earlier. Remove upvote
            if ($vote->upvoted())
            {
                $em->remove($vote);
                $post->downvote();
            }
            // Switch from downvote to upvote
            else
            {
                $vote->setUpvoted();
                $post->doubleUpvote();
                
                $em->persist($vote);
            }
        }
        
        $em->persist($post);
        $em->flush();
        
        return new JsonResponse(array(
            'done' => TRUE
        ));
    }
    
    public function downvoteAction($postHashId)
    {
        $user = $this->getUser();
        
        if (is_null($user))
            return new JsonResponse(array(
                'done' => FALSE
            ));
        
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();

        // Bad request data...
        if (is_null($postHashId))
            return new JsonResponse(array(
                'done' => FALSE
            ));
        
        $post = $em->getRepository('AppBundle:Post')->findOneByHashId($postHashId);
        $vote = $em->getRepository('AppBundle:VotePost')->findOneBy(array(
            'user' => $user,
            'post' => $post
        ));
        
        // If user hasn't yet voted this post
        if (is_null($vote))
        {
            $vote = new VotePost();
            $vote->setUser($user);
            $vote->setPost($post);
            $vote->setDownvoted();
            
            $post->downvote();
            
            $em->persist($vote);
        } else
        
        // If user has already voted this post
        {
            // Already voted earlier. Remove downvote
            if ($vote->downvoted())
            {
                $em->remove($vote);
                $post->upvote();
            }
            // Switch from upvote to downvote
            else
            {
                $vote->setDownvoted();
                $post->doubleDownvote();
                
                $em->persist($vote);
            }
        }
        
        $em->persist($post);
        $em->flush();
        
        return new JsonResponse(array(
            'done' => TRUE
        ));
    }
    
    
}


