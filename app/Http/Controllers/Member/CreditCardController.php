<?php

namespace App\Http\Controllers\Member;

use App\Models\CreditCard;
use App\Models\Member;
use App\Services\AsaasService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CreditCardController extends Controller
{
    public function index(Member $member)
    {
        $cards = $member->creditCards;

        return view('credit-cards.index', compact('member', 'cards'));
    }

    public function create(Member $member)
    {
        return view('credit-cards.create', compact('member'));
    }

    public function store(Request $request, Member $member)
    {
        $request->validate([
            'creditCardToken' => 'required|string',
        ]);

        $asaas = new AsaasService();

        // Registrar cartão no Asaas
        $asaasCard = $asaas->storeCard([
            'customer' => $member->asaas_customer_id,
            'creditCardToken' => $request->creditCardToken,
        ]);

        // Salvar localmente
        CreditCard::create([
            'member_id' => $member->id,
            'asaas_card_id' => $asaasCard['id'],
            'brand' => $asaasCard['creditCardBrand'],
            'last_digits' => $asaasCard['creditCardNumber'],
            'expiration_month' => $asaasCard['creditCardExpiryMonth'],
            'expiration_year' => $asaasCard['creditCardExpiryYear'],
        ]);

        return redirect()->route('credit-cards.index', $member)->with('success', 'Cartão salvo com sucesso!');
    }

    public function destroy(Member $member, CreditCard $creditCard)
    {
        // Verifica se o cartão pertence ao membro
        if ($creditCard->member_id !== $member->id) {
            abort(403);
        }

        // (Opcional) Deletar do Asaas aqui se necessário

        $creditCard->delete();

        return redirect()->route('credit-cards.index', $member)->with('success', 'Cartão removido com sucesso.');
    }
}
