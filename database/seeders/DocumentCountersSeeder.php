<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\DocumentCounter;
use App\Models\DocumentDefinition;

class DocumentCountersSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Automatically create a counter for every known document type
        $types = DocumentDefinition::pluck('document_type');

        foreach ($types as $type) {
            DocumentCounter::firstOrCreate(
                ['document_type' => $type],
                ['last_number' => 0]
            );
        }
    }
}
