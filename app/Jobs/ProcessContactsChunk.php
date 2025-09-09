<?php

namespace App\Jobs;

use App\Models\Import;
use App\Models\ImportFailure;
use Illuminate\Bus\Queueable;
use Illuminate\Bus\Batchable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Queue\InteractsWithQueue;

class ProcessContactsChunk implements ShouldQueue
{
    use Dispatchable, Batchable, InteractsWithQueue, Queueable;

    public function __construct(public int $importId, public array $rows) {}

    public function handle(): void
    {
        $import = Import::findOrFail($this->importId);

        $validRows = [];
        $failed = 0;
        $now = now();

        foreach ($this->rows as $r) {
            $data = [
                'name'  => $r['name'] ?? null,
                'phone' => isset($r['phone']) ? preg_replace('/\s+/', '', $r['phone']) : null,
            ];

            $v = Validator::make($data, [
                'name'  => ['required','max:50','regex:/^[\pL\s]+$/u'],
                'phone' => ['required','regex:/^\+90\d{10}$/'],
            ]); // Using Laravel's validator per row. :contentReference[oaicite:11]{index=11}


            if ($v->fails()) {
                $failed++;
                ImportFailure::create([
                    'import_id'  => $this->importId,
                    'row_number' => $r['row_number'],
                    'payload'    => $data,
                    'errors'     => $v->errors()->toArray(),
                ]);
                continue;
            }

            $validRows[] = [
                'name'       => $data['name'],
                'phone'      => $data['phone'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($validRows) {
            // One batched upsert—no Eloquent overhead, perfect for bulk. :contentReference[oaicite:12]{index=12}
            DB::table('contacts')->upsert(
                $validRows,
                ['phone'],               // unique key
                ['name','updated_at']    // columns to update on conflict
            );
        }

        $import->incrementEach([
            'processed' => count($this->rows),
            'succeeded' => count($validRows),
            'failed'    => $failed,
        ]);
    }
}
