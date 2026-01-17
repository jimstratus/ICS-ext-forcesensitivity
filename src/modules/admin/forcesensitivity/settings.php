<?php
/**
 * Force Sensitivity Detector - Admin Settings Module
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
 * Settings Controller
 * 
 * Manages all Force Sensitivity configuration options in the ACP.
 */
class _settings extends \IPS\Dispatcher\Controller
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
        \IPS\Dispatcher::i()->checkAcpPermission('fs_settings_manage');
        parent::execute();
    }
    
    /**
     * Settings Form
     *
     * @return void
     */
    protected function manage(): void
    {
        // Build the settings form
        $form = new \IPS\Helpers\Form;
        
        // Detection Settings Tab
        $form->addTab('fs_tab_detection');
        $form->addHeader('fs_detection_settings');
        
        $form->add(new \IPS\Helpers\Form\YesNo(
            'fs_detection_enabled',
            \IPS\Settings::i()->fs_detection_enabled,
            false,
            [],
            null,
            null,
            null,
            'fs_detection_enabled'
        ));
        
        // Probability Settings
        $form->addHeader('fs_probability_settings');
        
        $form->add(new \IPS\Helpers\Form\Number(
            'fs_base_probability',
            \IPS\Settings::i()->fs_base_probability * 100,
            true,
            [
                'min' => 0,
                'max' => 100,
                'step' => 0.1,
                'decimals' => 1
            ],
            null,
            null,
            '%',
            'fs_base_probability'
        ));
        
        $form->add(new \IPS\Helpers\Form\Number(
            'fs_min_probability',
            \IPS\Settings::i()->fs_min_probability * 100,
            true,
            [
                'min' => 0,
                'max' => 100,
                'step' => 0.1,
                'decimals' => 1
            ],
            null,
            null,
            '%',
            'fs_min_probability'
        ));
        
        $form->add(new \IPS\Helpers\Form\Number(
            'fs_max_probability',
            \IPS\Settings::i()->fs_max_probability * 100,
            true,
            [
                'min' => 0,
                'max' => 100,
                'step' => 0.1,
                'decimals' => 1
            ],
            null,
            null,
            '%',
            'fs_max_probability'
        ));
        
        // Ratio Management Tab
        $form->addTab('fs_tab_ratio');
        $form->addHeader('fs_ratio_settings');
        
        $form->add(new \IPS\Helpers\Form\Number(
            'fs_target_ratio',
            \IPS\Settings::i()->fs_target_ratio * 100,
            true,
            [
                'min' => 0,
                'max' => 100,
                'step' => 0.1,
                'decimals' => 1
            ],
            null,
            null,
            '%',
            'fs_target_ratio'
        ));
        
        $form->add(new \IPS\Helpers\Form\Select(
            'fs_ratio_enforcement',
            \IPS\Settings::i()->fs_ratio_enforcement,
            true,
            [
                'options' => [
                    'none' => 'fs_ratio_none',
                    'soft' => 'fs_ratio_soft',
                    'hard' => 'fs_ratio_hard'
                ]
            ],
            null,
            null,
            null,
            'fs_ratio_enforcement'
        ));
        
        $form->add(new \IPS\Helpers\Form\Number(
            'fs_ratio_window',
            \IPS\Settings::i()->fs_ratio_window,
            true,
            [
                'min' => 0,
                'max' => 10000
            ],
            null,
            null,
            \IPS\Member::loggedIn()->language()->addToStack('fs_users'),
            'fs_ratio_window'
        ));
        
        $form->add(new \IPS\Helpers\Form\YesNo(
            'fs_auto_adjust',
            \IPS\Settings::i()->fs_auto_adjust,
            false,
            [],
            null,
            null,
            null,
            'fs_auto_adjust'
        ));
        
        // Admin Controls Tab
        $form->addTab('fs_tab_admin');
        $form->addHeader('fs_admin_settings');
        
        $form->add(new \IPS\Helpers\Form\YesNo(
            'fs_admin_override',
            \IPS\Settings::i()->fs_admin_override,
            false,
            [],
            null,
            null,
            null,
            'fs_admin_override'
        ));
        
        $form->add(new \IPS\Helpers\Form\YesNo(
            'fs_reroll_enabled',
            \IPS\Settings::i()->fs_reroll_enabled,
            false,
            [
                'togglesOn' => ['fs_reroll_cooldown']
            ],
            null,
            null,
            null,
            'fs_reroll_enabled'
        ));
        
        $form->add(new \IPS\Helpers\Form\Number(
            'fs_reroll_cooldown',
            \IPS\Settings::i()->fs_reroll_cooldown,
            false,
            [
                'min' => 0,
                'max' => 31536000, // 1 year
                'unlimited' => 0,
                'unlimitedLang' => 'fs_no_cooldown'
            ],
            null,
            null,
            \IPS\Member::loggedIn()->language()->addToStack('fs_seconds'),
            'fs_reroll_cooldown'
        ));
        
        $form->add(new \IPS\Helpers\Form\YesNo(
            'fs_bulk_operations',
            \IPS\Settings::i()->fs_bulk_operations ?? true,
            false,
            [],
            null,
            null,
            null,
            'fs_bulk_operations'
        ));
        
        // Display Settings Tab
        $form->addTab('fs_tab_display');
        $form->addHeader('fs_display_settings');
        
        $form->add(new \IPS\Helpers\Form\YesNo(
            'fs_show_badge',
            \IPS\Settings::i()->fs_show_badge,
            false,
            [
                'togglesOn' => ['fs_badge_style']
            ],
            null,
            null,
            null,
            'fs_show_badge'
        ));
        
        $form->add(new \IPS\Helpers\Form\Select(
            'fs_badge_style',
            \IPS\Settings::i()->fs_badge_style ?? 'glow',
            false,
            [
                'options' => [
                    'simple' => 'fs_badge_simple',
                    'glow' => 'fs_badge_glow',
                    'animated' => 'fs_badge_animated'
                ]
            ],
            null,
            null,
            null,
            'fs_badge_style'
        ));
        
        $form->add(new \IPS\Helpers\Form\YesNo(
            'fs_show_in_posts',
            \IPS\Settings::i()->fs_show_in_posts,
            false,
            [],
            null,
            null,
            null,
            'fs_show_in_posts'
        ));
        
        // Labels
        $form->addHeader('fs_label_settings');
        
        $form->add(new \IPS\Helpers\Form\Text(
            'fs_sensitive_label',
            \IPS\Settings::i()->fs_sensitive_label ?? 'Force Sensitive',
            false,
            [
                'maxLength' => 100,
                'placeholder' => 'Force Sensitive'
            ],
            null,
            null,
            null,
            'fs_sensitive_label'
        ));
        
        $form->add(new \IPS\Helpers\Form\Text(
            'fs_blind_label',
            \IPS\Settings::i()->fs_blind_label ?? 'Force Blind',
            false,
            [
                'maxLength' => 100,
                'placeholder' => 'Force Blind'
            ],
            null,
            null,
            null,
            'fs_blind_label'
        ));
        
        // Advanced Settings Tab
        $form->addTab('fs_tab_advanced');
        $form->addHeader('fs_advanced_settings');
        
        $form->add(new \IPS\Helpers\Form\Number(
            'fs_log_retention',
            \IPS\Settings::i()->fs_log_retention ?? 0,
            false,
            [
                'min' => 0,
                'unlimited' => 0,
                'unlimitedLang' => 'fs_keep_forever'
            ],
            null,
            null,
            \IPS\Member::loggedIn()->language()->addToStack('fs_days'),
            'fs_log_retention'
        ));
        
        // Handle form submission
        if ($values = $form->values()) {
            // Convert percentages back to decimals
            $values['fs_base_probability'] = $values['fs_base_probability'] / 100;
            $values['fs_min_probability'] = $values['fs_min_probability'] / 100;
            $values['fs_max_probability'] = $values['fs_max_probability'] / 100;
            $values['fs_target_ratio'] = $values['fs_target_ratio'] / 100;
            
            // Save settings
            $form->saveAsSettings($values);
            
            // Log settings change
            \IPS\forcesensitivity\Log\Entry::logSystem(
                \IPS\Member::loggedIn(),
                'settings_changed',
                null,
                null,
                ['changed_by' => \IPS\Member::loggedIn()->member_id]
            );
            
            // Redirect with success message
            \IPS\Output::i()->redirect(
                \IPS\Http\Url::internal('app=forcesensitivity&module=forcesensitivity&controller=settings'),
                'fs_settings_saved'
            );
        }
        
        // Display statistics sidebar
        $stats = $this->getStatistics();
        
        // Output
        \IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('fs_settings_title');
        \IPS\Output::i()->sidebar['actions'] = $this->getSidebarActions();
        \IPS\Output::i()->output = \IPS\Theme::i()->getTemplate('settings', 'forcesensitivity', 'admin')->settings($form, $stats);
    }
    
    /**
     * Get current statistics
     * 
     * @return array
     */
    protected function getStatistics(): array
    {
        $stats = \IPS\forcesensitivity\ForceSensitivity\RatioManager::getAllTimeStats();
        
        return [
            'total' => $stats['total'],
            'sensitive' => $stats['sensitive'],
            'blind' => $stats['blind'],
            'ratio' => $stats['total'] > 0 ? round($stats['sensitive'] / $stats['total'] * 100, 1) : 0,
            'target_ratio' => \IPS\Settings::i()->fs_target_ratio * 100,
            'active_modifiers' => \IPS\forcesensitivity\ForceSensitivity\Modifier::count(['is_active=?', 1])
        ];
    }
    
    /**
     * Get sidebar actions
     * 
     * @return array
     */
    protected function getSidebarActions(): array
    {
        return [
            'export' => [
                'icon' => 'download',
                'title' => 'fs_export_settings',
                'link' => \IPS\Http\Url::internal('app=forcesensitivity&module=forcesensitivity&controller=settings&do=export')
            ],
            'import' => [
                'icon' => 'upload',
                'title' => 'fs_import_settings',
                'link' => \IPS\Http\Url::internal('app=forcesensitivity&module=forcesensitivity&controller=settings&do=import'),
                'data' => ['ipsDialog' => '', 'ipsDialog-title' => \IPS\Member::loggedIn()->language()->addToStack('fs_import_settings')]
            ],
            'reset' => [
                'icon' => 'refresh',
                'title' => 'fs_reset_defaults',
                'link' => \IPS\Http\Url::internal('app=forcesensitivity&module=forcesensitivity&controller=settings&do=reset'),
                'data' => ['confirm' => '', 'confirmMessage' => \IPS\Member::loggedIn()->language()->addToStack('fs_reset_confirm')]
            ]
        ];
    }
    
    /**
     * Export settings as JSON
     * 
     * @return void
     */
    protected function export(): void
    {
        $settings = [
            'fs_detection_enabled' => \IPS\Settings::i()->fs_detection_enabled,
            'fs_base_probability' => \IPS\Settings::i()->fs_base_probability,
            'fs_min_probability' => \IPS\Settings::i()->fs_min_probability,
            'fs_max_probability' => \IPS\Settings::i()->fs_max_probability,
            'fs_target_ratio' => \IPS\Settings::i()->fs_target_ratio,
            'fs_ratio_enforcement' => \IPS\Settings::i()->fs_ratio_enforcement,
            'fs_ratio_window' => \IPS\Settings::i()->fs_ratio_window,
            'fs_auto_adjust' => \IPS\Settings::i()->fs_auto_adjust,
            'fs_admin_override' => \IPS\Settings::i()->fs_admin_override,
            'fs_reroll_enabled' => \IPS\Settings::i()->fs_reroll_enabled,
            'fs_reroll_cooldown' => \IPS\Settings::i()->fs_reroll_cooldown,
            'fs_show_badge' => \IPS\Settings::i()->fs_show_badge,
            'fs_badge_style' => \IPS\Settings::i()->fs_badge_style,
            'fs_show_in_posts' => \IPS\Settings::i()->fs_show_in_posts,
            'fs_sensitive_label' => \IPS\Settings::i()->fs_sensitive_label,
            'fs_blind_label' => \IPS\Settings::i()->fs_blind_label,
            'exported_at' => date('Y-m-d H:i:s'),
            'version' => \IPS\forcesensitivity\Application::$version
        ];
        
        \IPS\Output::i()->sendOutput(
            json_encode($settings, JSON_PRETTY_PRINT),
            200,
            'application/json',
            [
                'Content-Disposition' => 'attachment; filename="forcesensitivity-settings-' . date('Y-m-d') . '.json"'
            ]
        );
    }
    
    /**
     * Import settings from JSON
     * 
     * @return void
     */
    protected function import(): void
    {
        $form = new \IPS\Helpers\Form;
        
        $form->add(new \IPS\Helpers\Form\Upload(
            'fs_import_file',
            null,
            true,
            [
                'allowedFileTypes' => ['json'],
                'temporary' => true
            ]
        ));
        
        if ($values = $form->values()) {
            $file = $values['fs_import_file'];
            $content = file_get_contents($file);
            $settings = json_decode($content, true);
            
            if ($settings === null) {
                \IPS\Output::i()->error('fs_import_invalid_json', '1FS201/1');
            }
            
            // Apply settings
            $toSave = [];
            $validKeys = [
                'fs_detection_enabled', 'fs_base_probability', 'fs_min_probability',
                'fs_max_probability', 'fs_target_ratio', 'fs_ratio_enforcement',
                'fs_ratio_window', 'fs_auto_adjust', 'fs_admin_override',
                'fs_reroll_enabled', 'fs_reroll_cooldown', 'fs_show_badge',
                'fs_badge_style', 'fs_show_in_posts', 'fs_sensitive_label', 'fs_blind_label'
            ];
            
            foreach ($validKeys as $key) {
                if (isset($settings[$key])) {
                    $toSave[$key] = $settings[$key];
                }
            }
            
            \IPS\Settings::i()->changeValues($toSave);
            
            \IPS\Output::i()->redirect(
                \IPS\Http\Url::internal('app=forcesensitivity&module=forcesensitivity&controller=settings'),
                'fs_settings_imported'
            );
        }
        
        \IPS\Output::i()->output = $form;
    }
    
    /**
     * Reset settings to defaults
     * 
     * @return void
     */
    protected function reset(): void
    {
        \IPS\Session::i()->csrfCheck();
        
        $defaults = [
            'fs_detection_enabled' => 1,
            'fs_base_probability' => 0.05,
            'fs_min_probability' => 0.01,
            'fs_max_probability' => 0.50,
            'fs_target_ratio' => 0.10,
            'fs_ratio_enforcement' => 'soft',
            'fs_ratio_window' => 100,
            'fs_auto_adjust' => 1,
            'fs_admin_override' => 1,
            'fs_reroll_enabled' => 1,
            'fs_reroll_cooldown' => 86400,
            'fs_show_badge' => 1,
            'fs_badge_style' => 'glow',
            'fs_show_in_posts' => 1,
            'fs_sensitive_label' => 'Force Sensitive',
            'fs_blind_label' => 'Force Blind'
        ];
        
        \IPS\Settings::i()->changeValues($defaults);
        
        \IPS\Output::i()->redirect(
            \IPS\Http\Url::internal('app=forcesensitivity&module=forcesensitivity&controller=settings'),
            'fs_settings_reset'
        );
    }
}
