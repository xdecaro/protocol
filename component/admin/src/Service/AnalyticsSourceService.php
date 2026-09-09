<?php
namespace Xdecaro\Component\Decaroprotocol\Administrator\Service;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use RuntimeException;

/** Protocol-owned analytics read surface. */
final class AnalyticsSourceService
{
    public function __construct(private DatabaseInterface $db)
    {
    }

    /** @return array<int,array<string,mixed>> */
    public function getMetrics(): array
    {
        $this->assertAuthorised();

        return [
            ['key' => 'protocol.records.total', 'label' => 'Protocol records'],
            ['key' => 'protocol.records.protocolled', 'label' => 'Protocolled records'],
            ['key' => 'protocol.records.draft', 'label' => 'Draft records'],
            ['key' => 'protocol.registers.active', 'label' => 'Active registers'],
        ];
    }

    /** @return array<int,array<string,mixed>> */
    public function getDatasets(): array
    {
        $this->assertAuthorised();

        return [
            ['key' => 'protocol.records.by_direction', 'label' => 'Records by direction'],
            ['key' => 'protocol.records.by_register', 'label' => 'Records by register'],
            ['key' => 'protocol.records.by_month', 'label' => 'Protocolled records by month'],
        ];
    }

    /** @return array<string,mixed> */
    public function getMetric(string $key, array $context = []): array
    {
        $this->assertAuthorised();

        if ($key === 'protocol.records.total') {
            return $this->count('#__decaroprotocol_records', 'Protocol records');
        }
        if ($key === 'protocol.records.protocolled') {
            return $this->countWhere('status', 'protocolled', 'Protocolled records');
        }
        if ($key === 'protocol.records.draft') {
            return $this->countWhere('status', 'draft', 'Draft records');
        }
        if ($key === 'protocol.registers.active') {
            $query = $this->db->getQuery(true)
                ->select('COUNT(*)')
                ->from($this->db->quoteName('#__decaroprotocol_registers'))
                ->where($this->db->quoteName('active') . ' = 1');

            return ['value' => (int) $this->db->setQuery($query)->loadResult(), 'label' => 'Active registers'];
        }

        throw new \InvalidArgumentException('Unknown Protocol analytics metric: ' . $key);
    }

    /** @return array<int,array<string,mixed>> */
    public function getDataset(string $key, array $context = []): array
    {
        $this->assertAuthorised();
        $limit = max(1, min(500, (int) ($context['limit'] ?? 100)));

        if ($key === 'protocol.records.by_direction') {
            $query = $this->db->getQuery(true)
                ->select([$this->db->quoteName('direction'), 'COUNT(*) AS total'])
                ->from($this->db->quoteName('#__decaroprotocol_records'))
                ->group($this->db->quoteName('direction'))
                ->order('total DESC');

            return array_values((array) $this->db->setQuery($query, 0, $limit)->loadAssocList());
        }

        if ($key === 'protocol.records.by_register') {
            $query = $this->db->getQuery(true)
                ->select([
                    $this->db->quoteName('r.id', 'register_id'),
                    $this->db->quoteName('r.title', 'register_title'),
                    $this->db->quoteName('r.code', 'register_code'),
                    'COUNT(p.id) AS total',
                ])
                ->from($this->db->quoteName('#__decaroprotocol_registers', 'r'))
                ->leftJoin($this->db->quoteName('#__decaroprotocol_records', 'p') . ' ON p.register_id = r.id')
                ->group([$this->db->quoteName('r.id'), $this->db->quoteName('r.title'), $this->db->quoteName('r.code')])
                ->order('total DESC');

            return array_values((array) $this->db->setQuery($query, 0, $limit)->loadAssocList());
        }

        if ($key === 'protocol.records.by_month') {
            $query = $this->db->getQuery(true)
                ->select([
                    'DATE_FORMAT(' . $this->db->quoteName('protocolled_at') . ", '%Y-%m') AS period",
                    'COUNT(*) AS total',
                ])
                ->from($this->db->quoteName('#__decaroprotocol_records'))
                ->where($this->db->quoteName('status') . ' = ' . $this->db->quote('protocolled'))
                ->where($this->db->quoteName('protocolled_at') . ' IS NOT NULL')
                ->group('period')
                ->order('period DESC');

            return array_values((array) $this->db->setQuery($query, 0, $limit)->loadAssocList());
        }

        throw new \InvalidArgumentException('Unknown Protocol analytics dataset: ' . $key);
    }

    /** @return array<string,mixed> */
    private function count(string $table, string $label): array
    {
        $query = $this->db->getQuery(true)->select('COUNT(*)')->from($this->db->quoteName($table));

        return ['value' => (int) $this->db->setQuery($query)->loadResult(), 'label' => $label];
    }

    /** @return array<string,mixed> */
    private function countWhere(string $column, string $value, string $label): array
    {
        $query = $this->db->getQuery(true)
            ->select('COUNT(*)')
            ->from($this->db->quoteName('#__decaroprotocol_records'))
            ->where($this->db->quoteName($column) . ' = ' . $this->db->quote($value));

        return ['value' => (int) $this->db->setQuery($query)->loadResult(), 'label' => $label];
    }

    private function assertAuthorised(): void
    {
        $user = Factory::getApplication()->getIdentity();
        if (!$user->authorise('core.manage', 'com_decaroprotocol')
            && !$user->authorise('core.edit', 'com_decaroprotocol')
            && !$user->authorise('core.admin', 'com_decaroprotocol')) {
            throw new RuntimeException('Not authorised to read Protocol analytics.', 403);
        }
    }
}
