<?php global $lana_security_settings; ?>

<div class="wrap" id="lana-security-dashboard">
    <h2>
		<?php _e( 'Lana Security - Dashboard', 'lana-security' ); ?>
    </h2>
    <br/>

	<?php if ( ! empty( $lana_security_settings ) ): ?>
		<?php foreach ( $lana_security_settings as $setting_id => $lana_security_setting ): ?>
            <div
                    class="lana-security <?php echo esc_attr( $lana_security_setting['option'] ? 'enabled' : 'disabled' ); ?>">
                <div class="content-box">
                    <h3><?php echo $lana_security_setting['label']; ?></h3>

                    <p class="description">
						<?php echo $lana_security_setting['description']; ?>
                    </p>

					<?php if ( $lana_security_setting['option'] == true ): ?>
                        <p class="option">
							<?php _e( 'Enabled', 'lana-security' ); ?>
                        </p>
					<?php endif; ?>

					<?php if ( $lana_security_setting['option'] == false ): ?>
                        <p class="option">
							<?php _e( 'Disabled', 'lana-security' ); ?>
                        </p>
					<?php endif; ?>
                </div>
            </div>
		<?php endforeach; ?>
	<?php endif; ?>
</div>
