<?php

namespace App\Http\Requests\Maps;

use App\Models\MapHex;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Illuminate\Validation\Rule;

class UpdateMapHexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('mapHex')) ?? false;
    }

    public function rules(): array
    {
        return [
            'tile_type' => ['required_without_all:terrain_type,is_visible,faction_id,claim_strength', Rule::in(MapHex::TILE_TYPES)],
            'terrain_type' => ['nullable', 'string', 'max:80'],
            'is_visible' => ['sometimes', 'boolean'],
            'faction_id' => ['nullable', 'exists:factions,id'],
            'claim_strength' => ['sometimes', 'integer', 'min:0', 'max:1000000'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $factionId = $this->integer('faction_id');

                if (! $factionId || $this->user()?->loadMissing('permissions')->canAccessAdmin()) {
                    return;
                }

                if ((int) $this->user()?->character?->faction_id !== $factionId) {
                    $validator->errors()->add('faction_id', 'You can only assign territory to your authorised faction.');
                }
            },
        ];
    }
}
