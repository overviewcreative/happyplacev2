<?php
/**
 * Comments Section Template Part
 * 
 * @package Happy_Place_Theme
 */

$background = $args['background'] ?? 'light';
$padding = $args['padding'] ?? 'xl';
$content_width = $args['content_width'] ?? 'narrow';
$section_id = $args['section_id'] ?? '';

$section_classes = [
    'hph-section',
    'hph-comments-section',
    'hph-bg-' . $background,
    'hph-py-' . $padding
];
?>

<section <?php if ($section_id): ?>id="<?php echo esc_attr($section_id); ?>"<?php endif; ?> 
         class="<?php echo esc_attr(implode(' ', $section_classes)); ?>">
    <div class="hph-container">
        <div class="hph-content-width-<?php echo esc_attr($content_width); ?> hph-mx-auto">
            
            <div class="hph-comments-header hph-text-center hph-mb-xl">
                <h3 class="hph-text-2xl hph-font-bold hph-mb-md hph-text-gray-900">
                    <?php
                    $comment_count = get_comments_number();
                    if ($comment_count == 0) {
                        echo 'Join the Conversation';
                    } else {
                        printf(
                            _n('1 Comment', '%s Comments', $comment_count, 'happy-place-theme'),
                            number_format_i18n($comment_count)
                        );
                    }
                    ?>
                </h3>
                
                <?php if ($comment_count == 0): ?>
                    <p class="hph-text-gray-600">
                        Be the first to share your thoughts on this article.
                    </p>
                <?php endif; ?>
            </div>
            
            <div class="hph-comments-wrapper hph-bg-white hph-p-xl hph-rounded-lg hph-shadow-sm">
                <?php comments_template(); ?>
            </div>
            
        </div>
    </div>
</section>

<style>
.hph-comments-section .comment-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.hph-comments-section .comment {
    border-bottom: 1px solid #e5e7eb;
    padding: 1.5rem 0;
}

.hph-comments-section .comment:last-child {
    border-bottom: none;
}

.hph-comments-section .comment-author {
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
}

.hph-comments-section .comment-meta {
    font-size: 0.875rem;
    color: #6b7280;
    margin-bottom: 1rem;
}

.hph-comments-section .comment-content {
    color: #4b5563;
    line-height: 1.6;
}

.hph-comments-section .comment-reply-link {
    font-size: 0.875rem;
    color: var(--hph-primary);
    text-decoration: none;
    font-weight: 500;
}

.hph-comments-section .comment-reply-link:hover {
    color: var(--hph-primary-dark);
}

.hph-comments-section .comment-form {
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid #e5e7eb;
}

.hph-comments-section .comment-form-comment textarea {
    width: 100%;
    min-height: 120px;
    padding: 0.75rem;
    border: 2px solid #e5e7eb;
    border-radius: 0.5rem;
    font-family: inherit;
    resize: vertical;
}

.hph-comments-section .comment-form-comment textarea:focus {
    outline: none;
    border-color: var(--hph-primary);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.hph-comments-section .form-submit input[type="submit"] {
    background: var(--hph-primary);
    color: white;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 0.5rem;
    font-weight: 500;
    cursor: pointer;
    transition: background-color 0.2s;
}

.hph-comments-section .form-submit input[type="submit"]:hover {
    background: var(--hph-primary-dark);
}
</style>