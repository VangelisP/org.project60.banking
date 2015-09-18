<?php
/*-------------------------------------------------------+
| Project 60 - CiviBanking                               |
| Copyright (C) 2013-2014 SYSTOPIA                       |
| Author: B. Endres (endres -at- systopia.de)            |
| http://www.systopia.de/                                |
+--------------------------------------------------------+
| This program is released as free software under the    |
| Affero GPL v3 license. You can redistribute it and/or  |
| modify it under the terms of this license which you    |
| can read by viewing the included agpl.txt or online    |
| at www.gnu.org/licenses/agpl.html. Removal of this     |
| copyright header is strictly prohibited without        |
| written permission from the original author(s).        |
+--------------------------------------------------------*/

    
require_once 'CRM/Core/Page.php';

class CRM_Banking_Page_AccountsTab extends CRM_Core_Page {
  function run() {
    if (isset($_REQUEST['cid'])) {
        $contact_id = (int) $_REQUEST['cid'];
        $bank_accounts = array();

        $bank_account = new CRM_Banking_BAO_BankAccount();
        $bank_account->contact_id = $contact_id;
        $bank_account->find();

        while ($bank_account->fetch()) {
            $bank_account_data = $bank_account->toArray();
            $bank_account_data['references']  = $bank_account->getReferences();
            $bank_account_data['data_parsed'] = json_decode($bank_account->data_parsed, true);
            $bank_accounts[$bank_account->id] = $bank_account_data;
        }

        $this->assign('results', $bank_accounts);
        $this->assign('contact_id', $contact_id);

        // add all account types
        $option_group = civicrm_api3('OptionGroup', 'getsingle', array('name' => 'civicrm_banking.reference_types'));
        $result =       civicrm_api3('OptionValue', 'get', array('option_group_id' => $option_group['id'], 'is_reserved' => 0));
        $this->assign('reference_types', $result['values']);
    }
    parent::run();
  }
}
