<?php

class ControllerAccountRecurring extends Controller
{
    public function index()
    {
        if (!$this->customer->isLogged()) {
            $this->session->data['redirect'] = $this->url->link('account/recurring');
            $this->response->redirect($this->url->link('account/login'));
        }

        $this->load->language('account/recurring');
        $this->document->setTitle($this->language->get('heading_title'));
        $url = '';

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $data['breadcrumbs'] = [];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_account'),
            'href' => $this->url->link('account/account')
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('account/recurring', $url)
        ];

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }

        $data['recurrings'] = [];

        $this->load->model('account/recurring');
        $recurringTotal = $this->model_account_recurring->getTotalOrderRecurrings();
        $results = $this->model_account_recurring->getOrderRecurrings(($page - 1) * 10, 10);
        foreach ($results as $result) {
            if ($result['status']) {
                $status = $this->language->get('text_status_' . $result['status']);
            } else {
                $status = '';
            }
            $data['recurrings'][] = [
                'order_recurring_id' => $result['order_recurring_id'],
                'product' => $result['product_name'],
                'status' => $status,
                'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
                'view' => $this->url->link(
                    'account/recurring/info',
                    'order_recurring_id=' . $result['order_recurring_id']
                ),
            ];
        }

        $pagination = new Pagination();
        $pagination->total = $recurringTotal;
        $pagination->page = $page;
        $pagination->limit = 10;
        $pagination->text = $this->language->get('text_pagination');
        $pagination->url = $this->url->link('account/recurring', 'page={page}');
        $data['pagination'] = $pagination->render();
        $data['continue'] = $this->url->link('account/account');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('account/recurring_list', $data));
    }

    public function info()
    {
        $this->load->language('account/recurring');

        if (isset($this->request->get['order_recurring_id'])) {
            $orderRecurringId = $this->request->get['order_recurring_id'];
        } else {
            $orderRecurringId = 0;
        }

        if (!$this->customer->isLogged()) {
            $this->session->data['redirect'] = $this->url->link(
                'account/recurring/info',
                'order_recurring_id=' . $orderRecurringId
            );
            $this->response->redirect($this->url->link('account/login'));
        }

        $this->load->model('account/recurring');
        $recurringInfo = $this->model_account_recurring->getOrderRecurring($orderRecurringId);

        if ($recurringInfo) {
            $this->document->setTitle($this->language->get('text_recurring'));
            $url = '';
            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $data['breadcrumbs'] = [];

            $data['breadcrumbs'][] = [
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/home')
            ];

            $data['breadcrumbs'][] = [
                'text' => $this->language->get('text_account'),
                'href' => $this->url->link('account/account')
            ];

            $data['breadcrumbs'][] = [
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('account/recurring', $url)
            ];

            $data['breadcrumbs'][] = [
                'text' => $this->language->get('text_recurring'),
                'href' => $this->url->link(
                    'account/recurring/info',
                    'order_recurring_id=' . $this->request->get['order_recurring_id'] . $url
                )
            ];

            $data['order_recurring_id'] = (int)$this->request->get['order_recurring_id'];
            $data['date_added'] = date(
                $this->language->get('date_format_short'),
                strtotime($recurringInfo['date_added'])
            );

            if ($recurringInfo['status']) {
                $data['status'] = $this->language->get('text_status_' . $recurringInfo['status']);
            } else {
                $data['status'] = '';
            }

            $data['payment_method'] = $recurringInfo['payment_method'];
            $data['order_id'] = $recurringInfo['order_id'];
            $data['product_name'] = $recurringInfo['product_name'];
            $data['product_quantity'] = $recurringInfo['product_quantity'];
            $data['recurring_description'] = $recurringInfo['recurring_description'];
            $data['reference'] = $recurringInfo['reference'];

            // Transactions
            $data['transactions'] = [];
            $results = $this->model_account_recurring->getOrderRecurringTransactions(
                $this->request->get['order_recurring_id']
            );
            foreach ($results as $result) {
                $data['transactions'][] = array(
                    'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
                    'type' => $result['type'],
                    'amount' => $this->currency->format($result['amount'], $recurringInfo['currency_code'])
                );
            }

            $data['order'] = $this->url->link('account/order/info', 'order_id=' . $recurringInfo['order_id']);
            $data['product'] = $this->url->link(
                'product/product',
                'product_id=' . $recurringInfo['product_id']
            );
            $data['recurring'] = $this->load->controller('extension/recurring/' . $recurringInfo['payment_code']);
            $data['column_left'] = $this->load->controller('common/column_left');
            $data['column_right'] = $this->load->controller('common/column_right');
            $data['content_top'] = $this->load->controller('common/content_top');
            $data['content_bottom'] = $this->load->controller('common/content_bottom');
            $data['footer'] = $this->load->controller('common/footer');
            $data['header'] = $this->load->controller('common/header');

            $this->response->setOutput($this->load->view('account/recurring_info', $data));
        } else {
            $this->document->setTitle($this->language->get('text_recurring'));
            $data['breadcrumbs'] = [];
            $data['breadcrumbs'][] = [
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/home')
            ];

            $data['breadcrumbs'][] = [
                'text' => $this->language->get('text_account'),
                'href' => $this->url->link('account/account')
            ];

            $data['breadcrumbs'][] = [
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('account/recurring')
            ];

            $data['breadcrumbs'][] = [
                'text' => $this->language->get('text_recurring'),
                'href' => $this->url->link(
                    'account/recurring/info',
                    'order_recurring_id=' . $orderRecurringId
                )
            ];

            $data['continue'] = $this->url->link('account/recurring');
            $data['column_left'] = $this->load->controller('common/column_left');
            $data['column_right'] = $this->load->controller('common/column_right');
            $data['content_top'] = $this->load->controller('common/content_top');
            $data['content_bottom'] = $this->load->controller('common/content_bottom');
            $data['footer'] = $this->load->controller('common/footer');
            $data['header'] = $this->load->controller('common/header');

            $this->response->setOutput($this->load->view('error/not_found', $data));
        }
    }
}
