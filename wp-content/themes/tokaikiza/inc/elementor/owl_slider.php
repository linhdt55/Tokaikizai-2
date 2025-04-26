<?php

//namespace Elementor;

class Elementor_Widget_Owl_Slider extends \Elementor\Widget_Base
{

	public function get_name()
	{
		return 'owl_slider';
	}

	public function get_title()
	{
		return __('Slider With Thumbnail', 'tokaikizai');
	}

	public function get_icon()
	{
		return 'eicon-slides';
	}

	public function get_categories()
	{
		return ['basic'];
	}

	protected function register_controls()
	{

		$this->start_controls_section(
			'content_section',
			[
				'label' => __('Content', 'tokaikizai'),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$repeater = new \Elementor\Repeater();

		$repeater->add_control(
			'image',
			[
				'label' => __('Image', 'tokaikizai'),
				'type' => \Elementor\Controls_Manager::MEDIA,
				'default' => [
					'url' => \Elementor\Utils::get_placeholder_image_src(),
				],
			]
		);

		$repeater->add_control(
			'link',
			[
				'label' => __('Link', 'tokaikizai'),
				'type' => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
			]
		);

		$repeater->add_control(
			'title_text',
			[
				'label' => __('Title', 'tokaikizai'),
				'type' => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
			]
		);
		$this->add_control(
			'list',
			[
				'label' => __('List', 'tokaikizai'),
				'type' => \Elementor\Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'default' => [
					[
						'title_text' => __('Title #1', 'tokaikizai'),
					]
				],
				'title_field' => '{{{ title_text }}}',
			]
		);

		$this->end_controls_section();
	}

	protected function render()
	{
		$settings = $this->get_settings_for_display();

		if ($settings['list']) {
?>
			<div class="banner_slider owl-carousel owl-theme">
				<?php
				foreach ($settings['list'] as $item) { ?>
					<div class="item">
						<a href="<?php if ($item['link']) {
										echo $item['link'];
									} ?>">
							<div class="box">
								<?php if ($item['image']) : ?>
									<img src="<?php echo $item['image']['url'] ?>" alt="<?php echo $item['image']['alt'] ?>" width="100%" height="100%" />
								<?php endif; ?>
							</div>
						</a>
					</div>
				<?php }
				?>
			</div>
			<div class="navigation-thumbs owl-carousel owl-theme">
				<?php
				foreach ($settings['list'] as $item) { ?>
					<div class="item">
						<a href="<?php if ($item['link']) {
										echo $item['link'];
									} ?>">
							<div class="box">
								<?php if ($item['image']) : ?>
									<img src="<?php echo $item['image']['url'] ?>" alt="<?php echo $item['image']['alt'] ?>" width="100%" height="100%" />
								<?php endif; ?>
							</div>
						</a>
					</div>
				<?php }
				?>
			</div>
			<style>
				.box {
					max-width: 944px;
					width: 100%;
				}
			</style>
<?php
		}
	}

	/*protected function _content_template() {
		?>
		<# if ( settings.list.length ) { #>
		<div class = "list row wrap">
			<# _.each( settings.list, function( item ) { #>
				<div class = "item col col-50">
					<# if ( item.list_title.length ) { #>
						<h4 class = "title">{{{ item.list_title }}}</h4>
					<# } #>
					<# if ( item.list_content.length ) { #>
						<div class = "content">{{{ item.list_content }}}</div>
					<# } #>
				</div>
			<# }); #>
		</div>
		<# } #>
		<?php
	}*/
}
