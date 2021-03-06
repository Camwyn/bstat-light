<?php
class bStat
{
	public  $id_base = 'bstat';
	public  $version = 5;

	public function __construct()
	{
		add_action( 'init', array( $this, 'init' ), 1 );
	} // END __construct

	public function init()
	{
		add_action( 'template_redirect', array( $this, 'template_redirect' ), 15 );

		wp_register_script( $this->id_base, plugins_url( plugin_basename( __DIR__ ) ) . '/js/bstat.js', array( 'jquery' ), $this->version, TRUE );
		wp_enqueue_script( $this->id_base );
	} // END init

	public function options()
	{
		if ( ! $this->options )
		{
			$this->options = (object) apply_filters(
				'go_config',
				array(
					'endpoint' => admin_url( '/admin-ajax.php?action=' . $this->id_base ),
					'secret' => $this->version,
				),
				$this->id_base
			);
		}

		return $this->options;
	} // END options

	public function template_redirect()
	{
		wp_localize_script( $this->id_base, $this->id_base, $this->wp_localize_script() );
	} // END template_redirect

	public function wp_localize_script()
	{
		$details = array(
			'post'       => is_singular() ? get_queried_object_id() : FALSE, // this is either an int or BOOL
			'blog'       => (int) $this->get_blog(),
			'endpoint'   => esc_js( $this->options()->endpoint ),
		);
		$details['signature'] = $this->get_signature( $details );

		return $details;
	}

	public function get_signature( $details )
	{
		return md5( (int) $details['post'] . (int) $details['blog'] . (string) $this->options()->secret );
	}

	public function get_blog()
	{
		global $wpdb;
		return isset( $wpdb->blogid ) ? $wpdb->blogid : 1;
	}

}

function bstat()
{
	global $bstat;

	if ( ! $bstat )
	{
		$bstat = new bStat;
	}

	return $bstat;
} // end bstat
