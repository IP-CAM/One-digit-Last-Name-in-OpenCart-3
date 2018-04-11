<?php
/**
 * @package       REVE Chat
 * @license       GNU/GPL 2 or later
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307,USA.
 *
 * The "GNU General Public License" (GPL) is available at
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 */

class ControllerExtensionModuleZevioo extends Controller {
    private $error = array();
	public function install() {
        $this->load->model('setting/event');
		//$this->model_setting_event->addEvent('zevioo_add_order', 'catalog/model/checkout/order/addOrder/after', 'extension/module/zevioo/eventAddOrder');
		//$this->model_setting_event->addEvent('zevioo_after_order', 'catalog/model/checkout/order/after', 'extension/module/zevioo/eventAfterAddOrder');
		$this->model_setting_event->addEvent('zevioo_add_edit_order', 'catalog/model/checkout/order/addOrderHistory/after', 'extension/module/zevioo/eventAddEditOrder');
	}
	
	public function uninstall() {
        $this->load->model('setting/event');
        $this->model_setting_event->deleteEventByCode('zevioo_add_edit_order');
		//$this->model_setting_event->deleteEventByCode('zevioo_after_order');
		//$this->model_setting_event->deleteEventByCode('zevioo_cancel_order');
    }
	
    public function index() {
        // Language
        $this->load->language('extension/module/zevioo');

        // Page Title
        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('module_zevioo', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}
		
		if (isset($this->error['username'])) {
			$data['error_username'] = $this->error['username'];
		} else {
			$data['error_username'] = '';
		}

		if (isset($this->error['password'])) {
			$data['error_password'] = $this->error['password'];
		} else {
			$data['error_password'] = '';
		}

        // BreadCrumbs
        $data['breadcrumbs'] = array(
            array(
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/dashboard', 'user_token='.$this->session->data['user_token'], true),
                'separator' => false
            ),
            array(
                'text' => $this->language->get('text_extension'),
                'href' => $this->url->link('extension/extension', 'user_token='.$this->session->data['user_token'], true),
                'separator' => '::'
            ),
            array(
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('extension/module/zevioo', 'user_token='.$this->session->data['user_token'], true),
                'separator' => '::'
            )
        );


        $text = array('heading_title',
            'text_username',
            'text_password',
			'text_canceled_status'
        );

        foreach($text as $key){
            $data[$key] = $this->language->get($key);
        }


        if (isset($this->request->post['module_zevioo_username'])) {
			$data['zevioo_username'] = $this->request->post['module_zevioo_username'];
		} else {
			$data['zevioo_username'] = $this->config->get('module_zevioo_username');
		}
		if (isset($this->request->post['module_zevioo_password'])) {
			$data['zevioo_password'] = $this->request->post['module_zevioo_password'];
		} else {
			$data['zevioo_password'] = $this->config->get('module_zevioo_password');
		}
		
		if (isset($this->request->post['module_zevioo_status'])) {
			$data['module_zevioo_status'] = $this->request->post['module_zevioo_status'];
		} else {
			$data['module_zevioo_status'] = $this->config->get('module_zevioo_status');
		}
		
		if (isset($this->request->post['module_zevioo_canceled_status_id'])) {
			$data['module_zevioo_canceled_status_id'] = $this->request->post['module_zevioo_canceled_status_id'];
		} else {
			$data['module_zevioo_canceled_status_id'] = $this->config->get('module_zevioo_canceled_status_id');
		}
		
		$this->load->model('localisation/order_status');
		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $data['user_token'] = $this->session->data['user_token'];
        
        // Buttons
        $data['action'] = $this->url->link('extension/module/zevioo', 'user_token='.$this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
		
        // Display Page
        $this->response->setOutput($this->load->view('extension/module/zevioo', $data));

    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/zevioo')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
		
		if (!$this->request->post['module_zevioo_username']) {
			$this->error['username'] = $this->language->get('error_username');
		}

		if (!$this->request->post['module_zevioo_password']) {
			$this->error['password'] = $this->language->get('error_password');
		}

        return !$this->error;
    }
}