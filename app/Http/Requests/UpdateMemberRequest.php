<?php

namespace App\Http\Requests;

use App\Enums\GenderEnum;
use App\Enums\MaritalStatusEnum;
use App\Enums\DeliveryMethodEnum;
use App\Enums\PaymentMethodEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check(); // Autorização tratada no controller
    }

    public function rules(): array
    {
        $memberId = $this->route('member')->id;

        return [
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'cpf' => ['required', 'digits:11', 'unique:members,cpf,' . $memberId],
            'rg' => ['nullable', 'string', 'max:20'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', Rule::in(GenderEnum::getValues())],
            'community_id' => ['required', 'exists:communities,id'],
            'phone' => ['nullable', 'string', 'max:15'],
            'cellphone' => ['nullable', 'string', 'max:15'],
            'email' => ['nullable', 'email', 'max:255', 'unique:members,email,' . $memberId, function ($attribute, $value, $fail) {
                if ($this->input('is_contributor') && empty($value)) {
                    $fail('O e-mail é obrigatório para membros contribuintes.');
                }
            }],
            'cep' => ['nullable', 'digits:8'],
            'address' => ['nullable', 'string', 'max:255'],
            'number' => ['nullable', 'string', 'max:10'],
            'complement' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:2'],
            'marital_status' => ['nullable', Rule::in(MaritalStatusEnum::getValues())],
            'father_name' => ['nullable', 'string', 'max:255'],
            'mother_name' => ['nullable', 'string', 'max:255'],
            'delivery_method' => ['required', Rule::in(DeliveryMethodEnum::getValues())],
            'payment_method' => ['required', Rule::in(PaymentMethodEnum::getValues())],
            'due_day' => ['required', 'integer', 'between:1,31'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['required', 'boolean'],
            'is_contributor' => ['required', 'boolean'],
            'observations' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'cpf.required' => 'O CPF é obrigatório.',
            'cpf.digits' => 'O CPF deve ter 11 dígitos.',
            'cpf.unique' => 'Já existe um membro cadastrado com este CPF.',
            'cep.digits' => 'O CEP deve ter 8 dígitos.',
            'email.required' => 'O e-mail é obrigatório para membros contribuintes.',
            'email.email' => 'O e-mail deve ser válido.',
            'email.unique' => 'Já existe um membro cadastrado com este e-mail.',
            'due_day.between' => 'O dia de vencimento deve estar entre 1 e 31.',
            'amount.numeric' => 'O valor deve ser um número.',
            'amount.min' => 'O valor não pode ser negativo.',
            'delivery_method.required' => 'O método de entrega é obrigatório.',
            'delivery_method.in' => 'O método de entrega selecionado é inválido.',
            'payment_method.required' => 'O método de pagamento é obrigatório.',
            'payment_method.in' => 'O método de pagamento selecionado é inválido.',
            'is_active.required' => 'O status de ativo é obrigatório.',
            'is_contributor.required' => 'O status de contribuinte é obrigatório.',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'cpf' => preg_replace('/\D/', '', $this->input('cpf', '')),
            'cep' => preg_replace('/\D/', '', $this->input('cep', '')),
            'phone' => preg_replace('/\D/', '', $this->input('phone', '')),
            'cellphone' => preg_replace('/\D/', '', $this->input('cellphone', '')),
            'amount' => $this->normalizeAmount($this->input('amount', '')),
        ]);
    }

    /**
     * Normaliza o valor monetário para formato numérico.
     */
    private function normalizeAmount($amount): ?float
    {
        if (empty($amount)) {
            return null;
        }

        // Remove caracteres não numéricos, exceto vírgula e ponto
        $amount = preg_replace('/[^0-9,.]/', '', $amount);
        // Converte formato brasileiro (ex.: 1.000,00) para float
        $amount = str_replace(['.', ','], ['', '.'], $amount);

        return is_numeric($amount) ? (float) $amount : null;
    }
}
