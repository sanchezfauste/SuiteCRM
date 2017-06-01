<?php
/**
 *
 * SugarCRM Community Edition is a customer relationship management program developed by
 * SugarCRM, Inc. Copyright (C) 2004-2013 SugarCRM Inc.
 *
 * SuiteCRM is an extension to SugarCRM Community Edition developed by SalesAgility Ltd.
 * Copyright (C) 2011 - 2017 SalesAgility Ltd.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY SUGARCRM, SUGARCRM DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 *
 * You can contact SugarCRM, Inc. headquarters at 10050 North Wolfe Road,
 * SW2-130, Cupertino, CA 95014, USA. or at email address contact@sugarcrm.com.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * SugarCRM" logo and "Supercharged by SuiteCRM" logo. If the display of the logos is not
 * reasonably feasible for  technical reasons, the Appropriate Legal Notices must
 * display the words  "Powered by SugarCRM" and "Supercharged by SuiteCRM".
 */

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

// XSS rules

if ($_REQUEST['action'] === 'ComposeView') {
    $GLOBALS['sugar_config']['http_referer']['actions'][] = 'ComposeView';
}

if ($_REQUEST['action'] === 'Popup') {
    $GLOBALS['sugar_config']['http_referer']['actions'][] = 'Popup';
}

if ($_REQUEST['action'] === 'GetFolders') {
    $GLOBALS['sugar_config']['http_referer']['actions'][] = 'GetFolders';
}


if ($_REQUEST['action'] === 'CheckEmail') {
    $GLOBALS['sugar_config']['http_referer']['actions'][] = 'CheckEmail';
}


if ($_REQUEST['action'] === 'ImportAndShowDetailView') {
    $GLOBALS['sugar_config']['http_referer']['actions'][] = 'ImportAndShowDetailView';
}

if ($_REQUEST['action'] === 'GetCurrentUserID') {
    $GLOBALS['sugar_config']['http_referer']['actions'][] = 'GetCurrentUserID';
}

if ($_REQUEST['action'] === 'DisplayDetailView') {
    $GLOBALS['sugar_config']['http_referer']['actions'][] = 'DisplayDetailView';
}

if ($_REQUEST['action'] === 'ImportFromListView') {
    $GLOBALS['sugar_config']['http_referer']['actions'][] = 'ImportFromListView';
}

if ($_REQUEST['action'] === 'GetFromFields') {
    $GLOBALS['sugar_config']['http_referer']['actions'][] = 'GetFromFields';
}

if ($_REQUEST['action'] === 'GetComposeViewFields') {
    $GLOBALS['sugar_config']['http_referer']['actions'][] = 'GetComposeViewFields';
}

if ($_REQUEST['action'] === 'SaveDraft') {
    $GLOBALS['sugar_config']['http_referer']['actions'][] = 'SaveDraft';
}

if ($_REQUEST['action'] === 'DetailDraftView') {
    $GLOBALS['sugar_config']['http_referer']['actions'][] = 'DetailDraftView';
}

if ($_REQUEST['action'] === 'ReplyTo') {
    $GLOBALS['sugar_config']['http_referer']['actions'][] = 'ReplyTo';
}


if ($_REQUEST['action'] === 'ReplyToAll') {
    $GLOBALS['sugar_config']['http_referer']['actions'][] = 'ReplyToAll';
}


if ($_REQUEST['action'] === 'Forward') {
    $GLOBALS['sugar_config']['http_referer']['actions'][] = 'Forward';
}


class EmailsController extends SugarController
{
    /**
     * @var Email $bean ;
     */
    public $bean;

    /**
     * @see EmailsController::composeBean()
     */
    const COMPOSE_BEAN_MODE_UNDEFINED = 0;

    /**
     * @see EmailsController::composeBean()
     */
    const COMPOSE_BEAN_MODE_REPLY_TO = 1;

    /**
     * @see EmailsController::composeBean()
     */
    const COMPOSE_BEAN_MODE_REPLY_TO_ALL = 2;

    /**
     * @see EmailsController::composeBean()
     */
    const COMPOSE_BEAN_MODE_FORWARD = 3;

    /**
     * @see EmailsViewList
     */
    public function action_index()
    {
        $this->view = 'list';
    }

    /**
     * @see EmailsViewDetaildraft
     */
    public function action_DetailDraftView()
    {
        $this->view = 'detaildraft';
    }

    /**
     * @see EmailsViewCompose
     */
    public function action_ComposeView()
    {
        $this->view = 'compose';
    }

    /**
     * @see EmailsViewSendemail
     */
    public function action_send()
    {
        $this->bean = $this->bean->populateBeanFromRequest($this->bean, $_REQUEST);
        $this->bean->save();

        $this->bean->handleMultipleFileAttachments();

        if ($this->bean->send()) {
            $this->bean->status = 'sent';
            $this->bean->save();
        } else {
            $this->bean->status = 'sent_error';
        }

        $this->view = 'sendemail';
    }


    /**
     * @see EmailsViewCompose
     */
    public function action_SaveDraft()
    {
        $this->bean = $this->bean->populateBeanFromRequest($this->bean, $_REQUEST);
        $this->bean->mailbox_id = $_REQUEST['inbound_email_id'];
        $this->bean->status = 'draft';
        $this->bean->save();
        $this->bean->handleMultipleFileAttachments();
        $this->view = 'savedraftemail';
    }

    /**
     * @see EmailsViewPopup
     */
    public function action_Popup()
    {
        $this->view = 'popup';
    }

    /**
     * Gets the values of the "from" field
     *
     */
    public function action_GetFromFields()
    {
        global $current_user;
        $email = new Email();
        $email->email2init();
        $ie = new InboundEmail();
        $ie->email = $email;
        $accounts = $ieAccountsFull = $ie->retrieveAllByGroupIdWithGroupAccounts($current_user->id);
        $data = array();
        foreach ($accounts as $inboundEmailId => $inboundEmail) {
            $storedOptions = unserialize(base64_decode($inboundEmail->stored_options));
            $data[] = array(
                'type' => $inboundEmail->module_name,
                'id' => $inboundEmail->id,
                'attributes' => array(
                    'from' => $storedOptions['from_addr']
                )
            );
        }


        echo json_encode(array('data' => $data));
        $this->view = 'ajax';
    }

    public function action_CheckEmail()
    {
        $inboundEmail = new InboundEmail();
        $inboundEmail->syncEmail();

        echo json_encode(array('response' => array()));
        $this->view = 'ajax';
    }

    /**
     * Used to list folders in the list view
     */
    public function action_GetFolders()
    {
        require_once 'include/SugarFolders/SugarFolders.php';
        global $current_user, $mod_strings;
        $email = new Email();
        $email->email2init();
        $ie = new InboundEmail();
        $ie->email = $email;
        $GLOBALS['log']->debug('********** EMAIL 2.0 - Asynchronous - at: refreshSugarFolders');
        $rootNode = new ExtNode('', '');
        $folderOpenState = $current_user->getPreference('folderOpenState', 'Emails');
        $folderOpenState = empty($folderOpenState) ? '' : $folderOpenState;

        try {
            $ret = $email->et->folder->getUserFolders($rootNode, sugar_unserialize($folderOpenState), $current_user,
                true);
            $out = json_encode(array('response' => $ret));
        } catch (SugarFolderEmptyException $e) {
            $GLOBALS['log']->fatal($e->getMessage());
            $out = json_encode(array('errors' => array($mod_strings['LBL_ERROR_NO_FOLDERS'])));
        }

        echo $out;
        $this->view = 'ajax';
    }


    /**
     * @see EmailsViewDetailnonimported
     */
    public function action_DisplayDetailView()
    {
        global $db;
        $emails = BeanFactory::getBean("Emails");
        $result = $emails->get_full_list('', "uid = '{$db->quote($_REQUEST['uid'])}'");
        if (empty($result)) {
            $this->view = 'detailnonimported';
        } else {
            header('location:index.php?module=Emails&action=DetailView&record=' . $result[0]->id);
        }
    }

    /**
     * @see EmailsViewDetailnonimported
     */
    public function action_ImportAndShowDetailView()
    {
        global $db;
        if (isset($_REQUEST['inbound_email_record']) && !empty($_REQUEST['inbound_email_record'])) {
            $inboundEmail = BeanFactory::getBean('InboundEmail', $db->quote($_REQUEST['inbound_email_record']));
            $inboundEmail->connectMailserver();
            $importedEmailId = $inboundEmail->returnImportedEmail($_REQUEST['msgno'], $_REQUEST['uid']);
            if ($importedEmailId !== false) {
                header('location:index.php?module=Emails&action=DetailView&record=' . $importedEmailId);
            }
        } else {
            // When something fail redirect user to index
            header('location:index.php?module=Emails&action=index');
        }

    }

    public function action_GetCurrentUserID()
    {
        global $current_user;
        echo json_encode(array("response" => $current_user->id));
        $this->view = 'ajax';
    }

    public function action_ImportFromListView()
    {
        global $db;
        $response = false;

        if (isset($_REQUEST['inbound_email_record']) && !empty($_REQUEST['inbound_email_record'])) {
            $inboundEmail = BeanFactory::getBean('InboundEmail', $db->quote($_REQUEST['inbound_email_record']));
            if (isset($_REQUEST['folder']) && !empty($_REQUEST['folder'])) {
                $inboundEmail->mailbox = $_REQUEST['folder'];
            }
            $inboundEmail->connectMailserver();

            if (isset($_REQUEST['all']) && $_REQUEST['all'] === 'true') {
                // import all in folder
                $inboundEmail->importAllFromFolder();
                $response = true;
            } else {
                foreach ($_REQUEST['uid'] as $uid) {
                    $result = $inboundEmail->returnImportedEmail($_REQUEST['msgno'], $uid);
                    $response = true;
                }
            }

        } else {
            $GLOBALS['log']->fatal('EmailsController::action_ImportFromListView() missing inbound_email_record');
        }
        echo json_encode(array('response' => $response));
        $this->view = 'ajax';
    }

    public function action_ReplyTo()
    {
        $this->composeBean($_REQUEST, self::COMPOSE_BEAN_MODE_REPLY_TO);
        $this->view = 'compose';
    }

    public function action_ReplyToAll()
    {
        $this->composeBean($_REQUEST, self::COMPOSE_BEAN_MODE_REPLY_TO_ALL);
        $this->view = 'compose';
    }

    public function action_Forward()
    {
        $this->composeBean($_REQUEST, self::COMPOSE_BEAN_MODE_FORWARD);
        $this->view = 'compose';
    }

    public function action_SendDraft()
    {
        $this->view = 'ajax';
        echo json_encode(array());
    }


    /**
     * @param array $request
     * @param int $mode
     * @throws InvalidArgumentException
     * @see EmailsController::COMPOSE_BEAN_MODE_UNDEFINED
     * @see EmailsController::COMPOSE_BEAN_MODE_REPLY_TO
     * @see EmailsController::COMPOSE_BEAN_MODE_REPLY_TO_ALL
     * @see EmailsController::COMPOSE_BEAN_MODE_FORWARD
     */
    public function composeBean($request, $mode = self::COMPOSE_BEAN_MODE_UNDEFINED)
    {

        if ($mode === self::COMPOSE_BEAN_MODE_UNDEFINED) {
            throw new InvalidArgumentException('$mode argument is EMAILS_COMPOSE_UNDEFINED');
        }

        global $db;
        global $mod_strings;

        if(isset($request['record']) && !empty($request['record'])) {
            $this->bean->retrieve($request['record']);

        } else {
            $inboundEmail = BeanFactory::getBean('InboundEmail', $db->quote($request['inbound_email_record']));
            $inboundEmail->connectMailserver();
            $importedEmailId = $inboundEmail->returnImportedEmail($request['msgno'], $request['uid']);
            $this->bean->retrieve($importedEmailId);
        }

        $_REQUEST['return_module'] = 'Emails';
        $_REQUEST['return_Action'] = 'index';

        if ($mode === self::COMPOSE_BEAN_MODE_REPLY_TO || $mode === self::COMPOSE_BEAN_MODE_REPLY_TO_ALL) {
            // Move email addresses from the "from" field to the "to" field
            $this->bean->to_addrs = $this->bean->from_addr;
            $this->bean->to_addrs_names = $this->bean->from_addr_name;
        } else {
            if ($mode === self::COMPOSE_BEAN_MODE_FORWARD) {
                $this->bean->to_addrs = '';
                $this->bean->to_addrs_names = '';
            }
        }

        if ($mode !== self::COMPOSE_BEAN_MODE_REPLY_TO_ALL) {
            $this->bean->cc_addrs_arr = array();
            $this->bean->cc_addrs_names = '';
            $this->bean->cc_addrs = '';
            $this->bean->cc_addrs_ids = '';
            $this->bean->cc_addrs_emails = '';
        }

        if ($mode === self::COMPOSE_BEAN_MODE_REPLY_TO || $mode === self::COMPOSE_BEAN_MODE_REPLY_TO_ALL) {
            // Add Re to subject
            $this->bean->name = $mod_strings['LBL_RE'] . $this->bean->name;
        } else {
            if ($mode === self::COMPOSE_BEAN_MODE_FORWARD) {
                // Add FW to subject
                $this->bean->name = $mod_strings['LBL_FW'] . $this->bean->name;
            } else {
                $this->bean->name = $mod_strings['LBL_NO_SUBJECT'] . $this->bean->name;
            }
        }

        // Move body into original message
        if (!empty($this->bean->description_html)) {
            $this->bean->description = '<br>' . $mod_strings['LBL_ORIGINAL_MESSAGE_SEPERATOR'] . '<br>' .
                $this->bean->description_html;
        } else {
            if (!empty($this->bean->description)) {
                $this->bean->description = PHP_EOL . $mod_strings['LBL_ORIGINAL_MESSAGE_SEPERATOR'] . PHP_EOL .
                    $this->bean->description;
            }
        }

    }
}