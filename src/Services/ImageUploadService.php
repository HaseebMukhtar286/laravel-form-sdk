<?php

namespace haseebmukhtar286\LaravelFormSdk\Services;
use Illuminate\Support\Facades\Storage;

class ImageUploadService
{

    public static function imageUpload($request)
    {
        $image = $request->file('file');
        if ($image) {
            $imageName = env('APP_NAME') . '/' . time() . '.' . $image->getClientOriginalExtension();
            $path = Storage::disk('s3')->put($imageName, file_get_contents($image), 'public');
            if ($path) {
                $imageUrl = Storage::disk('s3')->url($imageName);
                return response()->json(['imageUrl' => $imageUrl], 200);
            } else {
                return response()->json("Something went wrong", 200);
                
            }
        }
    }

}
