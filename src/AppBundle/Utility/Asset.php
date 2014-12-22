<?php

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
