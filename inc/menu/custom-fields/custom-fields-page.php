<div class="wrap wcp-main-wrap">
    <h2><?php _e( 'Custom Fields Builder', 'real-estate-manager' ); ?></h2>

    <?php
        $field_types = array(
            'text' => __( 'Text Field', 'real-estate-manager' ),
            'number' => __( 'Number Field', 'real-estate-manager' ),
            'select' => __( 'DropDown Field', 'real-estate-manager' ),
            'select2' => __( 'Multi Select Field', 'real-estate-manager' ),
            'date' => __( 'Date Field', 'real-estate-manager' ),
            'upload' => __( 'Multiple Files Upload', 'real-estate-manager' ),
            'video' => __( 'Video URL', 'real-estate-manager' ),
            'textarea' => __( 'Text Area', 'real-estate-manager' ),
            'shortcode' => __( 'Shortcode', 'real-estate-manager' ),
        );
        $saved_fields = get_option( 'rem_property_fields' );
    ?>
    <table style="width: 100%;">
        <tr>
            <td style="width: 20%; text-align: center;">
                <ul class="hard-coded-list">
                    <?php foreach ($field_types as $field_name => $field_label) {
                        echo '<li data-type="'.$field_name.'">';
                            if ($field_name == 'select' || $field_name == 'select2') {
                                include REM_PATH.'/inc/menu/custom-fields/fields/select.php';
                            }else {
                                include REM_PATH.'/inc/menu/custom-fields/fields/text.php';
                            }
                        echo '</li>';
                    } ?>
                </ul>
            </td>
            <td style="width: 80%;" class="form-meta-setting">
                <ul>
                    <?php
                        if(isset($saved_fields) && is_array($saved_fields)) {
                            foreach ($saved_fields as $data) {
                                echo '<li data-type="'.$data['type'].'">';
                                    $field_label = $field_types[$data['type']];
                                    if ($data['type'] == 'select' || $data['type'] == 'select2') {
                                        include REM_PATH.'/inc/menu/custom-fields/fields/select.php';
                                    } else {
                                        include REM_PATH.'/inc/menu/custom-fields/fields/text.php';
                                    }
                                echo '</li>';
                            }
                        } else {
                            include REM_PATH.'/inc/arrays/property-fields.php';
                            $fields = $inputFields;
                            foreach ($fields as $data) {
                                echo '<li data-type="'.$data['type'].'">';
                                    $field_label = $field_types[$data['type']];
                                    if ($data['type'] == 'select' || $data['type'] == 'select2') {
                                        include REM_PATH.'/inc/menu/custom-fields/fields/select.php';
                                    } else {
                                        include REM_PATH.'/inc/menu/custom-fields/fields/text.php';
                                    }
                                echo '</li>';
                            }
                        }
                    ?>
                </ul>
                <p class="description">
                    <?php _e( 'Please Drag and Drop the fields in above area from left panel', 'real-estate-manager' ); ?>
                    <br><br>
                    <?php _e( 'If Data Name contains the whole word "area", the chosen area unit will be shown after the value.', 'real-estate-manager' ); ?>
                </p>
            </td>
        </tr>
    </table>
    <div>
        <button class="button button-secondary reset-settings"><?php _e( 'Reset Fields', 'real-estate-manager' ); ?></button>
        <button class="button button-primary save-settings"><?php _e( 'Save Settings', 'real-estate-manager' ); ?></button>
    </div>
</div>
<style>
    .wcp-main-wrap .ui-accordion-content {
        border-top-color: #ddd;
        border-top: 1px solid #ddd;
        padding: 15px;
        outline: none;
    }
    .wcp-main-wrap .ui-accordion-header {
        color: #333;
        background-color: #f5f5f5;
        border-color: #ddd;
        display: block;
        padding: 6px 15px;
        border-top-left-radius: 3px;
        border-top-right-radius: 3px; 
        border-bottom: 0;
        font-size: 13px;
        cursor: move;
    }
    .form-meta-setting li {
        margin-bottom: 0;
        border-radius: 4px;
        border-color: #ddd;
        background-color: #fff;
        border: 1px solid #ddd;
        border-radius: 4px;
        -webkit-box-shadow: 0 1px 1px rgba(0,0,0,.05);
        box-shadow: 0 1px 1px rgba(0,0,0,.05);
    }

    .ui-state-highlight {
        height: 30px;
        background-color: yellow;
        width: 100%;
    }
    .ui-draggable-dragging .inside-contents , .hard-coded-list .inside-contents {
        display: none;
    }
    .form-meta-setting ul {
        max-width: 520px;
        margin: 0 auto;
        background-color: #FFFFFF;
        box-shadow: 0 10px 31px 0 rgba(7,152,255,.09);
        padding: 20px;
    }
    .hard-coded-list {
        box-shadow: 0 10px 31px 0 rgba(7,152,255,.09);
        background-color: #FFFFFF;
        padding: 20px;
        margin: 0;
    }
    .form-meta-setting p.description {
        max-width: 520px;
        margin: 0 auto;
        padding: 15px;
    }
    .ui-draggable-dragging, .ui-sortable-helper {
        max-width: 500px !important;
        width: 100% !important;
    }
    .wcp-main-wrap table tbody {
        vertical-align: top;
    }
    .hard-coded-list li span {
        cursor: move !important;
    }
    .inside-contents table tbody {
        vertical-align: middle;
    }
    .form-meta-setting ul {
        min-height: 250px;
    }
    .remove-field {
        float: right;
        margin-top: 5px;
    }
    .wcp-main-wrap .ui-icon {
         display: inline !important; 
         text-indent: 0 !important; 
    }
</style>