<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use AppBundle\Entity\Community;

class CommunityController extends Controller
{
    // Load default community page
    public function postsAction($communityName, $sort = '')
    {
        $em = $this->getDoctrine()->getManager();
        $communityRepo = $em->getRepository('AppBundle:Community');
        $postRepo = $em->getRepository('AppBundle:Post');
        
        $user = $this->getUser();
        $community = $communityRepo->findOneByName($communityName);
        
        // This community doesn't exist
        if (is_null($community))
            // If user is not logged in, redirect to home page
            if (is_null($user))
                return $this->redirect($this->generateUrl('freepost_homepage'));
            // Otherwhise create the new community
            else
                $community = $communityRepo->create($communityName, $user);
        
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
        
        return $this->render(
            'AppBundle:Default:Community/Page/posts.html.twig',
            array(
                'community'     => $community,
                'posts'         => $posts,
                'postsSorting'  => $sort
            )
        );
    }
    
    // Load community page, sort by HOT
    public function hotPostsAction($communityName)
    {
        return $this->forward('AppBundle:Community:posts', array(
            'communityName' => $communityName,
            'sort'          => 'HOT',
        ));
    }
    
    // Load community page, sort by NEW
    public function newPostsAction($communityName)
    {
        return $this->forward('AppBundle:Community:posts', array(
            'communityName' => $communityName,
            'sort'          => 'NEW',
        ));
    }
    
    // Load community about page
    public function aboutAction($communityName)
    {
        $em = $this->getDoctrine()->getManager();
        
        $community = $em->getRepository('AppBundle:Community')->findOneByName($communityName);
        
        return $this->render(
            'AppBundle:Default:Community/Page/about.html.twig',
            array('community' => $community)
        );
    }
    
    // Load community preferences page
    public function preferencesAction($communityName)
    {
        $em = $this->getDoctrine()->getManager();
        
        $community = $em->getRepository('AppBundle:Community')->findOneByName($communityName);
        
        return $this->render(
            'AppBundle:Default:Community/Page/preferences.html.twig',
            array('community' => $community)
        );
    }
    
    /* Update a community name. A user CAN NOT CHANGE the community name, but he
     * is allowed to change the name CaMeLcAsE.
     */
    public function updateDisplayNameAction($communityHashId)
    {
        $user = $this->getUser();
        
        if (is_null($user))
            return new JsonResponse(array(
                'done' => FALSE
            ));
        
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();

        // Retrieve POST data
        $communityName = $request->request->get('displayName');

        // The community
        $community = $em->getRepository('AppBundle:Community')->findOneByHashId($communityHashId);

        // Bad request data...
        if (is_null($communityName) || is_null($community) || strtolower($community->getName()) != strtolower($communityName))
            return new JsonResponse(array(
                'done' => FALSE
            ));

        // Update community name
        $community->setName($communityName);

        $em->persist($community);
        $em->flush();

        return new JsonResponse(array(
            'done' => TRUE
        ));
    }
    
    // Update a community description. This is basically the "About" page
    public function updateDescriptionAction($communityHashId)
    {
        $user = $this->getUser();
        
        if (is_null($user))
            return new JsonResponse(array(
                'done' => FALSE
            ));
        
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();

        // Retrieve POST data
        $communityDescription = $request->request->get('description');

        // The community
        $community = $em->getRepository('AppBundle:Community')->findOneByHashId($communityHashId);

        // Bad request data...
        if (is_null($communityDescription) || is_null($community))
            return new JsonResponse(array(
                'done' => FALSE
            ));

        // Update community description
        $community->setDescription($communityDescription);

        $em->persist($community);
        $em->flush();

        return new JsonResponse(array(
            'done' => TRUE
        ));
    }
    
    public function updatePictureAction($communityHashId)
    {
        $user = $this->getUser();
        
        if (is_null($user))
            return $this->render(
                'AppBundle:Default:Etc/postMessage.html.twig',
                array('message' => json_encode(array(
                    'action'    => 'updateCommunityPicture',
                    'status'    => 'error'
                )))
            );
        
        $request = $this->getRequest();
        $asset = $this->get('freepost.asset');
        $em = $this->getDoctrine()->getManager();

        // Retrieve POST data
        $communityPicture = $request->files->get('pictureFile');

        // The community
        $community = $em->getRepository('AppBundle:Community')->findOneByHashId($communityHashId);

        // Bad request data...
        if (is_null($community))
            return $this->render(
                'AppBundle:Default:Etc/postMessage.html.twig',
                array('message' => json_encode(array(
                    'action'    => 'updateCommunityPicture',
                    'status'    => 'error'
                )))
            );

        // Save the new picture
        if (!is_null($communityPicture))
            $asset->updateCommunityPicture($community, $communityPicture);

        return $this->render(
            'AppBundle:Default:Etc/postMessage.html.twig',
            array('message' => json_encode(array(
                'action'    => 'updateCommunityPicture',
                'status'    => 'done'
            )))
        );
    }
    
    /* Submit a new post.
     * This is POST called when the form from submitAction() is sent.
     */
    public function submitNewPostAction($communityHashId)
    {
        $em = $this->getDoctrine();
        $request = $this->getRequest();

        $user = $this->getUser();

        // Data for the new post
        $post = (object) array(
            'community' => $em->getRepository('AppBundle:Community')->findOneByHashId($communityHashId),
            
            // Purify HTML content
            'title'     => $request->request->get('title'),
            
            // Purify HTML content
            'text'      => $request->request->get('text')
        );

        // If user is not signed in, or POST data is not valid
        if (is_null($user) || is_null($post->community) || is_null($post->title) || strlen($post->title) < 1)
            return new JsonResponse(array(
                'done' => FALSE
            ));
        
        // Clear user input
        $post->title = $this->get('exercise_html_purifier.default')->purify(substr($post->title, 0, 255));
        $post->text = $this->get('exercise_html_purifier.default')->purify($post->text);

        // Create the new post
        $newPost = $em->getRepository('AppBundle:Post')->submitNew(
            $post->community,
            $user,
            $post->title,
            $post->text
        );
        
        // Send back the formatted code for the new post to display
        $html = $this->renderView(
            'AppBundle:Default:Etc/PostsList/post.html.twig',
            array(
                'community' => $post->community,
                'aPost'     => $newPost
            )
        );
        
        return new JsonResponse(array(
            'done' => TRUE,
            'html' => $html
        ));
    }
    
    // Search communities
    public function searchAction($communityName)
    {
        $user = $this->getUser();
        
        if (is_null($user))
            return new JsonResponse(array());
        
        $em = $this->getDoctrine()->getManager();
        
        $communities = $em->getRepository('AppBundle:Community')->search($communityName);
        
        return new JsonResponse(array(
            'html' => $this->renderView(
                'AppBundle:Default:Etc/communitiesSearchResults.html.twig',
                array('communities' => $communities)
            )
        ));
    }
    
}


