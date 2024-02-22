<?php

// namespace App\Http\Controllers\Api;
namespace haseebmukhtar286\LaravelFormSdk\Controllers;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use haseebmukhtar286\LaravelFormSdk\Services\ImageUploadService;

class ImageUploadController extends Controller
{

  public function imageUpload(Request $request,$id)
  {
    return ImageUploadService::imageUpload($request,$id);
  }
}
