<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');
$isProtocolled = !empty($this->item->id) && (string) $this->item->status !== 'draft';
$formatBytes = static function (int $bytes): string {
    if ($bytes < 1024) {
        return $bytes . ' B';
    }
    if ($bytes < 1048576) {
        return number_format($bytes / 1024, 1) . ' KiB';
    }
    return number_format($bytes / 1048576, 1) . ' MiB';
};
?>
<form action="<?php echo Route::_('index.php?option=com_decaroprotocol&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="record-form" class="form-validate xdecaro-scope decaroprotocol-admin">
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

<section class="xdecaro-scope decaroprotocol-admin decaroprotocol-documents mt-4" aria-labelledby="decaroprotocol-documents-title">
    <div class="card">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                <div>
                    <h2 id="decaroprotocol-documents-title" class="h4 mb-1"><?php echo Text::_('COM_DECAROPROTOCOL_DOCUMENTS_TITLE'); ?></h2>
                    <p class="text-muted mb-0"><?php echo Text::_('COM_DECAROPROTOCOL_DOCUMENTS_DESC'); ?></p>
                </div>
                <?php if ($this->documentsAccessible) : ?>
                    <a class="btn btn-outline-secondary" href="<?php echo Route::_('index.php?option=com_decarodocuments&view=documents'); ?>" target="_blank" rel="noopener noreferrer">
                        <?php echo Text::_('COM_DECAROPROTOCOL_DOCUMENTS_OPEN_MANAGER'); ?>
                    </a>
                <?php endif; ?>
            </div>

            <?php if (empty($this->item->id)) : ?>
                <div class="alert alert-info mb-0"><?php echo Text::_('COM_DECAROPROTOCOL_DOCUMENTS_SAVE_FIRST'); ?></div>
            <?php elseif (!$this->documentsIntegrationAvailable) : ?>
                <div class="alert alert-secondary mb-0"><?php echo Text::_('COM_DECAROPROTOCOL_DOCUMENTS_UNAVAILABLE'); ?></div>
            <?php elseif (!$this->documentsAccessible) : ?>
                <div class="alert alert-warning mb-0"><?php echo Text::_('COM_DECAROPROTOCOL_DOCUMENTS_NO_ACCESS'); ?></div>
            <?php else : ?>
                <?php if ($this->documents === []) : ?>
                    <div class="alert alert-light border"><?php echo Text::_('COM_DECAROPROTOCOL_DOCUMENTS_NONE'); ?></div>
                <?php else : ?>
                    <div class="table-responsive mb-3">
                        <table class="table decaroprotocol-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th><?php echo Text::_('COM_DECAROPROTOCOL_DOCUMENTS_FILE'); ?></th>
                                    <th><?php echo Text::_('COM_DECAROPROTOCOL_DOCUMENTS_SIZE'); ?></th>
                                    <th><?php echo Text::_('COM_DECAROPROTOCOL_DOCUMENTS_RELATION'); ?></th>
                                    <th class="text-end"><?php echo Text::_('JACTIONS'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($this->documents as $document) : ?>
                                <?php $documentId = (int) ($document['id'] ?? 0); ?>
                                <tr>
                                    <td data-label="<?php echo $this->escape(Text::_('COM_DECAROPROTOCOL_DOCUMENTS_FILE')); ?>">
                                        <a href="<?php echo Route::_('index.php?option=com_decarodocuments&task=document.download&id=' . $documentId); ?>">
                                            <?php echo $this->escape((string) (($document['title'] ?? '') !== '' ? $document['title'] : ($document['original_name'] ?? ''))); ?>
                                        </a>
                                        <div class="text-muted small"><?php echo $this->escape((string) ($document['original_name'] ?? '')); ?></div>
                                    </td>
                                    <td data-label="<?php echo $this->escape(Text::_('COM_DECAROPROTOCOL_DOCUMENTS_SIZE')); ?>"><?php echo $this->escape($formatBytes((int) ($document['file_size'] ?? 0))); ?></td>
                                    <td data-label="<?php echo $this->escape(Text::_('COM_DECAROPROTOCOL_DOCUMENTS_RELATION')); ?>"><?php echo $this->escape((string) ($document['relation_type'] ?? '')); ?></td>
                                    <td class="text-end" data-label="<?php echo $this->escape(Text::_('JACTIONS')); ?>">
                                        <?php if ($this->canManageDocuments) : ?>
                                            <form action="<?php echo Route::_('index.php?option=com_decaroprotocol&task=record.detachDocument'); ?>" method="post" class="d-inline">
                                                <input type="hidden" name="record_id" value="<?php echo (int) $this->item->id; ?>">
                                                <input type="hidden" name="document_id" value="<?php echo $documentId; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger"><?php echo Text::_('COM_DECAROPROTOCOL_DOCUMENTS_DETACH'); ?></button>
                                                <?php echo HTMLHelper::_('form.token'); ?>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <?php if ($this->canManageDocuments) : ?>
                    <form action="<?php echo Route::_('index.php?option=com_decaroprotocol&task=record.attachDocument'); ?>" method="post" class="row g-2 align-items-end">
                        <div class="col-12 col-md-6 col-lg-4">
                            <label class="form-label" for="decaroprotocol-document-id"><?php echo Text::_('COM_DECAROPROTOCOL_DOCUMENTS_ID'); ?></label>
                            <input class="form-control" type="number" min="1" step="1" required id="decaroprotocol-document-id" name="document_id" inputmode="numeric">
                            <div class="form-text"><?php echo Text::_('COM_DECAROPROTOCOL_DOCUMENTS_ID_DESC'); ?></div>
                        </div>
                        <div class="col-12 col-md-auto">
                            <input type="hidden" name="record_id" value="<?php echo (int) $this->item->id; ?>">
                            <button type="submit" class="btn btn-primary"><?php echo Text::_('COM_DECAROPROTOCOL_DOCUMENTS_ATTACH'); ?></button>
                            <?php echo HTMLHelper::_('form.token'); ?>
                        </div>
                    </form>
                <?php elseif ($isProtocolled) : ?>
                    <div class="alert alert-info mb-0"><?php echo Text::_('COM_DECAROPROTOCOL_DOCUMENTS_IMMUTABLE'); ?></div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</section>
