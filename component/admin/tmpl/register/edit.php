<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');
?>
<form action="<?php echo Route::_('index.php?option=com_decaroprotocol&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="register-form" class="form-validate">
    <div class="card"><div class="card-body"><?php echo $this->form->renderFieldset('details'); ?></div></div>
    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
