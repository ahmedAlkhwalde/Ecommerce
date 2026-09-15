<?php

namespace App\Exports;

use App\Models\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithCustomChunkSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Events\AfterSheet;

class ProductsExport implements FromQuery, WithHeadings, WithMapping, WithChunkReading, WithCustomChunkSize, ShouldQueue, WithEvents
{
    use Exportable;

    private int $processedRows = 0;
    private int $currentChunk = 0;

    public function query()
    {
        return Product::query()->with('category');
    }

    /**
     * تحديد حجم الـ Chunk لتقسيم الـ Queue Jobs
     */
    public function chunkSize(): int
    {
        return 20;
    }

    public function headings(): array
    {
        return [
            'ID',
            'اسم المنتج',
            'السعر',
            'التصنيف',
            'الكمية المتاحة',
            'تاريخ الإضافة',
        ];
    }

    public function map($product): array
    {
        $this->processedRows++;

        if (($this->processedRows - 1) % $this->chunkSize() === 0) {
            $this->currentChunk++;
            Log::info("📦 [Excel Chunk Processed]: تم جلب وتصدير الـ Chunk رقم ({$this->currentChunk})");
        }

        return [
            $product->id,
            $product->name,
            $product->price . ' USD',
            $product->category ? $product->category->name : 'N/A',
            $product->stock ?? 0,
            $product->created_at->format('Y-m-d'),
        ];
    }

    public function registerEvents(): array
    {
        return [
            BeforeExport::class => function (BeforeExport $event) {
                Log::info("🚀 [Excel Export]: بدأت عملية التصدير في الـ Queue...");
            },
            AfterSheet::class => function (AfterSheet $event) {
                Log::info("✅ [Excel Export Complete]: تم إكمال كتابة جميع الـ Chunks بنجاح!");
            },
        ];
    }
}