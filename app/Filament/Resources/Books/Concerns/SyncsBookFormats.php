<?php

namespace App\Filament\Resources\Books\Concerns;

use App\Enums\BookFormat;
use App\Enums\ProductType;
use App\Models\BookDetail;
use App\Models\Product;
use Illuminate\Validation\ValidationException;

trait SyncsBookFormats
{
    /** @var list<string> */
    protected array $pendingFormats = [];

    protected mixed $pendingDigitalPrice = null;

    protected ?string $pendingFilePath = null;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function extractBookFormats(array $data): array
    {
        $formats = $data['formats'] ?? [];
        $formats = is_array($formats) ? array_values(array_filter($formats, is_string(...))) : [];

        if ($formats === []) {
            throw ValidationException::withMessages([
                'formats' => __('filament/resources.products.fields.formats_required'),
            ]);
        }

        $offersPrint = in_array(BookFormat::Print->value, $formats, true);
        $offersDigital = in_array(BookFormat::Digital->value, $formats, true);

        $filePath = $this->normalizeUploadedPath($data['file_path'] ?? null);

        // Keep the previously saved PDF when the field is left untouched on edit.
        if ($offersDigital && $filePath === null) {
            $existing = $this->existingDigitalFilePath();
            $filePath = $existing;
        }

        if ($offersDigital && $filePath === null) {
            throw ValidationException::withMessages([
                'file_path' => __('filament/resources.products.fields.digital_file_required'),
            ]);
        }

        $this->pendingFormats = $formats;
        $this->pendingDigitalPrice = $offersDigital ? ($data['digital_price'] ?? null) : null;
        $this->pendingFilePath = $offersDigital ? $filePath : null;

        unset($data['formats'], $data['digital_price'], $data['file_path']);

        $data['type'] = $offersPrint
            ? ProductType::Physical->value
            : ProductType::Digital->value;

        if (! $offersPrint) {
            $data['stock'] = 0;
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function fillBookFormats(array $data, Product $record): array
    {
        $detail = $record->bookDetail;
        $formats = [];

        if ($detail instanceof BookDetail) {
            if ($detail->offers_print) {
                $formats[] = BookFormat::Print->value;
            }

            if ($detail->offers_digital) {
                $formats[] = BookFormat::Digital->value;
            }

            $data['digital_price'] = $detail->digital_price;
            $data['file_path'] = $detail->file_path;
        }

        if ($formats === []) {
            $formats[] = $record->type === ProductType::Digital
                ? BookFormat::Digital->value
                : BookFormat::Print->value;
        }

        $data['formats'] = $formats;

        return $data;
    }

    protected function syncBookDetailFormats(Product $record): void
    {
        if ($this->pendingFormats === []) {
            return;
        }

        $offersPrint = in_array(BookFormat::Print->value, $this->pendingFormats, true);
        $offersDigital = in_array(BookFormat::Digital->value, $this->pendingFormats, true);

        $payload = [
            'offers_print' => $offersPrint,
            'offers_digital' => $offersDigital,
            'digital_price' => $offersDigital ? $this->pendingDigitalPrice : null,
            'file_path' => $offersDigital ? $this->pendingFilePath : null,
        ];

        $detail = $record->bookDetail()->first();

        if ($detail instanceof BookDetail) {
            $detail->fill($payload)->save();

            return;
        }

        $record->bookDetail()->create([
            ...$payload,
            'author' => [
                'en' => $record->getTranslation('name', 'en') ?: $record->displayName(),
                'ar' => $record->getTranslation('name', 'ar') ?: $record->displayName(),
            ],
        ]);
    }

    private function existingDigitalFilePath(): ?string
    {
        $record = $this->record ?? null;

        if (! $record instanceof Product) {
            return null;
        }

        $path = $record->bookDetail?->file_path;

        return $this->normalizeUploadedPath($path);
    }

    private function normalizeUploadedPath(mixed $path): ?string
    {
        if (is_array($path)) {
            $path = $path[0] ?? null;
        }

        if (! is_string($path)) {
            return null;
        }

        $path = trim($path);

        return $path !== '' ? $path : null;
    }
}
