<?php

// namespace App\Http\Controllers\Api;
namespace haseebmukhtar286\LaravelFormSdk\Controllers;

use App\Http\Controllers\Controller;
use haseebmukhtar286\LaravelFormSdk\Services\PdfGenerateService;

class PdfController extends Controller
{

  public function pdfGenerate($id)
  {
    return PdfGenerateService::pdfGenerate($id);
  }
}
