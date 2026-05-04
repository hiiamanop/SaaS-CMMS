<?php

use App\Models\FieldConfiguration;
use Illuminate\Support\Facades\Cache;

if (!function_exists('get_field_config')) {
    function get_field_config($module, $fieldName) {
        // Cache all configurations in one go for efficiency
        $configs = Cache::remember("field_configs_all", 86400, function() {
            return FieldConfiguration::all()->mapWithKeys(function ($item) {
                return ["{$item->module}.{$item->field_name}" => [
                    'is_disabled' => (bool)$item->is_disabled,
                    'is_hidden'   => (bool)$item->is_hidden,
                    'is_required' => (bool)$item->is_required,
                    'placeholder' => $item->placeholder,
                ]];
            })->toArray();
        });

        return $configs["{$module}.{$fieldName}"] ?? null;
    }
}

if (!function_exists('field_is_disabled')) {
    function field_is_disabled($module, $fieldName) {
        $config = get_field_config($module, $fieldName);
        return $config ? $config['is_disabled'] : false;
    }
}

if (!function_exists('field_is_hidden')) {
    function field_is_hidden($module, $fieldName) {
        $config = get_field_config($module, $fieldName);
        return $config ? $config['is_hidden'] : false;
    }
}

if (!function_exists('field_is_required')) {
    function field_is_required($module, $fieldName) {
        $config = get_field_config($module, $fieldName);
        return $config ? $config['is_required'] : false;
    }
}

if (!function_exists('field_attributes')) {
    function field_attributes($module, $fieldName) {
        $config = get_field_config($module, $fieldName);
        if (!$config) return '';
        
        $attrs = [];
        if ($config['is_disabled']) $attrs[] = 'disabled';
        if ($config['is_required']) $attrs[] = 'required';
        if ($config['placeholder']) $attrs[] = 'placeholder="e.g. ' . htmlspecialchars($config['placeholder'], ENT_QUOTES) . '"';
        
        return implode(' ', $attrs);
    }
}

if (!function_exists('field_label')) {
    function field_label($module, $fieldName, $defaultLabel) {
        $required = field_is_required($module, $fieldName);
        $disabled = field_is_disabled($module, $fieldName);
        
        $html = $defaultLabel;
        if ($required) $html .= ' <span class="text-red-500">*</span>';
        if ($disabled) $html .= ' <span class="text-xs text-gray-400 font-normal ml-1 italic">(disabled)</span>';
        
        return $html;
    }
}
