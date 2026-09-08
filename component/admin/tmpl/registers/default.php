<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.multiselect');
?>
<form action="<?php echo Route::_('index.php?option=com_decaroprotocol&view=registers'); ?>" method="post" name="adminForm" id="adminForm" class="xdecaro-scope decaroprotocol-admin">
    <?php echo HTMLHelper::_('searchtools.default', array('view' => 'registers')); ?>
    <table class="table table-striped decaroprotocol-table">
        <thead><tr><th class="w-1 text-center"><?php echo HTMLHelper::_('grid.checkall'); ?></th><th><?php echo Text::_('JGLOBAL_TITLE'); ?></th><th><?php echo Text::_('COM_DECAROPROTOCOL_CODE'); ?></th><th><?php echo Text::_('COM_DECAROPROTOCOL_NUMBERING_MODE'); ?></th><th><?php echo Text::_('JSTATUS'); ?></th></tr></thead>
        <tbody>
        <?php foreach ($this->items as $i => $item) : ?>
            <tr>
                <td class="text-center"><?php echo HTMLHelper::_('grid.id', $i, $item->id); ?></td>
                <td data-label="<?php echo Text::_('JGLOBAL_TITLE'); ?>"><a href="<?php echo Route::_('index.php?option=com_decaroprotocol&view=register&layout=edit&id=' . (int) $item->id); ?>"><?php echo $this->escape($item->title); ?></a></td>
                <td data-label="<?php echo Text::_('COM_DECAROPROTOCOL_CODE'); ?>"><code><?php echo $this->escape($item->code); ?></code></td>
                <td data-label="<?php echo Text::_('COM_DECAROPROTOCOL_NUMBERING_MODE'); ?>"><?php echo Text::_('COM_DECAROPROTOCOL_NUMBERING_YEARLY'); ?></td>
                <td data-label="<?php echo Text::_('JSTATUS'); ?>"><?php echo (int) $item->active === 1 ? Text::_('JENABLED') : Text::_('JDISABLED'); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php echo $this->pagination->getListFooter(); ?>
    <input type="hidden" name="task" value="">
    <input type="hidden" name="boxchecked" value="0">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
