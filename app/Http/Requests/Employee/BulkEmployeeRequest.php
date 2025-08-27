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

namespace App\Http\Requests\Employee;

use App\Http\Requests\Request;
use App\Utils\Traits\MakesHash;
use Illuminate\Validation\Rule;

class BulkEmployeeRequest extends Request
{
    use MakesHash;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'ids' => ['required','bail','array'],
            'action' => 'in:archive,restore,delete',
        ];

        // Add company validation if user is authenticated
        if (auth()->check() && auth()->user()->company()) {
            $rules['ids'][] = Rule::exists('employees', 'id')->where('company_id', auth()->user()->company()->id);
        }

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