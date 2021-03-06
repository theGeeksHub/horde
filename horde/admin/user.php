<?php
/**
 * Copyright 1999-2013 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (LGPL-2). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl.
 *
 * @author   Chuck Hagenbuch <chuck@horde.org>
 * @category Horde
 * @license  http://www.horde.org/licenses/lgpl LGPL-2
 * @package  Horde
 */

require_once __DIR__ . '/../lib/Application.php';
Horde_Registry::appInit('horde', array(
    'permission' => array('horde:administration:users')
));

$auth = $injector->getInstance('Horde_Core_Factory_Auth')->create();
$vars = $injector->getInstance('Horde_Variables');

if ($conf['signup']['allow'] && $conf['signup']['approve']) {
    $signup = $injector->getInstance('Horde_Core_Auth_Signup');
}

$addForm = new Horde_Form($vars, _("Add a new user:"), 'adduser');
$addForm->setButtons(_("Add user"), _("Reset"));

$addForm->addHidden('', 'form', 'text', true, true);

/* Use hooks get any extra fields for new accounts. */
try {
    $extra = Horde::callHook('signup_getextra');
    if (!empty($extra)) {
        if (!isset($extra['user_name'])) {
            $addForm->addVariable(_("Username"), 'user_name', 'text', true);
        }
        if (!isset($extra['password'])) {
            $addForm->addVariable(_("Password"), 'password', 'passwordconfirm', false, false, _("type the password twice to confirm"));
        }
        foreach ($extra as $field_name => $field) {
            $readonly = isset($field['readonly']) ? $field['readonly'] : null;
            $desc = isset($field['desc']) ? $field['desc'] : null;
            $field_params = isset($field['params']) ? $field['params'] : array();

            $addForm->addVariable($field['label'], 'extra[' . $field_name . ']', $field['type'], $field['required'], $readonly, $desc, $field_params);
        }
    }
} catch (Horde_Exception_HookNotSet $e) {}

if (empty($extra)) {
    $addForm->addVariable(_("Username"), 'user_name', 'text', true);
    $addForm->addVariable(_("Password"), 'password', 'passwordconfirm', false, false, _("type the password twice to confirm"));
}

switch ($vars->form) {
case 'add':
    $addForm->validate($vars);
    if ($addForm->isValid() && $vars->get('formname') == 'adduser') {
        $addForm->getInfo($vars, $info);

        if (empty($info['user_name']) && isset($info['extra']['user_name'])) {
            $info['user_name'] = $info['extra']['user_name'];
        }

        if (empty($info['password']) && isset($info['extra']['password'])) {
            $info['password'] = $info['extra']['password'];
        }

        if (empty($info['user_name'])) {
            $notification->push(_("You must specify the username to add."), 'horde.error');
        } elseif ($auth->exists($info['user_name'])) {
            $notification->push(sprintf(_("The user \"%s\" already exists."), $info['user_name']), 'horde.error');
        } else {
            $credentials = array('password' => $info['password']);
            if (isset($info['extra'])) {
                foreach ($info['extra'] as $field => $value) {
                    $credentials[$field] = $value;
                }
            }

            try {
                $auth->addUser($info['user_name'], $credentials);
            } catch (Horde_Auth_Exception $e) {
                $notification->push(sprintf(_("There was a problem adding \"%s\" to the system: %s"), $info['user_name'], $e->getMessage()), 'horde.error');
                break;
            }

            if (isset($info['extra'])) {
                try {
                    Horde::callHook('signup_addextra', array($info['user_name'], $info['extra']));
                } catch (Horde_Exception $e) {
                    $notification->push(sprintf(_("Added \"%s\" to the system, but could not add additional signup information: %s."), $info['user_name'], $e->getMessage()), 'horde.warning');
                } catch (Horde_Exception_HookNotSet $e) {}
            }

            if ($vars->removeQueuedSignup) {
                $signup->removeQueuedSignup($info['user_name']);
            }

            $notification->push(sprintf(_("Successfully added \"%s\" to the system."), $info['user_name']), 'horde.success');
            $addForm->unsetVars($vars);
        }
    }
    break;

case 'remove_f':
    $f_user_name = $vars->user_name;
    $remove_form = true;
    break;

case 'remove':
    $f_user_name = $vars->user_name;
    $vars->remove('user_name');
    if ($vars->submit == _("Cancel")) {
        break;
    }
    if (empty($f_user_name)) {
        $notification->push(_("You must specify a username to remove."), 'horde.message');
        break;
    }
    try {
        $registry->removeUser($f_user_name);
        $notification->push(sprintf(_("Successfully removed \"%s\" from the system."), $f_user_name), 'horde.success');
    } catch (Horde_Exception $e) {
        $notification->push(sprintf(_("There was a problem removing \"%s\" from the system: ") . $e->getMessage(), $f_user_name), 'horde.error');
    }
    break;

case 'clear_f':
    $f_user_name = $vars->user_name;
    $clear_form = true;
    break;

case 'clear':
    $f_user_name = $vars->user_name;
    $vars->remove('user_name');
    if ($vars->submit == _("Cancel")) {
        break;
    }
    if (empty($f_user_name)) {
        $notification->push(_("You must specify a username to clear out."), 'horde.message');
        break;
    }
    try {
        $registry->removeUserData($f_user_name);
        $notification->push(sprintf(_("Successfully cleared data for user \"%s\" from the system."), $f_user_name), 'horde.success');
    } catch (Horde_Exception $e) {
        $notification->push(sprintf(_("There was a problem clearing data for user \"%s\" from the system: ") . $e->getMessage(), $f_user_name), 'horde.error');
    }
    break;

case 'update_f':
    $f_user_name = $vars->user_name;
    $update_form = true;
    break;

case 'update':
    $user_name_1 = Horde_Util::getPost('user_name');
    $user_name_2 = Horde_Util::getPost('user_name2', $user_name_1);
    $fullname = Horde_Util::getPost('user_fullname');
    $email = Horde_Util::getPost('user_email');

    $vars->remove('user_name');

    if ($auth->hasCapability('update')) {
        $user_pass_1 = Horde_Util::getPost('user_pass_1');
        $user_pass_2 = Horde_Util::getPost('user_pass_2');

        if (empty($user_name_1)) {
            $notification->push(_("You must specify the username to update."), 'horde.error');
        } elseif (empty($user_pass_1) || empty($user_pass_2)) {
            // Don't update, but don't complain.
        } elseif ($user_pass_1 != $user_pass_2) {
            $notification->push(_("Passwords must match."), 'horde.error');
        } else {
            try {
                $auth->updateUser($user_name_1, $user_name_2, array('password' => $user_pass_1));
            } catch (Horde_Auth_Exception $e) {
                $notification->push(sprintf(_("There was a problem updating \"%s\": %s"), $user_name_1, $e->getMessage()), 'horde.error');
                break;
            }
        }
    }
    if ($auth->hasCapability('lock')) {
        $user_locked = Horde_Util::getPost('user_locked');
        /* only execute lock/unlock if it would result in a change */
        if (($auth->isLocked($user_name_2)) && (!$user_locked)) {
            $auth->unlockUser($user_name_2);
        } elseif ((!$auth->isLocked($user_name_2)) && ($user_locked)) {
            $auth->lockUser($user_name_2);
        }
    }
    $identity = $injector->getInstance('Horde_Core_Factory_Identity')->create($user_name_1);
    $identity->setValue('fullname', $fullname);
    $identity->setValue('from_addr', $email);
    $identity->save();

    $notification->push(sprintf(_("Successfully updated \"%s\""), $user_name_2), 'horde.success');
    break;

case 'approve_f':
    $thisSignup = $signup->getQueuedSignup($vars->user_name);
    $info = $thisSignup->getData();

    $vars->set('password',
               array('original' => $info['password'],
                     'confirm' => $info['password']));
    unset($info['password']);
    $vars->set('extra', $info);

    $vars->set('removeQueuedSignup', true);
    $addForm->addHidden('', 'removeQueuedSignup', 'boolean', true);
    break;

case 'removequeued_f':
    $f_user_name = $vars->user_name;
    $removequeued_form = true;
    break;

case 'removequeued':
    try {
        $signup->removeQueuedSignup($vars->user_name);
        $notification->push(sprintf(_("The signup request for \"%s\" has been removed."), $vars->user_name));
    } catch (Horde_Exception $e) {
        $notification->push($e);
    }
    break;
}

$page_output->addScriptFile('stripe.js', 'horde');
if (isset($update_form) && $auth->hasCapability('list')) {
    $page_output->addScriptFile('userupdate.js', 'horde');
    $page_output->addInlineJsVars(array(
        'HordeAdminUserUpdate.pass_error' => _("Passwords must match.")
    ));
}

$page_output->header(array(
    'title' => _("User Administration")
));
require HORDE_TEMPLATES . '/admin/menu.inc';

if (isset($update_form) && $auth->hasCapability('list')) {
    $identity = $injector->getInstance('Horde_Core_Factory_Identity')->create($f_user_name);
    require HORDE_TEMPLATES . '/admin/user/update.inc';
} elseif (isset($remove_form) &&
          $auth->hasCapability('list') &&
          $auth->hasCapability('remove')) {
    require HORDE_TEMPLATES . '/admin/user/remove.inc';
} elseif (isset($clear_form)) {
    require HORDE_TEMPLATES . '/admin/user/clear.inc';
} elseif (isset($removequeued_form)) {
    require HORDE_TEMPLATES . '/admin/user/removequeued.inc';
} elseif ($auth->hasCapability('add')) {
    $vars->form = 'add';
    $addForm->renderActive(new Horde_Form_Renderer(), $vars, Horde::selfUrl(), 'post');
    if ($conf['signup']['allow'] && $conf['signup']['approve']) {
        require HORDE_TEMPLATES . '/admin/user/approve.inc';
    }
} else {
    require HORDE_TEMPLATES . '/admin/user/noadd.inc';
}

if ($auth->hasCapability('list')) {
    /* If we aren't supplied with a page number, default to page 0. */
    $page = $vars->get('page', 0);
    $search_pattern = $vars->get('search_pattern', '');

    $users = $auth->listUsers();

    /* Returns only users that match the specified pattern. */
    $users = preg_grep('/' . $search_pattern . '/', $users);
    sort($users);

    $viewurl = Horde::url('admin/user.php')->add('search_pattern', $search_pattern);

    $numitem = count($users);
    $perpage = 20;

    $min = $page * $perpage;
    while ($min > $numitem) {
        $page--;
        $min = $page * $perpage;
    }
    $max = $min + $perpage;

    $start = ($page * $perpage) + 1;
    $end = min($numitem, $start + $perpage - 1);

    require HORDE_TEMPLATES . '/admin/user/list.inc';
} else {
    require HORDE_TEMPLATES . '/admin/user/nolist.inc';
}

$page_output->footer();
