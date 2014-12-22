<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * PostRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PostRepository extends EntityRepository
{
    /* Retrieve a list of posts.
     * $user is used to find $user vote for each post
     */
    protected function findPosts($community = NULL, $user = NULL, $sort = '')
    {
        if (is_null($community))
            return NULL;
        
        $em = $this->getEntityManager();

        switch(strtoupper($sort))
        {
            case 'NEW':
                $query = $em->createQuery(
                    'SELECT p
                    FROM AppBundle:Post p
                    JOIN p.user u
                    LEFT JOIN p.votes v WITH v.post = p AND v.user = :user
                    WHERE p.community = :community
                    ORDER BY p.created DESC'
                )
                ->setParameter('user', $user)
                ->setParameter('community', $community);
                break;
            
            default: // HOT
                $query = $em->createQuery(
                    'SELECT p, v
                    FROM AppBundle:Post p
                    JOIN p.user u
                    LEFT JOIN p.votes v WITH v.post = p AND v.user = :user
                    WHERE p.community = :community
                    ORDER BY p.dateCreated DESC, p.vote DESC, p.created DESC'
                )
                ->setParameter('user', $user)
                ->setParameter('community', $community);
        }
        
        return $query->setMaxResults(32)->getResult();
    }
    
    // Retrieve a list of newest posts sorted by vote
    public function findHot($community = NULL, $user = NULL)
    {
        return $this->findPosts($community, $user, 'hot');
    }
    
    // Retrieve a list of newest posts sorted by date (newest first)
    public function findNew($community = NULL, $user = NULL)
    {
        return $this->findPosts($community, $user, 'new');
    }
    
    // Get my posts
    public function findMyPosts($user = NULL)
    {
        $em = $this->getEntityManager();

        return $em->createQuery(
            'SELECT p
            FROM AppBundle:Post p
            WHERE p.user = :user
            ORDER BY p.created DESC'
        )
        ->setParameter('user', $user)
        ->setMaxResults(100)
        ->getResult();
    }
    
    public function submitNew($community, $user, $title, $text)
    {
        $em = $this->getEntityManager();
        $datetime = new \DateTime();

        $p = new Post();
        $p->setCommunity($community);
        $p->setUser($user);
        $p->setTitle($title);
        $p->setText($text);
        $p->setCreated($datetime);
        $p->setDateCreated($datetime);

        $em->persist($p);

        $em->flush();
        
        return $p;
    }
}