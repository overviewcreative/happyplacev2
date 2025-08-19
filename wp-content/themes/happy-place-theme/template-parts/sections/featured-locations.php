<?php
/**
 * Featured Locations Section Template Part
 * 
 * @package HappyPlaceTheme
 */
?>

<section class="py-16 md:py-24 bg-gradient-to-r from-primary-50 to-secondary-light">
    <div class="hph-container">
        <div class="text-center mb-12">
            <span class="text-tertiary font-semibold uppercase tracking-wider">
                <?php esc_html_e('Featured Locations', 'happy-place-theme'); ?>
            </span>
            <h2 class="text-3xl md:text-4xl font-bold mt-2 mb-4">
                <?php esc_html_e('Explore Popular Areas', 'happy-place-theme'); ?>
            </h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Temporary static locations until API is ready -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="aspect-w-16 aspect-h-9 bg-gray-100">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/placeholder-location.jpg" 
                         alt="Downtown" class="object-cover w-full h-full">
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-semibold mb-2">Downtown</h3>
                    <p class="text-gray-600 mb-4">Vibrant city center with modern condos and apartments</p>
                    <div class="flex items-center text-sm text-gray-500">
                        <span class="mr-4">
                            <i class="fas fa-home mr-1"></i> 45 Properties
                        </span>
                        <span>
                            <i class="fas fa-map-marker-alt mr-1"></i> Central District
                        </span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="aspect-w-16 aspect-h-9 bg-gray-100">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/placeholder-location.jpg" 
                         alt="Waterfront" class="object-cover w-full h-full">
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-semibold mb-2">Waterfront</h3>
                    <p class="text-gray-600 mb-4">Luxurious homes with stunning water views</p>
                    <div class="flex items-center text-sm text-gray-500">
                        <span class="mr-4">
                            <i class="fas fa-home mr-1"></i> 28 Properties
                        </span>
                        <span>
                            <i class="fas fa-map-marker-alt mr-1"></i> Coastal Area
                        </span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="aspect-w-16 aspect-h-9 bg-gray-100">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/placeholder-location.jpg" 
                         alt="Suburbs" class="object-cover w-full h-full">
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-semibold mb-2">Suburbs</h3>
                    <p class="text-gray-600 mb-4">Family-friendly neighborhoods with spacious homes</p>
                    <div class="flex items-center text-sm text-gray-500">
                        <span class="mr-4">
                            <i class="fas fa-home mr-1"></i> 62 Properties
                        </span>
                        <span>
                            <i class="fas fa-map-marker-alt mr-1"></i> Residential District
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
