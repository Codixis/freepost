<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

class VotePost
{
    private $user;
    private $post;
    private $vote;
    private $datetime;

    public function __construct()
    {
        $this->post     = NULL;
        $this->user     = NULL;
        $this->vote     = 0;
        $this->datetime = new \DateTime();
    }
    
    public function getId()
    {
        return $this->id;
    }

    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    public function getUser()
    {
        return $this->user;
    }
    
    public function setPost($post)
    {
        $this->post = $post;

        return $this;
    }

    public function getPost()
    {
        return $this->post;
    }

    public function setVote($vote)
    {
        $this->vote = $vote;

        return $this;
    }

    public function getVote()
    {
        return $this->vote;
    }

    public function setDatetime($datetime)
    {
        $this->datetime = $datetime;

        return $this;
    }

    public function getDatetime()
    {
        return $this->datetime;
    }
    
    public function setUpvoted()
    {
        $this->vote = 1;

        return $this;
    }
    
    public function setDownvoted()
    {
        $this->vote = -1;

        return $this;
    }
    
    public function upvoted()
    {
        return $this->vote == 1;
    }
    
    public function downvoted()
    {
        return $this->vote == -1;
    }
}
