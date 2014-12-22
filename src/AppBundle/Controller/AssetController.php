<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class AssetController extends Controller
{
    public function communityPictureAction($communityHashId)
    {
        $em = $this->getDoctrine()->getManager();
        $asset = $this->get('freepost.asset');
        $community = $em->getRepository('AppBundle:Community')->findOneByHashId($communityHashId);

        return new Response(
            $asset->retrieveCommunityPicture($community),   // Raw file
            200,                                            // Response code
            array(                                          // Headers
                'Content-Type' => 'image/png',
                'X-Robots-Tag' => 'noindex',
                'Cache-Control' => 'max-age=2592000',       // 30 days
                'Content-Disposition' => 'inline; filename="' . $communityHashId . '"'
            )
        );
    }

    public function userPictureAction($userHashId)
    {
        $em = $this->getDoctrine()->getManager();
        $asset = $this->get('freepost.asset');
        $user = $em->getRepository('AppBundle:User')->findOneByHashId($userHashId);

        return new Response(
            $asset->retrieveUserPicture($user),             // Raw file
            200,                                            // Response code
            array(                                          // Headers
                'Content-Type' => 'image/png',
                'X-Robots-Tag' => 'noindex',
                'Cache-Control' => 'max-age=2592000',       // 30 days
                'Content-Disposition' => 'inline; filename="' . $userHashId . '"'
            )
        );
    }
}
