<?php

namespace App\Http\Controllers;

use App\Jobs\ImportContactsXml;
use App\Models\Import;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;

class ContactImportController extends Controller
{
    public function store(Request $request)
    {
        // Validate the uploaded file (accept .xml and XML mimetypes; adjust max as you like)
        $validated = $request->validate([
            'file' => [
                'required','file','mimes:xml',
                'mimetypes:text/xml,application/xml',
                'max:51200' // ~50MB
            ],
        ]); // File validation rules are built in. :contentReference[oaicite:6]{index=6}

        // Persist the file to storage (local disk or S3, etc.)
        $path = $request->file('file')->store('imports/contacts'); // uses configured filesystem
        // `store()` is the canonical way to save uploaded files. :contentReference[oaicite:7]{index=7}

        $import = Import::create([
            'file_path'=> $path,
            'status'   => 'processing',
            'started_at' => now(),
        ]);

        // Create an empty batch with lifecycle callbacks.
        $batch = Bus::batch([])
            ->name("Contacts XML Import #{$import->id}")
            ->allowFailures() // we log per-row failures
            ->then(function () use ($import) {
                $import->update([
                    'status' => 'completed',
                    'finished_at' => now(),
                ]);
            })
            ->catch(function () use ($import) {
                $import->update([
                    'status' => 'failed',
                    'finished_at' => now(),
                ]);
            })
            ->dispatch(); // Dispatch this batch right away. :contentReference[oaicite:8]{index=8}

        $import->update(['batch_id' => $batch->id]);

        // Dispatch a job that will stream-parse XML and add chunk-jobs into the batch.
        ImportContactsXml::dispatch($import->id, $batch->id)->onQueue('imports');

        return response()->json([
            'import_id' => $import->id,
            'batch_id'  => $batch->id,
            'message'   => 'Import started',
        ], 202);
    }

    public function show(Import $import)
    {
        $batch = $import->batch_id ? Bus::findBatch($import->batch_id) : null;

        return response()->json([
            'import' => $import->only([
                'id','status','total','processed','succeeded','failed','started_at','finished_at'
            ]),
            'progress' => $batch ? $batch->progress() : (
                $import->total ? intval($import->processed / max(1,$import->total) * 100) : 0
            ),
            'batch' => $batch ? $batch->toArray() : null,
        ]);
    }
}