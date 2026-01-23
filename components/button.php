<?php
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
