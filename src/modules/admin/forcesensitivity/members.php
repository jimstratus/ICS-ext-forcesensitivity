<?php
/**
 * Force Sensitivity Detector - Admin Members Module
 * 
 * @package     IPS\forcesensitivity
 * @subpackage  modules\admin
 * @author      jimstratus
 * @copyright   2026
 * @license     MIT
 */

namespace IPS\forcesensitivity\modules\admin\forcesensitivity;

/* To install this module, point to: \IPS\Dispatcher\Controller */

/**
 * Members Controller
 * 
 * Manages Force Sensitivity status for individual and bulk members.
 */
class _members extends \IPS\Dispatcher\Controller
{
    /**
     * @brief Has been CSRF-protected
     */
    public static $csrfProtected = true;
    
    /**
     * Execute
     *
     * @return void
     */
    public function execute(): void
    {
        \IPS\Dispatcher::i()->checkAcpPermission('fs_members_view');
        parent::execute();
    }
    
    /**
     * Members List
     *
     * @return void
     */
    protected function manage(): void
    {
        // Build table
        $table = new \IPS\Helpers\Table\Db(
            'forcesensitivity_status',
            \IPS\Http\Url::internal('app=forcesensitivity&module=forcesensitivity&controller=members')
        );
        
        $table->langPrefix = 'fs_';
        $table->include = ['member_id', 'is_force_sensitive', 'detection_date', 'detection_method', 'probability_used'];
        $table->mainColumn = 'member_id';
        
        // Sorting
        $table->sortBy = $table->sortBy ?: 'detection_date';
        $table->sortDirection = $table->sortDirection ?: 'desc';
        
        // Filters
        $table->filters = [
            'fs_filter_sensitive' => 'is_force_sensitive=1',
            'fs_filter_blind' => 'is_force_sensitive=0'
        ];
        
        // Parsers
        $table->parsers = [
            'member_id' => function($val) {
                $member = \IPS\Member::load($val);
                if ($member->member_id) {
                    return \IPS\Theme::i()->getTemplate('global', 'core')->userPhoto($member, 'mini') . ' ' . $member->link();
                }
                return \IPS\Member::loggedIn()->language()->addToStack('fs_deleted_member');
            },
            'is_force_sensitive' => function($val) {
                if ($val) {
                    $label = \IPS\Settings::i()->fs_sensitive_label ?: \IPS\Member::loggedIn()->language()->addToStack('fs_status_sensitive');
                    return "<span class='ipsBadge ipsBadge--positive'>{$label}</span>";
                }
                $label = \IPS\Settings::i()->fs_blind_label ?: \IPS\Member::loggedIn()->language()->addToStack('fs_status_blind');
                return "<span class='ipsBadge ipsBadge--neutral'>{$label}</span>";
            },
            'detection_date' => function($val) {
                $date = new \IPS\DateTime($val);
                return $date->localeDate();
            },
            'detection_method' => function($val) {
                return \IPS\Member::loggedIn()->language()->addToStack('fs_method_' . $val);
            },
            'probability_used' => function($val) {
                return round($val * 100, 2) . '%';
            }
        ];
        
        // Row buttons
        $table->rowButtons = function($row) {
            $buttons = [];
            $member = \IPS\Member::load($row['member_id']);
            
            if ($member->member_id && \IPS\Member::loggedIn()->hasAcpRestriction('forcesensitivity', 'forcesensitivity', 'fs_members_manage')) {
                // View history
                $buttons['history'] = [
                    'icon' => 'history',
                    'title' => 'fs_view_history',
                    'link' => \IPS\Http\Url::internal("app=forcesensitivity&module=forcesensitivity&controller=members&do=history&id={$row['member_id']}"),
                    'data' => ['ipsDialog' => '', 'ipsDialog-title' => \IPS\Member::loggedIn()->language()->addToStack('fs_member_history')]
                ];
                
                // Reroll
                if (\IPS\Settings::i()->fs_reroll_enabled) {
                    $buttons['reroll'] = [
                        'icon' => 'refresh',
                        'title' => 'fs_reroll',
                        'link' => \IPS\Http\Url::internal("app=forcesensitivity&module=forcesensitivity&controller=members&do=reroll&id={$row['member_id']}"),
                        'data' => ['confirm' => '', 'confirmMessage' => \IPS\Member::loggedIn()->language()->addToStack('fs_reroll_confirm')]
                    ];
                }
                
                // Toggle status
                if (\IPS\Settings::i()->fs_admin_override) {
                    if ($row['is_force_sensitive']) {
                        $buttons['setBlind'] = [
                            'icon' => 'times',
                            'title' => 'fs_set_blind',
                            'link' => \IPS\Http\Url::internal("app=forcesensitivity&module=forcesensitivity&controller=members&do=setStatus&id={$row['member_id']}&status=0"),
                            'data' => ['confirm' => '']
                        ];
                    } else {
                        $buttons['setSensitive'] = [
                            'icon' => 'check',
                            'title' => 'fs_set_sensitive',
                            'link' => \IPS\Http\Url::internal("app=forcesensitivity&module=forcesensitivity&controller=members&do=setStatus&id={$row['member_id']}&status=1"),
                            'data' => ['confirm' => '']
                        ];
                    }
                }
            }
            
            return $buttons;
        };
        
        // Bulk actions
        if (\IPS\Settings::i()->fs_bulk_operations && \IPS\Member::loggedIn()->hasAcpRestriction('forcesensitivity', 'forcesensitivity', 'fs_members_manage')) {
            $table->multiSelect = true;
            $table->multiSelectButtons = [
                'reroll' => [
                    'icon' => 'refresh',
                    'title' => 'fs_bulk_reroll',
                    'confirm' => true
                ],
                'setSensitive' => [
                    'icon' => 'check',
                    'title' => 'fs_bulk_set_sensitive',
                    'confirm' => true
                ],
                'setBlind' => [
                    'icon' => 'times',
                    'title' => 'fs_bulk_set_blind',
                    'confirm' => true
                ]
            ];
        }
        
        // Quick search for members
        $table->quickSearch = 'member_id';
        $table->advancedSearch = [
            'member_id' => \IPS\Helpers\Table\SEARCH_MEMBER,
            'detection_method' => [
                \IPS\Helpers\Table\SEARCH_SELECT,
                [
                    'options' => [
                        '' => 'any',
                        'registration' => 'fs_method_registration',
                        'admin' => 'fs_method_admin',
                        'reroll' => 'fs_method_reroll',
                        'event' => 'fs_method_event'
                    ]
                ]
            ],
            'detection_date' => \IPS\Helpers\Table\SEARCH_DATE_RANGE
        ];
        
        // Output
        \IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('fs_members_title');
        \IPS\Output::i()->sidebar['actions'] = $this->getSidebarActions();
        \IPS\Output::i()->output = (string) $table;
    }
    
    /**
     * Get sidebar actions
     * 
     * @return array
     */
    protected function getSidebarActions(): array
    {
        $actions = [];
        
        if (\IPS\Member::loggedIn()->hasAcpRestriction('forcesensitivity', 'forcesensitivity', 'fs_members_manage')) {
            $actions['detectAll'] = [
                'icon' => 'magic',
                'title' => 'fs_detect_undetermined',
                'link' => \IPS\Http\Url::internal('app=forcesensitivity&module=forcesensitivity&controller=members&do=detectUndetermined'),
                'data' => ['confirm' => '', 'confirmMessage' => \IPS\Member::loggedIn()->language()->addToStack('fs_detect_undetermined_confirm')]
            ];
        }
        
        return $actions;
    }
    
    /**
     * View member history
     * 
     * @return void
     */
    protected function history(): void
    {
        $memberId = (int) \IPS\Request::i()->id;
        $member = \IPS\Member::load($memberId);
        
        if (!$member->member_id) {
            \IPS\Output::i()->error('fs_invalid_member', '2FS301/1');
        }
        
        $entries = \IPS\forcesensitivity\Log\Entry::getForMember($member, 100);
        
        \IPS\Output::i()->output = \IPS\Theme::i()->getTemplate('members', 'forcesensitivity', 'admin')->history($member, $entries);
    }
    
    /**
     * Reroll a member's Force Sensitivity
     * 
     * @return void
     */
    protected function reroll(): void
    {
        \IPS\Session::i()->csrfCheck();
        \IPS\Dispatcher::i()->checkAcpPermission('fs_members_manage');
        
        $memberId = (int) \IPS\Request::i()->id;
        $member = \IPS\Member::load($memberId);
        
        if (!$member->member_id) {
            \IPS\Output::i()->error('fs_invalid_member', '2FS301/2');
        }
        
        // Perform detection
        $result = \IPS\forcesensitivity\ForceSensitivity\Detector::detect(
            $member,
            'reroll',
            null,
            \IPS\Member::loggedIn()
        );
        
        $message = $result ? 'fs_rerolled_sensitive' : 'fs_rerolled_blind';
        
        \IPS\Output::i()->redirect(
            \IPS\Http\Url::internal('app=forcesensitivity&module=forcesensitivity&controller=members'),
            $message
        );
    }
    
    /**
     * Set member status directly
     * 
     * @return void
     */
    protected function setStatus(): void
    {
        \IPS\Session::i()->csrfCheck();
        \IPS\Dispatcher::i()->checkAcpPermission('fs_members_manage');
        
        if (!\IPS\Settings::i()->fs_admin_override) {
            \IPS\Output::i()->error('fs_override_disabled', '1FS301/3');
        }
        
        $memberId = (int) \IPS\Request::i()->id;
        $status = (bool) \IPS\Request::i()->status;
        $member = \IPS\Member::load($memberId);
        
        if (!$member->member_id) {
            \IPS\Output::i()->error('fs_invalid_member', '2FS301/4');
        }
        
        \IPS\forcesensitivity\ForceSensitivity\Detector::setStatus(
            $member,
            $status,
            \IPS\Member::loggedIn(),
            'Admin override via ACP'
        );
        
        $message = $status ? 'fs_set_sensitive_success' : 'fs_set_blind_success';
        
        \IPS\Output::i()->redirect(
            \IPS\Http\Url::internal('app=forcesensitivity&module=forcesensitivity&controller=members'),
            $message
        );
    }
    
    /**
     * Detect all undetermined members
     * 
     * @return void
     */
    protected function detectUndetermined(): void
    {
        \IPS\Session::i()->csrfCheck();
        \IPS\Dispatcher::i()->checkAcpPermission('fs_members_manage');
        
        // Find members without a status
        $query = \IPS\Db::i()->select(
            'member_id',
            'core_members',
            [
                'member_id NOT IN (?)',
                \IPS\Db::i()->select('member_id', 'forcesensitivity_status')
            ],
            null,
            1000 // Process in batches
        );
        
        $count = 0;
        foreach ($query as $memberId) {
            $member = \IPS\Member::load($memberId);
            if ($member->member_id) {
                \IPS\forcesensitivity\ForceSensitivity\Detector::detect(
                    $member,
                    'admin',
                    null,
                    \IPS\Member::loggedIn()
                );
                $count++;
            }
        }
        
        \IPS\Output::i()->redirect(
            \IPS\Http\Url::internal('app=forcesensitivity&module=forcesensitivity&controller=members'),
            \IPS\Member::loggedIn()->language()->addToStack('fs_detected_count', false, ['sprintf' => [$count]])
        );
    }
    
    /**
     * Handle bulk actions
     * 
     * @return void
     */
    protected function multimod(): void
    {
        \IPS\Session::i()->csrfCheck();
        \IPS\Dispatcher::i()->checkAcpPermission('fs_members_manage');
        
        $action = \IPS\Request::i()->act;
        $ids = array_keys(\IPS\Request::i()->multimod ?? []);
        
        if (empty($ids)) {
            \IPS\Output::i()->redirect(
                \IPS\Http\Url::internal('app=forcesensitivity&module=forcesensitivity&controller=members')
            );
        }
        
        $count = 0;
        
        foreach ($ids as $statusId) {
            try {
                $status = \IPS\forcesensitivity\ForceSensitivity\Status::load($statusId);
                $member = \IPS\Member::load($status->member_id);
                
                if (!$member->member_id) {
                    continue;
                }
                
                switch ($action) {
                    case 'reroll':
                        \IPS\forcesensitivity\ForceSensitivity\Detector::detect(
                            $member,
                            'reroll',
                            null,
                            \IPS\Member::loggedIn()
                        );
                        $count++;
                        break;
                        
                    case 'setSensitive':
                        \IPS\forcesensitivity\ForceSensitivity\Detector::setStatus(
                            $member,
                            true,
                            \IPS\Member::loggedIn(),
                            'Bulk operation'
                        );
                        $count++;
                        break;
                        
                    case 'setBlind':
                        \IPS\forcesensitivity\ForceSensitivity\Detector::setStatus(
                            $member,
                            false,
                            \IPS\Member::loggedIn(),
                            'Bulk operation'
                        );
                        $count++;
                        break;
                }
            } catch (\Exception $e) {
                \IPS\Log::log($e, 'forcesensitivity');
            }
        }
        
        // Log bulk operation
        \IPS\forcesensitivity\Log\Entry::logSystem(
            \IPS\Member::loggedIn(),
            'bulk_operation',
            null,
            $action,
            [
                'count' => $count,
                'admin' => \IPS\Member::loggedIn()->member_id
            ]
        );
        
        \IPS\Output::i()->redirect(
            \IPS\Http\Url::internal('app=forcesensitivity&module=forcesensitivity&controller=members'),
            \IPS\Member::loggedIn()->language()->addToStack('fs_bulk_complete', false, ['sprintf' => [$count]])
        );
    }
}
