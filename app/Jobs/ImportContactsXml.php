<?php

namespace App\Jobs;

use App\Models\Import;
use Illuminate\Bus\Batchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use SimpleXMLElement;
use XMLReader;

class ImportContactsXml implements ShouldQueue
{
    use Queueable, Batchable, InteractsWithQueue, Dispatchable;

    public function __construct(public int $importId, public string $jobImportBatchId) {}

    public function handle(): void
    {
        $import = Import::findOrFail($this->importId);
        $batch  = Bus::findBatch($this->jobImportBatchId); // to add jobs dynamically. :contentReference[oaicite:9]{index=9}
        if (! $batch) return;

        $file = Storage::path($import->file_path);
        $reader = new XMLReader();

        // Stream open with safe/libxml options; no network access.
        $reader->open($file, null, LIBXML_NONET | LIBXML_COMPACT);

        $chunk = [];
        $chunkSize = 1000; // tune for your infra
        $row = 0;

        while ($reader->read()) {
            if ($reader->nodeType === XMLReader::ELEMENT && $reader->localName === 'contact') {
                // Pull a single <contact>...</contact> node as XML
                $xml = $reader->readOuterXml();
                // Convert just this node to SimpleXML to extract data
                $sx = new SimpleXMLElement($xml, LIBXML_NOCDATA);
                $chunk[] = [
                    'row_number' => ++$row,
                    'name'  => trim((string)($sx->name ?? '')),
                    'phone' => trim((string)($sx->phone ?? '')),
                ];

                if (count($chunk) >= $chunkSize) {
                    $batch->add(new ProcessContactsChunk($import->id, $chunk));
                    $chunk = [];
                }

                // Move to the next <contact> without scanning all children
                $reader->next('contact');
            }
        }

        if ($chunk) {
            $batch->add(new ProcessContactsChunk($import->id, $chunk));
        }

        $reader->close();

        // Update the totals for UI/progress
        $import->update(['total' => $row]);
    }
}