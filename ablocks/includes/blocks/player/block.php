<?php
namespace ABlocks\Blocks\Player;

use ABlocks\Classes\BlockBaseAbstract;
use ABlocks\Classes\CssGeneratorV2;
use ABlocks\Controls\CssFilter;

class Block extends BlockBaseAbstract {
	protected $block_name = 'player';
	protected $style_depends = [ 'ablocks-plyr-style' ];
	protected $script_depends = [ 'ablocks-plyr-script' ];

	public function build_css( $attributes ) {

		// Generate CSS start
		$css_generator = new CssGeneratorV2( $attributes, $this->block_name );

		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block-youtube-container > .plyr--video > .plyr__controls',
			$this->get_youtube_progress_style_css( $attributes ),
			$this->get_youtube_progress_style_css( $attributes, 'Tablet' ),
			$this->get_youtube_progress_style_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block-youtube-container > .plyr--youtube > .plyr__control--overlaid',
			$this->get_youtube_play_pause__button_style_css( $attributes ),
			$this->get_youtube_play_pause__button_style_css( $attributes, 'Tablet' ),
			$this->get_youtube_play_pause__button_style_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block-selfhosted-container > .plyr--video  > .plyr__controls',
			$this->get_self_video_style_css( $attributes ),
			$this->get_self_video_style_css( $attributes, 'Tablet' ),
			$this->get_self_video_style_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block-selfhosted-container > .plyr--html5 > .plyr__control--overlaid',
			$this->get_self_host_control_style_css( $attributes ),
			$this->get_self_host_control_style_css( $attributes, 'Tablet' ),
			$this->get_self_host_control_style_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block-vimeo-container > .plyr--video  > .plyr__controls',
			$this->get_vimeo_video_style_css( $attributes ),
			$this->get_vimeo_video_style_css( $attributes, 'Tablet' ),
			$this->get_vimeo_video_style_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .ablocks-block-vimeo-container > .plyr--vimeo > .plyr__control--overlaid',
			$this->get_vimeo_player_control_style_css( $attributes ),
			$this->get_vimeo_player_control_style_css( $attributes, 'Tablet' ),
			$this->get_vimeo_player_control_style_css( $attributes, 'Mobile' )
		);
		$css_generator->add_class_styles(
			'{{WRAPPER}} .plyr__poster',
			$this->get_poster_image( $attributes ),
			$this->get_poster_image( $attributes, 'Tablet' ),
			$this->get_poster_image( $attributes, 'Mobile' )
		);

		return $css_generator->generate_css();
		// Generate CSS end
	}
	public function get_youtube_progress_style_css( $attributes, $device = '' ) {
		$css = [];

		if ( isset( $attributes['youtubeProgressColor'] ) ) {
			$css['--plyr-color-main'] = $attributes['youtubeProgressColor'];
		}

		if ( isset( $attributes['youtubeIconSize'] ) ) {
			$css['--plyr-control-icon-size'] = $attributes['youtubeIconSize'] . 'px';
		}
		if ( isset( $attributes['playerbgColor'] ) ) {
			$css['--plyr-video-controls-background'] = $attributes['playerbgColor'];
		}
		$css['padding'] = '0';
		return $css;
	}
	public function get_youtube_play_pause__button_style_css( $attributes, $device = '' ) {
		$css = [];

		if ( isset( $attributes['youtubeUiColor'] ) ) {
			$css['--plyr-color-main'] = $attributes['youtubeUiColor'];
		}

		if ( isset( $attributes['youtubePlayPauseIconSize'] ) ) {
			$css['--plyr-control-icon-size'] = $attributes['youtubePlayPauseIconSize'] . 'px';
		}

		return $css;
	}
	public function get_self_video_style_css( $attributes, $device = '' ) {
		$css = [];

		if ( isset( $attributes['selfVideoProgressUiColor'] ) ) {
			$css['--plyr-color-main'] = $attributes['selfVideoProgressUiColor'];
		}

		if ( isset( $attributes['selfHostedPlayIconSize'] ) ) {
			$css['--plyr-control-icon-size'] = $attributes['selfHostedPlayIconSize'] . 'px';
		}
		if ( isset( $attributes['selfVideobgColor'] ) ) {
			$css['--plyr-video-controls-background'] = $attributes['selfVideobgColor'];
		}
		$css['padding'] = '0';

		return $css;
	}
	public function get_self_host_control_style_css( $attributes, $device = '' ) {
		$css = [];

		if ( isset( $attributes['selfVideoUiColor'] ) ) {
			$css['--plyr-color-main'] = $attributes['selfVideoUiColor'];
		}

		if ( isset( $attributes['selfHostedIconSize'] ) ) {
			$css['--plyr-control-icon-size'] = $attributes['selfHostedIconSize'] . 'px';
		}

		return $css;
	}
	public function get_vimeo_video_style_css( $attributes, $device = '' ) {
		$css = [];

		if ( isset( $attributes['vimeoProgressUiColor'] ) ) {
			$css['--plyr-color-main'] = $attributes['vimeoProgressUiColor'];
		}

		if ( isset( $attributes['vimeoPlayIconSize'] ) ) {
			$css['--plyr-control-icon-size'] = $attributes['vimeoPlayIconSize'] . 'px';
		}
		if ( isset( $attributes['vimeobgColor'] ) ) {
			$css['--plyr-video-controls-background'] = $attributes['vimeobgColor'];
		}
		$css['padding'] = '0';

		return $css;
	}
	public function get_vimeo_player_control_style_css( $attributes, $device = '' ) {
		$css = [];

		if ( isset( $attributes['vimeoUiColor'] ) ) {
			$css['--plyr-color-main'] = $attributes['vimeoUiColor'];
		}

		if ( isset( $attributes['vimeoIconSize'] ) ) {
			$css['--plyr-control-icon-size'] = $attributes['vimeoIconSize'] . 'px';
		}

		return $css;
	}
	public function get_poster_image( $attributes, $device = '' ) {
		$css = [];

		if ( ! empty( $attributes['posterImage'] ) ) {
			$css['background-image'] = 'url(' . esc_url( $attributes['posterImage'] ) . ') !important';
		}

		$css['background-size'] = 'cover';

		return $css;
	}
}
