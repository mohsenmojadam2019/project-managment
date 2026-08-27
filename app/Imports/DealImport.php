<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;
use App\Models\Deal; // فرض می‌کنم مدل Deal اینجا تعریف شده

class DealImport implements ToArray
{
    public static function fields(): array
    {
        // فیلدهای ثابت Deal
        $staticFields = array(
            array('id' => 'email', 'name' => __('modules.deal.leadContactEmail'), 'required' => 'Yes'),
            array('id' => 'name', 'name' => __('modules.deal.dealName'), 'required' => 'Yes'),
            array('id' => 'pipeline', 'name' => __('modules.deal.pipeline'), 'required' => 'Yes'),
            array('id' => 'stages', 'name' => __('modules.deal.stages'), 'required' => 'Yes'),
            array('id' => 'value', 'name' => __('modules.deal.dealValue'), 'required' => 'Yes'),
            array('id' => 'close_date', 'name' => __('modules.deal.closeDate'), 'required' => 'Yes'),
        );

        // گرفتن فیلدهای سفارشی از مدل Deal
        $deal = new Deal();
        $getCustomField = $deal->getCustomFieldGroupsWithFields();

        if ($getCustomField) {
            foreach ($getCustomField->fields as $customField) {
                $staticFields[] = [
                    'id' => $customField->name,
                    'name' => $customField->label,
                    'required' => 'No', // فیلدهای سفارشی معمولاً اختیاری هستن
                ];
            }
        }

        return $staticFields;
    }

    public function array(array $array): array
    {
        return $array;
    }
}