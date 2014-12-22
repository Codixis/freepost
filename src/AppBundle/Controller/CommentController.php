<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use AppBundle\Entity\Community;
use AppBundle\Entity\VoteComment;

class CommentController extends Controller
{
    public function upvoteAction($commentHashId)
    {
        $user = $this->getUser();
        
        if (is_null($user))
            return new JsonResponse(array(
                'done' => FALSE
            ));
        
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();

        // Bad request data...
        if (is_null($commentHashId))
            return new JsonResponse(array(
                'done' => FALSE
            ));
        
        $comment = $em->getRepository('AppBundle:Comment')->findOneByHashId($commentHashId);
        $vote    = $em->getRepository('AppBundle:VoteComment')->findOneBy(array(
            'user'    => $user,
            'comment' => $comment
        ));
        
        // If user hasn't yet voted this comment
        if (is_null($vote))
        {
            $vote = new VoteComment();
            $vote->setUser($user);
            $vote->setComment($comment);
            $vote->setUpvoted();
            
            $comment->upvote();
            
            $em->persist($vote);
        } else
        
        // If user has already voted this comment
        {
            // Already voted earlier. Remove upvote
            if ($vote->upvoted())
            {
                $em->remove($vote);
                $comment->downvote();
            }
            // Switch from downvote to upvote
            else
            {
                $vote->setUpvoted();
                $comment->doubleUpvote();
                
                $em->persist($vote);
            }
        }
        
        $em->persist($comment);
        $em->flush();
        
        return new JsonResponse(array(
            'done' => TRUE
        ));
    }
    
    public function downvoteAction($commentHashId)
    {
        $user = $this->getUser();
        
        if (is_null($user))
            return new JsonResponse(array(
                'done' => FALSE
            ));
        
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();

        // Bad request data...
        if (is_null($commentHashId))
            return new JsonResponse(array(
                'done' => FALSE
            ));
        
        $comment = $em->getRepository('AppBundle:Comment')->findOneByHashId($commentHashId);
        $vote    = $em->getRepository('AppBundle:VoteComment')->findOneBy(array(
            'user'    => $user,
            'comment' => $comment
        ));
        
        // If user hasn't yet voted this comment
        if (is_null($vote))
        {
            $vote = new VoteComment();
            $vote->setUser($user);
            $vote->setComment($comment);
            $vote->setDownvoted();
            
            $comment->downvote();
            
            $em->persist($vote);
        } else
        
        // If user has already voted this comment
        {
            // Already voted earlier. Remove downvote
            if ($vote->downvoted())
            {
                $em->remove($vote);
                $comment->upvote();
            }
            // Switch from upvote to downvote
            else
            {
                $vote->setDownvoted();
                $comment->doubleDownvote();
                
                $em->persist($vote);
            }
        }
        
        $em->persist($comment);
        $em->flush();
        
        return new JsonResponse(array(
            'done' => TRUE
        ));
    }
    
    /* Submit a new comment.
     * This is POST called when the form from submitAction() is sent.
     */
    public function submitNewAction($postHashId)
    {
        $em             = $em = $this->getDoctrine()->getManager();
        $request        = $this->getRequest();
        $commentRepo    = $em->getRepository('AppBundle:Comment');

        $user           = $this->getUser();

        // Data for the new comment
        $comment = (object) array(
            'post'          => $em->getRepository('AppBundle:Post')->findOneByHashId($postHashId),
            'parentComment' => $commentRepo->findOneByHashId($request->request->get('parentHashId')),
            'text'          => $request->request->get('text')
        );

        // If user is not signed in, or POST data is not valid
        if (is_null($user) || is_null($comment->post) || strlen($comment->text) < 1)
            return new JsonResponse(array(
                'done' => FALSE
            ));
        
        // Clear user input
        $comment->text = $this->get('exercise_html_purifier.default')->purify($comment->text);

        // Create the new comment
        $newComment = $commentRepo->submitNew(
            $comment->post,
            $comment->parentComment,
            $user,
            $comment->text
        );

        // Send back the formatted code for the new comment to display
        if (is_null($comment->parentComment))
            $templateData = array(
                'comments'  => array('root' => array($newComment)),
                'depth'     => 0,
                'op'        => $comment->post->getUser(),
                'parentId'  => 'root'
            );
        else
            $templateData = array(
                'comments'  => array($comment->parentComment->getId() => array($newComment)),
                'depth'     => 0,
                'op'        => $comment->post->getUser(),
                'parentId'  => $comment->parentComment->getId(),
            );
        
        $html = $this->renderView('AppBundle:Default:Post/comment.html.twig', $templateData);
        
        return new JsonResponse(array(
            'done' => TRUE,
            'html' => $html
        ));
    }
    
    // Edit a user comment
    public function editAction($commentHashId)
    {
        $em             = $em = $this->getDoctrine()->getManager();
        $request        = $this->getRequest();

        $user           = $this->getUser();
        $comment        = $em->getRepository('AppBundle:Comment')->findOneByHashId($commentHashId);
        $commentText    = $request->request->get('text');
        
        // If user is not signed in, or POST data is not valid
        if (is_null($user) || is_null($comment) || is_null($commentText) || strlen($commentText) < 1)
            return new JsonResponse(array(
                'done' => FALSE
            ));
        
        // Clear user input
        $commentText = $this->get('exercise_html_purifier.default')->purify($commentText);
        
        $comment->setText($commentText);
        
        $em->persist($comment);
        $em->flush();
        
        return new JsonResponse(array(
            'done' => TRUE
        ));
    }
}


