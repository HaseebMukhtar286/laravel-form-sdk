<?php

// namespace App\Http\Controllers\Api;
namespace haseebmukhtar286\LaravelFormSdk\Controllers;

use haseebmukhtar286\LaravelFormSdk\Services\ExcelGenerateService;

use App\Http\Controllers\Controller;

class ExcelGenerateController extends Controller
{

  public function excelGenerate($id)
  {
    return ExcelGenerateService::excelGenerate($id);
  }
}
