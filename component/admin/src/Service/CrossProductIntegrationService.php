<?php
namespace Xdecaro\Component\Decaroprotocol\Administrator\Service;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Throwable;

/**
 * Optional, API-only bridges from Protocol to shared cross-cutting products.
 * Protocol never reads or writes their private tables.
 */
final class CrossProductIntegrationService
{
    public function notificationsAvailable(): bool
    {
        return $this->boot('com_xdecaronotifications') !== null;
    }

    public function tasksAvailable(): bool
    {
        return $this->boot('com_xdecarotasks') !== null;
    }

    /** Returns null when Notifications is not installed/available. */
    public function notify(array $data): ?int
    {
        $component = $this->boot('com_xdecaronotifications');
        if ($component === null || !method_exists($component, 'getNotificationService')) {
            return null;
        }

        $payload = array_merge([
            'source_component' => 'com_decaroprotocol',
            'source_entity' => 'record',
            'category' => 'protocol',
            'priority' => 'normal',
        ], $data);

        return (int) $component->getNotificationService()->create($payload);
    }

    /**
     * Returns null when Tasks is unavailable. Optional assignee keys:
     * assignee_type, assignee_id, notify_assignee.
     */
    public function createTask(array $data, int $actorUserId = 0): ?int
    {
        $component = $this->boot('com_xdecarotasks');
        if ($component === null || !method_exists($component, 'getTaskService')) {
            return null;
        }

        $service = $component->getTaskService();
        $payload = array_merge([
            'source_component' => 'com_decaroprotocol',
            'source_entity' => 'record',
            'priority' => 'normal',
        ], $data);
        unset($payload['assignee_type'], $payload['assignee_id'], $payload['notify_assignee']);

        $taskId = (int) $service->create($payload, $actorUserId);
        $assigneeType = trim((string) ($data['assignee_type'] ?? ''));
        $assigneeId = trim((string) ($data['assignee_id'] ?? ''));
        if ($taskId > 0 && $assigneeType !== '' && $assigneeId !== '') {
            $service->assign(
                $taskId,
                $assigneeType,
                $assigneeId,
                $actorUserId,
                (bool) ($data['notify_assignee'] ?? true)
            );
        }

        return $taskId;
    }

    private function boot(string $component): ?object
    {
        try {
            $booted = Factory::getApplication()->bootComponent($component);

            return is_object($booted) ? $booted : null;
        } catch (Throwable) {
            return null;
        }
    }
}
