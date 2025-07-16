<?php
/**
 * Invoice Ninja (https://invoiceninja.com).
 *
 * @link https://github.com/invoiceninja/invoiceninja source repository
 *
 * @copyright Copyright (c) 2024. Invoice Ninja LLC (https://invoiceninja.com)
 *
 * @license https://www.elastic.co/licensing/elastic-license
 */

namespace App\Http\Requests\Resource;

use App\Http\Requests\Request;
use App\Models\Resource;
use App\Utils\Traits\MakesHash;
use Illuminate\Validation\Rule;

class StoreResourceRequest extends Request
{
    use MakesHash;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        return $user->can('create', Resource::class);
    }

    public function rules()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $rules = [];

        $rules['name'] = 'required|string|max:255';
        $rules['description'] = 'nullable|string';
        $rules['rate_per_hour'] = ['sometimes', 'bail', 'nullable', 'numeric', 'min:0', 'max:99999999999999'];
        $rules['rate_per_day'] = ['sometimes', 'bail', 'nullable', 'numeric', 'min:0', 'max:99999999999999'];
        $rules['rate_per_week'] = ['sometimes', 'bail', 'nullable', 'numeric', 'min:0', 'max:99999999999999'];
        $rules['rate_per_month'] = ['sometimes', 'bail', 'nullable', 'numeric', 'min:0', 'max:99999999999999'];
        $rules['documents'] = 'bail|sometimes|array';

        return $this->globalRules($rules);
    }

    public function prepareForValidation()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $input = $this->all();

        $input = $this->decodePrimaryKeys($input);

        if (array_key_exists('rate_per_hour', $input) && is_null($input['rate_per_hour'])) {
            $input['rate_per_hour'] = 0;
        }
        if (array_key_exists('rate_per_day', $input) && is_null($input['rate_per_day'])) {
            $input['rate_per_day'] = 0;
        }
        if (array_key_exists('rate_per_week', $input) && is_null($input['rate_per_week'])) {
            $input['rate_per_week'] = 0;
        }
        if (array_key_exists('rate_per_month', $input) && is_null($input['rate_per_month'])) {
            $input['rate_per_month'] = 0;
        }

        $this->replace($input);
    }

    public function messages()
    {
        return [
            'name.required' => 'The resource name is required.',
            'rate_per_hour.numeric' => 'The hourly rate must be a valid number.',
            'rate_per_hour.min' => 'The hourly rate must be at least 0.',
            'rate_per_day.numeric' => 'The daily rate must be a valid number.',
            'rate_per_day.min' => 'The daily rate must be at least 0.',
            'rate_per_week.numeric' => 'The weekly rate must be a valid number.',
            'rate_per_week.min' => 'The weekly rate must be at least 0.',
            'rate_per_month.numeric' => 'The monthly rate must be a valid number.',
            'rate_per_month.min' => 'The monthly rate must be at least 0.',
        ];
    }
} 