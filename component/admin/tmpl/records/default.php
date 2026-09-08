<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.multiselect');
?>
<form action="<?php echo Route::_('index.php?option=com_decaroprotocol&view=records'); ?>" method="post" name="adminForm" id="adminForm">
    <?php echo HTMLHelper::_('searchtools.default', array('view' => 'records')); ?>
    <div class="table-responsive">
        <table class="table table-striped decaroprotocol-table">
            <thead><tr>
                <th class="w-1 text-center"><?php echo HTMLHelper::_('grid.checkall'); ?></th>
                <th><?php echo Text::_('COM_DECAROPROTOCOL_PROTOCOL_NUMBER'); ?></th>
                <th><?php echo Text::_('COM_DECAROPROTOCOL_SUBJECT'); ?></th>
                <th><?php echo Text::_('COM_DECAROPROTOCOL_DIRECTION'); ?></th>
                <th><?php echo Text::_('COM_DECAROPROTOCOL_REGISTER'); ?></th>
                <th><?php echo Text::_('JSTATUS'); ?></th>
                <th><?php echo Text::_('JDATE'); ?></th>
            </tr></thead>
            <tbody>
            <?php if (!$this->items) : ?>
                <tr><td colspan="7" class="text-center"><?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></td></tr>
            <?php else : ?>
                <?php foreach ($this->items as $i => $item) :
                    $identity = $item->protocol_number ? ((int) $item->protocol_number . '/' . (int) $item->protocol_year) : Text::_('COM_DECAROPROTOCOL_DRAFT');
                    $directionKey = 'COM_DECAROPROTOCOL_DIRECTION_' . strtoupper((string) $item->direction);
                    $statusKey = 'COM_DECAROPROTOCOL_STATUS_' . strtoupper((string) $item->status);
                ?>
                <tr>
                    <td class="text-center"><?php echo HTMLHelper::_('grid.id', $i, $item->id); ?></td>
                    <td data-label="<?php echo Text::_('COM_DECAROPROTOCOL_PROTOCOL_NUMBER'); ?>"><span class="decaroprotocol-identity"><?php echo $this->escape($identity); ?></span></td>
                    <td data-label="<?php echo Text::_('COM_DECAROPROTOCOL_SUBJECT'); ?>"><a href="<?php echo Route::_('index.php?option=com_decaroprotocol&view=record&layout=edit&id=' . (int) $item->id); ?>"><?php echo $this->escape($item->subject); ?></a></td>
                    <td data-label="<?php echo Text::_('COM_DECAROPROTOCOL_DIRECTION'); ?>"><?php echo Text::_($directionKey); ?></td>
                    <td data-label="<?php echo Text::_('COM_DECAROPROTOCOL_REGISTER'); ?>"><?php echo $this->escape($item->register_title ?: '-'); ?></td>
                    <td data-label="<?php echo Text::_('JSTATUS'); ?>"><span class="decaroprotocol-status"><?php echo Text::_($statusKey); ?></span></td>
                    <td data-label="<?php echo Text::_('JDATE'); ?>"><?php echo $item->protocolled_at ? HTMLHelper::_('date', $item->protocolled_at, 'DATE_FORMAT_LC5') : $this->escape((string) $item->created); ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php echo $this->pagination->getListFooter(); ?>
    <input type="hidden" name="task" value="">
    <input type="hidden" name="boxchecked" value="0">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
