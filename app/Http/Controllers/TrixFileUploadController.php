<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TrixFileUploadController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $path = $request->attachment->store('trix-attachments', 'public');
        $url = Storage::disk('public')->url($path);

        return [
            'href' => $url,
            'url' => $url,
        ];
    }
}
