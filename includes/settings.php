<?php
function wpawss3_options_page() {
?>
<div class="col-md-6">
    <h2>WP AWS S3 Settings</h2><hr/>
    <form method="post" action="options.php">
        <?php settings_fields( 'wpawss3_options_group' ); ?>
        <h3>Database Settings</h3><hr/>
        <table class="col-md-10">
            <tr valign="top" class="col-md-12">
                <th scope="row"><label for="wpawss3_db_name">Database Name</label></th>
                <td><input type="text" id="wpawss3_db_name" name="wpawss3_db_name" class="col-md-10" value="<?php echo get_option('wpawss3_db_name'); ?>" /></td>
            </tr>
            <tr valign="top" class="col-md-12">
                <th scope="row"><label for="wpawss3_host">Host</label></th>
                <td><input type="text" id="wpawss3_host" name="wpawss3_host" class="col-md-10" value="<?php echo get_option('wpawss3_host'); ?>" /></td>
            </tr>
            <tr valign="top" class="col-md-12">
                <th scope="row"><label for="wpawss3_username">Username</label></th>
                <td><input type="text" id="wpawss3_username" name="wpawss3_username" class="col-md-10" value="<?php echo get_option('wpawss3_username'); ?>" /></td>
            </tr>
            <tr valign="top" class="col-md-12">
                <th scope="row"><label for="wpawss3_password">Password</label></th>
                <td><input type="password" id="wpawss3_password" name="wpawss3_password" class="col-md-10" value="<?php echo get_option('wpawss3_password'); ?>" /></td>
            </tr>
        </table>

        <hr/><h3>AWS Settings</h3><hr/>
        <table class="col-md-10">
            <tr valign="top" class="col-md-12">
                <th scope="row"><label for="wpawss3_aws_key">AWS Key</label></th>
                <td><input type="text" id="wpawss3_aws_key" name="wpawss3_aws_key" class="col-md-10" value="<?php echo get_option('wpawss3_aws_key'); ?>" /></td>
            </tr>
            <tr valign="top" class="col-md-12">
                <th scope="row"><label for="wpawss3_aws_secret_key">AWS Secret Key</label></th>
                <td><input type="text" id="wpawss3_aws_secret_key" name="wpawss3_aws_secret_key" class="col-md-10" value="<?php echo get_option('wpawss3_aws_secret_key'); ?>" /></td>
            </tr>
            <tr valign="top" class="col-md-12">
                <th scope="row"><label for="wpawss3_aws_region">AWS Region</label></th>
                <td><input type="text" id="wpawss3_aws_region" name="wpawss3_aws_region" class="col-md-10" value="<?php echo get_option('wpawss3_aws_region'); ?>" /></td>
            </tr>
            <tr valign="top" class="col-md-12">
                <th scope="row"><label for="wpawss3_aws_version">AWS Version</label></th>
                <td><input type="text" id="wpawss3_aws_version" name="wpawss3_aws_version" class="col-md-10" value="<?php echo get_option('wpawss3_aws_version'); ?>" /></td>
            </tr>
            <tr valign="top" class="col-md-12">
                <th scope="row"><label for="wpawss3_s3_bucket">AWS S3 Bucket</label></th>
                <td><input type="text" id="wpawss3_s3_bucket" name="wpawss3_s3_bucket" class="col-md-10" value="<?php echo get_option('wpawss3_s3_bucket'); ?>" /></td>
            </tr>
            <tr valign="top" class="col-md-12">
                <th scope="row"><label for="wpawss3_identity_pool_id">AWS Identity Pool ID</label></th>
                <td><input type="text" id="wpawss3_identity_pool_id" name="wpawss3_identity_pool_id" class="col-md-10" value="<?php echo get_option('wpawss3_identity_pool_id'); ?>" /></td>
            </tr>
            <tr valign="top" class="col-md-12">
                <th scope="row"><label for="wpawss3_s3_page_link">Redirect to</label></th>
                <td><input type="text" id="wpawss3_s3_page_link" name="wpawss3_s3_page_link" class="col-md-10" value="<?php echo get_option('wpawss3_s3_page_link'); ?>" /></td>
            </tr>
        </table>
        <?php  submit_button(); ?>
    </form>
</div>
<?php
}