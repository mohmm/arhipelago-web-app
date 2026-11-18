<?php

namespace App\Services\Maya;

use Illuminate\Http\JsonResponse;
use RuntimeException;

/**
 * Maya could not answer: the model was unreachable, too slow, or replied with
 * something unusable. Nothing was stored, so the traveller can simply resend.
 */
class MayaUnavailable extends RuntimeException
{
    public function render(): JsonResponse
    {
        return response()->json([
            'message' => __('Maya cannot answer right now. Please try again in a moment.'),
        ], 503);
    }
}
