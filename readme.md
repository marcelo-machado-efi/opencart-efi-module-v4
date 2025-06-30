# Plugin de Pagamento Efí para OpenCart 4

Este plugin oficial da **Efí (antiga Gerencianet)** permite oferecer múltiplas formas de pagamento em sua loja OpenCart 4.1.0.1 utilizando PHP 8.1: **Pix**, **Boleto Bancário**, **Cartão de Crédito** e **Open Finance**.

> ℹ️ Todos os campos obrigatórios devem ser preenchidos corretamente para que o plugin funcione.  
> 📘 Veja como obter suas credenciais e certificados na [documentação oficial da Efí](https://dev.efipay.com.br/docs).

---

## 🚀 Instalação do Plugin

1. Acesse o painel administrativo do OpenCart.
2. Vá até o menu `Extensões > Instalador de Extensões`.
3. Clique em **Enviar** e selecione o arquivo `efi.ocmod.zip` localizado na pasta `upload` deste repositório.
4. Após o upload, vá para `Extensões > Extensões`, escolha `Pagamentos` no seletor.
5. Procure por **Efí Bank** e clique em **Instalar**.
6. Após instalar, clique em **Editar** para configurar as credenciais, certificados e demais campos obrigatórios.

> 📌 **Importante**: após configurar o plugin, vá até `Extensões > Modificações` e clique em **Atualizar** (ícone de recarregar) para aplicar as alterações.

---

## ⚙️ Configurações Gerais

> ![Print da tela de configurações gerais](docs/config-geral.png)

| Campo                              | Descrição |
|------------------------------------|-----------|
| **Client_Id Produção**             | [🔗 Saiba como obter](https://dev.efipay.com.br/docs/api-cobrancas/credenciais#criar-uma-aplica%C3%A7%C3%A3o-ou-configurar-uma-j%C3%A1-existente) |
| **Client_Secret Produção**         | Mesmo link acima. |
| **Client_Id Desenvolvimento**      | Mesmo link acima. |
| **Client_Secret Desenvolvimento**  | Mesmo link acima. |
| **Identificador da conta**         | Código único da conta na Efí. |
| **Ordem de Exibição**              | Ordem em que a forma de pagamento será listada no checkout. |
| **Status do pedido ao finalizar o pagamento** | Define o status que será atribuído ao pedido após pagamento. |
| **Ativar ambiente de teste**       | Habilita o modo sandbox para testes. |
| **Ativar Plugin**                  | Liga ou desliga o uso do plugin. |

---

## 💸 Configuração do Pix

> ![Print da tela de configuração Pix](docs/config-pix.png)

| Campo                    | Descrição |
|--------------------------|-----------|
| **Chave Pix**            | Chave cadastrada no aplicativo da Efí. |
| **Tempo de expiração da cobrança** | Tempo em horas que a cobrança ficará disponível. |
| **Certificado**          | Arquivo `.pfx` gerado via painel Efí. [Ver instruções](https://dev.efipay.com.br/docs/api-pix/credenciais#gerando-um-certificado-p12) |
| **Desconto**             | Valor fixo ou percentual. `10` = R$10, `5%` = percentual. |
| **Validar mTLS**         | Veja [aqui](https://dev.efipay.com.br/docs/api-pix/webhooks#entendendo-o-padr%C3%A3o-mtls) se sua conta exige esse padrão. |
| **Ativar**               | Ativa o pagamento por Pix. |

---

## 🧾 Configuração do Boleto

> ![Print da tela de configuração Boleto](docs/config-boleto.png)

| Campo                    | Descrição |
|--------------------------|-----------|
| **Dias para vencimento do boleto** | Número de dias após emissão para o vencimento. |
| **Desconto**             | Valor fixo ou percentual. `5` ou `10%`. |
| **Enviar e-mail para o cliente final** | Se ativo, o boleto será enviado por e-mail. |
| **Configuração de multa** | Valor da multa após vencimento. Ex: `200` = 2%. |
| **Configuração de juros** | Valor de juros por dia. Ex: `33` = 0,033% ao dia. |
| **Mensagem no boleto**   | Texto opcional que aparecerá impresso. |
| **Ativar**               | Ativa o pagamento por boleto bancário. |

---

## 💳 Cartão de Crédito

> ![Print da tela de configuração Cartão](docs/config-cartao.png)

| Campo       | Descrição |
|-------------|-----------|
| **Ativar**  | Ativa o pagamento por cartão de crédito. |

---

## 🔐 Open Finance

> ![Print da tela de configuração Open Finance](docs/config-open-finance.png)

| Campo                             | Descrição |
|-----------------------------------|-----------|
| **Chave Pix para recebimento**    | Chave que receberá os pagamentos Open Finance. |
| **Desconto**                      | Valor fixo ou percentual. |
| **Certificado**                   | Certificado digital `.pfx`. |
| **Ativar**                        | Habilita a opção de Open Finance. |

---

## 📄 Changelog

Veja todas as mudanças no [CHANGELOG.md](CHANGELOG.md)

---

## ✅ Requisitos

- OpenCart `4.1.0.1`
- PHP `8.1` ou superior
- Conta ativa na [Efí](https://efipay.com.br)
- Certificados e chaves de API (conforme documentação)

---

## 📄 Licença

Distribuído sob a licença MIT. Veja mais em [LICENSE](LICENSE).

---

## 🛠 Suporte

Para dúvidas, abra uma _issue_ aqui no GitHub ou consulte a [documentação da Efí](https://dev.efipay.com.br/docs).
