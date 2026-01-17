<?php
/**
 * Force Sensitivity Detector - Admin Modifiers Module
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
 * Modifiers Controller
 * 
 * Manages probability modifiers for Force Sensitivity detection.
 */
class _modifiers extends \IPS\Dispatcher\Controller
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
        \IPS\Dispatcher::i()->checkAcpPermission('fs_modifiers_view');
        parent::execute();
    }
    
    /**
     * Modifiers List
     *
     * @return void
     */
    protected function manage(): void
    {
        // Build table
        $table = new \IPS\Helpers\Table\Db(
            'forcesensitivity_modifiers',
            \IPS\Http\Url::internal('app=forcesensitivity&module=forcesensitivity&controller=modifiers')
        );
        
        $table->langPrefix = 'fs_modifier_';
        $table->include = ['type', 'target_id', 'modifier', 'reason', 'start_date', 'end_date', 'is_active'];
        $table->mainColumn = 'type';
        
        // Sorting
        $table->sortBy = $table->sortBy ?: 'created_date';
        $table->sortDirection = $table->sortDirection ?: 'desc';
        
        // Filters
        $table->filters = [
            'fs_filter_active' => 'is_active=1',
            'fs_filter_inactive' => 'is_active=0',
            'fs_filter_member' => "type='member'",
            'fs_filter_group' => "type='group'",
            'fs_filter_global' => "type='global'",
            'fs_filter_event' => "type='event'"
        ];
        
        // Parsers
        $table->parsers = [
            'type' => function($val) {
                $class = match($val) {
                    'member' => 'ipsBadge--info',
                    'group' => 'ipsBadge--positive',
                    'global' => 'ipsBadge--warning',
                    'event' => 'ipsBadge--style2',
                    default => 'ipsBadge--neutral'
                };
                $label = \IPS\Member::loggedIn()->language()->addToStack('fs_modifier_type_' . $val);
                return "<span class='ipsBadge {$class}'>{$label}</span>";
            },
            'target_id' => function($val, $row) {
                switch ($row['type']) {
                    case 'member':
                        $member = \IPS\Member::load($val);
                        return $member->member_id ? $member->link() : \IPS\Member::loggedIn()->language()->addToStack('fs_deleted_member');
                    case 'group':
                        try {
                            $group = \IPS\Member\Group::load($val);
                            return $group->name;
                        } catch (\Exception $e) {
                            return \IPS\Member::loggedIn()->language()->addToStack('fs_deleted_group');
                        }
                    case 'global':
                        return \IPS\Member::loggedIn()->language()->addToStack('fs_all_members');
                    case 'event':
                        return $row['reason'] ?: \IPS\Member::loggedIn()->language()->addToStack('fs_event');
                    default:
                        return '—';
                }
            },
            'modifier' => function($val) {
                $percentage = round((float) $val * 100, 1);
                $class = $percentage >= 0 ? 'ipsType_positive' : 'ipsType_negative';
                $sign = $percentage >= 0 ? '+' : '';
                return "<span class='{$class}'><strong>{$sign}{$percentage}%</strong></span>";
            },
            'reason' => function($val) {
                return $val ?: '<em class="ipsType_light">—</em>';
            },
            'start_date' => function($val) {
                if (!$val) {
                    return '<em class="ipsType_light">' . \IPS\Member::loggedIn()->language()->addToStack('fs_immediately') . '</em>';
                }
                $date = new \IPS\DateTime($val);
                return $date->localeDate();
            },
            'end_date' => function($val) {
                if (!$val) {
                    return '<em class="ipsType_light">' . \IPS\Member::loggedIn()->language()->addToStack('fs_never') . '</em>';
                }
                $date = new \IPS\DateTime($val);
                return $date->localeDate();
            },
            'is_active' => function($val) {
                if ($val) {
                    return "<span class='ipsBadge ipsBadge--positive'>" . \IPS\Member::loggedIn()->language()->addToStack('fs_active') . "</span>";
                }
                return "<span class='ipsBadge ipsBadge--neutral'>" . \IPS\Member::loggedIn()->language()->addToStack('fs_inactive') . "</span>";
            }
        ];
        
        // Row buttons
        $table->rowButtons = function($row) {
            $buttons = [];
            
            if (\IPS\Member::loggedIn()->hasAcpRestriction('forcesensitivity', 'forcesensitivity', 'fs_modifiers_manage')) {
                // Edit
                $buttons['edit'] = [
                    'icon' => 'pencil',
                    'title' => 'edit',
                    'link' => \IPS\Http\Url::internal("app=forcesensitivity&module=forcesensitivity&controller=modifiers&do=form&id={$row['id']}")
                ];
                
                // Toggle active
                if ($row['is_active']) {
                    $buttons['deactivate'] = [
                        'icon' => 'pause',
                        'title' => 'fs_deactivate',
                        'link' => \IPS\Http\Url::internal("app=forcesensitivity&module=forcesensitivity&controller=modifiers&do=toggle&id={$row['id']}&status=0"),
                        'data' => ['confirm' => '']
                    ];
                } else {
                    $buttons['activate'] = [
                        'icon' => 'play',
                        'title' => 'fs_activate',
                        'link' => \IPS\Http\Url::internal("app=forcesensitivity&module=forcesensitivity&controller=modifiers&do=toggle&id={$row['id']}&status=1"),
                        'data' => ['confirm' => '']
                    ];
                }
                
                // Delete
                $buttons['delete'] = [
                    'icon' => 'times-circle',
                    'title' => 'delete',
                    'link' => \IPS\Http\Url::internal("app=forcesensitivity&module=forcesensitivity&controller=modifiers&do=delete&id={$row['id']}"),
                    'data' => ['delete' => '']
                ];
            }
            
            return $buttons;
        };
        
        // Output
        \IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('fs_modifiers_title');
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
        
        if (\IPS\Member::loggedIn()->hasAcpRestriction('forcesensitivity', 'forcesensitivity', 'fs_modifiers_manage')) {
            $actions['add'] = [
                'primary' => true,
                'icon' => 'plus',
                'title' => 'fs_add_modifier',
                'link' => \IPS\Http\Url::internal('app=forcesensitivity&module=forcesensitivity&controller=modifiers&do=form')
            ];
        }
        
        return $actions;
    }
    
    /**
     * Add/Edit modifier form
     * 
     * @return void
     */
    protected function form(): void
    {
        \IPS\Dispatcher::i()->checkAcpPermission('fs_modifiers_manage');
        
        $modifier = null;
        
        if (\IPS\Request::i()->id) {
            try {
                $modifier = \IPS\forcesensitivity\ForceSensitivity\Modifier::load(\IPS\Request::i()->id);
            } catch (\OutOfRangeException $e) {
                \IPS\Output::i()->error('fs_modifier_not_found', '2FS501/1');
            }
        }
        
        $form = new \IPS\Helpers\Form;
        
        // Type
        $form->add(new \IPS\Helpers\Form\Select(
            'fs_modifier_type',
            $modifier?->type ?? 'member',
            true,
            [
                'options' => [
                    'member' => 'fs_modifier_type_member',
                    'group' => 'fs_modifier_type_group',
                    'global' => 'fs_modifier_type_global',
                    'event' => 'fs_modifier_type_event'
                ],
                'toggles' => [
                    'member' => ['fs_modifier_member'],
                    'group' => ['fs_modifier_group']
                ]
            ],
            null,
            null,
            null,
            'fs_modifier_type'
        ));
        
        // Member target
        $form->add(new \IPS\Helpers\Form\Member(
            'fs_modifier_member',
            $modifier && $modifier->type === 'member' ? \IPS\Member::load($modifier->target_id) : null,
            false,
            [],
            null,
            null,
            null,
            'fs_modifier_member'
        ));
        
        // Group target
        $groups = [];
        foreach (\IPS\Member\Group::groups() as $group) {
            $groups[$group->g_id] = $group->name;
        }
        
        $form->add(new \IPS\Helpers\Form\Select(
            'fs_modifier_group',
            $modifier && $modifier->type === 'group' ? $modifier->target_id : null,
            false,
            [
                'options' => $groups
            ],
            null,
            null,
            null,
            'fs_modifier_group'
        ));
        
        // Modifier value
        $form->add(new \IPS\Helpers\Form\Number(
            'fs_modifier_value',
            $modifier ? $modifier->modifier * 100 : 5,
            true,
            [
                'min' => -100,
                'max' => 100,
                'step' => 0.1,
                'decimals' => 1
            ],
            null,
            null,
            '%',
            'fs_modifier_value'
        ));
        
        // Reason
        $form->add(new \IPS\Helpers\Form\Text(
            'fs_modifier_reason',
            $modifier?->reason ?? '',
            false,
            [
                'maxLength' => 255
            ],
            null,
            null,
            null,
            'fs_modifier_reason'
        ));
        
        // Date range
        $form->add(new \IPS\Helpers\Form\Date(
            'fs_modifier_start',
            $modifier && $modifier->start_date ? new \IPS\DateTime($modifier->start_date) : null,
            false,
            [
                'time' => true
            ],
            null,
            null,
            null,
            'fs_modifier_start'
        ));
        
        $form->add(new \IPS\Helpers\Form\Date(
            'fs_modifier_end',
            $modifier && $modifier->end_date ? new \IPS\DateTime($modifier->end_date) : null,
            false,
            [
                'time' => true
            ],
            null,
            null,
            null,
            'fs_modifier_end'
        ));
        
        // Active
        $form->add(new \IPS\Helpers\Form\YesNo(
            'fs_modifier_active',
            $modifier?->is_active ?? true,
            false,
            [],
            null,
            null,
            null,
            'fs_modifier_active'
        ));
        
        if ($values = $form->values()) {
            // Determine target ID
            $targetId = null;
            
            switch ($values['fs_modifier_type']) {
                case 'member':
                    $targetId = $values['fs_modifier_member']?->member_id;
                    if (!$targetId) {
                        $form->error = \IPS\Member::loggedIn()->language()->addToStack('fs_error_member_required');
                        goto output;
                    }
                    break;
                case 'group':
                    $targetId = $values['fs_modifier_group'];
                    if (!$targetId) {
                        $form->error = \IPS\Member::loggedIn()->language()->addToStack('fs_error_group_required');
                        goto output;
                    }
                    break;
            }
            
            if ($modifier) {
                // Update existing
                $modifier->type = $values['fs_modifier_type'];
                $modifier->target_id = $targetId;
                $modifier->modifier = $values['fs_modifier_value'] / 100;
                $modifier->reason = $values['fs_modifier_reason'] ?: null;
                $modifier->start_date = $values['fs_modifier_start']?->format('Y-m-d H:i:s');
                $modifier->end_date = $values['fs_modifier_end']?->format('Y-m-d H:i:s');
                $modifier->is_active = $values['fs_modifier_active'];
                $modifier->save();
            } else {
                // Create new
                \IPS\forcesensitivity\ForceSensitivity\Modifier::createModifier(
                    $values['fs_modifier_type'],
                    $targetId,
                    $values['fs_modifier_value'] / 100,
                    $values['fs_modifier_reason'] ?: null,
                    $values['fs_modifier_start'],
                    $values['fs_modifier_end'],
                    \IPS\Member::loggedIn()
                );
            }
            
            \IPS\Output::i()->redirect(
                \IPS\Http\Url::internal('app=forcesensitivity&module=forcesensitivity&controller=modifiers'),
                'fs_modifier_saved'
            );
        }
        
        output:
        \IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack($modifier ? 'fs_edit_modifier' : 'fs_add_modifier');
        \IPS\Output::i()->output = $form;
    }
    
    /**
     * Toggle modifier active status
     * 
     * @return void
     */
    protected function toggle(): void
    {
        \IPS\Session::i()->csrfCheck();
        \IPS\Dispatcher::i()->checkAcpPermission('fs_modifiers_manage');
        
        $id = (int) \IPS\Request::i()->id;
        $status = (bool) \IPS\Request::i()->status;
        
        try {
            $modifier = \IPS\forcesensitivity\ForceSensitivity\Modifier::load($id);
            $modifier->is_active = $status ? 1 : 0;
            $modifier->save();
        } catch (\OutOfRangeException $e) {
            \IPS\Output::i()->error('fs_modifier_not_found', '2FS501/2');
        }
        
        \IPS\Output::i()->redirect(
            \IPS\Http\Url::internal('app=forcesensitivity&module=forcesensitivity&controller=modifiers'),
            $status ? 'fs_modifier_activated' : 'fs_modifier_deactivated'
        );
    }
    
    /**
     * Delete modifier
     * 
     * @return void
     */
    protected function delete(): void
    {
        \IPS\Session::i()->csrfCheck();
        \IPS\Dispatcher::i()->checkAcpPermission('fs_modifiers_manage');
        
        $id = (int) \IPS\Request::i()->id;
        
        try {
            $modifier = \IPS\forcesensitivity\ForceSensitivity\Modifier::load($id);
            $modifier->delete();
        } catch (\OutOfRangeException $e) {
            \IPS\Output::i()->error('fs_modifier_not_found', '2FS501/3');
        }
        
        \IPS\Output::i()->redirect(
            \IPS\Http\Url::internal('app=forcesensitivity&module=forcesensitivity&controller=modifiers'),
            'fs_modifier_deleted'
        );
    }
}
