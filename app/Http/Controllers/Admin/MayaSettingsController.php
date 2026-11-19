<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MayaSettingsRequest;
use App\Settings\MayaSettings;
use App\Support\Toast;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * What Maya is told about who she is and how she speaks. The rules that keep
 * her to the app's data are in code and are not edited here.
 */
class MayaSettingsController extends Controller
{
    public function edit(MayaSettings $settings): Response
    {
        return Inertia::render('admin/maya', [
            'instructions' => $settings->instructions,
            'model' => config('maya.model'),
        ]);
    }

    public function update(MayaSettingsRequest $request, MayaSettings $settings): RedirectResponse
    {
        $settings->instructions = $request->validated('instructions');
        $settings->save();

        Toast::success(__('Instructions for :assistant saved. They apply from the next reply.', ['assistant' => config('maya.name')]));

        return back();
    }
}
