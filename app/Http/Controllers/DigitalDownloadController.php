<?php

namespace App\Http\Controllers;

use App\Models\Entitlement;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DigitalDownloadController extends Controller
{
    public function __invoke(OrderItem $orderItem): StreamedResponse
    {
        $allowed = Entitlement::query()->where('order_item_id', $orderItem->id)->exists();
        abort_unless($allowed, 403);

        $orderItem->load('product.bookDetail');
        $path = $orderItem->product?->bookDetail?->file_path;
        abort_unless(is_string($path) && $path !== '' && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path);
    }
}
