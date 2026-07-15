<?php

declare(strict_types=1);

namespace Ferraro\Schema\Parser;

/**
 * Class RelationshipParser
 * * Safely resolves ACF Relationship fields into structured arrays of post IDs or WP_Post objects,
 * * handling raw data inputs, numeric arrays, and string formats.
 */
final class RelationshipParser
{
    /**
     * Safely parses relational values from ACF into a structured array of clean WP_Post objects.
     *
     * @param mixed $fieldValue Raw field output from get_field()
     * @return array<\WP_Post>
     */
    public function parseRelationships(mixed $fieldValue): array
    {
        if (empty($fieldValue)) {
            return [];
        }

        $posts = [];
        $items = is_array($fieldValue) ? $fieldValue : [$fieldValue];

        foreach ($items as $item) {
            if ($item instanceof \WP_Post) {
                $posts[] = $item;
            } elseif (is_numeric($item)) {
                $resolvedPost = get_post((int) $item);
                if ($resolvedPost instanceof \WP_Post) {
                    $posts[] = $resolvedPost;
                }
            } elseif (is_string($item)) {
                // If the field returned a slug instead of ID/Post
                $resolvedPosts = get_posts([
                    'name' => $item,
                    'post_type' => 'any',
                    'posts_per_page' => 1,
                    'suppress_filters' => true,
                ]);
                if (!empty($resolvedPosts)) {
                    $posts[] = $resolvedPosts[0];
                }
            }
        }

        return $posts;
    }
}