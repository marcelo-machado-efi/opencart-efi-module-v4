<?php

namespace Opencart\Catalog\Controller\Extension\Efi\Payment;

class EfiOpenFinanceRedirect extends \Opencart\System\Engine\Controller
{
    public function index(): void
    {
        try {
            $data = [];

            // Pega os parâmetros da URL
            $data['identificadorPagamento'] = $this->request->get['identificadorPagamento'] ?? '';
            $data['erro'] = $this->request->get['erro'] ?? '';
            $data['logo'] = $this->getImagePath('efi_logo.png');
            $data['mensagem '] = $this->language->get('text_description_open_finance_redirect');

            // Adiciona a URL de verificação do status
            $data['verificaStatusUrl'] = $this->url->link('extension/efi/payment/efiopenfinance.detailopenfinance', 'language=' . $this->config->get('config_language'));

            // Carrega header e footer
            $data['header'] = $this->load->controller('common/header');
            $data['footer'] = $this->load->controller('common/footer');

            // Define o tipo de conteúdo
            $this->response->addHeader('Content-Type: text/html; charset=UTF-8');

            // Renderiza a nova view
            $this->response->setOutput(
                $this->load->view('extension/efi/payment/efi_open_finance_redirect', $data)
            );
        } catch (\Exception $e) {
            $this->logError("Erro no processamento: " . $e->getMessage());

            $data['error'] = $e->getMessage();
            $data['header'] = $this->load->controller('common/header');
            $data['footer'] = $this->load->controller('common/footer');

            $this->response->addHeader('Content-Type: text/html; charset=UTF-8');
            $this->response->setOutput(
                $this->load->view('extension/efi/payment/efi_error_ajax', $data)
            );
        }
    }


    private function logError(string $message): void
    {
        $log = new \Opencart\System\Library\Log('efi_open_finance_redirect.log');
        $log->write($message);
    }

    private function getImagePath(string $imgName): string
    {
        return 'extension/efi/catalog/view/image/' . $imgName;
    }
}
