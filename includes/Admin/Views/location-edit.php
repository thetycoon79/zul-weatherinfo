<?php
/**
 * Location edit/create view
 *
 * @package Zul\Weather
 * @var \Zul\Weather\Domain\Entities\Location|null $location
 * @var array $errors
 */

if (!defined('ABSPATH')) {
    exit;
}

use Zul\Weather\Domain\ValueObjects\Status;
use Zul\Weather\Support\Nonce;

$isEdit = $location !== null;
$nonce = new Nonce('zul_weather_action');
$pageTitle = $isEdit ? __('Edit Location', 'zul-weather') : __('Add New Location', 'zul-weather');
?>
<div class="wrap">
    <h1><?php echo esc_html($pageTitle); ?></h1>

    <?php settings_errors('zul_weather_notices'); ?>

    <?php if (!empty($errors)): ?>
        <div class="notice notice-error">
            <?php foreach ($errors as $error): ?>
                <p><?php echo esc_html($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" id="zul-weather-form">
        <?php echo $nonce->field(); ?>

        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                <!-- Main Content -->
                <div id="post-body-content">
                    <div id="titlediv">
                        <div id="titlewrap">
                            <label class="screen-reader-text" for="location"><?php esc_html_e('Location Name', 'zul-weather'); ?></label>
                            <input type="text" name="location" id="location" size="30"
                                   value="<?php echo esc_attr($location?->getLocation() ?? ($_POST['location'] ?? '')); ?>"
                                   placeholder="<?php esc_attr_e('Enter location name (e.g., London, UK)', 'zul-weather'); ?>"
                                   autocomplete="off" required>
                        </div>
                    </div>

                    <div class="postbox">
                        <div class="postbox-header">
                            <h2><?php esc_html_e('Description', 'zul-weather'); ?></h2>
                        </div>
                        <div class="inside">
                            <textarea name="description" id="description" rows="3" class="large-text"
                                      placeholder="<?php esc_attr_e('Optional description for this location', 'zul-weather'); ?>"><?php
                                echo esc_textarea($location?->getDescription() ?? ($_POST['description'] ?? ''));
                            ?></textarea>
                        </div>
                    </div>

                    <div class="postbox">
                        <div class="postbox-header">
                            <h2><?php esc_html_e('Coordinates', 'zul-weather'); ?></h2>
                        </div>
                        <div class="inside">
                            <p class="description">
                                <?php esc_html_e('Enter the latitude and longitude for this location. You can find coordinates using Google Maps.', 'zul-weather'); ?>
                            </p>
                            <table class="form-table" role="presentation">
                                <tr>
                                    <th scope="row">
                                        <label for="latitude"><?php esc_html_e('Latitude', 'zul-weather'); ?></label>
                                    </th>
                                    <td>
                                        <input type="number" name="latitude" id="latitude"
                                               value="<?php echo esc_attr($location?->getLatitude() ?? ($_POST['latitude'] ?? '')); ?>"
                                               step="0.0000001" min="-90" max="90" class="regular-text" required
                                               placeholder="<?php esc_attr_e('e.g., 51.5074', 'zul-weather'); ?>">
                                        <p class="description"><?php esc_html_e('Value between -90 and 90', 'zul-weather'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="longitude"><?php esc_html_e('Longitude', 'zul-weather'); ?></label>
                                    </th>
                                    <td>
                                        <input type="number" name="longitude" id="longitude"
                                               value="<?php echo esc_attr($location?->getLongitude() ?? ($_POST['longitude'] ?? '')); ?>"
                                               step="0.0000001" min="-180" max="180" class="regular-text" required
                                               placeholder="<?php esc_attr_e('e.g., -0.1278', 'zul-weather'); ?>">
                                        <p class="description"><?php esc_html_e('Value between -180 and 180', 'zul-weather'); ?></p>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div id="postbox-container-1" class="postbox-container">
                    <!-- Publish Box -->
                    <div class="postbox">
                        <div class="postbox-header">
                            <h2><?php esc_html_e('Publish', 'zul-weather'); ?></h2>
                        </div>
                        <div class="inside">
                            <div class="submitbox">
                                <div id="minor-publishing">
                                    <div class="misc-pub-section">
                                        <label for="status"><?php esc_html_e('Status:', 'zul-weather'); ?></label>
                                        <select name="status" id="status">
                                            <?php foreach (Status::cases() as $status): ?>
                                                <option value="<?php echo esc_attr($status->value); ?>"
                                                    <?php selected($location?->getStatus()->value ?? 'active', $status->value); ?>>
                                                    <?php echo esc_html($status->label()); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <?php if ($isEdit): ?>
                                        <div class="misc-pub-section">
                                            <span><?php esc_html_e('Shortcode:', 'zul-weather'); ?></span>
                                            <code id="location-shortcode">[zul_weather id="<?php echo esc_attr($location->getId()); ?>"]</code>
                                        </div>

                                        <div class="misc-pub-section">
                                            <span><?php esc_html_e('Created:', 'zul-weather'); ?></span>
                                            <?php echo esc_html($location->getCreateDt()->format('Y-m-d H:i')); ?>
                                        </div>

                                        <?php if ($location->getModifiedDt()): ?>
                                            <div class="misc-pub-section">
                                                <span><?php esc_html_e('Modified:', 'zul-weather'); ?></span>
                                                <?php echo esc_html($location->getModifiedDt()->format('Y-m-d H:i')); ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>

                                <div id="major-publishing-actions">
                                    <?php if ($isEdit): ?>
                                        <div id="delete-action">
                                            <a href="<?php echo esc_url($this->getDeleteUrl($location->getId())); ?>"
                                               class="submitdelete deletion"
                                               onclick="return confirm('<?php esc_attr_e('Are you sure you want to delete this location?', 'zul-weather'); ?>');">
                                                <?php esc_html_e('Delete', 'zul-weather'); ?>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                    <div id="publishing-action">
                                        <input type="submit" name="submit" class="button button-primary button-large"
                                               value="<?php echo $isEdit ? esc_attr__('Update', 'zul-weather') : esc_attr__('Publish', 'zul-weather'); ?>">
                                    </div>
                                    <div class="clear"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
