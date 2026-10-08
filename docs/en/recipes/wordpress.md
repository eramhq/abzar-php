---
title: "WordPress integration"
description: "Use content and slug hooks, REST field updates and query normalization."
---

# WordPress

These examples belong in your own plugin after its Composer autoloader is loaded. Abzar does not ship WordPress hooks. See [related projects](../related.md) for the separate persian-kit project.

If you have a reason to integrate abzar manually inside your own plugin or theme, a few patterns:

## Slug filter

```php
add_filter('sanitize_title', function (string $title, string $raw_title, string $context): string {
    if ($context !== 'save') {
        return $title;
    }

    return \Eram\Abzar\Text\Slug::generate($raw_title);
}, 10, 3);
```

## Content digit conversion

```php
add_filter('the_content', function (string $content): string {
    return \Eram\Abzar\Digits\DigitConverter::convertContent($content);
}, 20);
```

The HTML-aware `convertContent` leaves `<script>`, `<style>`, tags, attributes, and comments untouched.

## Gutenberg REST validation

```php
add_action('rest_api_init', function (): void {
    register_rest_field('post', 'customer_national_id', [
        'update_callback' => function ($value, \WP_Post $post) {
            if (!is_string($value)) {
                return new \WP_Error('invalid_nid', 'National ID must be a string', ['status' => 400]);
            }
            $ni = \Eram\Abzar\Validation\NationalId::tryFrom($value);
            if ($ni === null) {
                return new \WP_Error('invalid_nid', 'Invalid national ID', ['status' => 400]);
            }
            update_post_meta($post->ID, 'customer_national_id', $ni->value());
            update_post_meta($post->ID, 'customer_city', $ni->city());
            return true;
        },
    ]);
});
```

## Normalizing search queries

```php
add_action('pre_get_posts', function (\WP_Query $q): void {
    if (!$q->is_search() || !$q->is_main_query()) {
        return;
    }
    $s = $q->get('s');
    if (is_string($s) && $s !== '') {
        $normalizer = new \Eram\Abzar\Text\CharNormalizer();
        $q->set('s', $normalizer->normalizeForSearch($s));
    }
});
```

## Result and limitations

With the filters registered in a running WordPress application:

```php
echo apply_filters('sanitize_title', '', 'سلام دنیا', 'save'), "\n";
echo \Eram\Abzar\Digits\DigitConverter::convertContent('<p>Item 5</p>'), "\n";
```

```text
سلام-دنیا
<p>Item ۵</p>
```

The first output assumes no later filter changes the slug. Other `the_content` filters can also affect final HTML. Register REST fields on `rest_api_init`; the minimal update callback relies on the existing post endpoint's access checks and does not expose a read callback or schema. Adapt it to your data access policy before use.

Normalizing only the query does not normalize existing posts or build a search index. Apply matching rules to the data you search and test your database's collation. HTML conversion is not sanitization, and slug generation does not guarantee uniqueness.

Framework references: [REST fields](https://developer.wordpress.org/reference/functions/register_rest_field/) and [pre_get_posts](https://developer.wordpress.org/reference/hooks/pre_get_posts/).

Related: [integration overview](../framework-integration.md), [Persian text](../persian-text.md), [digits](../digits.md).
