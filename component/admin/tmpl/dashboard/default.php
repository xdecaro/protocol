<?php
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$cards = array(
    'total' => 'COM_DECAROPROTOCOL_STATS_TOTAL',
    'drafts' => 'COM_DECAROPROTOCOL_STATS_DRAFTS',
    'protocolled' => 'COM_DECAROPROTOCOL_STATS_PROTOCOLLED',
    'incoming' => 'COM_DECAROPROTOCOL_DIRECTION_INCOMING',
    'outgoing' => 'COM_DECAROPROTOCOL_DIRECTION_OUTGOING',
    'internal' => 'COM_DECAROPROTOCOL_DIRECTION_INTERNAL',
);
?>
<div class="decaroprotocol-dashboard">
    <?php foreach ($cards as $key => $label) : ?>
        <div class="decaroprotocol-card">
            <div class="decaroprotocol-card__value"><?php echo (int) ($this->stats[$key] ?? 0); ?></div>
            <div class="decaroprotocol-card__label"><?php echo Text::_($label); ?></div>
        </div>
    <?php endforeach; ?>
</div>
<div class="mt-4">
    <a class="btn btn-primary" href="<?php echo Route::_('index.php?option=com_decaroprotocol&view=records'); ?>"><?php echo Text::_('COM_DECAROPROTOCOL_OPEN_RECORDS'); ?></a>
</div>
