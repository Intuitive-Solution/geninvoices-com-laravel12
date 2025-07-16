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

class BulkResourceRequest extends Request
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

        if (! $user->can('edit', Resource::class)) {
            return false;
        }

        return true;
    }

    public function rules()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $rules = [];

        $rules['action'] = 'required';
        $rules['ids'] = 'required';

        return $rules;
    }

    public function prepareForValidation()
    {
        $input = $this->all();

        if (isset($input['ids'])) {
            $input['ids'] = $this->transformKeys($input['ids']);
        }

        $this->replace($input);
    }
} 