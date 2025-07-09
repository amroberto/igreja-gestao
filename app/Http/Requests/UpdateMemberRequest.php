<?php

namespace App\Http\Requests;

use App\Enums\GenderEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class UpdateMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check(); // Ajuste conforme sua política de autorização
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'cpf' => ['required', 'digits:11', 'unique:members,cpf,' . $this->member->id],
            'rg' => ['nullable', 'string', 'max:20'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', Rule::in(GenderEnum::getValues())],
            'community_id' => ['required', 'exists:communities,id'],
            'phone' => ['nullable', 'string', 'max:15'],
            'cellphone' => ['nullable', 'string', 'max:15'],
            'email' => ['nullable', 'email', 'max:255'],
            'cep' => ['nullable', 'digits:8'],
            'address' => ['nullable', 'string', 'max:255'],
            'number' => ['nullable', 'string', 'max:10'],
            'complement' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:2'],
            'user_id' => ['nullable|exists:user, id'],
            'observations' => ['nullable', 'string'],
            'marital_status' => ['nullable', Rule::in(\App\Enums\MaritalStatusEnum::getValues())],
            'is_active' => ['required', 'boolean'],
            'is_contributor' => ['required', 'boolean'],
            'father_name' => ['nullable', 'string', 'max:255'],
            'mother_name' => ['nullable', 'string', 'max:255'],
            'delivery_method' => ['required', Rule::in(\App\Enums\DeliveryMethodEnum::getValues())],
            'payment_method' => ['required', Rule::in(\App\Enums\PaymentMethodEnum::getValues())],
            'due_day' => ['required', 'integer', 'between:1,31'],
            'status' => ['required|string|in:ativo,inativo'],
            'amount' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'cpf.required' => 'O CPF é obrigatório.',
            'cpf.digits' => 'O CPF deve ter 11 dígitos.',
            'cpf.unique' => 'Já existe um membro cadastrado com este CPF.',
            'cep.digits' => 'O CEP deve ter 8 dígitos.',
            'due_day.between' => 'O dia de vencimento deve estar entre 1 e 31.',
            'amount.numeric' => 'O valor deve ser um número.',
            'amount.min' => 'O valor não pode ser negativo.',
            'status.required' => 'O status é obrigatório.',
            'status.in' => 'O status deve ser "ativo" ou "inativo".',
            'delivery_method.required' => 'O método de entrega é obrigatório.',
            'delivery_method.in' => 'O método de entrega selecionado é inválido.',
            'payment_method.in' => 'O método de pagamento selecionado é inválido.',
            'payment_method.required' => 'O método de pagamento é obrigatório.',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'cpf' => preg_replace('/\D/', '', $this->cpf ?? ''),
            'cep' => preg_replace('/\D/', '', $this->cep ?? ''),
            'amount' => str_replace(['R$', '.', ','], ['', '', '.'], $this->amount ?? ''),
            'phone' => preg_replace('/\D/', '', $this->phone ?? ''),
            'cellphone' => preg_replace('/\D/', '', $this->cellphone ?? ''),
        ]);
    }
}
