<?php

namespace Opencart\Admin\Controller\Extension\Efi\Payment;

class Efi extends \Opencart\System\Engine\Controller
{
	public function install(): void
	{
		$this->load->model('user/user_group');
		$group_id = $this->user->getGroupId();

		$routes = [
			'extension/efi/common/upload',
			'extension/efi/common/save',
		];

		foreach ($routes as $route) {
			$this->model_user_user_group->addPermission($group_id, 'access', $route);
			$this->model_user_user_group->addPermission($group_id, 'modify', $route);
		}
	}

	public function uninstall(): void
	{
		$this->load->model('user/user_group');
		$group_id = $this->user->getGroupId();

		$routes = [
			'extension/efi/common/upload',
			'extension/efi/common/save',
		];

		foreach ($routes as $route) {
			$this->model_user_user_group->removePermission($group_id, 'access', $route);
			$this->model_user_user_group->removePermission($group_id, 'modify', $route);
		}
	}

	public function index(): void
	{
		$this->load->language('extension/efi/payment/efi');
		$this->load->model('extension/efi/payment/efi_config');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = [
			[
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
			],
			[
				'text' => $this->language->get('text_extension'),
				'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment')
			],
			[
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/efi/payment/config', 'user_token=' . $this->session->data['user_token'])
			]
		];

		$data['options']                     = $this->model_extension_efi_payment_efi_config->getConfig($this->language);
		$data['save']                        = $this->url->link('extension/efi/common/save', 'user_token=' . $this->session->data['user_token']);
		$data['upload']                      = $this->url->link('extension/efi/common/upload', 'user_token=' . $this->session->data['user_token']);
		$data['back']                        = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment');
		$data['payment_efi_order_status_id'] = $this->config->get('payment_efi_order_status_id');

		$this->load->model('localisation/order_status');
		$data['order_statuses']       = $this->model_localisation_order_status->getOrderStatuses();
		$data['payment_efi_status']   = $this->config->get('payment_efi_status');
		$data['payment_efi_sort_order'] = $this->config->get('payment_efi_sort_order');

		$data['header']      = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer']      = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/efi/payment/efi', $data));
	}
}
