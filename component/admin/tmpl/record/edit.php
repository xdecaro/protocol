<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');
$isProtocolled = !empty($this->item->id) && (string) $this->item->status !== 'draft';
?>
<form action="<?php echo Route::_('index.php?option=com_decaroprotocol&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="record-form" class="form-validate">
    <?php if ($isProtocolled) : ?>
        <div class="decaroprotocol-meta">
            <div class="decaroprotocol-meta__item"><span class="decaroprotocol-meta__label"><?php echo Text::_('COM_DECAROPROTOCOL_PROTOCOL_NUMBER'); ?></span><span class="decaroprotocol-identity"><?php echo (int) $this->item->protocol_number . '/' . (int) $this->item->protocol_year; ?></span></div>
            <div class="decaroprotocol-meta__item"><span class="decaroprotocol-meta__label"><?php echo Text::_('COM_DECAROPROTOCOL_PROTOCOL_DATE'); ?></span><?php echo $this->escape((string) $this->item->protocolled_at); ?></div>
            <div class="decaroprotocol-meta__item"><span class="decaroprotocol-meta__label"><?php echo Text::_('JSTATUS'); ?></span><?php echo Text::_('COM_DECAROPROTOCOL_STATUS_' . strtoupper((string) $this->item->status)); ?></div>
        </div>
        <div class="alert alert-info"><?php echo Text::_('COM_DECAROPROTOCOL_IMMUTABLE_NOTICE'); ?></div>
    <?php endif; ?>
    <div class="card"><div class="card-body">
        <?php echo $this->form->renderFieldset('details'); ?>
    </div></div>
    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
