<?php
/**
 * Location list view
 *
 * @package Zul\Weather
 * @var array $locations
 * @var int $total
 * @var int $totalPages
 * @var int $page
 * @var array $filters
 */

if (!defined('ABSPATH')) {
    exit;
}

use Zul\Weather\Capabilities;

$canManage = Capabilities::userCanManage();
?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e('Weather Locations', 'zul-weather'); ?></h1>

    <?php if ($canManage): ?>
        <a href="<?php echo esc_url(admin_url('admin.php?page=zul-weather-new')); ?>" class="page-title-action">
            <?php esc_html_e('Add New', 'zul-weather'); ?>
        </a>
    <?php endif; ?>

    <hr class="wp-header-end">

    <?php settings_errors('zul_weather_notices'); ?>

    <!-- Filters -->
    <form method="get" class="zul-weather-filters">
        <input type="hidden" name="page" value="zul-weather">

        <div class="tablenav top">
            <div class="alignleft actions">
                <select name="status">
                    <option value=""><?php esc_html_e('All Statuses', 'zul-weather'); ?></option>
                    <option value="active" <?php selected($filters['status'] ?? '', 'active'); ?>><?php esc_html_e('Active', 'zul-weather'); ?></option>
                    <option value="inactive" <?php selected($filters['status'] ?? '', 'inactive'); ?>><?php esc_html_e('Inactive', 'zul-weather'); ?></option>
                </select>

                <?php submit_button(__('Filter', 'zul-weather'), 'secondary', 'filter_action', false); ?>
            </div>

            <div class="alignright">
                <p class="search-box">
                    <input type="search" name="s" value="<?php echo esc_attr($filters['search'] ?? ''); ?>" placeholder="<?php esc_attr_e('Search locations...', 'zul-weather'); ?>">
                    <?php submit_button(__('Search', 'zul-weather'), 'secondary', '', false); ?>
                </p>
            </div>
        </div>
    </form>

    <!-- Location Table -->
    <table class="wp-list-table widefat fixed striped zul-weather-table">
        <thead>
            <tr>
                <th scope="col" class="manage-column column-title column-primary"><?php esc_html_e('Location', 'zul-weather'); ?></th>
                <th scope="col" class="manage-column column-coordinates"><?php esc_html_e('Coordinates', 'zul-weather'); ?></th>
                <th scope="col" class="manage-column column-status"><?php esc_html_e('Status', 'zul-weather'); ?></th>
                <th scope="col" class="manage-column column-shortcode"><?php esc_html_e('Shortcode', 'zul-weather'); ?></th>
                <th scope="col" class="manage-column column-author"><?php esc_html_e('Author', 'zul-weather'); ?></th>
                <th scope="col" class="manage-column column-date"><?php esc_html_e('Date', 'zul-weather'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($locations)): ?>
                <tr>
                    <td colspan="6"><?php esc_html_e('No locations found.', 'zul-weather'); ?></td>
                </tr>
            <?php else: ?>
                <?php foreach ($locations as $location): ?>
                    <tr>
                        <td class="column-title column-primary" data-colname="<?php esc_attr_e('Location', 'zul-weather'); ?>">
                            <strong>
                                <?php if ($canManage): ?>
                                    <a href="<?php echo esc_url($this->getEditUrl($location->getId())); ?>" class="row-title">
                                        <?php echo esc_html($location->getLocation()); ?>
                                    </a>
                                <?php else: ?>
                                    <?php echo esc_html($location->getLocation()); ?>
                                <?php endif; ?>
                            </strong>

                            <?php if ($location->getDescription()): ?>
                                <p class="description"><?php echo esc_html(wp_trim_words($location->getDescription(), 10)); ?></p>
                            <?php endif; ?>

                            <?php if ($canManage): ?>
                                <div class="row-actions">
                                    <span class="edit">
                                        <a href="<?php echo esc_url($this->getEditUrl($location->getId())); ?>">
                                            <?php esc_html_e('Edit', 'zul-weather'); ?>
                                        </a> |
                                    </span>
                                    <span class="trash">
                                        <a href="<?php echo esc_url($this->getDeleteUrl($location->getId())); ?>" class="submitdelete" onclick="return confirm('<?php esc_attr_e('Are you sure you want to delete this location?', 'zul-weather'); ?>');">
                                            <?php esc_html_e('Delete', 'zul-weather'); ?>
                                        </a>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="column-coordinates" data-colname="<?php esc_attr_e('Coordinates', 'zul-weather'); ?>">
                            <code><?php echo esc_html($location->getLatitude()); ?>, <?php echo esc_html($location->getLongitude()); ?></code>
                        </td>
                        <td class="column-status" data-colname="<?php esc_attr_e('Status', 'zul-weather'); ?>">
                            <span class="zul-status zul-status-<?php echo esc_attr($location->getStatus()->value); ?>">
                                <?php echo esc_html($location->getStatus()->label()); ?>
                            </span>
                        </td>
                        <td class="column-shortcode" data-colname="<?php esc_attr_e('Shortcode', 'zul-weather'); ?>">
                            <code>[zul_weather id="<?php echo esc_attr($location->getId()); ?>"]</code>
                        </td>
                        <td class="column-author" data-colname="<?php esc_attr_e('Author', 'zul-weather'); ?>">
                            <?php
                            $author = get_user_by('id', $location->getCreatedBy());
                            echo $author ? esc_html($author->display_name) : __('Unknown', 'zul-weather');
                            ?>
                        </td>
                        <td class="column-date" data-colname="<?php esc_attr_e('Date', 'zul-weather'); ?>">
                            <?php echo esc_html($location->getCreateDt()->format('Y/m/d')); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="tablenav bottom">
            <div class="tablenav-pages">
                <span class="displaying-num">
                    <?php printf(
                        _n('%s item', '%s items', $total, 'zul-weather'),
                        number_format_i18n($total)
                    ); ?>
                </span>
                <span class="pagination-links">
                    <?php
                    echo paginate_links([
                        'base' => add_query_arg('paged', '%#%'),
                        'format' => '',
                        'prev_text' => '&laquo;',
                        'next_text' => '&raquo;',
                        'total' => $totalPages,
                        'current' => $page,
                    ]);
                    ?>
                </span>
            </div>
        </div>
    <?php endif; ?>
</div>
