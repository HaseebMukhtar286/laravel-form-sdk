<?php

// namespace YourVendor\YourPackage\Models;
namespace haseebmukhtar286\LaravelFormSdk\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SubmitionExport implements FromCollection, WithHeadings
{
    protected $colNames;
    protected $colValues;

    public function __construct($colNames, $colValues)
    {
        $this->colNames = $colNames;
        $this->colValues = $colValues;
    }

    public function collection()
    {
        $data = [$this->colValues];

        return collect($data);
    }

    public function headings(): array
    {
        return $this->colNames;
    }
}
