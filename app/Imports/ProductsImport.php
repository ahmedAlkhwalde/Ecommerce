<?php

namespace App\Imports;

use App\Models\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Events\AfterImport;

class ProductsImport implements ToModel, WithHeadingRow, WithChunkReading, WithValidation, SkipsOnFailure, SkipsOnError, ShouldQueue, WithEvents
{
    use SkipsFailures, SkipsErrors;

    private static int $importedCount = 0;

    public function model(array $row)
    {
        self::$importedCount++;

        // تصحيح استخدام self::$importedCount
        Log::info("📦 [Excel Import]: تم استيراد المنتج رقم (" . self::$importedCount . ")");

        return new Product([
            'title'       => $row['title'],
            'price'       => $row['price'],
            'stock'       => $row['stock'] ?? 0,
            'category_id' => $row['category_id'],
        ]);
    }

    public function chunkSize(): int
    {
        return 20;
    }

    public function rules(): array
    {
        return [
            '*.title'       => 'required|string|max:255',
            '*.price'       => 'required|numeric|min:0',
            '*.stock'       => 'nullable|integer|min:0',
            '*.category_id' => 'required|exists:categories,id',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterImport::class => function (AfterImport $event) {
                Log::info("✅ [Excel Import Complete]: تم استيراد المنتجات بنجاح إلى قاعدة البيانات!");
            },
        ];
    }
}