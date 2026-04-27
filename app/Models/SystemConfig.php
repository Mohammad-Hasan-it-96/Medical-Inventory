<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemConfig extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'key',
        'value',
        'group',
    ];

    /**
     * Cache duration in seconds (default: 24 hours)
     */
    protected static $cacheDuration = 86400;

    /**
     * Cache key prefix
     */
    protected static $cachePrefix = 'system_config_';

    /**
     * Get a configuration value by key
     *
     * @param string $key The configuration key
     * @param mixed $default Default value if key doesn't exist
     * @return mixed The configuration value
     */
    public static function get($key, $default = null)
    {
        // Try to get from cache first
        $cacheKey = self::$cachePrefix . $key;
        
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        
        // If not in cache, get from database
        $config = self::where('key', $key)->first();
        
        if (!$config) {
            return $default;
        }
        
        // Store in cache for future requests
        Cache::put($cacheKey, $config->value, self::$cacheDuration);
        
        return $config->value;
    }

    /**
     * Set a configuration value
     *
     * @param string $key The configuration key
     * @param mixed $value The value to set
     * @param string $group The group this config belongs to
     * @return bool Success status
     */
    public static function set($key, $value, $group = 'general')
    {
        // Update or create the config
        $config = self::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );
        
        // Update the cache
        $cacheKey = self::$cachePrefix . $key;
        Cache::put($cacheKey, $value, self::$cacheDuration);
        
        return $config ? true : false;
    }

    /**
     * Get all configurations by group
     *
     * @param string $group The group name
     * @return array Configurations in the specified group
     */
    public static function getGroup($group = 'general')
    {
        $cacheKey = self::$cachePrefix . 'group_' . $group;
        
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        
        $configs = self::where('group', $group)->get()->pluck('value', 'key')->toArray();
        
        Cache::put($cacheKey, $configs, self::$cacheDuration);
        
        return $configs;
    }

    /**
     * Clear the cache for a specific key or group
     *
     * @param string|null $key The key to clear (or null to clear all)
     * @param string|null $group The group to clear
     * @return void
     */
    public static function clearCache($key = null, $group = null)
    {
        if ($key) {
            Cache::forget(self::$cachePrefix . $key);
        } elseif ($group) {
            Cache::forget(self::$cachePrefix . 'group_' . $group);
        } else {
            // Clear all system config cache
            $keys = self::all()->pluck('key')->toArray();
            foreach ($keys as $k) {
                Cache::forget(self::$cachePrefix . $k);
            }
            
            // Clear all group caches
            $groups = self::distinct()->pluck('group')->toArray();
            foreach ($groups as $g) {
                Cache::forget(self::$cachePrefix . 'group_' . $g);
            }
        }
    }
}