<?php
class Elementor_Hitch_Member extends \Elementor\Widget_Base
{

    public function get_name()
    {
        return 'hitch-member';
    }

    public function get_title()
    {
        return esc_html__('Hitch Member', 'tokaikiza');
    }

    public function get_icon()
    {
        return 'eicon-form-horizontal';
    }

    public function get_categories()
    {
        return ['basic'];
    }

    public function get_keywords()
    {
        return ['finder', 'form'];
    }

    protected function render()
    {
?>
        <form action="<?php echo get_permalink(wc_get_page_id('shop'));  ?>" method="get" id="parts-finder" class="parts-finder">
            <?php
            $attr1 = 'pa_hitch-manufacturer';
            $manufa = get_terms($attr1, array(
                'hide_empty' => false,
            ));

            if (taxonomy_exists($attr1)) :
            ?>
                <div class="form-control item-field control-<?= $attr1; ?>">
                    <label for="<?= $attr1; ?>"><?= esc_html__('メーカー', 'tokaikiza') ?></label>
                    <select name="wpf_<?= $attr1; ?>" id="<?= $attr1; ?>" required>
                        <option value=""><?= esc_html__('Select option', 'tokaikiza') ?></option>
                        <?php foreach ($manufa as $manu) : ?>
                            <option data-id="<?= $manu->term_id; ?>" value="<?= urldecode($manu->slug); ?>"><?= $manu->name; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>
            <?php
            $attr2 = 'pa_car-model';
            $pa_car_model = get_terms($attr2, array(
                'hide_empty' => false,
            ));
            if (taxonomy_exists($attr2)) :
            ?>
                <div class="form-control item-field control-<?= $attr2; ?>">
                    <label for="<?= $attr2; ?>"><?= esc_html__('車種', 'tokaikiza') ?></label>
                    <select disabled name="wpf_<?= $attr2; ?>" id="<?= $attr2; ?>" required>
                        <option value=""><?= esc_html__('Select option', 'tokaikiza') ?></option>
                        <?php foreach ($pa_car_model as $item) : ?>
                            <option data-id="<?= $item->term_id; ?>" value="<?= urldecode($item->slug); ?>"><?= $item->name; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>
            <?php
            $attr = 'pa_applicable-model';
            $pa_applicable = get_terms($attr, array(
                'hide_empty' => false,
            ));
            if (taxonomy_exists($attr)) :
            ?>
                <div class="form-control item-field control-<?= $attr; ?>">
                    <label for="<?= $attr; ?>"><?= esc_html__('適合型式', 'tokaikiza') ?></label>
                    <select disabled name="wpf_<?= $attr; ?>" id="<?= $attr; ?>" required>
                        <option value=""><?= esc_html__('Select option', 'tokaikiza') ?></option>
                        <?php foreach ($pa_applicable as $item) : ?>
                            <option value="<?= urldecode($item->slug); ?>"><?= $item->name; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>
            <?php
            $oem_parts = get_terms(array(
                'taxonomy' => 'product_cat',
                'hide_empty' => false,
                'meta_query' => array(
                    array(
                        'key'       => 'category_type',
                        'value'     => 'oem_parts',
                        'compare'   => 'LIKE'
                    )
                ),
            ));
            foreach ($oem_parts as $item) {
                $arrayoem_parts[$item->term_id] = $item->slug;
            };
            // echo '<input type="hidden" value="' . implode(",", array_keys($arrayoem_parts)) . '"/ name="exclude_type">';
            ?>
            <input type="hidden" name="wpf" value="category">
            <div class="form-control control-action">
                <button type="submit"><?= esc_html__('Search', 'tokaikiza') ?></button>
                <button type="reset"><?= esc_html__('Reset', 'tokaikiza') ?></button>
            </div>
        </form>
		<script type="text/javascript">
			jQuery(document).ready(function($){
				$(document).on('change','select#pa_hitch-manufacturer',function(e){
					e.preventDefault();
					var manufaValue = $(this).find("option:selected").data('id');
					if(manufaValue !=''){
						$.ajax({
						   type : "post",
						   dataType : "json",
						   url : '<?php echo admin_url('admin-ajax.php'); ?>',
						   data : {
								action: "hitch_select_manufacturer_ajax",
								manufa: manufaValue,
						   },
						   beforeSend: function(){
						   },
						   success: function(response) {
								if(response.data.length){
									$('select#pa_car-model').html(response.data);
									$("select#pa_car-model").attr("disabled", false);
									$('select#pa_applicable-model').html('<option value="">Select option</option>');
									$("select#pa_applicable-model").attr("disabled", true);
								}else{
									$('select#pa_car-model').html('<option value="">Select option</option>');
									$("select#pa_car-model").attr("disabled", true);
									$("select#pa_applicable-model").attr("disabled", true);
								}
						   },
						   error: function( jqXHR, textStatus, errorThrown ){
								console.log( 'The following error occured' );
						   }
					   });
					}
				});
				$(document).on('change','select#pa_car-model',function(e){
					e.preventDefault();
					var carmodelValue = $(this).find("option:selected").data('id');
					var manufaValue = $('select#pa_hitch-manufacturer').find("option:selected").data('id');
					if(carmodelValue !=''){
						$.ajax({
						   type : "post",
						   dataType : "json",
						   url : '<?php echo admin_url('admin-ajax.php'); ?>',
						   data : {
								action: "hitch_select_carmodel_ajax",
								carmodel: carmodelValue,
								manufa: manufaValue,
						   },
						   beforeSend: function(){
						   },
						   success: function(response) {
								if(response.data.length){
									$('select#pa_applicable-model').html(response.data);
									$("select#pa_applicable-model").attr("disabled", false);
								}else{
									$("select#pa_applicable-model").attr("disabled", true);
								}
						   },
						   error: function( jqXHR, textStatus, errorThrown ){
								console.log( 'The following error occured' );
						   }
					   });
					}
				});
			});
		</script>
<?php
    }
}
