<?php
/**
 * Button Component
 * Reusable button với các style khác nhau và CSS chung
 * 
 * @param string $text - Button text
 * @param string $type - Button type: primary, secondary, outline
 * @param string $href - Link URL (optional, nếu có sẽ render thành <a>)
 * @param string $class - Additional CSS classes
 * @param string $id - Button ID
 * @param string $buttonType - Button type attribute: button, submit, reset (default: button)
 * @param string $onclick - onclick handler
 * @param array $data - Data attributes array (e.g., ['product-id' => '123'])
 * @param bool $disabled - Disabled state
 * @param string $width - Width CSS value (e.g., '200px', 'auto', '100%')
 * @param string $icon - Icon HTML/SVG (optional)
 * @param string $iconPosition - Icon position: 'left' or 'right' (default: 'left')
 * @param string $target - Target attribute for links (e.g., '_blank')
 */
if (!isset($text)) $text = 'Button';
if (!isset($type)) $type = 'primary';
if (!isset($class)) $class = '';
if (!isset($buttonType)) $buttonType = 'button';
if (!isset($disabled)) $disabled = false;
if (!isset($width)) $width = 'auto';
if (!isset($iconPosition)) $iconPosition = 'left';
if (!isset($data)) $data = [];


$buttonClass = "btn btn-{$type}";
if ($class) {
    $buttonClass .= " {$class}";
}


$style = "border-radius: 30px; height: 40px; display: inline-flex; align-items: center; justify-content: center; padding: 0 20px; line-height: 1;";
if ($width !== 'auto') {
    $style .= " width: {$width};";
}


$attributes = [];
$attributes[] = "class=\"{$buttonClass}\"";
$attributes[] = "style=\"{$style}\"";

if (isset($id)) {
    $attributes[] = "id=\"{$id}\"";
}

if (isset($onclick)) {
    $attributes[] = "onclick=\"{$onclick}\"";
}


foreach ($data as $key => $value) {
    $attributes[] = "data-{$key}=\"" . e($value) . "\"";
}

if ($disabled) {
    $attributes[] = "disabled";
}

$attributesStr = implode(' ', $attributes);
$buttonText = e($text);


$content = '';
if (isset($icon)) {

    if ($iconPosition === 'left') {
        $content = $icon . '<span>' . $buttonText . '</span>';
    } else {
        $content = '<span>' . $buttonText . '</span>' . $icon;
    }
} else {
    $content = $buttonText;
}


if (isset($href)) {

    $linkAttributes = array_filter($attributes, function($attr) {
        return !str_contains($attr, 'type=') && !str_contains($attr, 'disabled');
    });
    

    if (isset($target)) {
        $linkAttributes[] = "target=\"" . e($target) . "\"";
    }
    
    $linkAttributesStr = implode(' ', $linkAttributes);
    echo "<a href=\"{$href}\" {$linkAttributesStr}>{$content}</a>";
} else {
    $attributes[] = "type=\"{$buttonType}\"";
    $attributesStr = implode(' ', $attributes);
    echo "<button {$attributesStr}>{$content}</button>";
}
?>
