# WordPress

**Prefer [`eramhq/persian-kit`](https://github.com/eramhq/persian-kit)** — it's an official plugin that wires abzar into WordPress hooks, ships admin tools for database normalization, and is maintained alongside abzar.

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
register_rest_field('post', 'customer_national_id', [
    'update_callback' => function ($value, \WP_Post $post) {
        $result = \Eram\Abzar\Validation\NationalId::validate((string) $value);
        if (!$result->isValid()) {
            return new \WP_Error('invalid_nid', (string) $result, ['status' => 400]);
        }
        update_post_meta($post->ID, 'customer_national_id', $value);
        update_post_meta($post->ID, 'customer_city', $result->details()['city'] ?? null);
        return true;
    },
]);
```

## Normalizing search queries

```php
add_filter('pre_get_posts', function (\WP_Query $q): \WP_Query {
    if (!$q->is_search() || !$q->is_main_query()) {
        return $q;
    }
    $s = $q->get('s');
    if (is_string($s) && $s !== '') {
        $normalizer = new \Eram\Abzar\Text\CharNormalizer();
        $q->set('s', $normalizer->normalizeForSearch($s));
    }
    return $q;
});
```
