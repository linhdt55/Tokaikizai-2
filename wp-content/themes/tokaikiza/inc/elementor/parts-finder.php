<?php
class Elementor_Parts_Finder extends \Elementor\Widget_Base
{

    public function get_name()
    {
        return 'parts-finder';
    }

    public function get_title()
    {
        return esc_html__('Parts Finder', 'tokaikiza');
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
            $attr = 'pa_year-normal';
            $years = get_terms($attr, array(
                'hide_empty' => false,
            ));

            if (taxonomy_exists($attr)) :
            ?>
                <div class="form-control control-<?= $attr; ?>">
                    <label for="<?= $attr; ?>"><?= esc_html__('Year', 'tokaikiza') ?></label>
                    <select name="filter_year-normal" id="<?= $attr; ?>" required>
                        <option value=""><?= esc_html__('Select Year', 'tokaikiza') ?></option>
                        <?php foreach ($years as $year) : ?>
                            <option data-id="<?= $year->term_id; ?>" value="<?= $year->slug; ?>"><?= $year->name; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>
            <?php
            $attr = 'pa_brand';
            $pa_brand = get_terms($attr, array(
                'hide_empty' => false,
            ));
            if (taxonomy_exists($attr)) :
            ?>
                <div class="form-control control-<?= $attr; ?>">
                    <label for="<?= $attr; ?>"><?= esc_html__('Brand', 'tokaikiza') ?></label>
                    <select disabled name="filter_brand" id="<?= $attr; ?>" required>
                        <option value=""><?= esc_html__('Select Brand', 'tokaikiza') ?></option>
                        <?php foreach ($pa_brand as $item) : ?>
                            <option value="<?= $item->slug; ?>"><?= $item->name; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>
            <?php
            $attr = 'pa_model-normal';
            $pa_brand = get_terms($attr, array(
                'hide_empty' => false,
            ));
            if (taxonomy_exists($attr)) :
            ?>
                <div class="form-control control-<?= $attr; ?>">
                    <label for="<?= $attr; ?>"><?= esc_html__('Model', 'tokaikiza') ?></label>
                    <select disabled name="filter_model-normal" id="<?= $attr; ?>" required>
                        <option value=""><?= esc_html__('Select Model', 'tokaikiza') ?></option>
                        <?php foreach ($pa_brand as $item) : ?>
                            <option value="<?= $item->slug; ?>"><?= $item->name; ?></option>
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
            <input type="hidden" name="layout" value="full">
            <div class="form-control control-action">
                <button type="submit"><?= esc_html__('Search', 'tokaikiza') ?></button>
                <button type="reset"><?= esc_html__('Reset', 'tokaikiza') ?></button>
            </div>
        </form>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $(document).on('change', 'select#pa_year-normal', function(e) {
                    e.preventDefault();
                    var yearValue = $(this).find("option:selected").data('id');
                    if (yearValue != '') {
                        $.ajax({
                            type: "post",
                            dataType: "json",
                            url: '<?php echo admin_url('admin-ajax.php'); ?>',
                            data: {
                                action: "parts_finder_select_year_ajax",
                                year: yearValue,
                            },
                            beforeSend: function() {},
                            success: function(response) {
                                if (response.data.length) {
                                    $('select#pa_brand').html(response.data);
                                    $("select#pa_brand").attr("disabled", false);
                                    $('select#pa_model-normal').html('<option value="">Select Model</option>');
                                    $("select#pa_model-normal").attr("disabled", true);
                                } else {
                                    $("select#pa_brand").attr("disabled", true);
                                    $("select#pa_model-normal").attr("disabled", true);
                                }
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                console.log('The following error occured');
                            }
                        });
                    }
                });
                $(document).on('change', 'select#pa_brand', function(e) {
                    e.preventDefault();
                    var brandValue = $(this).find("option:selected").data('id');
                    var yearValue = $('select#pa_year-normal').val();
                    if (brandValue != '') {
                        $.ajax({
                            type: "post",
                            dataType: "json",
                            url: '<?php echo admin_url('admin-ajax.php'); ?>',
                            data: {
                                action: "parts_finder_select_ajax",
                                brand: brandValue,
                                year: yearValue,
                            },
                            beforeSend: function() {},
                            success: function(response) {
                                if (response.data.length) {
                                    $('select#pa_model-normal').html(response.data);
                                    $("select#pa_model-normal").attr("disabled", false);
                                } else {
                                    $("select#pa_model-normal").attr("disabled", true);
                                }
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                console.log('The following error occured');
                            }
                        });
                    }
                });
            });
        </script>
<?php
    }
}
