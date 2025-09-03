# Agent Photo Field Update

## Overview
Updated all agent-related templates to use the `profile_photo` ACF field instead of the WordPress featured image for displaying agent photos.

## Files Modified

### 1. template-parts/agent-card.php
- **Line ~45**: Updated photo retrieval logic
- **Change**: Now uses `get_field('profile_photo', $agent_id)` instead of `get_the_post_thumbnail_url()`

### 2. template-parts/agent-card-list.php  
- **Line ~45**: Updated photo retrieval logic
- **Change**: Now uses `get_field('profile_photo', $agent_id)` instead of `get_the_post_thumbnail_url()`

### 3. single-agent.php
- **Line ~67**: Updated photo retrieval logic
- **Change**: Now uses `get_field('profile_photo', $agent_id)` instead of `get_the_post_thumbnail_url()`

## Implementation Details

### Photo Field Logic
All templates now follow this hierarchy:

1. **Primary**: `profile_photo` ACF field
   - Handles both image array format and attachment ID
   - Uses appropriate image size (medium for cards, large for single page)

2. **Fallback**: WordPress featured image
   - Maintains backward compatibility
   - Uses if profile_photo field is not set

3. **Final Fallback**: Placeholder image
   - Uses theme placeholder when no image is available
   - Path: `/assets/images/placeholder-agent.jpg`

### ACF Field Expected Format
The `profile_photo` field should be configured as:
- **Field Type**: Image
- **Return Format**: Image Array (preferred) or Image ID
- **Preview Size**: Medium
- **Library**: All

### Image Sizes Used
- **Agent Cards**: `medium` (300x300px typically)
- **Single Agent**: `large` (1024x1024px typically)

## Benefits

1. **Dedicated Profile Photos**: Agents can have specific profile photos separate from featured images
2. **Better Image Management**: Profile photos can be optimized specifically for agent display
3. **Fallback Support**: Maintains compatibility with existing data using featured images
4. **Consistent Sizing**: Uses appropriate image sizes for different contexts

## Migration Notes

### For Existing Sites
- Existing agents using featured images will continue to work
- Profile photos can be added gradually
- No data loss or breaking changes

### For New Agents
- Set the `profile_photo` ACF field for best results
- Featured image is optional and can be used for other purposes
- Placeholder will show if neither image is set

## Testing Checklist

- [ ] Agent cards display profile photos correctly
- [ ] Agent list cards display profile photos correctly  
- [ ] Single agent page displays profile photo correctly
- [ ] Fallback to featured image works when profile_photo is not set
- [ ] Placeholder image displays when no images are available
- [ ] Images are properly sized for each context
- [ ] No broken image links or 404 errors

## Future Enhancements

Consider adding:
- Image optimization for agent photos
- Multiple photo support (gallery)
- Image crop/focus point controls
- Automatic image compression
- WebP format support
