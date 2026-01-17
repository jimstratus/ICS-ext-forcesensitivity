<?php
/**
 * Force Sensitivity Detector - Admin Logs Module
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
 * Logs Controller
 * 
 * Displays and manages the Force Sensitivity audit log.
 */
class _logs extends \IPS\Dispatcher\Controller
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
        \IPS\Dispatcher::i()->checkAcpPermission('fs_logs_view');
        parent::execute();
    }
    
    /**
     * Logs List
     *
     * @return void
     */
    protected function manage(): void
    {
        // Build table
        $table = new \IPS\Helpers\Table\Db(
            'forcesensitivity_log',
            \IPS\Http\Url::internal('app=forcesensitivity&module=forcesensitivity&controller=logs')
        );
        
        $table->langPrefix = 'fs_log_';
        $table->include = ['timestamp', 'member_id', 'action', 'old_value', 'new_value', 'performed_by', 'ip_address'];
        $table->mainColumn = 'timestamp';
        
        // Sorting
        $table->sortBy = $table->sortBy ?: 'timestamp';
        $table->sortDirection = $table->sortDirection ?: 'desc';
        
        // Filters
        $table->filters = [
            'fs_filter_detection' => "action='detection'",
            'fs_filter_override' => "action='admin_override'",
            'fs_filter_reroll' => "action='reroll'",
            'fs_filter_bulk' => "action='bulk_operation'"
        ];
        
        // Parsers
        $table->parsers = [
            'timestamp' => function($val) {
                $date = new \IPS\DateTime($val);
                return $date->localeDateTime();
            },
            'member_id' => function($val) {
                $member = \IPS\Member::load($val);
                if ($member->member_id) {
                    return $member->link();
                }
                return \IPS\Member::loggedIn()->language()->addToStack('fs_deleted_member') . " (ID: {$val})";
            },
            'action' => function($val) {
                $class = match($val) {
                    'detection' => 'ipsBadge--info',
                    'admin_override' => 'ipsBadge--warning',
                    'reroll' => 'ipsBadge--intermediate',
                    'bulk_operation' => 'ipsBadge--style2',
                    default => 'ipsBadge--neutral'
                };
                $label = \IPS\Member::loggedIn()->language()->addToStack('fs_log_action_' . $val);
                return "<span class='ipsBadge {$class}'>{$label}</span>";
            },
            'old_value' => function($val) {
                if (!$val) {
                    return '<em class="ipsType_light">—</em>';
                }
                return $val === 'sensitive' 
                    ? (\IPS\Settings::i()->fs_sensitive_label ?: \IPS\Member::loggedIn()->language()->addToStack('fs_status_sensitive'))
                    : (\IPS\Settings::i()->fs_blind_label ?: \IPS\Member::loggedIn()->language()->addToStack('fs_status_blind'));
            },
            'new_value' => function($val) {
                if (!$val) {
                    return '<em class="ipsType_light">—</em>';
                }
                return $val === 'sensitive' 
                    ? (\IPS\Settings::i()->fs_sensitive_label ?: \IPS\Member::loggedIn()->language()->addToStack('fs_status_sensitive'))
                    : (\IPS\Settings::i()->fs_blind_label ?: \IPS\Member::loggedIn()->language()->addToStack('fs_status_blind'));
            },
            'performed_by' => function($val) {
                if (!$val) {
                    return '<em class="ipsType_light">' . \IPS\Member::loggedIn()->language()->addToStack('fs_system') . '</em>';
                }
                $member = \IPS\Member::load($val);
                if ($member->member_id) {
                    return $member->link();
                }
                return \IPS\Member::loggedIn()->language()->addToStack('fs_deleted_member');
            },
            'ip_address' => function($val) {
                if (!$val) {
                    return '<em class="ipsType_light">—</em>';
                }
                return "<code>{$val}</code>";
            }
        ];
        
        // Row buttons
        $table->rowButtons = function($row) {
            $buttons = [];
            
            // View details
            if ($row['details']) {
                $buttons['details'] = [
                    'icon' => 'search',
                    'title' => 'fs_view_details',
                    'link' => \IPS\Http\Url::internal("app=forcesensitivity&module=forcesensitivity&controller=logs&do=details&id={$row['id']}"),
                    'data' => ['ipsDialog' => '', 'ipsDialog-title' => \IPS\Member::loggedIn()->language()->addToStack('fs_log_details')]
                ];
            }
            
            return $buttons;
        };
        
        // Advanced search
        $table->advancedSearch = [
            'member_id' => \IPS\Helpers\Table\SEARCH_MEMBER,
            'action' => [
                \IPS\Helpers\Table\SEARCH_SELECT,
                [
                    'options' => [
                        '' => 'any',
                        'detection' => 'fs_log_action_detection',
                        'admin_override' => 'fs_log_action_admin_override',
                        'reroll' => 'fs_log_action_reroll',
                        'bulk_operation' => 'fs_log_action_bulk_operation',
                        'settings_changed' => 'fs_log_action_settings_changed'
                    ]
                ]
            ],
            'performed_by' => \IPS\Helpers\Table\SEARCH_MEMBER,
            'timestamp' => \IPS\Helpers\Table\SEARCH_DATE_RANGE
        ];
        
        // Output
        \IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('fs_logs_title');
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
        
        $actions['exportCsv'] = [
            'icon' => 'file-csv',
            'title' => 'fs_export_csv',
            'link' => \IPS\Http\Url::internal('app=forcesensitivity&module=forcesensitivity&controller=logs&do=export&format=csv')
        ];
        
        $actions['exportJson'] = [
            'icon' => 'file-code',
            'title' => 'fs_export_json',
            'link' => \IPS\Http\Url::internal('app=forcesensitivity&module=forcesensitivity&controller=logs&do=export&format=json')
        ];
        
        if (\IPS\Member::loggedIn()->hasAcpRestriction('forcesensitivity', 'forcesensitivity', 'fs_logs_prune')) {
            $actions['prune'] = [
                'icon' => 'trash',
                'title' => 'fs_prune_logs',
                'link' => \IPS\Http\Url::internal('app=forcesensitivity&module=forcesensitivity&controller=logs&do=prune'),
                'data' => ['ipsDialog' => '', 'ipsDialog-title' => \IPS\Member::loggedIn()->language()->addToStack('fs_prune_logs')]
            ];
        }
        
        return $actions;
    }
    
    /**
     * View log entry details
     * 
     * @return void
     */
    protected function details(): void
    {
        $id = (int) \IPS\Request::i()->id;
        
        try {
            $entry = \IPS\forcesensitivity\Log\Entry::load($id);
        } catch (\OutOfRangeException $e) {
            \IPS\Output::i()->error('fs_log_not_found', '2FS401/1');
        }
        
        $details = $entry->getDetails();
        
        \IPS\Output::i()->output = \IPS\Theme::i()->getTemplate('logs', 'forcesensitivity', 'admin')->details($entry, $details);
    }
    
    /**
     * Export logs
     * 
     * @return void
     */
    protected function export(): void
    {
        $format = \IPS\Request::i()->format ?? 'csv';
        
        // Build filters from request
        $filters = [];
        
        if (\IPS\Request::i()->member_id) {
            $filters['member_id'] = (int) \IPS\Request::i()->member_id;
        }
        
        if (\IPS\Request::i()->action) {
            $filters['action'] = \IPS\Request::i()->action;
        }
        
        if (\IPS\Request::i()->date_start) {
            $filters['date_start'] = \IPS\Request::i()->date_start;
        }
        
        if (\IPS\Request::i()->date_end) {
            $filters['date_end'] = \IPS\Request::i()->date_end;
        }
        
        if ($format === 'json') {
            $content = \IPS\forcesensitivity\Log\Entry::exportJson($filters);
            $filename = 'forcesensitivity-logs-' . date('Y-m-d') . '.json';
            $contentType = 'application/json';
        } else {
            $content = \IPS\forcesensitivity\Log\Entry::exportCsv($filters);
            $filename = 'forcesensitivity-logs-' . date('Y-m-d') . '.csv';
            $contentType = 'text/csv';
        }
        
        \IPS\Output::i()->sendOutput(
            $content,
            200,
            $contentType,
            [
                'Content-Disposition' => "attachment; filename=\"{$filename}\""
            ]
        );
    }
    
    /**
     * Prune old logs
     * 
     * @return void
     */
    protected function prune(): void
    {
        \IPS\Dispatcher::i()->checkAcpPermission('fs_logs_prune');
        
        $form = new \IPS\Helpers\Form;
        
        $form->add(new \IPS\Helpers\Form\Number(
            'fs_prune_days',
            30,
            true,
            [
                'min' => 1,
                'max' => 365
            ],
            null,
            null,
            \IPS\Member::loggedIn()->language()->addToStack('fs_days_old'),
            'fs_prune_days'
        ));
        
        if ($values = $form->values()) {
            \IPS\Session::i()->csrfCheck();
            
            $deleted = \IPS\forcesensitivity\Log\Entry::prune($values['fs_prune_days']);
            
            \IPS\Output::i()->redirect(
                \IPS\Http\Url::internal('app=forcesensitivity&module=forcesensitivity&controller=logs'),
                \IPS\Member::loggedIn()->language()->addToStack('fs_logs_pruned', false, ['sprintf' => [$deleted]])
            );
        }
        
        \IPS\Output::i()->output = $form;
    }
}
