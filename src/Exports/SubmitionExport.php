<?php

// namespace YourVendor\YourPackage\Models;
namespace haseebmukhtar286\LaravelFormSdk\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SubmitionExport implements FromCollection, WithHeadings
{
    protected $dataArr;
    protected $colValues;

    public function __construct($finalArr)
    {

        $this->dataArr = $finalArr;
    }

    public function collection()
    {
        $data = [$this->dataArr];

        return collect($data);
    }

    public function headings(): array
    {
        return isset($this->dataArr[0]) ? array_keys($this->dataArr[0]) : [];
    }
}
