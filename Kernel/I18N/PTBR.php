<?php

namespace PlugHacker\PlugCore\Kernel\I18N;

use PlugHacker\PlugCore\Kernel\Abstractions\AbstractI18NTable;

class PTBR extends AbstractI18NTable
{
    protected function getTable()
    {
        return [
            'Invoice created: #%s.' => 'Invoice criada: #%s',
            'Invoice canceled: #%s.' => 'Invoice cancelada: #%s',
            'Webhook received: %s %s.%s' => 'Webhook recebido: %s %s.%s',
            'Order paid.' => 'Pedido pago.',
            'Order created at Plug. Id: %s' => 'Pedido criado na Plug. Id %s',
            'Order pending at Plug. Id: %s' => 'Pedido pendente na Plug. Id %s',
            'Order waiting for online retries at Plug.' => 'Pedido aguardando por retentativas online na Plug.',
            'Order canceled.' => 'Pedido cancelado.',
            'Payment received: %.2f' => 'Pagamento recebido: %.2f',
            'Canceled amount: %.2f' => 'Quantia cancelada: %.2f',
            'Refunded amount: %.2f' => 'Quantia estornada: %.2f',
            'Partial Payment' => 'Pagamento Parcial',
            'Charge canceled.' => 'Cobrança cancelada.',
            'Charge not found' => 'Cobrança não encontrada',
            'Creditmemo created: #%s.' => 'Creditmemo criado: #%s.',
            'until now' => 'até agora',
            'Extra amount paid: %.2f' => "Quantia extra paga: %.2f",
            "Order '%s' canceled at Plug" => "Pedido '%s' cancelado na Plug",
            'Remaining amount: %.2f' => "Quantidade faltante: %.2f",
            "Some charges couldn't be canceled at Plug. Reasons:" => "Algumas cobranças não puderam ser canceladas na Plug. Razões:",
            "without interest" => "sem juros",
            "with %.2f%% of interest" => "com %.2f%% de juros",
            "%dx of %s %s (Total: %s)" => "%dx de %s %s (Total: %s)",
            "Order payment failed" => "Pagamento do pedido falhou",
            "The order will be canceled" => "O pedido será cancelado",
            "An error occurred when trying to create the order. Please try again. Error Reference: %s" => 'Ocorreu um erro ao tentar criar o pedido. Por favor, tente novamente. Referência do erro: %s',
            "Can't cancel current order. Please cancel it by Plug panel" => "Não foi possível cancelar o pedido. Por favor, realize o cancelamento no portal Plug.",
            "Charge canceled with success" => "Charge cancelada com sucesso",
            'Invalid address. Please fill the street lines and try again.' => 'Endereço inválido. Preencha rua, número e bairro e tente novamente.',
            "The informed card couldn't be deleted." => "O cartão informado não pode ser deletado.",
            "The card '%s' was deleted." => "O cartão '%s' foi deletado.",
            "The card '%s' couldn't be deleted." => "O cartão '%s' não pôde ser deletado.",
            "Different paid amount for this invoice. Paid value: %.2f" => "Esta Invoice foi paga com um valor diferente do Grand Total do pedido. Valor pago: %.2f",
            "The %s should not be empty!" => "O campo %s não deve estar vazio",
            "street" => "rua",
            "number" => "número",
            "neighborhood" => "bairro",
            "city" => "cidade",
            "country" => "país",
            "state" => "estado",
            "document" => "CPF",
            "Can't create order." => "Não foi possível criar o pedido",
            'Invalid address configuration. Please fill the address configuration on admin panel.' => 'Configurações de endereço inválidas. Preencha as configurações de endereço no painel de administração',
            'week' => "semana",
            'weeks' => "semanas",
            'month' => "mês",
            'months' => "meses",
            'year' => "ano",
            'years' => "anos",
            'discount' => "desconto",
            'Credit Card' => "Cartão de Crédito",
            'Subscription invoice paid.' => 'Fatura de assinatura paga.',
            'invoice' => 'fatura',
            'Subscription canceled with success!' => "Assinatura cancelada com sucesso!",
            'Error on cancel subscription' => "Erro ao cancelar a assinatura",
            'Subscription not found' => "Assinatura não encontrada",
            'Subscription already canceled' => "Assinatura já está cancelada",
            'monthly' => 'Mensal',
            'bimonthly' => 'Bimestral',
            'quarterly' => 'Trimestral',
            'yearly' => 'Anual',
            'biennial' => 'Bienal',
            'Subscription created at Plug. Id: %s' => 'Assinatura criada na Plug. Id %s',
            'weekly' => 'Semanal',
            'semiannual' => 'Semestral',
            'Invalid address configuration. Please fill the address configuration on admin panel.' => 'Configurações de endereço inválido. Preencha as configurações de endereço no painel de administração',
            'New order status: %s' => 'Novo status do pedido: %s',
            'Subscription invoice created: %.2f' => 'Fatura de assinatura criada: %.2f',
            'Url boleto' => 'Boleto url',
            'You can only add two or more subscriptions to your cart that have the same payment method (credit card or boleto) and same frequency (monthly, annual, etc)' => 'Você só pode adicionar duas ou mais assinaturas que possuam o mesmo tipo de pagamento (cartão e ou boleto) e mesma frequência (mensal, semestral, anual...).',
            "It's not possible to have any other product with a product plan" => 'Não é possível ter nenhum outro tipo de produto no carrinho, junto com um produto plano',
            'You must have only one product plan in the cart' => 'Você só pode ter um produto plano no carrinho',
            'Plug module should be configured on Websites scope, please change to website scope to apply these changes' => 'O módulo da Plug deve ser configurado no escopo de Websites/Default, favor mudar para o escopo de Websites/Default para aplicar as mudanças.',
            'Antifraud aproved' => 'Aprovado no antifraude',
            'Antifraud reproved' => 'Reprovado no antifraude',
            'Antifraud pending' => 'Analise pendente no antifraude',
            'Waiting manual analise in antifraud' => 'Aguardando análise manual em antifraude',
            'card_not_supported' => 'O cartão não suporta este tipo de compra, use a função de débito.',
            'expired_card' => 'A data de validade do cartão é inválida, verifique os detalhes do seu cartão.',
            'fraud_confirmed' => 'A cobrança foi recusada por fraude confirmada, transação não permitida para cartão, não tente novamente.',
            'fraud_suspect' => 'A cobrança foi recusada por suspeita de fraude, entre em contato com a central do cartão.',
            'generic' => 'O cartão foi recusado por motivo desconhecido, entre em contato com a central do cartão.',
            'insufficient_funds' => 'O cartão não tem fundos insuficientes, não permitido.',
            'invalid_amount' => 'O valor da cobrança não é válido ou excedeu o máximo permitido, valor da transação não permitido.',
            'invalid_cvv' => 'O código de segurança (CVV) é inválido.',
            'invalid_data' => 'O cartão foi recusado por dados inválidos, verifique os detalhes do seu cartão.',
            'invalid_installment' => 'A cobrança foi recusada devido a um número inválido de parcelas.',
            'invalid_merchant' => 'A cobrança foi recusada porque o comerciante não é válido, conta de origem inválida.',
            'invalid_number' => 'O número do cartão é inválido, verifique os detalhes do seu cartão.',
            'invalid_pin' => 'O cartão foi recusado porque o PIN é inválido',
            'issuer_not_available' => 'Não foi possível entrar em contato com o emissor do cartão, cobrança não autorizada, dados do cartão inválidos.',
            'lost_card' => 'O cartão foi recusado porque o cartão foi reportado como perdido, transação não permitida, não tente novamente.',
            'not_permitted' => 'A cobrança não é permitida no cartão, a transação não é permitida no cartão.',
            'pickup_card' => 'O cartão não pode ser usado para fazer essas cobranças, entre em contato com a central do cartão.',
            'pin_try_exceeded' => 'O cartão foi recusado porque o número máximo de tentativas de PIN foi excedido, tentativas excedidas, entre em contato com a central do cartão',
            'restricted_card' => 'O cartão não pode ser usado para fazer esta cobrança, desbloqueie o cartão.',
            'security_violation' => 'O cartão foi recusado por um motivo desconhecido, verifique os detalhes do seu cartão.',
            'service_not_allowed' => 'O cartão foi recusado porque não suporta cobrança internacional, o cartão não permite transações internacionais.',
            'stolen_card' => 'O cartão foi recusado porque o cartão foi reportado como roubado, transação não permitida, não tente novamente.',
            'transaction_not_allowed' => 'O cartão foi recusado por motivo desconhecido, erro de cartão.',
            'try_again' => 'O cartão foi recusado por motivo desconhecido, refaça a transação.'
        ];
    }
}
