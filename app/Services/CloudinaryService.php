<?php

namespace App\Services;

use Cloudinary\Cloudinary;

class CloudinaryService
{
    protected $cloudinary;

    public function __construct()
    {
        $this->cloudinary = new Cloudinary(
            config('services.cloudinary.url')
        );
    }
    // Upload image
    public function upload($file)
    {
        return $this->cloudinary
            ->uploadApi()
            ->upload($file);
    }

    // Delete image
    public function delete($publicId)
    {
        return $this->cloudinary
            ->uploadApi()
            ->destroy($publicId);
    }
}