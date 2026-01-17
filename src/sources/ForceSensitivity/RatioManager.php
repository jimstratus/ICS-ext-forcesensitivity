<?php
/**
 * Force Sensitivity Detector - Ratio Manager
 * 
 * @package     IPS\forcesensitivity
 * @subpackage  ForceSensitivity
 * @author      jimstratus
 * @copyright   2026
 * @license     MIT
 */

namespace IPS\forcesensitivity\ForceSensitivity;

/**
 * Manages community-wide Force Sensitivity ratios
 * 
 * This class handles the calculation and enforcement of the desired
 * Force Sensitive to total users ratio.
 */
class _RatioManager
{
    /**
     * Cached current ratio (per-request)
     */
    protected static ?float $cachedRatio = null;
    
    /**
     * Get the current Force Sensitivity ratio
     * 
     * Calculates the ratio of Force Sensitive users within the configured
     * window of recent users.
     * 
     * @param bool $useCache Whether to use cached value
     * @return float Ratio between 0.0 and 1.0
     */
    public static function getCurrentRatio(bool $useCache = true): float
    {
        if ($useCache && static::$cachedRatio !== null) {
            return static::$cachedRatio;
        }
        
        $window = (int) \IPS\Settings::i()->fs_ratio_window;
        
        if ($window <= 0) {
            // Use all users
            $stats = static::getAllTimeStats();
        } else {
            // Use windowed stats
            $stats = static::getWindowedStats($window);
        }
        
        if ($stats['total'] === 0) {
            static::$cachedRatio = 0.0;
            return 0.0;
        }
        
        static::$cachedRatio = $stats['sensitive'] / $stats['total'];
        return static::$cachedRatio;
    }
    
    /**
     * Get all-time statistics
     * 
     * @return array{total: int, sensitive: int, blind: int}
     */
    public static function getAllTimeStats(): array
    {
        $result = \IPS\Db::i()->select(
            'COUNT(*) as total, SUM(is_force_sensitive) as sensitive',
            'forcesensitivity_status'
        )->first();
        
        return [
            'total' => (int) $result['total'],
            'sensitive' => (int) $result['sensitive'],
            'blind' => (int) $result['total'] - (int) $result['sensitive']
        ];
    }
    
    /**
     * Get windowed statistics
     * 
     * @param int $window Number of recent records to consider
     * @return array{total: int, sensitive: int, blind: int}
     */
    public static function getWindowedStats(int $window): array
    {
        // Get the most recent $window records
        $result = \IPS\Db::i()->select(
            'is_force_sensitive',
            'forcesensitivity_status',
            null,
            'detection_date DESC',
            $window
        );
        
        $total = 0;
        $sensitive = 0;
        
        foreach ($result as $row) {
            $total++;
            if ($row['is_force_sensitive']) {
                $sensitive++;
            }
        }
        
        return [
            'total' => $total,
            'sensitive' => $sensitive,
            'blind' => $total - $sensitive
        ];
    }
    
    /**
     * Calculate probability adjustment based on current ratio
     * 
     * @param float $baseProbability The base probability before adjustment
     * @return float The adjustment to apply (can be negative)
     */
    public static function calculateAdjustment(float $baseProbability): float
    {
        $enforcementMode = \IPS\Settings::i()->fs_ratio_enforcement;
        
        if ($enforcementMode === 'none') {
            return 0.0;
        }
        
        $currentRatio = static::getCurrentRatio();
        $targetRatio = (float) \IPS\Settings::i()->fs_target_ratio;
        
        // Calculate how far we are from target
        $deviation = $targetRatio - $currentRatio;
        
        // If we're within 5% of target, no adjustment needed
        if (abs($deviation) < 0.05) {
            return 0.0;
        }
        
        if ($enforcementMode === 'soft') {
            return static::calculateSoftAdjustment($baseProbability, $deviation, $targetRatio);
        }
        
        if ($enforcementMode === 'hard') {
            return static::calculateHardAdjustment($baseProbability, $deviation, $targetRatio, $currentRatio);
        }
        
        return 0.0;
    }
    
    /**
     * Calculate soft enforcement adjustment
     * 
     * Provides gradual, gentle adjustments toward the target ratio.
     * 
     * @param float $baseProbability Base probability
     * @param float $deviation How far from target (positive = under target)
     * @param float $targetRatio The target ratio
     * @return float Adjustment value
     */
    protected static function calculateSoftAdjustment(
        float $baseProbability,
        float $deviation,
        float $targetRatio
    ): float {
        // Scale adjustment based on deviation magnitude
        // Maximum adjustment is ±50% of base probability
        $scaleFactor = $deviation / max($targetRatio, 0.01);
        $adjustment = $baseProbability * $scaleFactor * 0.5;
        
        // Cap adjustment at ±25% absolute
        return max(-0.25, min(0.25, $adjustment));
    }
    
    /**
     * Calculate hard enforcement adjustment
     * 
     * Provides aggressive adjustments to quickly reach target ratio.
     * 
     * @param float $baseProbability Base probability
     * @param float $deviation How far from target
     * @param float $targetRatio The target ratio
     * @param float $currentRatio Current ratio
     * @return float Adjustment value
     */
    protected static function calculateHardAdjustment(
        float $baseProbability,
        float $deviation,
        float $targetRatio,
        float $currentRatio
    ): float {
        // Significantly under target (< 80% of target)
        if ($currentRatio < $targetRatio * 0.8) {
            // Double the probability
            return $baseProbability;
        }
        
        // Significantly over target (> 120% of target)
        if ($currentRatio > $targetRatio * 1.2) {
            // Reduce probability by 75%
            return -($baseProbability * 0.75);
        }
        
        // Normal hard adjustment
        $scaleFactor = $deviation / max($targetRatio, 0.01);
        return $baseProbability * $scaleFactor;
    }
    
    /**
     * Get ratio trend data for dashboard
     * 
     * @param int $days Number of days to look back
     * @return array Array of daily ratio data
     */
    public static function getTrendData(int $days = 30): array
    {
        $trends = [];
        $now = new \IPS\DateTime();
        
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = clone $now;
            $date->sub(new \DateInterval("P{$i}D"));
            $dateStr = $date->format('Y-m-d');
            
            // Get cumulative stats up to this date
            $result = \IPS\Db::i()->select(
                'COUNT(*) as total, SUM(is_force_sensitive) as sensitive',
                'forcesensitivity_status',
                ['detection_date <= ?', $dateStr . ' 23:59:59']
            )->first();
            
            $total = (int) $result['total'];
            $sensitive = (int) $result['sensitive'];
            
            $trends[] = [
                'date' => $dateStr,
                'total' => $total,
                'sensitive' => $sensitive,
                'ratio' => $total > 0 ? round($sensitive / $total, 4) : 0
            ];
        }
        
        return $trends;
    }
    
    /**
     * Clear the cached ratio
     * 
     * Should be called after any status changes.
     * 
     * @return void
     */
    public static function clearCache(): void
    {
        static::$cachedRatio = null;
    }
}
