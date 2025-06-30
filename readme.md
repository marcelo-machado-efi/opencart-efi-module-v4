# Plugin de Pagamento Efí para OpenCart 4

Este é um plugin oficial da Efí  desenvolvido para o OpenCart 4.1.0.1 com suporte ao PHP 8.1.  
Ele permite que lojistas aceitem pagamentos por **Pix**, **Cartão de Crédito**, **Boleto Bancário** e **Open Finance**, de forma integrada e segura, utilizando as APIs da Efí.

> 💡 **Importante:** Todos os tributos de configuração são obrigatórios para o correto funcionamento do plugin.  
> 📚 Consulte [nossa documentação](#) para instruções detalhadas sobre como obter os dados de autenticação.

---

## ⚙️ Configuração Geral

> ![Print da tela de configurações gerais](docs/config-geral.png)

| Campo                            | Descrição                                                                 |
|----------------------------------|---------------------------------------------------------------------------|
| **Client ID (Produção)**         | Identificador do cliente no ambiente de produção.                        |
| **Client Secret (Produção)**     | Chave secreta do cliente no ambiente de produção.                        |
| **Client ID (Sandbox)**          | Identificador do cliente no ambiente de testes (sandbox).                |
| **Client Secret (Sandbox)**      | Chave secreta do cliente no ambiente de testes (sandbox).                |
| **Código da Conta Efí**          | Código da conta fornecido pela Efí.                                      |
| **Ordenação**                    | Ordem de exibição das formas de pagamento na finalização do pedido.      |
| **Status do Pedido (Pago)**      | Status atribuído ao pedido após confirmação de pagamento.                |
| **Ambiente Sandbox**             | Define se o ambiente de testes será utilizado.                           |
| **Status do Módulo**             | Ativa ou desativa o módulo de pagamento Efí.                             |

---

## 💸 Configuração do Pix

> ![Print da tela de configuração Pix](docs/config-pix.png)

| Campo                   | Descrição                                                                 |
|-------------------------|---------------------------------------------------------------------------|
| **Chave Pix**           | Chave Pix cadastrada na conta da Efí.                                     |
| **Expiração da Cobrança** | Tempo de expiração em minutos da cobrança gerada.                        |
| **Certificado (.pfx)**  | Certificado digital utilizado na autenticação Pix.                        |
| **Desconto**            | Desconto aplicado para pagamentos via Pix (ex: `5%`, `10`).               |
| **MTLS**                | Define se a autenticação será mTLS.                                       |
| **Ativar Pix**          | Habilita ou desabilita o método de pagamento Pix.                         |

---

## 🧾 Configuração do Boleto

> ![Print da tela de configuração Boleto](docs/config-boleto.png)

| Campo                   | Descrição                                                                 |
|-------------------------|---------------------------------------------------------------------------|
| **Dias para vencimento**| Número de dias até o vencimento do boleto.                               |
| **Desconto**            | Valor de desconto oferecido para pagamento no boleto.                    |
| **Multa (%)**           | Percentual de multa após o vencimento.                                   |
| **Juros (%)**           | Percentual de juros diário após o vencimento.                            |
| **Mensagem no boleto**  | Texto personalizado exibido no boleto.                                   |
| **Enviar por e-mail**   | Define se o boleto será enviado automaticamente por e-mail.              |
| **Ativar Boleto**       | Ativa ou desativa o método de pagamento por boleto.                      |

---

## 🔐 Configuração do Open Finance

> ![Print da tela de configuração Open Finance](docs/config-open-finance.png)

| Campo                            | Descrição                                                                 |
|----------------------------------|---------------------------------------------------------------------------|
| **Chave Open Finance**           | Chave fornecida pela Efí para autenticação.                              |
| **Desconto**                     | Desconto aplicado para pagamentos via Open Finance.                      |
| **Certificado (.pfx)**           | Certificado digital para comunicação segura.                             |
| **Ativar Open Finance**          | Ativa ou desativa a opção de pagamento via Open Finance.                 |

---

## 🧰 Requisitos

- OpenCart **4.1.0.1**
- PHP **8.1** ou superior
- Conta ativa na [Efí](https://efipay.com.br)
- Certificados e chaves de API obtidos via [painel da Efí](https://efipay.com.br)

---

## 📝 Licença

Este projeto é disponibilizado sob a licença MIT.  
Veja o arquivo [LICENSE](LICENSE) para mais detalhes.

---

## ❓ Suporte

Para dúvidas, problemas ou sugestões, entre em contato com nosso suporte técnico via [documentação oficial](#) ou abra uma issue neste repositório.

