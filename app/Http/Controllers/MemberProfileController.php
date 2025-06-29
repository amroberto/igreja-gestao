<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Community;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MemberProfileController extends Controller
{
    private const MAX_255 = 'max:255';

    private function removeMasks(array $data): array
    {
        $data['rg'] = preg_replace('/\D/', '', $data['rg'] ?? '');
        $data['cpf'] = preg_replace('/\D/', '', $data['cpf'] ?? '');
        $data['cnpj'] = preg_replace('/\D/', '', $data['cnpj'] ?? '');
        $data['phone'] = preg_replace('/\D/', '', $data['phone'] ?? '');
        $data['cellphone'] = preg_replace('/\D/', '', $data['cellphone'] ?? '');
        $data['cep'] = preg_replace('/\D/', '', $data['cep'] ?? '');
        return $data;
    }


    public function edit()
    {
        $member = Auth::user()->member;
        $communities = Community::all();
        return view('member.profile.edit', compact('member', 'communities'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        if (!($user instanceof \Illuminate\Database\Eloquent\Model)) {
            $user = \App\Models\User::find($user->id);
        }
        $member = $user->member;

        try {
            if (!$member) {
                throw new \Exception('Perfil de membro não encontrado.');
            }

            $rules = [
                'name' => ['required', 'string', self::MAX_255],
                'cpf' => ['required', 'string', 'size:11', 'unique:members,cpf,' . $member->id],
                'gender' => ['nullable', 'in:masculino,feminino,outro'],
                'rg' => ['nullable', 'string', 'max:20'],
                'email' => ['required', 'email', self::MAX_255, 'unique:members,email,' . $member->id],
                'birth_date' => ['nullable', 'date', 'before:today'],
                'phone' => ['nullable', 'string', 'max:20'],
                'cellphone' => ['nullable', 'string', 'max:20'],
                'cep' => ['nullable', 'string', 'size:8'],
                'address' => ['nullable', 'string', self::MAX_255],
                'number' => ['nullable', 'string', 'max:10'],
                'complement' => ['nullable', 'string', self::MAX_255],
                'district' => ['nullable', 'string', self::MAX_255],
                'city' => ['nullable', 'string', self::MAX_255],
                'state' => ['nullable', 'string', 'size:2'],
                'marital_status' => ['nullable', 'in:solteiro,casado,divorciado,viuvo'],
                'father_name' => ['nullable', 'string', self::MAX_255],
                'mother_name' => ['nullable', 'string', self::MAX_255],
            ];

            Log::debug('Dados recebidos no request', ['request' => $request->all(), 'birth_date' => $request->input('birth_date')]);

            $data = $request->validate($rules);

            Log::debug('Dados validados', ['data' => $data, 'birth_date' => $data['birth_date'] ?? 'null']);

            // Converter data para o formato americano antes de salvar
            if (!empty($data['birth_date'])) {
                $data['birth_date'] = Carbon::parse($data['birth_date'])->format('Y-m-d');
            }

            $data = $this->removeMasks($data);

            Log::debug('Dados após removeMasks', ['data' => $data, 'birth_date' => $data['birth_date'] ?? 'null']);

            $member->fill($data);
            $saved = $member->save();

            if ($saved) {
                $freshMember = $member->fresh();
                Log::debug('Dados salvos no modelo', ['birth_date' => $freshMember->birth_date]);
            } else {
                Log::error('Falha ao salvar o modelo', ['data' => $data]);
            }

            if (method_exists($user, 'save')) {
                $user->name = $data['name'];
                $user->email = $data['email'];
                $user->save();
            }

            Log::info('Perfil de membro atualizado com sucesso', [
                'user_id' => $user->id,
                'member_id' => $member->id,
                'data' => $data
            ]);

            return redirect()->route('member.dashboard')->with('success', 'Perfil atualizado com sucesso.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar perfil', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Erro ao atualizar perfil: ' . $e->getMessage());
        }
    }
}
