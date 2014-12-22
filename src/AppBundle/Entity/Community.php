<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

use AppBundle\Utility\Crypto;

class Community
{
    private $id;
    private $name;
    private $description;
    private $hashId;
    private $created;
    
    private $posts;
    private $users;
    
    public function __construct()
    {
        $this->hashId       = Crypto::randomString(36, 8);
        $this->name         = '';
        $this->description  = '';
        $this->created      = new \DateTime();
        
        $this->posts = new ArrayCollection();
        $this->users = new ArrayCollection();
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }
    
    public function getDescription()
    {
        return $this->description;
    }
    
    public function setHashId($hashId)
    {
        $this->hashId = $hashId;

        return $this;
    }
    
    public function getHashId()
    {
        return $this->hashId;
    }
    
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }
    
    public function getCreated()
    {
        return $this->created;
    }
    
    public function getPosts()
    {
        return $this->posts;
    }
    
    public function getUsers()
    {
        return $this->users;
    }
    
    public function addUser(User $user)
    {
        if (!$this->users->contains($user))
            $this->users[] = $user;
    }
}
