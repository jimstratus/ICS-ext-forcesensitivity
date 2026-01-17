<?php
/**
 * Force Sensitivity Detector - Content Router Extension
 * 
 * @package     IPS\forcesensitivity
 * @subpackage  extensions
 * @author      jimstratus
 * @copyright   2026
 * @license     MIT
 */

namespace IPS\forcesensitivity\extensions\core\MemberFilter;

/* To install this extension, point to: \IPS\core\extensions\core\MemberFilter */

/**
 * Member Filter Extension
 * 
 * Allows filtering members by Force Sensitivity status in ACP.
 */
class _ForceSensitivity
{
    /**
     * Determine if the filter is available in a given area
     *
     * @param string $area Area to check
     * @return bool
     */
    public function availableIn(string $area): bool
    {
        return in_array($area, ['bulkmail', 'group_promotions', 'automatic_moderation', 'reports']);
    }

    /**
     * Get Setting Field
     *
     * @param array $existing Existing settings
     * @return array|false Settings array or FALSE if no settings
     */
    public function getSettingField(array $existing = []): array|false
    {
        return [
            new \IPS\Helpers\Form\Select(
                'fs_filter_status',
                $existing['fs_filter_status'] ?? null,
                false,
                [
                    'options' => [
                        '' => 'fs_filter_any',
                        'sensitive' => 'fs_status_sensitive',
                        'blind' => 'fs_status_blind',
                        'undetermined' => 'fs_status_undetermined'
                    ]
                ]
            )
        ];
    }

    /**
     * Save settings
     *
     * @param array $values Form values
     * @return array Settings to save
     */
    public function save(array $values): array
    {
        return ['fs_filter_status' => $values['fs_filter_status'] ?? null];
    }

    /**
     * Get the WHERE clause
     *
     * @param array $data Saved filter data
     * @return array|false WHERE clause or FALSE if not applicable
     */
    public function getQueryWhereClause(array $data): array|false
    {
        if (empty($data['fs_filter_status'])) {
            return false;
        }

        $status = $data['fs_filter_status'];

        if ($status === 'undetermined') {
            // Members without a status record
            return [
                'core_members.member_id NOT IN (?)',
                \IPS\Db::i()->select('member_id', 'forcesensitivity_status')
            ];
        }

        $isSensitive = ($status === 'sensitive') ? 1 : 0;

        return [
            'core_members.member_id IN (?)',
            \IPS\Db::i()->select('member_id', 'forcesensitivity_status', ['is_force_sensitive=?', $isSensitive])
        ];
    }

    /**
     * Callback for streaming exports
     *
     * @param \IPS\Member $member The member
     * @param array $data Filter data
     * @return bool Should include member?
     */
    public function matches(\IPS\Member $member, array $data): bool
    {
        if (empty($data['fs_filter_status'])) {
            return true;
        }

        $status = \IPS\forcesensitivity\ForceSensitivity\Status::loadByMember($member);

        if ($data['fs_filter_status'] === 'undetermined') {
            return $status === null;
        }

        if ($status === null) {
            return false;
        }

        if ($data['fs_filter_status'] === 'sensitive') {
            return $status->is_force_sensitive;
        }

        return !$status->is_force_sensitive;
    }
}
