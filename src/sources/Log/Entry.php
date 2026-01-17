<?php
/**
 * Force Sensitivity Detector - Log Entry Model
 * 
 * @package     IPS\forcesensitivity
 * @subpackage  Log
 * @author      jimstratus
 * @copyright   2026
 * @license     MIT
 */

namespace IPS\forcesensitivity\Log;

/**
 * Audit Log Entry Model
 * 
 * Records all Force Sensitivity detection events and admin actions
 * for audit and review purposes.
 */
class _Entry extends \IPS\Patterns\ActiveRecord
{
    /**
     * @brief Database Table
     */
    public static $databaseTable = 'forcesensitivity_log';
    
    /**
     * @brief Database ID Field
     */
    public static $databaseColumnId = 'id';
    
    /**
     * @brief Multiton Store
     */
    protected static $multitons;
    
    /**
     * Action type constants
     */
    const ACTION_DETECTION = 'detection';
    const ACTION_ADMIN_OVERRIDE = 'admin_override';
    const ACTION_REROLL = 'reroll';
    const ACTION_MODIFIER_ADDED = 'modifier_added';
    const ACTION_MODIFIER_REMOVED = 'modifier_removed';
    const ACTION_SETTINGS_CHANGED = 'settings_changed';
    const ACTION_BULK_OPERATION = 'bulk_operation';
    
    /**
     * Create a new log entry
     * 
     * @param \IPS\Member $member The member affected
     * @param string $action Action type (use class constants)
     * @param string|null $oldValue Previous value
     * @param string|null $newValue New value
     * @param \IPS\Member|null $performer Admin who performed action (null for system)
     * @param array|null $details Additional details
     * @return static
     */
    public static function log(
        \IPS\Member $member,
        string $action,
        ?string $oldValue = null,
        ?string $newValue = null,
        ?\IPS\Member $performer = null,
        ?array $details = null
    ): static {
        $entry = new static();
        $entry->member_id = $member->member_id;
        $entry->action = $action;
        $entry->old_value = $oldValue;
        $entry->new_value = $newValue;
        $entry->performed_by = $performer?->member_id;
        $entry->ip_address = \IPS\Request::i()->ipAddress();
        $entry->timestamp = (new \IPS\DateTime())->format('Y-m-d H:i:s');
        $entry->details = $details ? json_encode($details) : null;
        $entry->save();
        
        return $entry;
    }
    
    /**
     * Log a system action (no performer)
     * 
     * @param \IPS\Member $member The member affected
     * @param string $action Action type
     * @param string|null $oldValue Previous value
     * @param string|null $newValue New value
     * @param array|null $details Additional details
     * @return static
     */
    public static function logSystem(
        \IPS\Member $member,
        string $action,
        ?string $oldValue = null,
        ?string $newValue = null,
        ?array $details = null
    ): static {
        return static::log($member, $action, $oldValue, $newValue, null, $details);
    }
    
    /**
     * Get log entries for a specific member
     * 
     * @param \IPS\Member $member The member
     * @param int $limit Maximum entries to return
     * @return array
     */
    public static function getForMember(\IPS\Member $member, int $limit = 50): array
    {
        $entries = [];
        
        foreach (\IPS\Db::i()->select(
            '*',
            static::$databaseTable,
            ['member_id=?', $member->member_id],
            'timestamp DESC',
            $limit
        ) as $row) {
            $entries[] = static::constructFromData($row);
        }
        
        return $entries;
    }
    
    /**
     * Get all log entries with filtering
     * 
     * @param array $filters Filter options
     * @param string $order Order by clause
     * @param array $limit Limit [offset, count]
     * @return array
     */
    public static function getAll(array $filters = [], string $order = 'timestamp DESC', array $limit = [0, 50]): array
    {
        $where = [];
        
        if (isset($filters['member_id'])) {
            $where[] = ['member_id=?', $filters['member_id']];
        }
        
        if (isset($filters['action'])) {
            $where[] = ['action=?', $filters['action']];
        }
        
        if (isset($filters['performed_by'])) {
            $where[] = ['performed_by=?', $filters['performed_by']];
        }
        
        if (isset($filters['date_start'])) {
            $where[] = ['timestamp>=?', $filters['date_start']];
        }
        
        if (isset($filters['date_end'])) {
            $where[] = ['timestamp<=?', $filters['date_end']];
        }
        
        $entries = [];
        
        foreach (\IPS\Db::i()->select(
            '*',
            static::$databaseTable,
            $where ?: null,
            $order,
            $limit
        ) as $row) {
            $entries[] = static::constructFromData($row);
        }
        
        return $entries;
    }
    
    /**
     * Count log entries
     * 
     * @param array $where Where conditions
     * @return int
     */
    public static function count(array $where = []): int
    {
        return \IPS\Db::i()->select(
            'COUNT(*)',
            static::$databaseTable,
            $where ?: null
        )->first();
    }
    
    /**
     * Get the affected member
     * 
     * @return \IPS\Member
     */
    public function getMember(): \IPS\Member
    {
        return \IPS\Member::load($this->member_id);
    }
    
    /**
     * Get the admin who performed the action
     * 
     * @return \IPS\Member|null Null if system action
     */
    public function getPerformer(): ?\IPS\Member
    {
        if ($this->performed_by) {
            $member = \IPS\Member::load($this->performed_by);
            return $member->member_id ? $member : null;
        }
        return null;
    }
    
    /**
     * Get action label (translated)
     * 
     * @return string
     */
    public function getActionLabel(): string
    {
        return \IPS\Member::loggedIn()->language()->addToStack('fs_log_action_' . $this->action);
    }
    
    /**
     * Get old value label (translated status)
     * 
     * @return string
     */
    public function getOldValueLabel(): string
    {
        if ($this->old_value === null) {
            return '—';
        }
        
        if ($this->old_value === 'sensitive') {
            return \IPS\Settings::i()->fs_sensitive_label ?: \IPS\Member::loggedIn()->language()->addToStack('fs_status_sensitive');
        }
        
        if ($this->old_value === 'blind') {
            return \IPS\Settings::i()->fs_blind_label ?: \IPS\Member::loggedIn()->language()->addToStack('fs_status_blind');
        }
        
        return $this->old_value;
    }
    
    /**
     * Get new value label (translated status)
     * 
     * @return string
     */
    public function getNewValueLabel(): string
    {
        if ($this->new_value === null) {
            return '—';
        }
        
        if ($this->new_value === 'sensitive') {
            return \IPS\Settings::i()->fs_sensitive_label ?: \IPS\Member::loggedIn()->language()->addToStack('fs_status_sensitive');
        }
        
        if ($this->new_value === 'blind') {
            return \IPS\Settings::i()->fs_blind_label ?: \IPS\Member::loggedIn()->language()->addToStack('fs_status_blind');
        }
        
        return $this->new_value;
    }
    
    /**
     * Get details as array
     * 
     * @return array|null
     */
    public function getDetails(): ?array
    {
        if ($this->details) {
            return json_decode($this->details, true);
        }
        return null;
    }
    
    /**
     * Get formatted timestamp
     * 
     * @return string
     */
    public function getFormattedTimestamp(): string
    {
        $date = new \IPS\DateTime($this->timestamp);
        return $date->localeDateTime();
    }
    
    /**
     * Get performer label (name or "System")
     * 
     * @return string
     */
    public function getPerformerLabel(): string
    {
        $performer = $this->getPerformer();
        
        if ($performer) {
            return $performer->name;
        }
        
        return \IPS\Member::loggedIn()->language()->addToStack('fs_system');
    }
    
    /**
     * Export logs as CSV
     * 
     * @param array $filters Filter options
     * @return string CSV content
     */
    public static function exportCsv(array $filters = []): string
    {
        $entries = static::getAll($filters, 'timestamp DESC', [0, 10000]);
        
        $output = fopen('php://temp', 'r+');
        
        // Header row
        fputcsv($output, [
            'ID',
            'Timestamp',
            'Member ID',
            'Member Name',
            'Action',
            'Old Value',
            'New Value',
            'Performed By',
            'IP Address',
            'Details'
        ]);
        
        foreach ($entries as $entry) {
            $member = $entry->getMember();
            $performer = $entry->getPerformer();
            
            fputcsv($output, [
                $entry->id,
                $entry->timestamp,
                $entry->member_id,
                $member->name ?? 'Unknown',
                $entry->action,
                $entry->old_value ?? '',
                $entry->new_value ?? '',
                $performer ? $performer->name : 'System',
                $entry->ip_address ?? '',
                $entry->details ?? ''
            ]);
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }
    
    /**
     * Export logs as JSON
     * 
     * @param array $filters Filter options
     * @return string JSON content
     */
    public static function exportJson(array $filters = []): string
    {
        $entries = static::getAll($filters, 'timestamp DESC', [0, 10000]);
        
        $data = [];
        
        foreach ($entries as $entry) {
            $member = $entry->getMember();
            $performer = $entry->getPerformer();
            
            $data[] = [
                'id' => $entry->id,
                'timestamp' => $entry->timestamp,
                'member' => [
                    'id' => $entry->member_id,
                    'name' => $member->name ?? 'Unknown'
                ],
                'action' => $entry->action,
                'old_value' => $entry->old_value,
                'new_value' => $entry->new_value,
                'performed_by' => $performer ? [
                    'id' => $performer->member_id,
                    'name' => $performer->name
                ] : null,
                'ip_address' => $entry->ip_address,
                'details' => $entry->getDetails()
            ];
        }
        
        return json_encode($data, JSON_PRETTY_PRINT);
    }
    
    /**
     * Prune old log entries
     * 
     * @param int $daysToKeep Number of days of logs to retain
     * @return int Number of entries deleted
     */
    public static function prune(int $daysToKeep): int
    {
        $cutoff = new \IPS\DateTime();
        $cutoff->sub(new \DateInterval("P{$daysToKeep}D"));
        
        return \IPS\Db::i()->delete(
            static::$databaseTable,
            ['timestamp < ?', $cutoff->format('Y-m-d H:i:s')]
        );
    }
}
