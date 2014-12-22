<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\SecurityContextInterface;

use AppBundle\Entity\Community;

class UserController extends Controller
{
    public function signinAction(Request $request)
    {
        $user = $this->getUser();
        
        if (!is_null($user))
            return $this->redirect($this->generateUrl('freepost_user', array('userName' => $user->getUsername())));
        
        $session = $request->getSession();

        // get the login error if there is one
        if ($request->attributes->has(SecurityContextInterface::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(
                SecurityContextInterface::AUTHENTICATION_ERROR
            );
        } elseif (null !== $session && $session->has(SecurityContextInterface::AUTHENTICATION_ERROR)) {
            $error = $session->get(SecurityContextInterface::AUTHENTICATION_ERROR);
            $session->remove(SecurityContextInterface::AUTHENTICATION_ERROR);
        } else {
            $error = '';
        }

        // last username entered by the user
        $lastUsername = (null === $session) ? '' : $session->get(SecurityContextInterface::LAST_USERNAME);

        return $this->render(
            'AppBundle:Default:Home/signin.html.twig',
            array(
                // last username entered by the user
                'last_username' => $lastUsername,
                'error'         => $error,
            )
        );
    }
    
    public function signupAction()
    {
        $user = $this->getUser();
        
        if (!is_null($user))
            return $this->redirect($this->generateUrl('freepost_user', array('userName' => $user->getUsername())));
        
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();
        $userRepo = $em->getRepository('AppBundle:User');
        
        $username = $request->request->get('username');
        $password = $request->request->get('password');
        
        $usernameExists = $userRepo->usernameExists($username);
        
        // Check input data
        if (strlen($username) < 5 || strlen($password) < 5 || $usernameExists)
            return $this->redirect($this->generateUrl('freepost_user_signin'));
        
        // Create the new user
        $newUser = $userRepo->createNew($username, $password);
        
        // Automatically signin the new user
        $token = new UsernamePasswordToken($newUser, null, 'secured_area', $newUser->getRoles());
        $this->get('security.token_storage')->setToken($token);
        
        return $this->redirect($this->generateUrl('freepost_user', array('userName' => $newUser->getUsername())));
    }
    
    // Load default community page
    public function communitiesAction($userName)
    {
        $user = $this->getUser();
        
        // Only allow to see my homepage (not that of other users)
        if (is_null($user) || strtolower($user->getUsername()) != strtolower($userName))
            return $this->redirect($this->generateUrl('freepost_homepage'));
        
        $em = $this->getDoctrine()->getManager();
        
        return $this->render(
            'AppBundle:Default:User/Page/communities.html.twig',
            array(
                'user'  => $user
            )
        );
    }
    
    // "My posts" page
    public function myPostsAction($userName)
    {
        $user = $this->getUser();
        
        // Only allow to see my homepage (not that of other users)
        if (is_null($user) || strtolower($user->getUsername()) != strtolower($userName))
            return $this->redirect($this->generateUrl('freepost_homepage'));
        
        $em = $this->getDoctrine()->getManager();
        
        $myPosts = $em->getRepository('AppBundle:Post')->findMyPosts($user);
        
        return $this->render(
            'AppBundle:Default:User/Page/myposts.html.twig',
            array(
                'posts' => $myPosts,
                'user'  => $user
            )
        );
    }
    
    // "My comments" page
    public function commentsAction($userName)
    {
        $user = $this->getUser();
        
        // Only allow to see my homepage (not that of other users)
        if (is_null($user) || strtolower($user->getUsername()) != strtolower($userName))
            return $this->redirect($this->generateUrl('freepost_homepage'));
        
        $em = $this->getDoctrine()->getManager();
        
        $comments = $em->getRepository('AppBundle:Comment')->findComments($user);
        
        return $this->render(
            'AppBundle:Default:User/Page/mycomments.html.twig',
            array(
                'comments'  => $comments,
                'page'      => 'MYCOMMENTS',
                'user'      => $user
            )
        );
    }
    
    // "My comments replies" page
    public function repliesAction($userName)
    {
        $user = $this->getUser();
        
        // Only allow to see my homepage (not that of other users)
        if (is_null($user) || strtolower($user->getUsername()) != strtolower($userName))
            return $this->redirect($this->generateUrl('freepost_homepage'));
        
        $em          = $this->getDoctrine()->getManager();
        $commentRepo = $em->getRepository('AppBundle:Comment');
        
        // Load user replies
        $comments = $commentRepo->findReplies($user);
        
        // Set replies as "read"
        $commentRepo->setRepliesAsRead($user);
        
        return $this->render(
            'AppBundle:Default:User/Page/replies.html.twig',
            array(
                'comments'  => $comments,
                'page'      => 'REPLIES',
                'user'      => $user
            )
        );
    }
    
    // Return number of user unread replies
    public function unreadRepliesAction()
    {
        $user = $this->getUser();
        
        // Only allow to see my homepage (not that of other users)
        if (is_null($user))
            return $this->redirect($this->generateUrl('freepost_homepage'));
        
        $em = $this->getDoctrine()->getManager();
        
        $unreadReplies = $em->getRepository('AppBundle:Comment')->findNumberOfUnreadReplies($user);
        
        return new JsonResponse(array(
            'count' => $unreadReplies
        ));
    }
    
    // Load user preferences page
    public function preferencesAction($userName)
    {
        $user = $this->getUser();
        
        if (is_null($user) || strtolower($user->getUsername()) != strtolower($userName))
            return $this->redirect($this->generateUrl('freepost_homepage'));
        
        $em = $this->getDoctrine()->getManager();
        
        return $this->render(
            'AppBundle:Default:User/Page/preferences.html.twig',
            array('user' => $user)
        );
    }
    
    // Check if username exists
    public function checkUsernameAction($userName)
    {
        $em = $this->getDoctrine()->getManager();
        
        $exists = $em->getRepository('AppBundle:User')->usernameExists($userName);
        
        return new JsonResponse(array(
            'exists' => $exists
        ));
    }
    
    // Ajax-load a community posts to show in user homepage
    public function readCommunityPostsAction($communityHashId)
    {
        $user = $this->getUser();
        
        // Only allow to see my homepage (not that of other users)
        if (is_null($user))
            return $this->redirect($this->generateUrl('freepost_homepage'));
        
        $em = $this->getDoctrine()->getManager();
        $communityRepo = $em->getRepository('AppBundle:Community');
        $postRepo = $em->getRepository('AppBundle:Post');
        
        $community = $communityRepo->findOneByHashId($communityHashId);
        $posts = $postRepo->findHot($community, $user);
        
        return $this->render(
            'AppBundle:Default:User/communityPosts.html.twig',
            array(
                'community'     => $community,
                'posts'         => $posts,
                'postsSorting'  => 'HOT'
            )
        );
    }
    
    // Follow this community
    public function followAction($communityName)
    {
        $user = $this->getUser();
        
        if (is_null($user))
            return new JsonResponse(array(
                'done' => FALSE
            ));
        
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();
        $userRepo = $em->getRepository('AppBundle:User');
        
        // The community
        $community = $em->getRepository('AppBundle:Community')->findOneByName($communityName);

        // Bad request data...
        if (is_null($community))
            return new JsonResponse(array(
                'done' => FALSE
            ));

        // Already following this community
        if ($userRepo->isFollowingCommunity($user, $community))
            return new JsonResponse(array(
                'done' => FALSE
            ));
        
        $user->addCommunity($community);

        $em->persist($user);
        $em->persist($community);
        $em->flush();
        
        return new JsonResponse(array(
            'done' => TRUE
        ));
    }
    
    // Check if $user is following $communityName
    public function followsCommunityAction($communityName)
    {
        $user = $this->getUser();
        
        if (is_null($user))
            return new JsonResponse(array(
                'follows' => FALSE
            ));
        
        $em = $this->getDoctrine()->getManager();
        $userRepo = $em->getRepository('AppBundle:User');
        $communityRepo = $em->getRepository('AppBundle:Community');
        
        $community = $communityRepo->findOneByName($communityName);
        
        $follows = $userRepo->isFollowingCommunity($user, $community);
        
        return new JsonResponse(array(
            'follows' => $follows
        ));
    }
    
    /* Update a user name. A user CAN NOT CHANGE his username, but he
     * is allowed to change the name CaMeLcAsE.
     */
    public function updateDisplayNameAction()
    {
        $user = $this->getUser();
        
        if (is_null($user))
            return new JsonResponse(array(
                'done' => FALSE
            ));
        
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();

        // Retrieve POST data
        $displayName = $request->request->get('displayName');

        // Bad request data...
        if (is_null($displayName) || strtolower($user->getUsername()) != strtolower($displayName))
            return new JsonResponse(array(
                'done' => FALSE
            ));
        
        // Update username
        $user->setUsername($displayName);

        $em->persist($user);
        $em->flush();

        return new JsonResponse(array(
            'done' => TRUE
        ));
    }
    
    public function updatePictureAction()
    {
        $user = $this->getUser();
        
        if (is_null($user))
            return $this->render(
                'AppBundle:Default:Etc/postMessage.html.twig',
                array('message' => json_encode(array(
                    'action'    => 'updateUserPicture',
                    'status'    => 'error'
                )))
            );
        
        $request = $this->getRequest();
        $asset = $this->get('freepost.asset');
        $em = $this->getDoctrine()->getManager();

        // Retrieve POST data
        $userPicture = $request->files->get('pictureFile');

        // Save the new picture
        if (!is_null($userPicture))
            $asset->updateUserPicture($user, $userPicture);

        return $this->render(
            'AppBundle:Default:Etc/postMessage.html.twig',
            array('message' => json_encode(array(
                'action'    => 'updateUserPicture',
                'status'    => 'done'
            )))
        );
    }
}


