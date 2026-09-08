<?php
namespace Xdecaro\Component\Decaroprotocol\Administrator\Service;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

final class ProtocolService
{
    private $db;

    public function __construct(DatabaseInterface $db)
    {
        $this->db = $db;
    }

    public function protocol($recordId, $userId)
    {
        $recordId = (int) $recordId;
        $userId = (int) $userId;

        if ($recordId < 1) {
            throw new \InvalidArgumentException(Text::_('COM_DECAROPROTOCOL_ERROR_RECORD_ID'));
        }

        $utcNow = Factory::getDate()->toSql();
        $localDate = Factory::getDate();
        $offset = Factory::getApplication()->get('offset', 'UTC');
        $localDate->setTimezone(new \DateTimeZone($offset ?: 'UTC'));
        $year = (int) $localDate->format('Y');

        $this->db->transactionStart();

        try {
            $record = $this->loadRecordForUpdate($recordId);

            if (!$record) {
                throw new \RuntimeException(Text::_('COM_DECAROPROTOCOL_ERROR_RECORD_NOT_FOUND'));
            }

            if ($record->status !== 'draft') {
                throw new \RuntimeException(Text::_('COM_DECAROPROTOCOL_ERROR_ALREADY_PROTOCOLLED'));
            }

            if (trim((string) $record->subject) === '') {
                throw new \RuntimeException(Text::_('COM_DECAROPROTOCOL_ERROR_SUBJECT_REQUIRED'));
            }

            $register = $this->loadRegister((int) $record->register_id);
            if (!$register || (int) $register->active !== 1) {
                throw new \RuntimeException(Text::_('COM_DECAROPROTOCOL_ERROR_REGISTER_NOT_AVAILABLE'));
            }

            if ($register->numbering_mode !== 'yearly') {
                throw new \RuntimeException(Text::_('COM_DECAROPROTOCOL_ERROR_NUMBERING_MODE'));
            }

            $number = $this->nextNumber((int) $record->register_id, $year, $utcNow);

            $query = $this->db->getQuery(true)
                ->update($this->db->quoteName('#__decaroprotocol_records'))
                ->set($this->db->quoteName('protocol_number') . ' = ' . (int) $number)
                ->set($this->db->quoteName('protocol_year') . ' = ' . (int) $year)
                ->set($this->db->quoteName('protocolled_at') . ' = ' . $this->db->quote($utcNow))
                ->set($this->db->quoteName('protocolled_by') . ' = ' . (int) $userId)
                ->set($this->db->quoteName('status') . ' = ' . $this->db->quote('protocolled'))
                ->set($this->db->quoteName('modified') . ' = ' . $this->db->quote($utcNow))
                ->set($this->db->quoteName('modified_by') . ' = ' . (int) $userId)
                ->where($this->db->quoteName('id') . ' = ' . $recordId)
                ->where($this->db->quoteName('status') . ' = ' . $this->db->quote('draft'));

            $this->db->setQuery($query)->execute();

            if ((int) $this->db->getAffectedRows() !== 1) {
                throw new \RuntimeException(Text::_('COM_DECAROPROTOCOL_ERROR_PROTOCOL_UPDATE'));
            }

            $this->writeAudit($recordId, 'protocolled', $userId, $utcNow, array(
                'register_id' => (int) $record->register_id,
                'protocol_year' => $year,
                'protocol_number' => $number,
            ));

            $this->db->transactionCommit();

            return array(
                'record_id' => $recordId,
                'number' => $number,
                'year' => $year,
                'protocolled_at' => $utcNow,
            );
        } catch (\Throwable $e) {
            $this->db->transactionRollback();
            throw $e;
        }
    }

    private function loadRecordForUpdate($recordId)
    {
        $sql = 'SELECT ' . implode(',', array(
            $this->db->quoteName('id'),
            $this->db->quoteName('register_id'),
            $this->db->quoteName('status'),
            $this->db->quoteName('subject'),
        )) . ' FROM ' . $this->db->quoteName('#__decaroprotocol_records')
            . ' WHERE ' . $this->db->quoteName('id') . ' = ' . (int) $recordId
            . ' FOR UPDATE';

        return $this->db->setQuery($sql)->loadObject();
    }

    private function loadRegister($registerId)
    {
        $query = $this->db->getQuery(true)
            ->select(array(
                $this->db->quoteName('id'),
                $this->db->quoteName('active'),
                $this->db->quoteName('numbering_mode'),
            ))
            ->from($this->db->quoteName('#__decaroprotocol_registers'))
            ->where($this->db->quoteName('id') . ' = ' . (int) $registerId);

        return $this->db->setQuery($query)->loadObject();
    }

    private function nextNumber($registerId, $year, $utcNow)
    {
        $insert = 'INSERT IGNORE INTO ' . $this->db->quoteName('#__decaroprotocol_register_counters')
            . ' (' . implode(',', array(
                $this->db->quoteName('register_id'),
                $this->db->quoteName('protocol_year'),
                $this->db->quoteName('last_number'),
                $this->db->quoteName('updated'),
            )) . ') VALUES (' . (int) $registerId . ',' . (int) $year . ',0,' . $this->db->quote($utcNow) . ')';
        $this->db->setQuery($insert)->execute();

        $lock = 'SELECT ' . $this->db->quoteName('last_number')
            . ' FROM ' . $this->db->quoteName('#__decaroprotocol_register_counters')
            . ' WHERE ' . $this->db->quoteName('register_id') . ' = ' . (int) $registerId
            . ' AND ' . $this->db->quoteName('protocol_year') . ' = ' . (int) $year
            . ' FOR UPDATE';

        $last = $this->db->setQuery($lock)->loadResult();
        if ($last === null) {
            throw new \RuntimeException(Text::_('COM_DECAROPROTOCOL_ERROR_COUNTER_LOCK'));
        }

        $next = (int) $last + 1;

        $query = $this->db->getQuery(true)
            ->update($this->db->quoteName('#__decaroprotocol_register_counters'))
            ->set($this->db->quoteName('last_number') . ' = ' . $next)
            ->set($this->db->quoteName('updated') . ' = ' . $this->db->quote($utcNow))
            ->where($this->db->quoteName('register_id') . ' = ' . (int) $registerId)
            ->where($this->db->quoteName('protocol_year') . ' = ' . (int) $year);
        $this->db->setQuery($query)->execute();

        return $next;
    }

    private function writeAudit($recordId, $event, $userId, $created, array $data)
    {
        $payload = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($payload === false) {
            throw new \RuntimeException(Text::_('COM_DECAROPROTOCOL_ERROR_AUDIT_JSON'));
        }

        $query = $this->db->getQuery(true)
            ->insert($this->db->quoteName('#__decaroprotocol_audit'))
            ->columns(array(
                $this->db->quoteName('record_id'),
                $this->db->quoteName('event'),
                $this->db->quoteName('user_id'),
                $this->db->quoteName('created'),
                $this->db->quoteName('data_json'),
            ))
            ->values(implode(',', array(
                (int) $recordId,
                $this->db->quote($event),
                (int) $userId,
                $this->db->quote($created),
                $this->db->quote($payload),
            )));
        $this->db->setQuery($query)->execute();
    }
}
