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

/* This class is used to manage uploaded files, such as pictures.
 */

namespace AppBundle\Utility;

class Asset
{
    const PATH_ROOT                         = '../asset/';
    const PATH_COMMUNITY_PICTURE            = '../asset/community/picture/';
    const PATH_COMMUNITY_PICTURE_DEFAULT    = 'bundles/app/images/fullscreen.png';

    const PATH_USER_PICTURE                 = '../asset/user/picture/';
    const PATH_USER_PICTURE_DEFAULT         = 'bundles/app/images/fullscreen.png';

    // Size of Community picture
    const COMMUNITY_PICTURE_WIDTH	= 256;
    const COMMUNITY_PICTURE_HEIGHT	= 256;

    // Size of user picture
    const USER_PICTURE_WIDTH		= 256;
    const USER_PICTURE_HEIGHT		= 256;

    protected static function createThumbnail($filePath, $width, $height)
    {
        try {
            $imagick = new \Imagick();
            $imagick->readImage($filePath);
            $imagick->thumbnailImage($width, $height);
            $imagick->writeImage($filePath);
        } catch (Exception $e) {}
    }

    /* $CommunityPicture is a instance of
     *   Symfony\Component\HttpFoundation\File\UploadedFile
     * which corresponds to a form <input type="file" />.
     */
    public function updateCommunityPicture($community, $communityPicture)
    {
        $fileName = $community->getHashId() . '.png';
        $fileFullPath = Asset::PATH_COMMUNITY_PICTURE . $fileName;

        $communityPicture->move(
            Asset::PATH_COMMUNITY_PICTURE,
            $fileName
        );

        Asset::createThumbnail($fileFullPath, Asset::COMMUNITY_PICTURE_WIDTH, Asset::COMMUNITY_PICTURE_HEIGHT);
    }

    /* Retrieve a community picture.
     * This method return the binary file.
     */
    public function retrieveCommunityPicture($community)
    {
        $filePath = Asset::PATH_COMMUNITY_PICTURE . $community->getHashId() . '.png';

        return readfile(file_exists($filePath) ? $filePath : Asset::PATH_COMMUNITY_PICTURE_DEFAULT);
    }

    /* $userPicture is a instance of
     *   Symfony\Component\HttpFoundation\File\UploadedFile
     * which corresponds to a form <input type="file" />.
     */
    public function updateUserPicture($user, $userPicture)
    {
        $fileName = $user->getHashId() . '.png';
        $fileFullPath = Asset::PATH_USER_PICTURE . $fileName;

        $userPicture->move(
            Asset::PATH_USER_PICTURE,
            $fileName
        );

        Asset::createThumbnail($fileFullPath, Asset::USER_PICTURE_WIDTH, Asset::USER_PICTURE_HEIGHT);
    }

    /* Retrieve a user picture.
     * This method return the binary file.
     */
    public function retrieveUserPicture($user)
    {
        $filePath = Asset::PATH_USER_PICTURE . $user->getHashId() . '.png';

        return readfile(file_exists($filePath) ? $filePath : Asset::PATH_USER_PICTURE_DEFAULT);
    }

}
