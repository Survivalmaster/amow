<?php

namespace App\Http\Requests\Maps;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ClaimMapHexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('claim', $this->route('mapHex')) ?? false;
    }

    public function rules(): array
    {
        return [
            'faction_id' => ['required', 'exists:factions,id'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $factionId = $this->integer('faction_id');

                if ($this->user()?->loadMissing('permissions')->canAccessAdmin()) {
                    return;
                }

                if ((int) $this->user()?->character?->faction_id !== $factionId) {
                    $validator->errors()->add('faction_id', 'You can only claim territory for your authorised faction.');
                }
            },
        ];
    }
}
