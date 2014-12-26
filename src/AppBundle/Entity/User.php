<?php

/* freepost
 * http://freepo.st
 *
 * Copyright © 2014-2015 zPlus
 * 
 * This file is part of freepost.
 * freepost is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * freepost is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with freepost. If not, see <http://www.gnu.org/licenses/>.
 */

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

use AppBundle\Utility\Crypto;

/**
 * User
 */
class User implements AdvancedUserInterface, \Serializable
{
    private $id;
    private $hashId;
    private $email;
    private $username;
    private $password;
    private $salt;
    private $registered;
    private $resetPasswordSecretToken;
    private $votes;
    
    private $isActive;
    private $posts;
    private $comments;
    private $communities;
    private $commentVotes;
    private $postVotes;

    public function __construct()
    {
        $this->hashId        = Crypto::randomString(36, 8);
        $this->email         = NULL;
        $this->username      = '';
        $this->password      = '';
        $this->salt          = '';
        $this->registered    = new \DateTime();
        $this->resetPasswordSecretToken = '';
        $this->isActive      = TRUE;
        
        $this->posts         = new ArrayCollection();
        $this->comments      = new ArrayCollection();
        $this->communities   = new ArrayCollection();
        $this->votes         = new ArrayCollection();
        $this->commentVotes  = new ArrayCollection();
        $this->postVotes     = new ArrayCollection();
    }
    
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set hashId
     *
     * @param string $hashId
     * @return User
     */
    public function setHashId($hashId)
    {
        $this->hashId = $hashId;

        return $this;
    }

    /**
     * Get hashId
     *
     * @return string 
     */
    public function getHashId()
    {
        return $this->hashId;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set username
     *
     * @param string $username
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string 
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = Crypto::sha512($password);

        return $this;
    }

    /**
     * Get password
     *
     * @return string 
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set salt
     *
     * @param string $salt
     * @return User
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * Get salt
     *
     * @return string 
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Set registered
     *
     * @param \DateTime $registered
     * @return User
     */
    public function setRegistered($registered)
    {
        $this->registered = $registered;

        return $this;
    }

    /**
     * Get registered
     *
     * @return \DateTime 
     */
    public function getRegistered()
    {
        return $this->registered;
    }

    /**
     * Set resetPasswordSecretToken
     *
     * @param string $resetPasswordSecretToken
     * @return User
     */
    public function setResetPasswordSecretToken($resetPasswordSecretToken)
    {
        $this->resetPasswordSecretToken = $resetPasswordSecretToken;

        return $this;
    }

    /**
     * Get resetPasswordSecretToken
     *
     * @return string 
     */
    public function getResetPasswordSecretToken()
    {
        return $this->resetPasswordSecretToken;
    }

    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getIsActive()
    {
        return $this->isActive;
    }

    public function getPosts()
    {
        return $this->posts;
    }
    
    public function getCommunities()
    {
        return $this->communities;
    }
    
    public function addCommunity(Community $community)
    {
        $community->addUser($this); // synchronously updating inverse side
        
        if (!$this->communities->contains($community))
            $this->communities[] = $community;
    }
    
    public function isAccountNonExpired()
    {
        return true;
    }

    public function isAccountNonLocked()
    {
        return true;
    }

    public function isCredentialsNonExpired()
    {
        return true;
    }

    public function isEnabled()
    {
        return $this->isActive;
    }
    
    public function getRoles()
    {
        return array('ROLE_USER');
    }

    public function eraseCredentials()
    {
    }

    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->hashId,
            $this->username,
            $this->password,
            $this->email
        ));
    }

    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->hashId,
            $this->username,
            $this->password,
            $this->email
        ) = unserialize($serialized);
    }
    
    
}

















