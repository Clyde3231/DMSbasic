<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\DocumentDefinition;

class DocumentDefinitionsSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $definitions = [
            [
                'document_type' => 'pull_out_receipt',
                'form_code' => 'ADM-WHS-003',
                'ref_prefix' => 'PO',
                'display_name' => 'Material Pull-Out Form'
            ],
            [
                'document_type' => 'cash_advance',
                'form_code' => 'ADM-ACC-001',
                'ref_prefix' => 'CA',
                'display_name' => 'Cash Advance Form'
            ],
            [
                'document_type' => 'reimbursement',
                'form_code' => 'ADM-ACC-002',
                'ref_prefix' => 'RE',
                'display_name' => 'Reimbursement Form'
            ],
            // Add more forms below as needed:
            // [
            //     'document_type' => '...', 
            //     'form_code' => '...', 
            //     'ref_prefix' => '...',
            //     'display_name' => '...'
            // ]
        ];

        foreach ($definitions as $def) {
            DocumentDefinition::updateOrCreate(
                ['document_type' => $def['document_type']],
                [
                    'form_code' => $def['form_code'],
                    'ref_prefix' => $def['ref_prefix'],
                    'display_name' => $def['display_name'],
                ]
            );
        }
    }
}
