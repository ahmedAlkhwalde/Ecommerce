<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait UploadImageTrait
{
    public function uploadImage(Request $request, string $fieldName = 'image', string $folderName = 'images'): ?string
    {
        if ($request->hasFile($fieldName)) {
            $image = $request->file($fieldName);
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path($folderName), $imageName);

            return $folderName . '/' . $imageName;
        }

        return null;
    }
}
